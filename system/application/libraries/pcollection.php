<?php

class Pcollection {
	
	var $persistent;
	var $presentation;
	
	var $filters; // array(p_field, p_operator, p_value1, p_value2)
	var $sort; // 	array(p_field, type = 'ASC')
	
	var $limit;
	var $start;
	
	function addFilter($field, $operator, $value1, $value2 = "", $fieldTable = "") {
		$filter = array();
		$filter["field"] = $field;
		$filter["operator"] = $operator;
		$filter["value1"] = $value1;
		$filter["value2"] = $value2;
		$filter["fieldTable"] = $fieldTable;
		array_push($this->filters, $filter);
	}
	
	function addSort($field, $type = 'ASC') {
		$sort = array();
		$sort["field"] = $field;
		$sort["type"] = $type;
		array_push($this->sort, $sort);
	}
	
	function count() {
		
		$CI =& get_instance();
		$CI->load->database();
		
		$SQL = "SELECT COUNT(`".$this->persistent->table."`.`id`) n ";
		
		$SQL .= " FROM `".$this->persistent->table."` ";

		$SQL .= $this->_queryJoins();
		
		$SQL .= $this->_queryFilters();
		
		$query = $CI->db->query($SQL);
		$result = $query->result();
		
		return $result[0]->n;
		
	}
	
	function clearAllFilters() {
		$this->filters = array();
	}
	
	function query() {
		
		$CI =& get_instance();
		$CI->load->database();
		
		$SQL = "SELECT `".$this->persistent->table."`.`id` `Itemid`,";
		
		if (isset($this->presentation)) {
			$fields = $this->presentation->fields;
		} else {
			$fields = $this->persistent->fields;
		}
		
		//	BASIC FIELDS
		foreach ($fields as $field) {
			
			if ($field["name"] != "Itemid") {
			
				$hidden = false;
				if (isset($field["hiddenGrid"])) {
					$hidden = $field["hiddenGrid"];
				}
				
				if (!$hidden) {
					$SQL .= "`".$this->persistent->table."`.`".$field["name"]."`,";
				}
				
			}
		}
		//	RELATION FIELDS
		
		if (isset($this->presentation)) {
			$relations = $this->presentation->relations;
		} else {
			$relations = $this->persistent->relations;
		}
		
		$relationCount = 1;
		
		foreach ($relations as $relation) {
			
			$hidden = false;
			if (isset($relation["hiddenGrid"])) {
				$hidden = $relation["hiddenGrid"];
			}
			
			if (!$hidden) {
				switch ($relation["type"]) {
					case "OneOnOne":
						$SQL .= "`".$this->persistent->table."`.`".$relation["field"]."`,";
						if (count($relation["displayFields"]) > 0) {
							$SQL .= "CONCAT_WS(' ',";
							foreach ($relation["displayFields"] as $displayField) {
								if (strpos($displayField, ".") > 0) {
									//	If display field is from relations relation
									$displayField = explode(".", $displayField); // 0 - relation name, 1 - field name
									
									$relationObject = $this->persistent->getRelation($relation["name"]);
									$relationDisplayObject = $relationObject->getRelation($displayField[0]);
									$SQL .= "`".$relationObject->table.$relationCount."_".$relationDisplayObject->table."`.`".$displayField[1]."`,";
									
								} else {
									$relationObject = $this->persistent->getRelation($relation["name"]);
									$SQL .= "`".$relationObject->table.$relationCount."`.`".$displayField."`,";
								}
							}
							$SQL = substr($SQL, 0, strlen($SQL)-1);
							$SQL .= ") ".$relation["name"]."_display,";
						}
						break;
					case "OneToMany":
						break;
				}
			}
			$relationCount++;
		}
		$SQL = substr($SQL, 0, strlen($SQL)-1);
		
		$SQL .= " FROM `".$this->persistent->table."` ";
		
		$SQL .= $this->_queryJoins();
		
		$SQL .= $this->_queryFilters();
		
		$SQL .= $this->_ordering();
		
		$this->start = "" ? 0 : $this->start;
		$this->start = abs($this->start);
		
		$SQL .= " LIMIT ".$this->start.",".$this->limit;

		$query = $CI->db->query($SQL);

		return $query;
		
	}
	
