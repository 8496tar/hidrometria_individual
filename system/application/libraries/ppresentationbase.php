<?php

class PresentationField {
	
	var $name;
	var $hiddenGrid;
	var $hiddenForm;
	var $search;
	var $title;
	var $page;
	var $allowBlank;
	var $width;
	var $height;
	var $widthGrid;
	var $titleGrid;
	var $ordering;
	var $format;	//	default value format if needed (for example datetime field)
	var $type;	//	defines presentation types for certain field types (for example varchar: type = 'textarea' will render field as text area)
	var $items;	//	if field type is combo you can set options here ( [ ['id_1','name_1','icon_1'], ['id_2','name_2','icon_2'],... ] )
	var $default;
	var $position;	//	used for field ordering
	var $properties;	//	extjs properties for direct manipulation ( [property_name, value] )
	var $gridRenderer;	//	custom grid renderer
	
	function addProperty($name, $value) {
		array_push($this->properties, array("name"=>$name, "value"=>$value));
	}
	
	function _generateProperties() {
		
		$html = '';
		foreach ($this->properties as $property) {
			$html .= ','.$property["name"].':'.$property["value"];
		}
		return $html;	
			
	}
	
	function PresentationField() {

		$this->name = "";
		$this->hiddenGrid = false;
		$this->hiddenForm = false;
		$this->search = true;
		$this->title = "";
		$this->page = 1;
		$this->allowBlank = true;
		$this->width = "";
		$this->height = "";
		$this->widthGrid = "";
		$this->titleGrid = "";
		$this->ordering = false;	//	if true this field is used for ordering
		$this->format = "";
		$this->type = "";
		$this->items = array();
		$this->default = "";
		$this->position = -1;
		$this->properties = array();
		$this->gridRenderer = "";
		
	}
	
}

class PresentationRelation {
	
	var $name;
	var $hiddenGrid;
	var $hiddenForm;
	var $search;
	var $title;
	var $page;
	var $allowBlank;
	var $control;
	var $widthGrid;
	var $titleGrid;
	var $width;
	var $height;
	var $position;	//	used for field ordering
	
	function PresentationRelation() {
		
		$this->name = "";
		$this->hiddenGrid = false;
		$this->hiddenForm = false;
		$this->search = true;
		$this->title = "";
		$this->page = 1;
		$this->allowBlank = true;
		$this->control = "lookup";
		$this->widthGrid = "";
		$this->titleGrid = "";
		$this->width = "";
		$this->height = "";
		$this->position = -1;
	
	}
}

class PresentationCustom {
	
	var $name;
	var $function;
	var $page;
	var $position;
	
	function PresentationCustom() {
		$this->page = 1;
		$this->position = -1;
	}
	
}

class PresentationSpecial {
	
	var $type;
	var $html;
	var $page;
	var $position;
	
	function PresentationSpecial() {
		$this->page = 1;
		$this->position = -1;
	}
	
}

class Ppresentationbase {
	
	var $persistent;
	
	var $listView; // array(width, height, title)
	var $editorWindow; //	array(width, height, title)
	var $searchWindow; //	array(width, height, title)
	
	var $pages; //	array(name)
	
	var $fields; // array([type - internal], [special - internal], name, hidden, search, title, page, allowBlank, position)
	
	var $relations; //	array([type -internal], name, field, object, title, hidden, displayPage, page, allowBlank, control ["lookup","dropdown"], $lookupWindow [ array (width, height, title) ], position )
	
	//	custom items allow user to insert custom functionality into ppo generated UI
	var $custom; //  array(name, function, page, position)
	
	//	special items - html, horizontal lines etc
	var $special; //  array(type, html, page, position)
	
	var $positionCount;	//	field position counter
	
	var $showItemId;

