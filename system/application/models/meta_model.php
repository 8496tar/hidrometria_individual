<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'persistent.php';

class Meta_model extends Persistent {
	
	function Meta_model() {
		
		$this->init();

		$this->name = "meta_model";
		$this->table = "meta";
		
		$this->addField("nome", "varchar", 30);
		$this->addField("sobrenome", "varchar", 30);
		$this->addField("telefone", "int", 8);
		$this->addField("celular", "int", 8);
		$this->addRelation("login", "usuarios_model", "usuario_id", array("login"));		
		$this->sync();

	}
	
}