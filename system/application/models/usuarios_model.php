<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'persistent.php';

class Usuarios_model extends Persistent {
	
	function Usuarios_model() {
		
		$this->init();

		$this->name = "usuarios_model";
		$this->table = "usuarios";
		
		$this->addField("login", "varchar", 30);
		$this->addField("email", "varchar", 50);
		$this->addField("senha", "varchar", 20);
		$this->addField("chave", "varchar", 20);
		$this->addField("nova_chave", "varchar", 20);

		$this->addRelation("usuario_grupos", "usuario_grupos_model", "usuariogrupo_id", array("grupo"));
		
		$this->sync();

	}
	
}