<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'persistent.php';

class Sensores_model extends Persistent {
	
	function Sensores_model() {
		
		$this->init();

		$this->name = "sensores_model";
		$this->table = "sensores";
		
		$this->addField("identificador", "varchar", 10);
		$this->addField("data_instalado", "date", "");
		$this->addField("data_removido", "date", "");
		
		$this->sync();

	}
	
}
