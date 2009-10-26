<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'persistent.php';

class setor_tipos_model extends Persistent {
	
	function setor_tipos_model() {
		
		$this->init();

		$this->name = "setor_tipos_model";
		$this->table = "setor_tipos";
		
		$this->addField("tipo", "varchar", 30);
		$this->addField("desc", "varchar", 100);
		$this->sync();

	}
	
}
