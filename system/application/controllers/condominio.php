<?php

class Condominio extends MY_Controller {
	
	var $data = array();
	
	function index() {
		//	This is nothing to do with GoPHP directly, sets 'title' for view
		$this->data['title'] = 'Gerenciamento do condomínio';
		$data["content"] =$this->load->view('standard', $this->data, true);
		$this->load->view("admin_layout", $data);
		
	}

	function Condominio()
	{		
		parent::MY_Controller();
		
		//	Controller internal name
		
		//		Must be the same as file name (case sensitive!)
		
		$this->name = 'condominio';
		
		
		
		//	Loads model
		$this->load->model('condominio_model');
		//	links model to controler
		$this->addPersistent(&$this->condominio_model);
		
		//	Defines the grid properites
		$this->presentation->defineListView('846','600','Condmínio');
		
		//	Defines the 'Edit window' properties
		$this->presentation->defineEditorWindow(400, 370, 'Editar usuario', array('labelAlign'=>'right','labelPad'=>5) );
		
		//	Creates a new page on editor window
		$this->presentation->addPage('Base info');
		
		
		$this->presentation->addPage('Job info');

		
		$this->renderer->listViewPageSize = 50;
		
	}
	
}

