<?php

class Comodos extends MY_Controller {
	
	var $data = array();
	
	function index() {
		//	This is nothing to do with GoPHP directly, sets 'title' for view
		$this->data['title'] = 'Gerenciamento de cômodos';
		$data["content"] =$this->load->view('standard', $this->data, true);
		$this->load->view("admin_layout", $data);
		
	}

	function Comodos()
	{		
		parent::MY_Controller();
		
		//	Controller internal name
		
		//		Must be the same as file name (case sensitive!)
		
		$this->name = 'comodos';
		
		
		
		//	Loads model
		$this->load->model('comodos_model');
		//	links model to controler
		$this->addPersistent(&$this->comodos_model);
		
		//	Defines the grid properites
		$this->presentation->defineListView('846','600','Cômodos');
		
		//	Defines the 'Edit window' properties
		$this->presentation->defineEditorWindow(400, 370, 'Editar cômodo', array('labelAlign'=>'right','labelPad'=>5) );
		//	Defines the 'Search window' properties
		$this->presentation->defineSearchWindow(400, 250, 'Procurar por cômodo');		
		
		//	Creates a new page on editor window
		$this->presentation->addPage('Base info');
		
		//	Defines a field from model
		$field = new PresentationField();
		$field->name = 'nomeComodo';
		$field->title = 'Cômodo';
		$field->titleGrid = 'Cômodo';
		$field->widthGrid = 100;
		//	Adds a field object to presentation object
		$this->presentation->setField($field);
		
		$field = new PresentationField();
		$field->name = 'descricao';
		$field->title = 'Descrição';
		$field->titleGrid = 'Descrição';
		$field->widthGrid = 200;
		$this->presentation->setField($field);
		
		$this->presentation->addPage('Job info');

		$this->renderer->listViewPageSize = 50;
		
	}
	
}

