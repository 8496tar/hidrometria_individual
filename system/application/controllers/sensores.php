<?php

class Sensores extends MY_Controller {
	
	var $data = array();
	
	function index() {
		//	This is nothing to do with GoPHP directly, sets 'title' for view
		$this->data['title'] = 'Gerenciamento de sensores';
		$data["content"] =$this->load->view('standard', $this->data, true);
		$this->load->view("admin_layout", $data);
		
	}

	function Sensores()
	{		
		parent::MY_Controller();
		
		//	Controller internal name
		
		//		Must be the same as file name (case sensitive!)
		
		$this->name = 'sensores';
		
		
		
		//	Loads model
		$this->load->model('sensores_model');
		//	links model to controler
		$this->addPersistent(&$this->sensores_model);
		
		//	Defines the grid properites
		$this->presentation->defineListView('846','600','Sensores');
		
		//	Defines the 'Edit window' properties
		$this->presentation->defineEditorWindow(400, 370, 'Editar sensor', array('labelAlign'=>'right','labelPad'=>5) );
		//	Defines the 'Search window' properties
		$this->presentation->defineSearchWindow(400, 250, 'Procurar por sensores');
		
		
		//	Creates a new page on editor window
		$this->presentation->addPage('Base info');
		
		//	Defines a field from model
		$field = new PresentationField();
		$field->name = 'identificador';
		$field->title = 'identificador';
		$field->titleGrid = 'Chip';
		$field->widthGrid = 100;
		//	Adds a field object to presentation object
		$this->presentation->setField($field);
		
		$field = new PresentationField();
		$field->name = 'data_instalado';
		$field->title = 'data_instalado';
		$field->titleGrid = 'Data de Instalação';
		$field->widthGrid = 100;
		$this->presentation->setField($field);
		
		$field = new PresentationField();
		$field->name = 'data_removido';
		$field->title = 'data_removido';
		$field->titleGrid = 'Data de remoção';
		$field->widthGrid = 100;
		$this->presentation->setField($field);
		
		
		$this->presentation->addPage('Sensores');

		
		$this->renderer->listViewPageSize = 50;
		
	}
	
}