	function addPage($name, $labelWidth = 100, $layout = 'form', $controlWidth = 230, $columns = 1) {
		array_push($this->pages, array("title"=>$name, "labelWidth"=>$labelWidth, "layout"=>$layout, "controlWidth"=>$controlWidth, "columns"=>$columns));
	}
	
	function defineEditorWindow($width, $height, $title, $labelStyle = '') {
		$this->editorWindow["width"] = $width;
		$this->editorWindow["height"] = $height;
		$this->editorWindow["title"] = $title;
		if (!is_array($labelStyle)) {
			$labelStyle = array("labelAlign"=>"left", "labelPad"=>5);
		}
		$this->editorWindow["labelStyle"] = $labelStyle;	//	labelStyle = [ labelAlign[left, top, right], labelPadding ]
	}
	
	function defineListView($width, $height, $title) {
		$this->listView["width"] = $width;
		$this->listView["height"] = $height;
		$this->listView["title"] = $title;
	}
	
	function defineSearchWindow($width, $height, $title) {
		$this->searchWindow["width"] = $width;
		$this->searchWindow["height"] = $height;
		$this->searchWindow["title"] = $title;
	}
	
	function getPageCount() {
		//$this->organizePages();
		$pages = array();
		foreach($this->fields as $field) {
			if (!in_array($field["page"], $pages)) {
				array_push($pages, $field["page"]);
			}
		}
		foreach($this->relations as $relation) {
			if (!in_array($relation["page"], $pages)) {
				array_push($pages, $relation["page"]);
			}
		}
		foreach($this->custom as $custom) {
			if (!in_array($custom["page"], $pages)) {
				array_push($pages, $custom["page"]);
			}
		}
		return count($pages);
	}
	
	function _init_relation_extra($item, $relation) {
		return $item;
	}
	
	function init() {
		foreach ($this->persistent->fields as $field) {
			$item["type"] = $field["type"];
			$item["special"] = $field["special"];

			$item["name"] = $field["name"];
			$item["hiddenGrid"] = false;
			$item["hiddenForm"] = false;
			$item["search"] = true;
			$item["title"] = $field["name"];
			$item["page"] = 1;
			$item["allowBlank"] = "true";
			$item["width"] = "";
			$item["height"] = "";
			$item["widthGrid"] = "";
			$item["titleGrid"] = "";
			$item["ordering"] = ($this->persistent->orderingField == $field["name"]);
			$item["format"] = "";
			$item["ptype"] = "";
			$item["default"] = "";
			$item["properties"] = "";
			$item["gridRenderer"] = "";
			array_push($this->fields, $item);
		}
		foreach ($this->persistent->relations as $relation) {
			$item = array();
			$item["type"] = $relation["type"];
			$item["name"] = $relation["name"];
			$item["field"] = $relation["field"];
			$item["object"] = $this->persistent->getRelation($relation["name"]);
			$item["title"] = $relation["name"];
			$item["hiddenGrid"] = false;
			$item["hiddenForm"] = false;
			$item["search"] = true;
			$item["displayFields"] = $relation["displayFields"];
			$item["page"] = 1;
			$item["allowBlank"] = "true";
			$item["control"] = "lookup";
			$item["widthGrid"] = "";
			$item["titleGrid"] = "";
			$item["width"] = "";
			$item["height"] = "";
			$lookupWindow = array();
			$lookupWindow["width"] = 320;
			$lookupWindow["height"] = 400;
			$lookupWindow["title"] = $relation["name"];
			$item["lookupWindow"] = $lookupWindow;
			array_push($this->relations, $this->_init_relation_extra($item, $relation));
		}
	}
	
