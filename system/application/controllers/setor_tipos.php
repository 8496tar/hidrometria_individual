<?php
class Setor_tipos extends MY_Controller {
	
	var $data = array();
	
	function index() {
		
		$data["content"] =$this->load->view('standard', $this->data, true);
		$this->load->view("admin_layout", $data);
		
	}

	function Setor_tipos()
	{		
		parent::MY_Controller();
		
		$this->name = "setor_tipos";
		$this->data['title'] = "Gerencie tipos de setor";
		
		$this->load->model("setor_tipos_model");
		$this->addPersistent(&$this->setor_tipos_model);
		
		$this->presentation->defineListView("846","600","Tipos de setores");
		
		$this->presentation->defineEditorWindow(400, 250, "Editar setor");
		$this->presentation->defineSearchWindow(400, 250, "Procurando em setores");
		
		$this->presentation->addPage("Gerenciando tipos de setor");
		
		$field = new PresentationField();
		$field->name = "setor";
		$field->title = "Nome do Setor";
		$field->titleGrid = "Nome do Setor";
		$field->widthGrid = 100;
		$this->presentation->setField($field);
		
		$field = new PresentationField();
		$field->name = "desc";
		$field->title = "Descrição";
		$field->titleGrid = "Descrição";
		$field->widthGrid = 100;
		$this->presentation->setField($field);
		
		$this->renderer->listViewPageSize = 50;
		
	}
	
}
