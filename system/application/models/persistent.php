<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Persistent extends Model {
	
	var $id;
	var $name;
	var $table;
	
	var $data;
		/*
		*	array = ( (name, value) )
		*/
	
	var $fields;
		/*
		 * 	array = ( (name, type, length, pk, comment, special) )
		 */

	var $orderingField;		//	field name of a field used for ordering
	
	var $groupingField;		//	field name of a field used for grouping
	var $groupingRelation;	//	relation name of a relation used for grouping
	
	var $relations;
		/*
		 * 	array = ( (name, object, field, displayFields, type ) )
		 */
		
	function addField($name, $type, $length, $pk=false, $autoInc=false, $comment='', $special='') {
		
		foreach ($this->fields as $field) {
			if ($field["name"] == $name) return false;
		}
		
		switch ($type) {
			case "image":
				$type = "varchar";
				$special = "image";
				break;
			default:
				break;
		}

		$field = array();
		$field["name"] = $name;
		$field["type"] = $type;
		$field["length"] = $length;
		$field["pk"] = $pk;
		$field["autoInc"] = $autoInc;
		$field["comment"] = $comment;
		$field["special"] = $special;
		array_push($this->fields, $field);
		return true;

	}
	
	function addRelation($name, $className, $field, $displayFields = array(), $type = "OneOnOne") {
		
			/*
			 *	$name - relation name
			 *	className - name of desired class
			 *	$field - database field to store related object id
			 *	$displayFields - array of fields for lookup [OneOnOne, OneToMany]
			 *  $type
			 */
		
		foreach ($this->relations as $relation) {
			if ($relation["name"] == $name) return false;
		}
		
		$relation = array();
		$relation["name"] = $name;
		$relation["className"] = $className;
		$relation["field"] = $field;
		$relation["displayFields"] = $displayFields;
		$relation["type"] = $type;
		array_push($this->relations, $relation);
		return true;

	}
		
	function checkTableExists() {
		
		$CI =& get_instance();
		$SQL = "SHOW TABLES";
        $query = $CI->db->query($SQL);
		foreach ($query->result() as $item) {
			$item = (array)$item;
			foreach ( $item as $key => $value ) {
			    if ($item[$key] == $this->table) return true;
			}
		}
		return false;
		
	}
	
	function checkRelationTableExists($relation) {
		
		$CI =& get_instance();
		$SQL = "SHOW TABLES";
        $query = $CI->db->query($SQL);
		foreach ($query->result() as $item) {
			$item = (array)$item;
			foreach ( $item as $key => $value ) {
				$relationObject = $this->getRelation($relation["name"]);
			    if ($item[$key] == $this->table."_".$relationObject->table) return true;
			}
		}
		return false;
		
	}
		
	function createField($field) {
		
		$CI =& get_instance();
		
		$length = ($field["length"] > 0)?"(".$field["length"].")":"";
		$SQL = "ALTER TABLE ".$this->table." ADD COLUMN `".$field["name"]."` ".$field["type"].$length.";";
        $query = $CI->db->query($SQL);
		return true;
		
	}
	
	function clearRelation($name) {
		
		for ($i=0;$i<count($this->relations);$i++) {
			if ($this->relations[$i]["name"] == $name) {
				$CI =& get_instance();
				$rand = rand(1000,9999);
				$CI->load->model($this->relations[$i]["className"],'tempObject'.$rand,TRUE);
				$tempObject =& $CI->{'tempObject'.$rand};
				$this->relations[$i]["object"] = $this->serialize($tempObject);
				return true;
			}
		}
		
		return false;
		
	}
	
	function clearTempRelations() {

		$CI =& get_instance();
		
		foreach ($this->relations as $relation) {
			switch ($relation["type"]) {
				case "OneOnOne":
					break;
				case "OneToMany":
					$object = $this->getRelation($relation["name"]);
					foreach ($object->relations as $object_relation) {
						if ($object_relation["name"] == $this->name) {
							$SQL = "DELETE FROM ".$object->table." WHERE ".$object_relation["field"]."=-1";
 							$CI->db->query($SQL);
						}
					}	
					break;
				case "ManyToMany":
					break;
			}
		}
		
	}
	
	function createFieldRelation($fieldName) {
		
		$CI =& get_instance();
		$SQL = "ALTER TABLE ".$this->table." ADD COLUMN `".$fieldName."` INT(16);";
        $CI->db->query($SQL);
		return true;
		
	}
	
	function createTable() {
		$CI =& get_instance();
		$SQL = "CREATE TABLE ".$this->table." (id INT(50) NOT NULL AUTO_INCREMENT PRIMARY KEY) DEFAULT CHARSET=utf8;";
        $CI->db->query($SQL);
		return true;
	}
	
	function createReferenceTable($relation) {
		$CI =& get_instance();
		$relationObject = $this->getRelation($relation["name"]);
		$SQL = "CREATE TABLE ".$this->table."_".$relationObject->table." (id INT(50) NOT NULL AUTO_INCREMENT PRIMARY KEY, ".$this->table."_id INT(50) NOT NULL, ".$relationObject->table."_id INT(50) NOT NULL) DEFAULT CHARSET=utf8;";
        $CI->db->query($SQL);
		return true;
	}
	
	function delete() {
		$CI =& get_instance();
		$SQL = "DELETE FROM ".$this->table." WHERE `id`=".$this->id;
        $CI->db->query($SQL);
		return true;
	}
	
	function getNextOrdering() {
		
		$CI =& get_instance();
		$SQL = "SELECT MAX(".$this->orderingField.") M FROM ".$this->table;
        $query = $CI->db->query($SQL);
        $result = $query->result();
        if ($result[0]->M > 0) {
        	return $result[0]->M + 1;
        } else {
        	return 1;
        }
		
	}
	
	function getRelation($relationName) {
		
		foreach ($this->relations as $relation) {
			if ($relation["name"] == $relationName) {
				if (isset($relation["object"])) {
					return $this->unserialize($relation["object"]);
				} else {
					$CI =& get_instance();
					$rand = rand(1000,9999);
					$CI->load->model($relation["className"],'tempObject'.$rand,TRUE);
					$tempObject =& $CI->{'tempObject'.$rand};
					return $tempObject;
				}
			}
		}
		return false;
	}
	
	function getValue($fieldName) {
		
		foreach ($this->data as $data) {
			if ($data["fieldName"] == $fieldName) {
				return $data["value"];
			}
		}
		return false;
		
	}

	function init() {
		$CI =& get_instance();
		$CI->load->database();
		
		$this->id = "";
		
		$this->data = array();
		$this->fields = array();
		$this->relations = array();
	}
	
	function open($id) {
		
		if (!($id > 0)) return false;
		
		$CI =& get_instance();
		
		$SQL = "SELECT ";
				
		foreach ($this->fields as $field) {
			
			$SQL .= "`".$field["name"]."`,";
			
		}
		
		foreach ($this->relations as $relation) {
			switch ($relation["type"]) {
    			case "OneOnOne":
    			case "OneToMany":
					$SQL .= "`".$relation["field"]."`,";
					break;
    			case "ManyToMany":
    				break;
			}			
		}

		$SQL = substr($SQL, 0, strlen($SQL)-1);
		
		$SQL .= " FROM ".$this->table." WHERE id=".$id;
		
    	$query = $CI->db->query($SQL);
    	
    	if (count($query->result()) == 1) {
    		
    		foreach ($query->result() as $item) {
    			$item = (array)$item;
    			foreach ($this->fields as $field) {
					$this->setValue($field["name"], $item[$field["name"]]);
				}
    			foreach ($this->relations as $relation) {
    				switch ($relation["type"]) {
    					case "OneOnOne":
    					case "OneToMany":
    						if ($item[$relation["field"]] != "") {
    							$relationObject = $this->getRelation($relation["name"]);
    							$relationObject->open($item[$relation["field"]]);
    							$this->setRelation($relation["name"], $relationObject);
    						}
    						break;
    					case "ManyToMany":
    						break;
    				}
				}
    		}
    		
    		$this->id = $id;
    		
    		return true;
    		
    	} else {
    		return false;
    	}
		
	}
	
	function orderingDown($relation = '') {
		
		//	relation = array('field', 'id')
		
		$CI =& get_instance();
		
		if ($relation != '') {
			$whereSQL = " AND ".$relation["field"]."=".$relation["id"];
		} else {
			$whereSQL = "";
		}
		
		if ($this->groupingField != "") {
			$whereSQL = " AND ".$this->groupingField."=".$this->getValue($this->groupingField);	
		}
		
		if ($this->groupingRelation != "") {
			
			foreach ($this->relations as $groupingRelation) {
				if ($groupingRelation["name"] == $this->groupingRelation) {
					$object = $this->getRelation($this->groupingRelation);
					$whereSQL = " AND ".$groupingRelation["field"]."=".$object->id;		
				}
			}
			
		}

		//	find previous item ordering
		
		$SQL = "
			SELECT 
				id, 
				$this->orderingField next_ordering 
			FROM 
				".$this->table." 
			WHERE 
				".$this->orderingField." > ".$this->getValue($this->orderingField)." 
				".$whereSQL."
			ORDER BY 2 LIMIT 1";
		$query = $CI->db->query($SQL);
		$result = $query->result();

		
		//	if none found item is first, no update needed
		if (!($result[0]->next_ordering > 0)) return false;
		
		$next_id = $result[0]->id;
		$next_ordering = $result[0]->next_ordering;
		
		$SQL = "UPDATE ".$this->table." SET ".$this->orderingField."=".$this->getValue($this->orderingField)." WHERE id=".$next_id;
		$CI->db->query($SQL);
		
		$SQL = "UPDATE ".$this->table." SET ".$this->orderingField."=".$next_ordering." WHERE id=".$this->id;
		$CI->db->query($SQL);
		
		return true;
		
	}
	
	function orderingUp($relation = '') {
		//	relation = array('field', 'id')
		
		$CI =& get_instance();
		
		if ($relation != '') {
			$whereSQL = " AND ".$relation["field"]."=".$relation["id"];
		} else {
			$whereSQL = "";
		}
		
		if ($this->groupingField != "") {
			$whereSQL = " AND ".$this->groupingField."=".$this->getValue($this->groupingField);	
		}
		
		if ($this->groupingRelation != "") {
			
			foreach ($this->relations as $groupingRelation) {
				if ($groupingRelation["name"] == $this->groupingRelation) {
					$object = $this->getRelation($this->groupingRelation);
					$whereSQL = " AND ".$groupingRelation["field"]."=".$object->id;		
				}
			}
			
		}

		//	find previous item ordering
		
		$SQL = "
			SELECT 
				id, 
				$this->orderingField prev_ordering 
			FROM 
				".$this->table." 
			WHERE 
				".$this->orderingField." < ".$this->getValue($this->orderingField)." 
				".$whereSQL."
			ORDER BY 2 DESC LIMIT 1";
		$query = $CI->db->query($SQL);
		$result = $query->result();

		
		//	if none found item is first, no update needed
		if (!($result[0]->prev_ordering > 0)) return false;
		
		$prev_id = $result[0]->id;
		$prev_ordering = $result[0]->prev_ordering;

		$SQL = "UPDATE ".$this->table." SET ".$this->orderingField."=".$this->getValue($this->orderingField)." WHERE id=".$prev_id;
		$CI->db->query($SQL);

		$SQL = "UPDATE ".$this->table." SET ".$this->orderingField."=".$prev_ordering." WHERE id=".$this->id;
		$CI->db->query($SQL);
		
		return true;
		
	}

	function save() {

		$CI =& get_instance();
		
		$method = "update";
		if ($this->id == "") {
			$method = "insert";
		} else {
			$SQL = "SELECT COUNT(id) n FROM ".$this->table." WHERE id=".$this->id;
		    $query = $CI->db->query($SQL);
		    $result = $query->result();
			if ($result[0]->n == 0) {
				$method = "insert";
			}
		}
		
		switch ($method) {
			case "insert":
				
				$fields = ""; $values = "";
				
				if ($this->id != "") {
					$fields .= "`id`,";	
					$values .= $this->id.",";
				}
				
				foreach ($this->fields as $field) {
					
					
					$fields .= "`".$field["name"]."`,";

					if ((strpos($field["type"], "char") !== false) || (strpos($field["type"], "text") !== false)) {
						$values .= "'".str_replace('\'','\\\'',$this->getValue($field["name"]))."',";
					} elseif (strpos($field["type"], "datetime") !== false) {
						$val = $this->getValue($field["name"]);
						if (strtotime($val) !== false) {
							$values .= "'".date("Y-m-d H:i:s", strtotime($val))."',";
						} else {
							$values .= "NULL,";	
						}
					} elseif (strpos($field["type"], "date") !== false) {
						$val = $this->getValue($field["name"]);
						if (strtotime($val) !== false) {
							$values .= "'".date("Y-m-d", strtotime($val))."',";
						} else {
							$values .= "NULL,";	
						}
					} elseif (strpos($field["type"], "tinyint") !== false) {
						$values .= ($this->getValue($field["name"]) == "on")?"1,":"0,";
					} else {
						if ($field["name"] == $this->orderingField) {
							$values .= $this->getNextOrdering().",";
						} elseif ($this->getValue($field["name"]) != "") {
							$values .= $this->getValue($field["name"]).",";
						} else {
							$values .= "NULL,";
						}
					}
				}
				foreach ($this->relations as $relation) {
					switch ($relation["type"]) {
						case "OneOnOne":
						case "OneToMany":
							$fields .= "`".$relation["field"]."`,";
							$object = $this->getRelation($relation["name"]);
							if ($object->id != "") {
								
								$values .= $object->id.",";
							} else {
								$values .= "-1,";
							}
							break;
						case "ManyToMany":
							break;
					}
				}
				//$fields = substr($fields, 0, strlen($fields)-1);
				//$values = substr($values, 0, strlen($values)-1);
				$fields = rtrim($fields, ",");
				$values = rtrim($values, ",");
				$SQL = "INSERT INTO ".$this->table." (".$fields.") VALUES (".$values.")";

		    	$CI->db->query($SQL);
		    	
		    	$this->id = $CI->db->insert_id();
		    	
		    	$this->saveTempRelations();
				
				break;
			case "update":
				
				$SQL = "UPDATE ".$this->table." SET ";
				
				foreach ($this->fields as $field) {
					
					$SQL .= "`".$field["name"]."`=";

					if ((strpos($field["type"], "char") !== false) || (strpos($field["type"], "text") !== false)) {
						$SQL .= "'".str_replace('\'','\\\'',$this->getValue($field["name"]))."',";
					} elseif (strpos($field["type"], "datetime") !== false) {
						$val = $this->getValue($field["name"]);
						if (strtotime($val) !== false) {
							$SQL .= "'".date("Y-m-d H:i:s", strtotime($val))."',";
						} else {
							$SQL .= "NULL,";	
						}
					} elseif (strpos($field["type"], "date") !== false) {
						$val = $this->getValue($field["name"]);
						if (strtotime($val) !== false) {
							$SQL .= "'".date("Y-m-d", strtotime($val))."',";
						} else {
							$SQL .= "NULL,";	
						}
					}					
					elseif ((strpos($field["type"], "tinyint") !== false)) {
						$SQL .= ($this->getValue($field["name"]) == "on")?"1,":"0,";
					}  else {
						if ($this->getValue($field["name"]) != "") {
							$SQL .= $this->getValue($field["name"]).",";
						} else {
							$SQL .= "NULL,";
						}
					}
				}
				
				foreach ($this->relations as $relation) {
					switch ($relation["type"]) {
						case "OneOnOne":
							
							$SQL .= "`".$relation["field"]."`=";
							$object = $this->getRelation($relation["name"]);
							if ($object->id != "") {
								$SQL .= $object->id.",";
							} else {
								$SQL .= "NULL,";
							}

							break;
						case "OneToMany":
							break;
						case "ManyToMany":
							break;
					}					
				}
				
				//$SQL = substr($SQL, 0, strlen($SQL)-1);
				
				$SQL = rtrim($SQL, ",");
				
				$SQL .= " WHERE id=".$this->id;

		    	$query = $CI->db->query($SQL);
				
				break;
		}
		
		return true;		
		
	}
	
	function saveTempRelations() {

		$CI =& get_instance();
		$CI->load->library('session');
		
		foreach ($this->relations as $relation) {
			switch ($relation["type"]) {
				case "OneOnOne":
					break;
				case "OneToMany":
					$undefined = ($CI->session->userdata('user') > 0)?($CI->session->userdata('user') * -1):-1;
					$object = $this->getRelation($relation["name"]);
					foreach ($object->relations as $object_relation) {
						if ($object_relation["name"] == $this->name) {
							$SQL = "UPDATE ".$object->table." SET ".$object_relation["field"]."=".$this->id." WHERE ".$object_relation["field"]."=".$undefined;
 							$CI->db->query($SQL);
						}
					}	
					break;
				case "ManyToMany":
					break;
			}
		}
		
	}
	
	function serialize($object) {

		/*
			converts object properties to serialized string to prevent serializing entire object when storing to session
		*/
		
		$session = array();
		
		$session["id"] = $object->id;
		$session["data"] = $object->data;
		$session["table"] = $object->table;
		$session["fields"] = $object->fields;
		$session["orderingField"] = $object->orderingField;
		$session["relations"] = $object->relations;
		
		return serialize($session);
		
	}
	
	function setOrdering($fieldName) {
		foreach ($this->fields as $field) {
			if ($field["name"] == $fieldName) $this->orderingField = $fieldName;
		}
	}
	
	function setGrouping($fieldOrRelationName) {
		foreach ($this->fields as $field) {
			if ($field["name"] == $fieldOrRelationName) {
				$this->groupingField = $fieldOrRelationName;
				return true;
			}
		}
		foreach ($this->relations as $relation) {
			if ($relation["name"] == $fieldOrRelationName) {
				$this->groupingRelation = $relation["name"];
				return true;
			}
		}
	}
	
	function setRelation($name, &$object) {
		
		$check = false;
		foreach ($this->relations as $relation) {
			if ($relation["name"] == $name) $check = true;
		}

		if (!$check) return false;

		for ($i=0;$i<count($this->relations);$i++) {
			switch ($this->relations[$i]["type"]) {
				case "OneOnOne":
				case "OneToMany":
					if ($this->relations[$i]["name"] == $name) {
						$this->relations[$i]["object"] = $this->serialize($object);
				
						return true;
					}	
					break;
				case "ManyToMany":
					
					break;
			}
		}
		
	}
	
	function setValue($fieldName, $value) {
		
		$check = false;
		foreach ($this->fields as $field) {
			if ($field["name"] == $fieldName) $check = true;
		}

		if (!$check) return false;
		
		for ($i=0;$i<count($this->data);$i++) {
			if ($this->data[$i]["fieldName"] == $fieldName) {
				$this->data[$i]["value"] = $value;
				return true;
			}
		}
		
		$data = array();
		$data["fieldName"] = $fieldName;
		$data["value"] = $value;
		array_push($this->data, $data);
		return true;
		
	}

	function sync() {
		
		if (!$this->checkTableExists()) {
			$this->createTable();
		}
		$this->updateFields();
		
	}
	
	function unserialize($serialized) {
		
		$obj = new Persistent();
		$obj->init();
		
		$session = unserialize($serialized);
		
		$obj->id = $session["id"];
		$obj->data = $session["data"];
		$obj->table = $session["table"];
		$obj->fields = $session["fields"];
		$obj->orderingField = $session["orderingField"];
		$obj->relations = $session["relations"];
		
		return $obj;
		
	}
	
	function updateField($field) {
		
		$CI =& get_instance();
		$length = ($field["length"] == 0)?'':'('.$field["length"].')';
		$SQL = "ALTER TABLE ".$this->table." MODIFY `".$field["name"]."` ".$field["type"].$length.";";
        $query = $CI->db->query($SQL);
		return true;
		
	}
	
	function updateFieldRelation($fieldName) {
		
		$CI =& get_instance();
		$SQL = "ALTER TABLE ".$this->table." MODIFY `".$fieldName."` INT(16);";
        $query = $CI->db->query($SQL);
		return true;
		
	}
	
	function updateFields() {
		
		$CI =& get_instance();
		$SQL = "DESCRIBE ".$this->table.";";
        $query = $CI->db->query($SQL);
        $result = $query->result();
        foreach ($this->fields as $field) {
        	$createField = true;
			foreach ($result as $item) {
				if ($item->Field == $field["name"]) {
					switch ($field["type"]) {
						case "text":
						case "float":
							if ($item->Type != $field["type"]) {
								$this->updateField($field);
							}
							break;
						default:
							if ($field["length"] > 0) {
								if ($item->Type != $field["type"]."(".$field["length"].")") {
									$this->updateField($field);
								}
							} else {
								if ($item->Type != $field["type"]) {
									$this->updateField($field);
								}
							}
							break;
					}
					$createField = false;
				}
			}
			if ($createField) $this->createField($field);
		}
		
		foreach ($this->relations as $relation) {
			$createRelation = true;
			foreach ($result as $item) {
				if ($item->Field == $relation["field"]) {
					switch ($relation["type"]) {
						case "OneOnOne":
						case "OneToMany":
							//$this->checkRelation($relation);
							break;
						case "ManyToMany":
							if ($this->checkRelationTableExists($relation)) {
		
							} else {
								$this->createReferenceTable($relation);
							}
							break;
					}
					$createRelation = false;
				}
			}
			if ($createRelation) $this->createFieldRelation($relation["field"]);
		}
		
	}
	
	function Persistent() {

	}
	
}