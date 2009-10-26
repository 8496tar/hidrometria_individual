<?php

require_once("ppresentationbase.php");

class Ppresentation extends Ppresentationbase  {
	
	function getRelationPresentation($relationName) {
		foreach ($this->relations as $relation) {
			if ($relation["name"] == $relationName) {
				return $relation["presentation"];
			}
		}
	}
	
	function _init_relation_extra($item, $relation) {
		$presentation = new Ppresentationbase();
		$presentation->persistent = $item["object"];
		$presentation->init();
		$item["presentation"] = $presentation; 
		return $item;
	}
	
	function setRelationPresentation($relationName, $presentation) {
		for ($i=0;$i<count($this->relations);$i++) {
			if ($this->relations[$i] == $relationName) {
				$this->relations[$i]["presentation"] = $presentation;
				return true;
			}
		}
	}

	function Ppresentation() {
		parent::Ppresentationbase();
	}
	
}