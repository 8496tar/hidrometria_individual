<?php

class Usuarios extends MY_Controller {
	
	var $data = array();
	
	function index() {
		//	This is nothing to do with GoPHP directly, sets 'title' for view
		$this->data['title'] = 'Gerenciamento de usuarios';
		$data["content"] =$this->load->view('standard', $this->data, true);
		$this->load->view("admin_layout", $data);
		
	}

	function Usuarios()
	{		
		parent::MY_Controller();
		
		//	Controller internal name
		
		//		Must be the same as file name (case sensitive!)
		
		$this->name = 'usuarios';
		
		
		
		//	Loads model
		$this->load->model('usuarios_model');
		//	links model to controler
		$this->addPersistent(&$this->usuarios_model);
		//	Defines the grid properites
		$this->presentation->defineListView('1000','600','Usuarios');
		
		//	Defines the 'Edit window' properties
		$this->presentation->defineEditorWindow(400, 370, 'Editar usuario', array('labelAlign'=>'right','labelPad'=>5) );
		//	Defines the 'Search window' properties
		$this->presentation->defineSearchWindow(400, 250, 'Procurar por usuario');
		
		
		//	Creates a new page on editor window
		$this->presentation->addPage('Base info');
		
		//	Defines a field from model
		$field = new PresentationField();
		$field->name = 'login';
		$field->title = 'login';
		$field->titleGrid = 'Login';
		$field->widthGrid = 100;
		//	Adds a field object to presentation object
		$this->presentation->setField($field);
		
		$field = new PresentationField();
		$field->name = 'email';
		$field->title = 'e-mail';
		$field->titleGrid = 'E-mail';
		$field->widthGrid = 100;
		$this->presentation->setField($field);
		
		$field = new PresentationField();
		$field->name = 'senha';
		$field->title = 'senha';
		$field->titleGrid = 'Senha';
		$field->widthGrid = 100;
		$this->presentation->setField($field);
		
		
		$this->presentation->addPage('Job info');

		//	Defines a relation from model
		$relation = new PresentationRelation();
		$relation->name = 'grupo';
		$relation->title = 'grupo';
		$relation->widthGrid = 100;
		$relation->titleGrid = 'Permissão de Usuário';
		$relation->page = 1;
		//	Adds a relation object to presentation object
		$this->presentation->setRelation($relation);
		
		$this->renderer->listViewPageSize = 50;
		
	}
	
}
