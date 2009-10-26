<?php

class CustomToolButton {
	
	var $handler;
	var $text;
	var $icon;
	
	function CustomToolButton() {
		
	}
	
}

class MY_Controller extends Controller {
	
	var $persistent;
	var $collection;
	var $presentation;
	var $renderer;
	var $id;	//	internal - must be unique
	var $name;
	
	var $customFilter;	//	if set adds a condition(s) to query in collection class
	var $customOrdering;//  if set adds a custom ordering to query in collection class
	var $customClear;	//	if set calls user load function to prepare additional data needed for custom UI components (called when `New` button is clicked)
	var $customLoad;	//	if set calls user load function to load additional data needed for custom UI components
	var $customSave;	//	if set calls user save function to save additional data from custom UI components
	
	var $customToolButtons;	//	array of custom toolbar buttons array("icon", "link");
	
	function addPersistent($persistent) {
		$this->id = 'c_'.rand(1000,9999);
		unset($this->persistent);
		$this->persistent =& $persistent;
		unset($this->collection->persistent);
		$this->collection->persistent = &$persistent;
		unset($this->presentation->persistent);
		$this->presentation->persistent = &$persistent;
		$this->presentation->init();
	}
	
	function addToolButton($toolButton) {
		array_push($this->customToolButtons, $toolButton);
	}
	
	function clear() {
		$this->persistent->clearTempRelations();

		if ($this->customClear != "") {
			$result = call_user_func(array(&$this, $this->customClear));
			if (!$result) {
				echo "{success: false, errors: { reason: '".$result."' }}";
				return false;
			}
		}	
	}
	
	function delete() {
		if ($this->input->post('relation') != "") {
			foreach ($this->persistent->relations as $relation) {
				if ($relation['name'] == $this->input->post('relation')) {
					$this->addPersistent($this->persistent->getRelation($relation['name']));
				}
			}
		}
		unset($this->persistent->id);
		$this->persistent->id = $this->input->post('Itemid');
		$this->persistent->delete();
		echo "{success: true}";
	}
	
	function lookup_json_list() {
		
		if ($this->input->post('relationParent') != "") {
			foreach ($this->persistent->relations as $relation) {
				if ($relation['name'] == $this->input->post('relationParent')) {
					$this->addPersistent($this->persistent->getRelation($relation['name']));
				}
			}
		}
		
		$relationName = $this->input->post('relation');		
		
		foreach ($this->persistent->relations as $relation) {
			
			if ($relation['name'] == $relationName) {
				
				$this->load->model($relation['className'], "relationObject");
				$relationObject = $this->relationObject;
				
				unset($this->collection->presentation);
				
				$this->collection->persistent =& $relationObject;
				if ($this->input->post('start')) {
					$this->collection->start = $this->input->post('start');
					$this->collection->limit = $this->input->post('limit');
				}
				
				if (strlen($this->input->post('searchVal'))) {
					foreach ($relation['displayFields'] as $displayField) {
						if (!strpos($displayField, ".")) {
							foreach ($relationObject->fields as $field) {
								if ($displayField == $field['name']) {
									if (($field['type'] == 'varchar') || ($field['type'] == 'text')) {
										$this->collection->addFilter($displayField, 'like', '%'.$this->input->post('searchVal').'%');
									} else {
										$this->collection->addFilter($displayField, '=', $this->input->post('searchVal'));
									}
								}
							}
						} else {
							$arr = explode(".", $displayField);
							$displayRelation = $arr[0];
							$displayField = $arr[1];
							foreach ($relationObject->relations as $relation) {
								if ($displayRelation == $relation['name']) {
									$this->load->model($relation['name'], "relationObject_2");
									$relationObject_2 = $this->relationObject_2;
									foreach ($relationObject_2->fields as $field) {
										if ($displayField == $field['name']) {
											if (($field['type'] == 'varchar') || ($field['type'] == 'text')) {
												$this->collection->addFilter($displayField, 'like', '\'%'.$this->input->post('searchVal').'%\'', '', $relationObject_2->table);
											} else {
												$this->collection->addFilter($displayField, '=', $this->input->post('searchVal'), '', $relationObject_2->table);
											}
										}
									}
								}
							}
						}
					}
				}
				
				$this->collection->sort = array();
				
				$result = $this->collection->query()->result_array();
				
				$resultFinal = array();
				foreach ($result as $item) {

					$itemFinal = array();
					$itemFinal['Itemid'] = $item['Itemid'];

					$itemFinal[$relationName.'_display'] = '';
					
					foreach ($relation['displayFields'] as $displayField) {
						if (strpos($displayField, ".") > 0) {
							$displayField = explode(".", $displayField);
							$itemFinal[$relationName.'_display'] .= $item[$displayField[0].'_display'].' ';	
						} else {
							if (isset($item[$displayField])) {
								$itemFinal[$relationName.'_display'] .= $item[$displayField].' ';
							} else {
								$itemFinal[$relationName.'_display'] .= $item[$relation["name"].'_display'].' ';	
							}
						}
					}
					$itemFinal[$relationName.'_display'] = substr($itemFinal[$relationName.'_display'], 0, strlen($itemFinal[$relationName.'_display'])-1);
					
					array_push($resultFinal, $itemFinal);
				}
				
				echo json_encode(array("totalCount"=>$this->collection->count(), "items"=>$resultFinal));

			}
			
		}
		
	}
	
