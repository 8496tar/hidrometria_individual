<?php

class Ap_comodo extends MY_Controller {
	
	var $data = array();
	
	function index() {
		//	This is nothing to do with GoPHP directly, sets 'title' for view
		$this->data['title'] = 'Gerenciamento de medição';
		$data["content"] =$this->load->view('standard', $this->data, true);
		$this->load->view("admin_layout", $data);
		
	}

	function Ap_comodo()
	{		
		parent::MY_Controller();
		
		//	Controller internal name
		
		//		Must be the same as file name (case sensitive!)
		
		$this->name = 'ap_comodo';
		
		
		
		//	Loads model
		$this->load->model('ap_comodo_model');
		//	links model to controler
		$this->addPersistent(&$this->ap_comodo_model);
		
		//	Defines the grid properites
		$this->presentation->defineListView('846','600','Ligar Sensores');
		
		//	Defines the 'Search window' properties
		$this->presentation->defineSearchWindow(400, 250, 'Procurar por usuario');	
		
		//	Creates a new page on editor window
		$this->presentation->addPage('Base info');
		
		//	Defines a relation from model
		$relation = new PresentationRelation();
		$relation->name = 'comodo_id';
		$relation->title = 'Comodo';
		$relation->widthGrid = 100;
		$relation->titleGrid = 'Comodo';
		$relation->page = 1;
		//	Adds a relation object to presentation object
		$this->presentation->setRelation($relation);
		
		$relation = new PresentationRelation();
		$relation->name = 'sensor_id';
		$relation->title = 'Chip do sensor';
		$relation->widthGrid = 100;
		$relation->titleGrid = 'Chip Identificador';
		$relation->page = 1;
		//	Adds a relation object to presentation object
		$this->presentation->setRelation($relation);
		
		$relation = new PresentationRelation();
		$relation->name = 'apartamento_id';
		$relation->title = 'Apartamento';
		$relation->widthGrid = 100;
		$relation->titleGrid = 'Numero apartamento';
		$relation->page = 1;
		//	Adds a relation object to presentation object
		$this->presentation->setRelation($relation);
		
		$this->renderer->listViewPageSize = 50;
		
	}
	
}

