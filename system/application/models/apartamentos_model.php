<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'persistent.php';

class Apartamentos_model extends Persistent {
	
	function Apartamentos_model() {
		
		$this->init();

		$this->name = "apartamentos_model";
		$this->table = "apartamentos";
		
		$this->addField("numero", "int", 5);

		$this->addRelation("Andar", "setores_model", "setor_id", array("setor"));
		$this->addRelation("Usuario", "usuarios_model", "usuario_id", array("email"));
		
		$this->sync();

	}
	
}