<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'persistent.php';

class Setores_model extends Persistent {
	
	function Setores_model() {
		
		$this->init();

		$this->name = "setores_model";
		$this->table = "setores";
		
		$this->addField("nome", "varchar", 30);
		$this->addField("descricao", "varchar", 150);

		$this->addRelation("Tipo de Setor", "setor_tipos_model", "tipo_id", array("tipo"));
		
		$this->sync();

	}
	
}