	function _ordering() {
		
		$CI =& get_instance();
		
		$SQL = "";
		
		if (($this->persistent->orderingField != "") && (count($this->sort) == 0)) {
			$SQL = " ORDER BY ".$this->persistent->orderingField;	
		} elseif (($this->persistent->orderingField != "") && (count($this->sort) > 0)) {
			$SQL = " ORDER BY ";
			foreach ($this->sort as $sort) {
				$SQL .= $sort["field"]." ".$sort["type"].",";
			}
			$SQL .= $this->persistent->orderingField;
		} elseif (count($this->sort)) {
			$SQL = " ORDER BY ";
			foreach ($this->sort as $sort) {
				$SQL .= $sort["field"]." ".$sort["type"].",";
			}
			$SQL = substr($SQL,0,strlen($SQL)-1);
		}
		
		return $SQL;
		
	}
	
	function _queryFilters() {
		
		$SQL = "";
		
		$SQL .= " WHERE 1=1";
		
		foreach ($this->filters as $filter) {
			
			if ($filter["fieldTable"] == "") {
				foreach ($this->persistent->fields as $field) {
					if ($field["name"] == $filter["field"]) {
						if (strpos($field["type"], "char") != false) {
							$filter["value1"] = "'".$filter["value1"]."'";
							$filter["value2"] = "'".$filter["value2"]."'";
						}
						if (strpos($field["type"], "text") != false) {
							$filter["value1"] = "'".$filter["value1"]."'";
							$filter["value2"] = "'".$filter["value2"]."'";
						}
					}
				}
			} else {
				$relationCount = 1;
				foreach ($this->persistent->relations as $relation) {
					
					$relationObject = $this->persistent->getRelation($relation["name"]);
					
					if ($relationObject->table == $filter["fieldTable"]) {
						$filter["fieldTable"] .= $relationCount;
					}
					
					switch ($relation["type"]) {
						case "OneOnOne":
							foreach ($relationObject->fields as $field) {
								if ("`".$relationObject->table.$relationCount."`.`".$field["name"]."`" == $filter["field"]) {
									$filter["value1"] = "'".$filter["value1"]."'";
									$filter["value2"] = "'".$filter["value2"]."'";
								}
							}	
							break;
						case "OneToMany":
							break;
					}
					$relationCount++;
				}
			}
			
			if ($filter["fieldTable"] != "") {
				$filter["field"] = "`".$filter["fieldTable"]."`.`".$filter["field"]."`";
			} else {
				$filter["field"] = "`".$this->persistent->table."`.`".$filter["field"]."`";
			}
			
			switch (strtolower($filter["operator"])) {
				case "between":
					$SQL .= " AND ".$filter["field"]." ".strtoupper($filter["operator"])." ".$filter["value1"]." AND ".$filter["value2"];
					break;
				default:
					$SQL .= " AND ".$filter["field"]." ".strtoupper($filter["operator"])." ".$filter["value1"];
					break;
			}
		}
		
		return $SQL;
		
	}
	
	function _queryJoins() {
		
		$SQL = "";
		
		$relationCount = 1;
		foreach ($this->persistent->relations as $relation) {
			switch ($relation["type"]) {
				case "OneOnOne":
					
					$relationObject = $this->persistent->getRelation($relation["name"]);
					$SQL .= " LEFT JOIN `".$relationObject->table."` `".$relationObject->table.$relationCount."` ON `".$relationObject->table.$relationCount."`.`id` = `".$this->persistent->table."`.`".$relation["field"]."` ";
					
					if (count($relation["displayFields"]) > 0) {
						foreach ($relation["displayFields"] as $displayField) {
							if (strpos($displayField, ".") > 0) {
								//	If display field is from relations relation
								$displayField = explode(".", $displayField); // 0 - relation name, 1 - field name
								$relationDisplayObject = $relationObject->getRelation($displayField[0]);
								foreach ($relationObject->relations as $_relation) {
									if ($_relation["name"] == $displayField[0]) {
										$SQL .= " LEFT JOIN `".$relationDisplayObject->table."` `".$relationObject->table.$relationCount."_".$relationDisplayObject->table."` ON `".$relationObject->table.$relationCount."_".$relationDisplayObject->table."`.id = `".$relationObject->table.$relationCount."`.`".$_relation["field"]."` ";
									}
								}
								
							}
						}
					}
					
					break;
				case "OneToMany":
					break;
			}
			$relationCount++;
		}
		
		return $SQL;
		
	}
	
	function Pcollection() {
		$this->filters = array();
		$this->sort = array();
		$this->limit = 10;
		$this->start = 0;
	}
	
}