	function orderFormComponents() {
		
		$components = array();
		
		for ($i=0;$i<$this->positionCount;$i++) {
			
			foreach ($this->fields as $field) {
				if (isset($field["position"])) {
					if ($field["position"] == $i) array_push($components, array("data"=>$field, "type"=>"field"));
				}
			}
			
			foreach ($this->relations as $relation) {
				if (isset($relation["position"])) {
					if ($relation["position"] == $i) array_push($components, array("data"=>$relation, "type"=>"relation"));
				}
			}
			
			foreach ($this->custom as $custom) {
				if (isset($custom["position"])) {
					if ($custom["position"] == $i) array_push($components, array("data"=>$custom, "type"=>"custom"));
				}
			}
			
			foreach ($this->special as $special) {
				if (isset($special["position"])) {
					if ($special["position"] == $i) array_push($components, array("data"=>$special, "type"=>"special"));
				}
			}
			
		}
		
		foreach ($this->fields as $field) {
			if (!isset($field["position"])) {
				array_push($components, array("data"=>$field, "type"=>"field"));
			}
		}
		
		foreach ($this->relations as $relation) {
			if (!isset($relation["position"])) {
				array_push($components, array("data"=>$relation, "type"=>"relation"));
			}
		}
		
		foreach ($this->custom as $custom) {
			if (!isset($custom["position"])) {
				array_push($components, array("data"=>$custom, "type"=>"custom"));
			}
		}
		
		foreach ($this->special as $special) {
			if (!isset($special["position"])) {
				array_push($components, array("data"=>$special, "type"=>"special"));
			}
		}
		
		return $components;
		
	}

	function organizePages() {
		$CI =& get_instance();
		$CI->load->helper("array");
		$array = sort2d($this->fields, "page");
		/*
		for($i=0;$i<count($this->fields);$i++) {
			if ($this->fields[$i]["page"] == "")
		}
		*/
	}
	
	function setField($presentationField) {
		
		if ($presentationField->name == 'ItemId') {
			$_field["name"] = 'Itemid';
			$_field["hiddenGrid"] = $presentationField->hiddenGrid;
			$_field["hiddenForm"] = $presentationField->hiddenForm;
			$_field["search"] = $presentationField->search;
			$_field["title"] = 'ID';
			if ($presentationField->title != "") {
				$_field["title"] = $presentationField->title;
			}
			$_field["page"] = $presentationField->page;
			if ($presentationField->allowBlank) {
				$_field["allowBlank"] = "true";
			} else {
				$_field["allowBlank"] = "false";
			}
			$_field["width"] = $presentationField->width;
			$_field["height"] = $presentationField->height;
			$_field["widthGrid"] = $presentationField->widthGrid;
			$_field["titleGrid"] = $presentationField->titleGrid;
			$_field["ordering"] = $presentationField->ordering;
			$_field["format"] = $presentationField->format;
			$_field["ptype"] = $presentationField->type;
			$_field["items"] = $presentationField->items;
			$_field["default"] = $presentationField->default;
			$_field["position"] = ($presentationField->position == -1)?$this->positionCount:$presentationField->position;
			$_field["properties"] = $presentationField->_generateProperties();
			$_field["gridRenderer"] = $presentationField->gridRenderer;
			$_field["type"] = 'ItemId';
			array_push($this->fields, $_field);
			
			$this->showItemId = true;
			$this->positionCount++;
			return true;	
		}
		
		for($i=0;$i<count($this->fields);$i++) {
			if ($this->fields[$i]["name"] == $presentationField->name) {
				$this->fields[$i]["hiddenGrid"] = $presentationField->hiddenGrid;
				$this->fields[$i]["hiddenForm"] = $presentationField->hiddenForm;
				$this->fields[$i]["search"] = $presentationField->search;
				if ($presentationField->title != "") {
					$this->fields[$i]["title"] = $presentationField->title;
				}
				$this->fields[$i]["page"] = $presentationField->page;
				if ($presentationField->allowBlank) {
					$this->fields[$i]["allowBlank"] = "true";
				} else {
					$this->fields[$i]["allowBlank"] = "false";
				}
				$this->fields[$i]["width"] = $presentationField->width;
				$this->fields[$i]["height"] = $presentationField->height;
				$this->fields[$i]["widthGrid"] = $presentationField->widthGrid;
				$this->fields[$i]["titleGrid"] = $presentationField->titleGrid;
				$this->fields[$i]["ordering"] = $presentationField->ordering;
				$this->fields[$i]["format"] = $presentationField->format;
				$this->fields[$i]["ptype"] = $presentationField->type;
				$this->fields[$i]["items"] = $presentationField->items;
				$this->fields[$i]["default"] = $presentationField->default;
				$this->fields[$i]["position"] = ($presentationField->position == -1)?$this->positionCount:$presentationField->position;
				$this->fields[$i]["properties"] = $presentationField->_generateProperties();
				$this->fields[$i]["gridRenderer"] = $presentationField->gridRenderer;
				$this->positionCount++;				
				return true;
			}
		}
		return false;
	}
	
