<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'persistent.php';

class Ap_comodo_model extends Persistent {
	
	function Ap_comodo_model() {
		
		$this->init();

		$this->name = "ap_comodo_model";
		$this->table = "ap_comodo";

		$this->addRelation("Sensor", "sensores_model", "sensor_id", array("identificador"));
		$this->addRelation("Numero apt", "apartamentos_model", "apartamento_id", array("numero"));
		$this->addRelation("Comodo", "comodos_model", "comodo_id", array("nomeComodo"));
		
		$this->sync();

	}
	
}