	function json_list() {

		if ($this->input->post('relation') != '') {
			$table = $this->persistent->table;
			$this->load->model($this->input->post('relation'), "json_list_tempObject");
			$this->addPersistent($this->json_list_tempObject);
			foreach ($this->persistent->relations as $relation) {
				$object = $this->persistent->getRelation($relation['name']);
				if ($object->table == $table) {
					$this->collection->addFilter($relation['field'], '=', $this->input->post('Itemid'));
				}
			}
			//	initialize relations persistent presentation object
			$this->presentation = new Ppresentation();
			$this->presentation->persistent = $this->persistent;
			$this->presentation->init();
			$this->customFilters = array();
		}
		$this->collection->start = $this->input->post('start');
		$this->collection->limit = $this->input->post('limit');
		foreach ($this->presentation->fields as $field) {
			if ($field['search']) {
				if ($this->input->post($field['name']) != "") {
					switch (strtolower($field['type'])) {
						case "char":
						case "varchar":
							$this->collection->addFilter($field['name'], 'LIKE', '%'.$this->input->post($field['name']).'%');
							break;
						default:
							$this->collection->addFilter($field['name'], '=', $this->input->post($field['name']));
							break;
					}
				}
			}
		}
		foreach ($this->presentation->relations as $relation) {
			switch ($relation['type']) {
				case "OneOnOne":
					if ($relation['search']) {
						if ($this->input->post($relation['name']) != "") {
							$this->collection->addFilter("id", '=', $this->input->post($relation['name']), '', $relation['object']->table);
						}
					}
					break;
				case "OneToMany":
					break;
			}
			
		}
		foreach ($this->customFilters as $customFilter) {
			$this->collection->addFilter(
				$customFilter["filter"],
				$customFilter["operator"],
				$customFilter["value"],
				$customFilter["value2"],
				$customFilter["table"]
			);
		}
		
		foreach ($this->customOrdering as $customOrder) {
			$this->collection->addSort(
				$customOrder["field"],
				$customOrder["type"]
			);
		}
		
		$this->collection->presentation = $this->presentation;
		echo json_encode(array("totalCount"=>$this->collection->count(), "items"=>$this->collection->query()->result_array()));;
		
	}
	
	function load() {
		
		if ($this->input->post('relation') != "") {
			foreach ($this->persistent->relations as $relation) {
				if ($relation['name'] == $this->input->post('relation')) {
					$this->addPersistent($this->persistent->getRelation($relation['name']));
				}
			}
		}
		
		if ($this->persistent->open($this->input->post('Itemid'))) {
			
			$object = array();
			$object['Itemid'] = $this->persistent->id;
			foreach ($this->persistent->fields as $field) {
				$object[$field['name']] = $this->persistent->getValue($field['name']);
			}
			
			foreach ($this->persistent->relations as $relation) {
				switch ($relation['type']) {
					case "OneOnOne":
						$object[$relation['name'].'_display'] = "";
						$relationObject = $this->persistent->getRelation($relation['name']);
						
						foreach ($relation['displayFields'] as $displayField) {
							
							if (strpos($displayField, ".") > 0) {
								//	If display field is from relations relation
								$displayField = explode(".", $displayField); // 0 - relation name, 1 - field name
								$relationDisplayObject = $relationObject->getRelation($displayField[0]);
								$object[$relation['name'].'_display'] .= $relationDisplayObject->getValue($displayField[1])." ";
							} else {
								$object[$relation['name'].'_display'] .= $relationObject->getValue($displayField)." ";
							}						
							
						}
						$object[$relation['name'].'_display'] = substr($object[$relation['name'].'_display'], 0, strlen($object[$relation['name'].'_display'])-1);
						$object[$relation['name']] = $relationObject->id;
						break;
					case "OneToMany":
						break;
				}
			}
			
			$json = array();
			$json['success'] = true;
			if ($this->customLoad != "") {
				$object = array_merge($object, call_user_func(array(&$this, $this->customLoad),$this->persistent->id));
			}
			$json['data'] = $object;
			echo json_encode($json);
			return true;
		} else {
			echo "{success: false, errors: { reason: 'Error loading form' }}";
			return false;
		}
	}
	
