<?php

class Setores extends MY_Controller {
	
	var $data = array();
	
	function index() {
		//	This is nothing to do with GoPHP directly, sets 'title' for view
		$this->data['title'] = 'Gerenciamento de setores';
		$data["content"] =$this->load->view('standard', $this->data, true);
		$this->load->view("admin_layout", $data);
		
	}

	function Setores()
	{		
		parent::MY_Controller();
		
		//	Controller internal name
		
		//		Must be the same as file name (case sensitive!)
		
		$this->name = 'setores';
		
		
		
		//	Loads model
		$this->load->model('setores_model');
		//	links model to controler
		$this->addPersistent(&$this->setores_model);
		
		//	Defines the grid properites
		$this->presentation->defineListView('846','600','Setores');
		
		//	Defines the 'Edit window' properties
		$this->presentation->defineEditorWindow(400, 370, 'Editar setor', array('labelAlign'=>'right','labelPad'=>5) );
		//	Defines the 'Search window' properties
		$this->presentation->defineSearchWindow(400, 250, 'Procurar por setor');
		
		
		//	Creates a new page on editor window
		$this->presentation->addPage('Base info');
		
		//	Defines a field from model
		$field = new PresentationField();
		$field->name = 'descricao';
		$field->title = 'Descrição';
		$field->titleGrid = 'Descrição';
		$field->widthGrid = 100;
		//	Adds a field object to presentation object
		$this->presentation->setField($field);
		
		
		
		$this->presentation->addPage('Job info');

		//	Defines a relation from model
		$relation = new PresentationRelation();
		$relation->name = 'tipo_id';
		$relation->title = 'Tipo de setor';
		$relation->widthGrid = 100;
		$relation->titleGrid = 'Tipo de Setor';
		$relation->page = 1;
		//	Adds a relation object to presentation object
		$this->presentation->setRelation($relation);
		
		$this->renderer->listViewPageSize = 50;
		
	}
	
}

