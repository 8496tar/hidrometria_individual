<?php
class Usuario_grupos extends MY_Controller {
	
	var $data = array();
	
	function index() {
		
		$data["content"] =$this->load->view('standard', $this->data, true);
		$this->load->view("admin_layout", $data);
		
	}

	function Usuario_grupos()
	{		
		parent::MY_Controller();
		
		$this->name = "usuario_grupos";
		$this->data['title'] = "Criando grupos de usuario";
		
		$this->load->model("usuario_grupos_model");
		$this->addPersistent(&$this->usuario_grupos_model);
		
		$this->presentation->defineListView("846","600","grupos de usuarios");
		
		$this->presentation->defineEditorWindow(400, 250, "Editar grupo");
		$this->presentation->defineSearchWindow(400, 250, "Procurando grupos");
		
		$this->presentation->addPage("Gerenciando grupos de usuario");
		
		$field = new PresentationField();
		$field->name = "grupo";
		$field->title = "Grupo";
		$field->titleGrid = "Nome do Grupo";
		$field->widthGrid = 100;
		$this->presentation->setField($field);
		
		$this->renderer->listViewPageSize = 50;
		
	}
	
}