	function setRelation($presentationRelation) {

		for($i=0;$i<count($this->relations);$i++) {
			if ($this->relations[$i]["name"] == $presentationRelation->name) {
				$this->relations[$i]["hiddenGrid"] = $presentationRelation->hiddenGrid;
				$this->relations[$i]["hiddenForm"] = $presentationRelation->hiddenForm;
				$this->relations[$i]["search"] = $presentationRelation->search;
				if ($presentationRelation->title != "") {
					$this->relations[$i]["title"] = $presentationRelation->title;
				}
				$this->relations[$i]["page"] = $presentationRelation->page;
				if ($presentationRelation->allowBlank) {
					$this->relations[$i]["allowBlank"] = "true";
				} else {
					$this->relations[$i]["allowBlank"] = "false";
				}
				$this->relations[$i]["widthGrid"] = $presentationRelation->widthGrid;
				$this->relations[$i]["titleGrid"] = $presentationRelation->titleGrid;
				$this->relations[$i]["width"] = $presentationRelation->width;
				$this->relations[$i]["height"] = $presentationRelation->height;
				$this->relations[$i]["position"] = ($presentationRelation->position == -1)?$this->positionCount:$presentationRelation->position;
				$this->positionCount++;
				return true;
			}				
		}
		return false;
	}
	
	function setCustom($custom) {
		for($i=0;$i<count($this->custom);$i++) {
			if ($this->custom[$i]["name"] == $custom->name) {
				$this->custom[$i]["function"] = $custom->function;
				$this->custom[$i]["page"] = $custom->page;
				$this->custom[$i]["position"] = $custom->position;
				return true;
			}
		}
		$item = array();
		$item["name"] = $custom->name;
		$item["function"] = $custom->function;
		$item["page"] = $custom->page;
		$item["position"] = ($custom->position == -1)?$this->positionCount:$custom->position;
		$this->positionCount++;
		array_push($this->custom, $item);
		return true;
	}
	
	function setSpecial($special) {
		$item = array();
		$item["type"] = $special->type;
		$item["html"] = $special->html;
		$item["page"] = $special->page;
		$item["position"] = ($special->position == -1)?$this->positionCount:$special->position;
		$this->positionCount++;
		array_push($this->special, $item);
		return true;
	}

	function Ppresentationbase() {
		$this->fields = array();
		$this->relations = array();
		$this->custom = array();
		$this->special = array();
		$this->pages = array();
		$this->listView = array();
		$this->listView["width"] = 320;
		$this->listView["height"] = 240;
		$this->listView["title"] = "";
		$this->editorWindow = array();
		$this->editorWindow["width"] = 320;
		$this->editorWindow["height"] = 240;
		$this->editorWindow["title"] = "Item edit";
		$this->editorWindow["labelStyle"] = array("labelAlign"=>"left", "labelPad"=>5);
		$this->searchWindow = array();
		$this->searchWindow["width"] = 320;
		$this->searchWindow["height"] = 240;
		$this->searchWindow["title"] = "Search";
		$this->positionCount = 1;
		$this->showItemId = false;
	}
	
}