	function ordering_down() {
		
		if ($this->input->post('relation') != "") {
			$params = array();
			$params["field"] = $this->persistent->name."_id";
			$params["id"] = $this->input->post('parentid');
			foreach ($this->persistent->relations as $relation) {
				if ($relation['name'] == $this->input->post('relation')) {
					$this->addPersistent($this->persistent->getRelation($relation['name']));
				}
			}
		} else {
			$params = '';
		}
		
		if ($this->persistent->open($this->input->post('Itemid'))) {
		
			$this->persistent->orderingDown($params);
			
			echo "{success: true}";
			return true;
				
		}
		
		echo "{success: false, errors: { reason: 'Error loading item' }}";
		return false;
		
	}
	
	function ordering_up() {
		
		if ($this->input->post('relation') != "") {
			$params = array();
			$params["field"] = $this->persistent->name."_id";
			$params["id"] = $this->input->post('parentid');
			foreach ($this->persistent->relations as $relation) {
				if ($relation['name'] == $this->input->post('relation')) {
					$this->addPersistent($this->persistent->getRelation($relation['name']));
				}
			}
		} else {
			$params = '';
		}
		
		if ($this->persistent->open($this->input->post('Itemid'))) {
		
			$this->persistent->orderingUp($params);
			
			echo "{success: true}";
			return true;
				
		}
		
		echo "{success: false, errors: { reason: 'Error loading item' }}";
		return false;
		
	}

	function post() {
		
		if ($this->input->post('relation') != "") {
			foreach ($this->persistent->relations as $relation) {
				if ($relation['name'] == $this->input->post('relation')) {
					$this->addPersistent($this->persistent->getRelation($relation['name']));
				}
			}
		}
		
		unset($this->persistent->id);
		$this->persistent->id = $this->input->post('Itemid');
		$this->persistent->open($this->input->post('Itemid'));

		log_message('debug', print_r($_REQUEST, true));
		
		foreach ($this->persistent->fields as $field) {
			switch ($field['special']) {
				case "image":
					
					$this->load->library("pimage");
					
					if ($_FILES[$field['name']]['name']) {
						
						$fileName = ($this->persistent->getValue($field['name']) != "")?$this->persistent->getValue($field['name']):uniqid('', false);
						$this->persistent->setValue($field['name'], $fileName);
						
						if ($_FILES[$field['name']]['type'] == "image/jpeg") {  
							move_uploaded_file ($_FILES[$field['name']]['tmp_name'],$this->config->item('ppo_images_path').$fileName.".jpg");
							$this->pimage->createResizedImages($fileName);
							unlink($this->config->item('ppo_images_path').$fileName.".jpg");
						}
						
					}
					break;
				default:
					$this->persistent->setValue($field['name'], $this->input->post($field['name']));
					break;
			}
		}
		foreach ($this->persistent->relations as $relation) {
			if ($this->input->post($relation['name'])) {
				$relationObject = $this->persistent->getRelation($relation['name']);
				if ($relationObject->open($this->input->post($relation['name']))) {
					$this->persistent->setRelation($relation['name'], &$relationObject);
				}
			} else {
				$this->persistent->clearRelation($relation['name']);
			}
		}
		
		if ($this->persistent->save()) {
			
			if ($this->customSave != "") {
				$result = call_user_func(array(&$this, $this->customSave),$this->persistent->id);
				if (!$result) {
					echo "{success: false, errors: { reason: '".$result."' }}";
					return false;
				}
			}
			
			echo "{success: true}";
			return true;
		} else {
			echo "{success: false, errors: { reason: 'Error saving form' }}";
			return false;
		}
	}
	
	function setCustomFilter($fieldName, $operator, $value, $value2 = '', $table = '') {
		array_push($this->customFilters, array("filter"=>$fieldName, "operator"=>$operator, "value"=>$value, "value2"=>$value2, "table"=>$table));
	}
	
	function setCustomOrdering($fieldName, $type = 'ASC') {
		array_push($this->customOrdering, array("field"=>$fieldName, "type"=>$type));
	}
	
	function setCustomClear($functionName) {
		$this->customClear = $functionName;
	}
	
	function setCustomLoad($functionName) {
		$this->customLoad = $functionName;
	}
	
	function setCustomSave($functionName) {
		$this->customSave = $functionName;
	}

    function MY_Controller()
    {
        parent::Controller();
        $this->id = 'c_'.rand(1000,9999);
        $this->load->library("pcollection");
        $this->collection =& $this->pcollection;
        $this->load->library("ppresentation");
        $this->presentation =& $this->ppresentation;
        $this->load->library("extjs_renderer");
        $this->renderer =& $this->extjs_renderer;
        $this->renderer->controller =& $this;
        $this->customClear = "";
        $this->customLoad = "";
        $this->customSave = "";
        $this->customFilters = array();
        $this->customOrdering = array();
        $this->customToolButtons = array();
    }
    
}