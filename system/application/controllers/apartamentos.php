<?php

class Apartamentos extends MY_Controller {
	
	var $data = array();
	
	function index() {
		//	This is nothing to do with GoPHP directly, sets 'title' for view
		$this->data['title'] = 'Gerenciamento de apartamentos';
		$data["content"] =$this->load->view('standard', $this->data, true);
		$this->load->view("admin_layout", $data);
		
	}

	function Apartamentos()
	{		
		parent::MY_Controller();
		
		//	Controller internal name
		
		//		Must be the same as file name (case sensitive!)
		
		$this->name = 'usuarios';
		
		
		
		//	Loads model
		$this->load->model('apartamentos_model');
		//	links model to controler
		$this->addPersistent(&$this->apartamentos_model);
		
		//	Defines the grid properites
		$this->presentation->defineListView('846','600','Apartamentos');
		
		//	Defines the 'Edit window' properties
		$this->presentation->defineEditorWindow(400, 370, 'Editar apt', array('labelAlign'=>'right','labelPad'=>5) );
		//	Defines the 'Search window' properties
		$this->presentation->defineSearchWindow(400, 250, 'Procurar por apartamento');
		
		
		//	Creates a new page on editor window
		$this->presentation->addPage('Base info');


		//	Defines a relation from model
		$relation = new PresentationRelation();
		$relation->name = 'Usuario';
		$relation->title = 'Email do usuario';
		$relation->widthGrid = 100;
		$relation->titleGrid = 'Selecione o usuario';
		$relation->page = 1;
		//	Adds a relation object to presentation object
		$this->presentation->setRelation($relation);
		
				//	Defines a relation from model
		$relation = new PresentationRelation();
		$relation->name = 'Andar';
		$relation->title = 'Andar';
		$relation->widthGrid = 100;
		$relation->titleGrid = 'Andar';
		$relation->page = 1;
		//	Adds a relation object to presentation object
		$this->presentation->setRelation($relation);
		
		$this->renderer->listViewPageSize = 50;
		
	}
	
}
