<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'persistent.php';

class Usuario_grupos_model extends Persistent {
	
	function Usuario_grupos_model() {
		
		$this->init();

		$this->name = "usuario_grupos_model";
		$this->table = "usuario_grupos";
		
		$this->addField("grupo", "varchar", 100);
		
		$this->sync();

	}
	
}