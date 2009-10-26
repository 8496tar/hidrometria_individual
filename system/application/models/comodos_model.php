<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'persistent.php';

class Comodos_model extends Persistent {
	
	function Comodos_model() {
		
		$this->init();

		$this->name = "comodos_model";
		$this->table = "comodos";
		
		$this->addField("nomeComodo", "varchar", 30);
		$this->addField("descricao", "varchar", 255);
		
		$this->sync();

	}
	
}
