<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'persistent.php';

class Condominio_model extends Persistent {
	
	function Condominio_model() {
		
		$this->init();

		$this->name = "condominio_model";
		$this->table = "condominio";
		
		$this->addField("nome", "varchar", 50);
		$this->addField("logradouro", "varchar", 100);
		$this->addField("complemento", "varchar", 100);
		$this->addField("CNPJ", "varchar", 16);
		
		$this->sync();

	}
	
}
