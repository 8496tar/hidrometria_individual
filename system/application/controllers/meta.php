<?php

class Meta extends MY_Controller {
	
	var $data = array();
	
	function index() {
		//	This is nothing to do with GoPHP directly, sets 'title' for view
		$this->data['title'] = 'Informações de usuarios';
		$data["content"] =$this->load->view('standard', $this->data, true);
		$this->load->view("admin_layout", $data);
		
	}

	function Meta()
	{		
		parent::MY_Controller();
		
		//	Controller internal name
		
		//		Must be the same as file name (case sensitive!)
		
		$this->name = 'meta';
		
		
		
		//	Loads model
		$this->load->model('meta_model');
		//	links model to controler
		$this->addPersistent(&$this->meta_model);
		//	Defines the grid properites
		$this->presentation->defineListView('1000','600','meta');
		
		//	Defines the 'Edit window' properties
		$this->presentation->defineEditorWindow(400, 370, 'Informarções Pessoais', array('labelAlign'=>'right','labelPad'=>5) );
		//	Defines the 'Search window' properties
		$this->presentation->defineSearchWindow(400, 250, 'Procurar por usuario');
		
		$this->presentation->addPage('Base info');
		
		$this->renderer->listViewPageSize = 50;
		
	}
	
}
