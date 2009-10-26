<?php

class Extjs_renderer {
	
	var $title;
	var $controller;
	var $listViewPageSize;
	
	function ajaxurl() {
		
		$CI =& get_instance();
		
		if ($CI->config->item('index_page') != '') {
			return $CI->config->item('base_url').$CI->config->item('index_page').'/';
		} else {
			return $CI->config->item('base_url');
		}
		
	}
	
	function _renderEditorWindowOneToManyRelationControls() {
		$CI =& get_instance();
		
		?>
		<script type="text/javascript">
			Ext.onReady(function(){
			<?php 
				foreach ($this->controller->presentation->relations as $relation) {
					switch ($relation["type"]) {
						case "OneOnOne":
							break;
						case "OneToMany":		
						
							$relationObject = $this->controller->persistent->getRelation($relation["name"]);
							$presentationObject = $this->controller->presentation->getRelationPresentation($relation["name"]);
							
							?>
							
							function rendererOrderingViewOneToManyGridColumnModel_<?= $relation["name"]; ?>(data,cellmd,record,row,col,store) {
						    	return '<a href="#" onclick="orderingUp(' + record.get('Itemid') + ', \'<?= $relation["name"]; ?>\', \'listViewOneToManyGrid_<?= $relation["name"]; ?>\')"><img src="<?= $CI->config->item('base_url'); ?>images/ppo/ordering_up.png" alt="up" border="0"></a>&nbsp;<a href="#" onclick="orderingDown(' + record.get('Itemid') + ', \'<?= $relation["name"]; ?>\', \'listViewOneToManyGrid_<?= $relation["name"]; ?>\')"><img src="<?= $CI->config->item('base_url'); ?>images/ppo/ordering_down.png" alt="down" border="0"></a>';
						    }
							
							<?php
							
							//	custom field renderers
						    foreach ($presentationObject->fields as $ro_field) {
						    	if ($ro_field["gridRenderer"] != "") {
						    		?>
						    		function rendererViewOneToManyGridColumnModel_<?= $ro_field["name"]; ?>(data,cellmd,record,row,col,store) {
								    	return <?= $ro_field["gridRenderer"] ?>;
								    }
						    		<?php
						    	}
						    }
							
							foreach ($presentationObject->fields as $ro_field) {
								switch ($ro_field["special"]) {
       			        			case "image":
										?>
				        				function rendererViewOneToManyGridColumnModel_<?= $relation["name"]; ?>(data,cellmd,record,row,col,store) {
									    	return '<img src="<?= $CI->config->item('ppo_images_url'); ?>' + record.get('<?= $ro_field["name"]; ?>') + '<?= $CI->config->item('ppo_images_small_sufix'); ?>.jpg" alt="">';
									    }               			        				
				        				<?php
				        				break;
       			        			default:
       			        				break;
								}
							}
							
							?>

							listViewOneToManyGridColumnModel_<?= $relation["name"]; ?> = new Ext.grid.ColumnModel([
               		        	<?php
               			        	$_tmp = "";
               			        	
               			        	foreach ($presentationObject->fields as $ro_field) {
               			        		
               			        		if (!$ro_field["hiddenGrid"]) {
	               			        		$widthGrid = "";
	               			        		if (isset($ro_field["widthGrid"])) {
	               			        			if ($ro_field["widthGrid"] > 0) {
	               			        				$widthGrid = ", width: ".$ro_field["widthGrid"];
	               			        			}
	               			        		}
	               			        			
	               			        		$titleGrid = ", header: '".$ro_field["name"]."'";
	               			        		if (isset($ro_field["titleGrid"])) {
	               			        			$ro_field["titleGrid"] = ($ro_field["titleGrid"] != "")?$ro_field["titleGrid"]:$ro_field["name"];
	               			        			$titleGrid = ", header: '".$ro_field["titleGrid"]."'";
	               			        		}
	               			        		
	               			        		$_renderer = ($ro_field["ordering"])?", renderer: rendererOrderingViewOneToManyGridColumnModel_".$relation["name"]:"";
	               			        		$_renderer = ($ro_field["gridRenderer"] != "")?", renderer: rendererViewOneToManyGridColumnModel_".$ro_field["name"]:$_renderer;
	               			        		
	               			        		switch ($ro_field["special"]) {
	               			        			case "image":
	               			        				$_renderer = ", renderer: rendererViewOneToManyGridColumnModel_".$relation["name"];
	               			        				break;
	               			        			default:
	               			        				break;
	               			        		}

	               			        		$_tmp .= "{dataIndex: '".$ro_field["name"]."'".$widthGrid.$titleGrid.$_renderer."},\n";
               			        		}
               			        		
               			        	}
               			        	
               			        	foreach ($presentationObject->relations as $ro_relation) {
               			        		
               			        		if (!$ro_relation["hiddenGrid"]) {
               			        		
	               			        		$widthGrid = "";
	               			        		if (isset($ro_relation["widthGrid"])) {
	               			        			if ($ro_relation["widthGrid"] > 0) {
	               			        				$widthGrid = ", width: ".$ro_relation["widthGrid"];
	               			        			}
	               			        		}
	               			        		
	               			        		$titleGrid = ", header: '".$ro_relation["name"]."'";
	               			        		if (isset($ro_relation["titleGrid"])) {
	               			        			if ($ro_relation["titleGrid"] != "") {
	               			        				$titleGrid = ", header: '".$ro_relation["titleGrid"]."'";
	               			        			}
	               			        		}
	               			        		
	               			        		$_tmp .= "{dataIndex: '".$ro_relation["name"]."_display'".$widthGrid.$titleGrid."},\n";
	               			        		
	               			        	}
	               			        	
               			        	}
               			        	
               			        	$_tmp = substr($_tmp, 0, strlen($_tmp)-2)."\n";
               			        	echo $_tmp;
               			        	
               			        ?>
               		        ]);
               		        
               		        listViewOneToManyGridDataStore_<?= $relation["name"]; ?> = new Ext.data.Store({
               			        id:'listViewOneToManyGridDataStore_<?= $relation["name"]; ?>',
               			        proxy: new Ext.data.HttpProxy({
               			           url: '<?= Extjs_renderer::ajaxurl();; ?><?= $this->controller->name; ?>/json_list'
               			        }),
               			        reader: new Ext.data.JsonReader({
               			            root: 'items',
               			            totalProperty: 'totalCount',
               			            id: 'Itemid'
               			        }, [
               			        <?php
               			        	$_tmp = "{name: 'Itemid', mapping: 'Itemid'},\n";
               			        	foreach ($relationObject->fields as $ro_field) {
               			        		$_tmp .= "{name: '".$ro_field["name"]."', mapping: '".$ro_field["name"]."'},\n";
               			        	}
               			        	foreach ($relationObject->relations as $ro_relation) {
               			        		$_tmp .= "{name: '".$ro_relation["name"]."', mapping: '".$ro_relation["name"]."'},\n";
               			        		$_tmp .= "{name: '".$ro_relation["name"]."_display', mapping: '".$ro_relation["name"]."_display'},\n";
               			        	}
               			        	$_tmp = substr($_tmp, 0, strlen($_tmp)-2)."\n";
               			        	echo $_tmp;
               			        ?>
               			        ]),
               			
               			        baseParams: {limit:<?= $this->listViewPageSize; ?>, relation: '<?= $relation["name"]; ?>'}
               			        
               			    });
               			    
               			    listViewOneToManyGrid_<?= $relation["name"]; ?> = new Ext.grid.GridPanel({
				        		id:'listViewOneToManyGrid_<?= $relation["name"]; ?>',
						        cm: listViewOneToManyGridColumnModel_<?= $relation["name"]; ?>,
						        ds: listViewOneToManyGridDataStore_<?= $relation["name"]; ?>,
						        trackMouseOver:false,
						        sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
						        loadMask: true,
						        width: <?= ($this->controller->presentation->editorWindow["width"] - 50); ?>,
						        height:<?php if ($relation["height"] > 0) { echo $relation["height"]; } else { echo 350; }; ?>,
						        title: '<?= $relation["title"]; ?>', 
						        frame:true,
						        tbar:[			        
						        	new Ext.Toolbar.Button({
						        		text:'New',
						        		icon:'<?= $CI->config->item('base_url')."images/ppo/add.png"; ?>',
						        		cls:'x-btn-text-icon',
						        		handler: function(){
						        			Ext.getCmp('editorWindowFormRelation_<?= $relation["name"]; ?>').getForm().reset();
						        			Ext.getCmp('editorWindowFormRelation_<?= $relation["name"]; ?>_Field_<?= $this->controller->persistent->name; ?>').setValue( Ext.getCmp('Itemid').getValue() );
						        			<?php
						        				/*	TO DO: display a current persistent value and enable selecting different parrent object
						        			Ext.getCmp('editorWindowFormRelation_<?= $relation["name"]; ?>_Field_<?= $this->controller->persistent->name; ?>_display').setValue('Test');
						        				*/
						        			?>
											Ext.getCmp('editorWindowRelation_<?= $relation["name"]; ?>').show();
						        		}
						        	}),
						        	new Ext.Toolbar.Button({
						        		text:'Edit',
						        		icon:'<?= $CI->config->item('base_url')."images/ppo/edit.png"; ?>',
						        		cls:'x-btn-text-icon',
						        		handler: function(){
											if (Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getSelectionModel().getCount() > 0) {
							        			Ext.getCmp('editorWindowRelation_<?= $relation["name"]; ?>').show();
							        			Ext.getCmp('editorWindowFormRelation_<?= $relation["name"]; ?>').getForm().load({
								                	url:'<?= Extjs_renderer::ajaxurl();; ?><?= $this->controller->name; ?>/load', 
								                	waitMsg:'Loading',
								                	disableCaching: true,
								                	success:function(form, action) {			        					
							            				Ext.getCmp('editorWindowRelation_<?= $relation["name"]; ?>').show();
								                	},
								                	failure:function(action) {
								                		var jsonData = Ext.util.JSON.decode(action.response.responseText);
								                		Ext.MessageBox.alert('Error', jsonData.errors.reason);
													},
													params:{ relation: '<?= $relation["name"]; ?>', Itemid: Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getSelectionModel().getSelected().get('Itemid') }
								                });
							        			
						        			} else {
						        				Ext.Msg.show({
													   title:'Edit record',
													   msg: 'Please select record to edit!',
													   buttons: Ext.Msg.OK
						        				});
							        		}
						        		}
						        		
						        	}),
						        	new Ext.Toolbar.Button({
						        		text:'Delete',
						        		icon:'<?= $CI->config->item('base_url')."images/ppo/delete.png"; ?>',
						    			cls:'x-btn-text-icon',
						        		handler: (function(){
											if (Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getSelectionModel().getCount() > 0) {
						        				Ext.Msg.show({
												   title:'Delete record?',
												   msg: 'You are about to delete selected record. Are you shure?',
												   buttons: Ext.Msg.YESNO,
												   fn: (function(btn, text){
												    if (btn == 'yes'){
												    	
												        Ext.Ajax.request({
														   url: '<?= Extjs_renderer::ajaxurl();; ?><?= $this->controller->name; ?>/delete',
														   success: (function(response, options) {
																Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getStore().reload();
															}),
														   failure: (function(response, options) {
															   var jsonData = Ext.util.JSON.decode(action.response.responseText);
										                	   Ext.MessageBox.alert('Error', jsonData.errors.reason);
														   }),
														   params:{ relation: '<?= $relation["name"]; ?>', Itemid: Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getSelectionModel().getSelected().get('Itemid') }
														});
												    }
												    
												   }),
												   animEl: 'elId',
												   icon: Ext.MessageBox.QUESTION
												});
						        			} else {
						        				Ext.Msg.show({
													   title:'Delete record?',
													   msg: 'Please select record to delete!',
													   buttons: Ext.Msg.OK
						        				});
							        		}
						        		})
						        	}),
						        	{xtype: 'tbseparator'},
						        	new Ext.Toolbar.Button({
						        		text:'Search',
						        		icon:'<?= $CI->config->item('base_url')."images/ppo/search.png"; ?>',
						    			cls:'x-btn-text-icon',
						        		handler: function() {
											Ext.getCmp('searchWindowRelation_<?= $relation["name"]; ?>').show();
						        		}
						        	})
						        ],
						        bbar: new Ext.PagingToolbar({
						            store: listViewOneToManyGridDataStore_<?= $relation["name"]; ?>,
						            pageSize: <?= $this->listViewPageSize; ?>,
						            displayInfo: true,
						            displayMsg: 'Topics {0} - {1} of {2}',
						            emptyMsg: "No topics to display"
						        })
						    });
					
							<?php
							break;
					}
				}
			?>
			});
		</script>
		<?php
	}
	
	function renderEditorWindow() {
		$this->_renderEditorWindowOneToManyRelationControls();
		$CI =& get_instance();
		
		$pageCount = $this->controller->presentation->getPageCount();
		$idHiddenRendered = false;
		?>
		<script type="text/javascript">
			Ext.onReady(function(){

				tabs = new Ext.FormPanel({
					id:'editorWindowForm',
			        frame:true,
			        border:false,
			        width: 'auto',
			        bodyStyle:'background:transparent;',
			        labelAlign: '<?= $this->controller->presentation->editorWindow["labelStyle"]["labelAlign"]; ?>',
			        labelPad: <?= $this->controller->presentation->editorWindow["labelStyle"]["labelPad"]; ?>,
			        items: [{
						xtype:'tabpanel',
			            activeTab: 0,
			            defaults:{height:'auto', autoHeight:true, bodyStyle:'background:transparent; padding:10px'},
			            border:false,
			            bodyStyle:'background:transparent;',
			            deferredRender:false,
			            items:[
					<?php 

						for ($p=1;$p<=$pageCount;$p++) {
							if (isset($this->controller->presentation->pages[$p-1])) {
								$page = $this->controller->presentation->pages[$p-1];
								$pageTitle = $page["title"];
							} else {
								$pageTitle = "Page ".$p; 
							}
							
							$layoutConfig = "";
							if ($page["layout"] == "table") {
							$layoutConfig = "
								layoutConfig: {
	 								columns: ".$page["columns"]."
								},";
							}
					?>
				            {
				                title:'<?= $pageTitle; ?>',
				                layout:'<?= $page["layout"]; ?>',
				                labelWidth : <?= $page["labelWidth"]; ?>,
				                defaults: { width : <?= $page["controlWidth"]; ?>},
				                defaultType: 'textfield',
				                <?= $layoutConfig; ?>
				                layoutOnTabChange:true,
				                autoScroll:true,
				                items: [
				                <?php
				                	if (!$idHiddenRendered) {
				                		$idHiddenRendered = true;
				                		if ($this->controller->presentation->showItemId) {
	
				                		} else {
				                		?>
				                		{
											id: 'Itemid',
				                			xtype:'hidden',
										    name: 'Itemid',
										    value: ''
										},
				                		<?php
				                		}
				                	}
									$editors = "";
									
									foreach ($this->controller->presentation->orderFormComponents() as $component) {
										
										switch ($component["type"]) {
											case "field":
												$field = $component["data"];
												
												if (($field["page"] == $p) && ($field["type"] == "ItemId")) {
													if ($field["hiddenForm"]) {
														$editors .= "
															{
																id: 'Itemid',
									                			xtype:'hidden',
															    name: 'Itemid',
															    value: ''
															},
														";
													} else {
														$editors .= "
															{
																id: 'Itemid',
																xtype:'numberfield',
																readOnly:true,
											                    fieldLabel: '".$field["title"]."',
											                    name: '".$field["name"]."',
											                    value: ''
											                    ".$field["properties"]."
											                },";
													}
												}
												
												if (($field["page"] == $p) && (!$field["hiddenForm"]) && (!$field["ordering"])) {
													
													switch ($field["type"]) {
														case "char":
														case "varchar":
															switch ($field["ptype"]) {
																case "textarea":
																	$editors .= "
																	{
																		xtype:'textarea',
													                    fieldLabel: '".$field["title"]."',
													                    name: '".$field["name"]."',
													                    allowBlank:".$field["allowBlank"].",
													                    value: '',
													                    width: ".$field["width"].",
													                    height: ".$field["height"].",
													                    grow:false
													                    ".$field["properties"]."
													                },";
																	break;
																default:
																	$editors .= "
																	{
																		xtype:'textfield',
													                    fieldLabel: '".$field["title"]."',
													                    name: '".$field["name"]."',
													                    allowBlank:".$field["allowBlank"].",
													                    value: ''
													                    ".$field["properties"]."
													                },";
																	break;
															}
															break;
														case "int":
															switch ($field["ptype"]) {
																case "combo":
																		$data = "";
																		$default = "";
																		if (is_array($field["items"])) {
																			foreach ($field["items"] as $fieldItem) {
																				if ($fieldItem[0] == $field["default"]) {
																					$default = $fieldItem[1];
																				}
																				$data .= "['".$fieldItem[0]."','".$fieldItem[1]."'],";
																			}
																			$data = substr($data, 0, strlen($data)-1);
																		}
																	
																		$editors .= "
																		{
																			xtype:'combo',
																			name: '".$field["name"]."_text',
														                    fieldLabel: '".$field["title"]."',
														                    hiddenName: '".$field["name"]."',
														                    hiddenValue: '".$field["default"]."',
														                    allowBlank:".$field["allowBlank"].",
														                    emptyText: '".$default."',
														                    mode: 'local',
																	        triggerAction: 'all',
																	        selectOnFocus:true,
																	        valueField:'id',
						        											displayField:'name',
																	        store: new Ext.data.SimpleStore({
																	        	fields: ['id', 'name'],
																	        	data : [
																	        			".$data."
											       									   ]
																	    	})
																	    	".$field["properties"]."
														                },";
																	break;
																default:
																	$editors .= "
																		{
																			xtype:'numberfield',
																			allowDecimals:false,
														                    fieldLabel: '".$field["title"]."',
														                    name: '".$field["name"]."',
														                    allowBlank:".$field["allowBlank"].",
														                    value: ''
														                    ".$field["properties"]."
														                },";
																	break;
															}
															break;
														case "float":
															$editors .= "
																{
																	xtype:'numberfield',
																	allowDecimals:true,
												                    fieldLabel: '".$field["title"]."',
												                    name: '".$field["name"]."',
												                    allowBlank:".$field["allowBlank"].",
												                    value: ''
												                    ".$field["properties"]."
												                },";
															break;
														case "date":
														case "datetime":
															$editors .= "
																{
																	xtype:'datefield',
												                    fieldLabel: '".$field["title"]."',
												                    name: '".$field["name"]."',
												                    allowBlank:".$field["allowBlank"].",
												                    value: '',
													                format: '".$field["format"]."'
													                ".$field["properties"]."
												                },";
															break;
														case "text":
															$editors .= "
																{
																	xtype:'htmleditor',
												                    fieldLabel: '".$field["title"]."',
												                    name: '".$field["name"]."',
												                    allowBlank:".$field["allowBlank"].",
												                    value: ''";
															
															if ($field["height"] != "") {
																$editors .= ",height:".$field["height"];
															}
															
															$editors .= "
																	".$field["properties"]."
												                },";
															break;
														case "tinyint":
															$editors .= "
																{
																	xtype:'checkbox',
												                    fieldLabel: '".$field["title"]."',
												                    name: '".$field["name"]."',
												                    allowBlank:".$field["allowBlank"].",
												                    value: ''
												                    ".$field["properties"]."
												                },";
															break;
													}
												}
												break;
											case "relation":
												$relation = $component["data"];
												if (($relation["page"] == $p) && (!$relation["hiddenForm"])) {
													switch ($relation["type"]) {
														case "OneOnOne":
															switch ($relation["control"]) {
																case "lookup":
																	$editors .= "
																		{
																			xtype:'hidden',
																			id:'editorWindowForm_Field_".$relation["name"]."',
																		    name: '".$relation["name"]."',
																		    value: ''
																		},
																		new Ext.form.TwinTriggerField(
																		{
																			id:'editorWindowForm_Field_".$relation["name"]."_display',
														                    fieldLabel: '".$relation["title"]."',
														                    name: '".$relation["name"]."_display',
														                    trigger1Class:'x-form-clear-trigger',
														                    trigger2Class:'x-form-search-trigger',
														                    allowBlank:".$relation["allowBlank"].",
														                    value: '',
														                    hideMode: 'offsets',
														                    anchor: '-10',
														                    onTrigger1Click: (function() {
														                    	Ext.getCmp('editorWindowForm_Field_".$relation["name"]."').setValue('');
														                    	this.setValue('');
														                    }),
														                    onTrigger2Click: (function() {
														                    	Ext.getCmp('lookupGrid_".$relation["name"]."').getStore().reload();
														                    	Ext.getCmp('lookupWindow_".$relation["name"]."').show();
														                    })
														                }),";
																	break;
																case "combo":
																	
																	$editors .= "
																		{
																			xtype:'combo',
																			id:'editorWindowForm_Field_".$relation["name"]."_display',
														                    fieldLabel: '".$relation["title"]."',
														                    name: '".$relation["name"]."_display',
														                    allowBlank:".$field["allowBlank"].",
														                    value: '',
																		    store: new Ext.data.Store({
																		     	autoLoad: true, 
														                    	proxy: new Ext.data.HttpProxy({
														                     		url: '".$CI->config->item('base_url').$CI->config->item('index_page').'/'.$this->controller->name."/lookup_json_list'
														                     	}),
														                 		reader: new Ext.data.JsonReader({
														                 			root: 'items',
														                 			totalProperty: 'totalCount',
														                 			id: 'Itemid'
														                 		}, [
																					{name: 'Itemid', mapping: 'Itemid'},
														                 			{name: '".$relation["name"]."_display', mapping: '".$relation["name"]."_display'}
														                 		]),
																				baseParams: {limit:30, relation:'".$relation["name"]."'}
																			}),
																		    valueField: 'Itemid',
															                displayField: '".$relation["name"]."_display',
																		    typeAhead: true,
																		    mode: 'remote',
																		    triggerAction: 'all',
																		    selectOnFocus:true,
																		    allowBlank:".$field["allowBlank"].",
																		    hiddenName: '".$relation["name"]."'
														                },";
																	
																	break;
															}
															break;
														case "OneToMany":
															
															$editors .= "
															{
																xtype:'panel',
																width: ".($this->controller->presentation->editorWindow["width"] - 40).",
																items: [
																	Ext.getCmp('listViewOneToManyGrid_".$relation["name"]."')
																]
															},
															";
															
															break;
													}
												}
												
												break;
											case "custom":
												$custom = $component["data"];
												if ($custom["page"] == $p) {
													$editors .= call_user_func(array(&$this->controller, $custom["function"]));
												}
												
												break;
											case "special":
												$special = $component["data"];
												if ($special["page"] == $p) {
													switch ($special["type"]) {
														case "line-break":
															
															$editors .= "
															{
																xtype: 'label',
											                    html: '<hr>'
															},
															";
															
															break;
													}
												}
												break;
											
										}
										
									}
									$editors = substr($editors, 0, strlen($editors)-1);
									echo $editors;
								?>
						        ]
			            	}
					<?php
							if ($p < $pageCount) echo ",\n";		
						}
					?>
						]
			    	}]
					
				});
				
				editorWindow = new Ext.Window({
					id		 	: 'editorWindow',
		            title    	: '<?= $this->controller->presentation->editorWindow["title"]; ?>',
		            closable 	: true,
		            closeAction	: 'hide',
		            width    	: <?= $this->controller->presentation->editorWindow["width"]; ?>,
		            height   	: <?= $this->controller->presentation->editorWindow["height"]; ?>,
		            //border : false,
		            plain    	: true,
		            items    	: [tabs],
		            autoScroll	:true,
					buttons: [{
			            text: 'Save',
			            icon:'<?= $CI->config->item('base_url')."images/ppo/save.png"; ?>',
			        	cls:'x-btn-text-icon',
			            handler: (function() {
			            	if (tabs.getForm().isValid()) {
				            	
			            		Ext.getCmp('editorWindowForm').getForm().submit({
				                	url:'<?= Extjs_renderer::ajaxurl();; ?><?= $this->controller->name; ?>/post', 
				                	waitMsg:'Saving',
				                	disableCaching: true,
				                	success:function(form, action) {
			            				editorWindow.hide();
			            				Ext.getCmp('listViewGrid').getStore().reload();
				                	},
				                	failure:function(form, action) {
				                		var jsonData = Ext.util.JSON.decode(action.response.responseText);
				                		Ext.MessageBox.alert('Error', jsonData.errors.reason);
									}
				                });
								
		                	}
			            })
			        },{
			            text: 'Cancel',
			            icon:'<?= $CI->config->item('base_url')."images/ppo/cancel.png"; ?>',
			        	cls:'x-btn-text-icon',
			            handler: (function() {
			            	editorWindow.hide();
			            })
			        }]
		        });
			});
		</script>
		<?php
	}
	
	function renderEditorWindowRelation() {
		//$this->_renderEditorWindowOneToManyRelationControls();
		
		$CI =& get_instance();
		
		
		foreach ($this->controller->presentation->relations as $relation) {
			
			switch ($relation["type"]) {
				case "OneOnOne":
					break;
				case "OneToMany":
					
					$presentation = $relation["presentation"];
					$pageCount = $presentation->getPageCount();
					$idHiddenRendered = false;
					?>
					<script type="text/javascript">
						Ext.onReady(function(){
			
							tabsRelation_<?= $relation["name"]; ?> = new Ext.FormPanel({
								id:'editorWindowFormRelation_<?= $relation["name"]; ?>',
						        frame:true,
						        border:false,
						        width: 'auto',
						        bodyStyle:'background:transparent;',
						        fileUpload: true,
						        labelAlign: '<?= $presentation->editorWindow["labelStyle"]["labelAlign"]; ?>',
			        			labelPad: <?= $presentation->editorWindow["labelStyle"]["labelPad"]; ?>,
						        items: [{
									xtype:'tabpanel',
						            activeTab: 0,
						            defaults:{height:'auto', autoHeight:true, bodyStyle:'background:transparent; padding:10px'},
						            border:false,
						            bodyStyle:'background:transparent;',
						            layoutOnTabChange:true,
						            items:[
								<?php 
									for ($p=1;$p<=$pageCount;$p++) {
										if (isset($presentation->pages[$p-1])) {
											$page = $presentation->pages[$p-1];
											$pageTitle = $page["title"];
										} else {
											$pageTitle = "Page ".$p; 
											$page["layout"] = 'form';
											$page["labelWidth"] = 230;
											$page["controlWidth"] = 230;
										}
								?>
							            {
							                title:'<?= $pageTitle; ?>',
							                layout:'<?= $page["layout"]; ?>',
							                labelWidth : <?= $page["labelWidth"]; ?>,
				                			defaults: { width : <?= $page["controlWidth"]; ?>},
							                defaultType: 'textfield',
							                deferredRender: true,
							                items: [
							                <?php
							                	if (!$idHiddenRendered) {
							                		$idHiddenRendered = true;
							                		?>
							                		{
														xtype:'hidden',
													    name: 'Itemid',
													    value: ''
													},
							                		<?php	
							                	}
												$editors = "";
												
												foreach ($presentation->orderFormComponents() as $component) {
										
													switch ($component["type"]) {
														case "field":
															$field = $component["data"];
															
															if ($field["type"] == "ItemId") {
																$editors .= "
																	{
																		id: 'Itemid',
																		xtype:'numberfield',
																		readOnly:true,
													                    fieldLabel: '".$field["title"]."',
													                    name: '".$field["name"]."',
													                    value: ''
													                    ".$field["properties"]."
													                },";
															}
															
															if (($field["page"] == $p) && (!$field["hiddenForm"]) && (!$field["ordering"])) {
																
																switch ($field["type"]) {
																	case "char":
																	case "varchar":
																		switch ($field["special"]) {
																			case "image":
																				$editors .= "
																                    {
																			            xtype: 'fileuploadfield',
																			            id: '".$field["name"]."',
																			            emptyText: 'Select an image',
																			            fieldLabel: '".$field["title"]."',
																			            name: '".$field["name"]."',
																			            buttonCfg: {
																			                text: '',
																			                iconCls: 'upload-icon'
																			            }
																			            ".$field["properties"]."
																			        },
																	                ";
																				break;
																			default:
																				case "varchar":
																					switch ($field["ptype"]) {
																						case "textarea":
																							$editors .= "
																							{
																								xtype:'textarea',
																			                    fieldLabel: '".$field["title"]."',
																			                    name: '".$field["name"]."',
																			                    allowBlank:".$field["allowBlank"].",
																			                    value: '',
																			                    width: ".$field["width"].",
																			                    height: ".$field["height"].",
																			                    grow:false
																			                    ".$field["properties"]."
																			                },";
																							break;
																						default:
																							$editors .= "
																							{
																								xtype:'textfield',
																			                    fieldLabel: '".$field["title"]."',
																			                    name: '".$field["name"]."',
																			                    allowBlank:".$field["allowBlank"].",
																			                    value: ''
																			                    ".$field["properties"]."
																			                },";
																							break;
																					}
																				break;
																		}
																		break;
																	case "int":
																		$editors .= "
																			{
																				xtype:'numberfield',
																				allowDecimals:false,
															                    fieldLabel: '".$field["title"]."',
															                    name: '".$field["name"]."',
															                    allowBlank:".$field["allowBlank"].",
															                    value: ''
															                    ".$field["properties"]."
															                },";
																		break;
																	case "float":
																		$editors .= "
																			{
																				xtype:'numberfield',
																				allowDecimals:true,
															                    fieldLabel: '".$field["title"]."',
															                    name: '".$field["name"]."',
															                    allowBlank:".$field["allowBlank"].",
															                    value: ''
															                    ".$field["properties"]."
															                },";
																		break;
																	case "date":
																	case "datetime":
																		$editors .= "
																			{
																				xtype:'datefield',
															                    fieldLabel: '".$field["title"]."',
															                    name: '".$field["name"]."',
															                    allowBlank:".$field["allowBlank"].",
															                    value: '',
												                    			format: '".$field["format"]."'
												                    			".$field["properties"]."
															                },";
																		break;
																	case "text":
																		$editors .= "
																			{
																				xtype:'htmleditor',
															                    fieldLabel: '".$field["title"]."',
															                    name: '".$field["name"]."',
															                    allowBlank:".$field["allowBlank"].",
															                    value: ''";
																		
																		if ($field["height"] != "") {
																			$editors .= ",height:".$field["height"];
																		}
																		
																		$editors .= "
																				".$field["properties"]."
															                },";
																		break;
																	case "tinyint":
																		$editors .= "
																			{
																				xtype:'checkbox',
															                    fieldLabel: '".$field["title"]."',
															                    name: '".$field["name"]."',
															                    allowBlank:".$field["allowBlank"].",
															                    value: ''
															                    ".$field["properties"]."
															                },";
																		break;
																}
															}
															break;
															
														case "relation":
															$presentationRelation = $component["data"];			

															if (($presentationRelation["page"] == $p) && (!$relation["hiddenForm"])) {
																switch ($presentationRelation["type"]) {
																	case "OneOnOne":
																		switch ($presentationRelation["control"]) {
																			case "lookup":
																				/*
																					TO DO: enable selecting different parent from lookup
																				*/
																				if ($presentationRelation["name"] == $this->controller->persistent->name) {
																					$editors .= "
																						{
																							xtype:'hidden',
																							id:'editorWindowFormRelation_".$relation["name"]."_Field_".$presentationRelation["name"]."',
																						    name: '".$presentationRelation["name"]."',
																						    value: ''
																						},
																						";
																					break;
																				} else {
																					$editors .= "
																						{
																							xtype:'hidden',
																							id:'editorWindowFormRelation_".$relation["name"]."_Field_".$presentationRelation["name"]."',
																						    name: '".$presentationRelation["name"]."',
																						    value: ''
																						},
																						new Ext.form.TwinTriggerField(
																						{
																							id:'editorWindowFormRelation_".$relation["name"]."_Field_".$presentationRelation["name"]."_display',
																		                    fieldLabel: '".$presentationRelation["title"]."',
																		                    name: '".$presentationRelation["name"]."_display',
																		                    trigger1Class:'x-form-clear-trigger',
																		                    trigger2Class:'x-form-search-trigger',
																		                    allowBlank:".$presentationRelation["allowBlank"].",
																		                    value: '',
																		                    hideMode: 'offsets',
																		                    anchor: '-10',
																		                    onTrigger1Click: (function() {
																		                    	Ext.getCmp('editorWindowFormRelation_".$relation["name"]."_Field_".$presentationRelation["name"]."').setValue('');
																		                    	this.setValue('');
																		                    }),
																		                    onTrigger2Click: (function() {
																		                    	Ext.getCmp('lookupGridRelation_".$relation["name"]."_".$presentationRelation["name"]."').getStore().reload();
																		                    	Ext.getCmp('lookupWindowRelation_".$relation["name"]."_".$presentationRelation["name"]."').show();
																		                    })
																		                }),";
																					break;
																				}
																			case "combo":
																				
																				$editors .= "
																					{
																						xtype:'combo',
																						id:'editorWindowFormRelation_".$presentationRelation["name"]."_Field_".$presentationRelation["name"]."_display',
																	                    fieldLabel: '".$presentationRelation["title"]."',
																	                    name: '".$presentationRelation["name"]."_display',
																	                    allowBlank:".$field["allowBlank"].",
																	                    value: '',
																					    store: new Ext.data.Store({
																					     	autoLoad: true, 
																	                    	proxy: new Ext.data.HttpProxy({
																	                     		url: '".$CI->config->item('base_url').$CI->config->item('index_page').'/'.$this->controller->name."/lookup_json_list'
																	                     	}),
																	                 		reader: new Ext.data.JsonReader({
																	                 			root: 'items',
																	                 			totalProperty: 'totalCount',
																	                 			id: 'Itemid'
																	                 		}, [
																								{name: 'Itemid', mapping: 'Itemid'},
																	                 			{name: '".$presentationRelation["name"]."_display', mapping: '".$presentationRelation["name"]."_display'}
																	                 		]),
																							baseParams: {limit:30, relation:'".$presentationRelation["name"]."'}
																						}),
																					    valueField: 'Itemid',
																		                displayField: '".$presentationRelation["name"]."_display',
																					    typeAhead: true,
																					    mode: 'remote',
																					    triggerAction: 'all',
																					    selectOnFocus:true,
																					    allowBlank:".$field["allowBlank"].",
																					    hiddenName: '".$presentationRelation["name"]."'
																	                },";
																				
																				break;
																		}
																		break;
																}
															}
															
															break;
															
														case "special":
															$special = $component["data"];
															if ($special["page"] == $p) {
																switch ($special["type"]) {
																	case "line-break":
																		
																		$editors .= "
																		{
																			xtype: 'label',
														                    html: '<hr>'
																		},
																		";
																		
																		break;
																}
															}
															break;
															
													}	
													
												}

												$editors = substr($editors, 0, strlen($editors)-1);
												echo $editors;
											?>
									        ]
						            	}
								<?php
										if ($p < $pageCount) echo ",\n";		
									}
								?>
									]
						    	}]
								
							});
							
							editorWindowRelation_<?= $relation["name"]; ?> = new Ext.Window({
								id		 	: 'editorWindowRelation_<?= $relation["name"]; ?>',
					            title    	: '<?= $presentation->editorWindow["title"]; ?>',
					            closable 	: true,
					            closeAction	: 'hide',
					            width    	: <?= $presentation->editorWindow["width"]; ?>,
					            height   	: <?= $presentation->editorWindow["height"]; ?>,
					            //border : false,
					            plain    	: true,
					            items    	: [tabsRelation_<?= $relation["name"]; ?>],
								buttons: [{
						            text: 'Save',
						            icon:'<?= $CI->config->item('base_url')."images/ppo/save.png"; ?>',
			        				cls:'x-btn-text-icon',
						            handler: (function() {
						            	if (tabsRelation_<?= $relation["name"]; ?>.getForm().isValid()) {
						            		Ext.getCmp('editorWindowFormRelation_<?= $relation["name"]; ?>').getForm().submit({
							                	url:'<?= Extjs_renderer::ajaxurl();; ?><?= $this->controller->name; ?>/post', 
							                	params:{ relation: '<?= $relation["name"]; ?>' },
							                	waitMsg:'Saving',
							                	disableCaching: true,
							                	success:function(form, action) {
						            				editorWindowRelation_<?= $relation["name"]; ?>.hide();
						            				Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getStore().reload();
							                	},
							                	failure:function(form, action) {
							                		var jsonData = Ext.util.JSON.decode(action.response.responseText);
							                		Ext.MessageBox.alert('Error', jsonData.errors.reason);
												}
							                });
											
					                	}
						            })
						        },{
						            text: 'Cancel',
						            icon:'<?= $CI->config->item('base_url')."images/ppo/cancel.png"; ?>',
			        				cls:'x-btn-text-icon',
						            handler: (function() {
						            	editorWindowRelation_<?= $relation["name"]; ?>.hide();
						            })
						        }]
					        });
					        
						});
					</script>
					<?php
				
					break;
				case "ManyToMany":
					break;
			}
		}
	}
	
	function renderLookupWindows() {
		$CI =& get_instance();
		
		if (count($this->controller->presentation->relations) == 0) return false;
		foreach ($this->controller->presentation->relations as $relation) {
			?>
			<script type="text/javascript">
				Ext.onReady(function(){

					lookupGridColumnModel_<?= $relation["name"]; ?> = new Ext.grid.ColumnModel([
                    	{header: '<?= $relation["title"]; ?>', width: <?= abs($relation["lookupWindow"]["width"] - 45); ?>, dataIndex: '<?= $relation["name"]; ?>_display'}
                    ]);
                            			        
                    lookupGridDataStore_<?= $relation["name"]; ?> = new Ext.data.Store({
                    	proxy: new Ext.data.HttpProxy({
                     		url: '<?= Extjs_renderer::ajaxurl();; ?><?= $this->controller->name; ?>/lookup_json_list'
                     	}),
                 		reader: new Ext.data.JsonReader({
                 			root: 'items',
                 			totalProperty: 'totalCount',
                 			id: 'Itemid'
                 		}, [
							{name: '<?= $relation["name"]; ?>_id', mapping: 'Itemid'},
                 			{name: '<?= $relation["name"]; ?>_display', mapping: '<?= $relation["name"]; ?>_display'}
                 		]),
						baseParams: {limit:30, relation:'<?= $relation["name"]; ?>'}
					});

                    lookupGrid_<?= $relation["name"]; ?> = new Ext.grid.GridPanel({
		        		id:'lookupGrid_<?= $relation["name"]; ?>',
		        		width: 'auto',
		        		region: 'center',
				        cm: lookupGridColumnModel_<?= $relation["name"]; ?>,
				        ds: lookupGridDataStore_<?= $relation["name"]; ?>,
				        trackMouseOver:false,
				        sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
				        loadMask: true,
				        bbar: new Ext.PagingToolbar({
				            store: lookupGridDataStore_<?= $relation["name"]; ?>,
				            pageSize: 10,
				            displayInfo: true,
				            displayMsg: 'Topics {0} - {1} of {2}',
				            emptyMsg: "No topics to display"
				        })
				    });

				    lookupFilter_<?= $relation["name"]; ?> = new Ext.form.TriggerField({
						id:'lookupFilter_<?= $relation["name"]; ?>',
						emptyText:'Filter',
						triggerClass:'x-form-search-trigger',
						width: <?= abs($relation["lookupWindow"]["width"] - 30); ?>,
            			onTriggerClick: (function() {
            				lookupGridDataStore_<?= $relation["name"]; ?>.baseParams.searchVal = this.getValue();
            				lookupGridDataStore_<?= $relation["name"]; ?>.reload();
            			})
				    });

				    lookupWindowTopPanel_<?= $relation["name"]; ?> = new Ext.Panel({
				        border:false,
				        region: 'north',
				        bodyStyle:'background:transparent; padding-top: 3px;',
				        height: 30,
				        items: [lookupFilter_<?= $relation["name"]; ?>]
				    });

					lookupWindow_<?= $relation["name"]; ?> = new Ext.Window({
						id		 	: 'lookupWindow_<?= $relation["name"]; ?>',
			            title    	: '<?= $relation["lookupWindow"]["title"]; ?>',
			            closable 	: true,
			            closeAction	: 'hide',
			            width    	: <?= $relation["lookupWindow"]["width"]; ?>,
			            height   	: <?= $relation["lookupWindow"]["height"]; ?>,
			            //border : false,
			            plain    	: true,
			            layout	 	: 'border',
			            items    : [
						        lookupWindowTopPanel_<?= $relation["name"]; ?>,
					        	lookupGrid_<?= $relation["name"]; ?>
					        	],
						buttons: [{
				            text: 'OK',
				            handler: (function() {
				            	try {
				            		Ext.getCmp('editorWindowForm_Field_<?= $relation["name"]; ?>').setValue(Ext.getCmp('lookupGrid_<?= $relation["name"]; ?>').getSelectionModel().getSelected().get('<?= $relation["name"]; ?>_id'));
					            	Ext.getCmp('editorWindowForm_Field_<?= $relation["name"]; ?>_display').setValue(Ext.getCmp('lookupGrid_<?= $relation["name"]; ?>').getSelectionModel().getSelected().get('<?= $relation["name"]; ?>_display'));
				            	} catch (err) { }
					            <?php
					            if ($relation["search"]) {
					            	?>
					            	try {
						            	Ext.getCmp('searchWindowFormField_<?= $relation["name"]; ?>').setValue(Ext.getCmp('lookupGrid_<?= $relation["name"]; ?>').getSelectionModel().getSelected().get('<?= $relation["name"]; ?>_id'));
							            Ext.getCmp('searchWindowFormField_<?= $relation["name"]; ?>_display').setValue(Ext.getCmp('lookupGrid_<?= $relation["name"]; ?>').getSelectionModel().getSelected().get('<?= $relation["name"]; ?>_display'));	
							        } catch (err) { }
					            	<?php
								}
					            ?>
				            	lookupWindow_<?= $relation["name"]; ?>.hide();
				            })
				        },{
				            text: 'Cancel',
				            icon:'<?= $CI->config->item('base_url')."images/ppo/cancel.png"; ?>',
			        		cls:'x-btn-text-icon',
				            handler: (function() {
				            	lookupWindow_<?= $relation["name"]; ?>.hide();
				            })
				        }]
			        });
				});
			</script>
			<?php
		}
	}
	
	function renderLookupWindowsRelation() {
		
		$CI =& get_instance();
		
		
		foreach ($this->controller->presentation->relations as $relation) {
			
			switch ($relation["type"]) {
				case "OneOnOne":
					break;
				case "OneToMany":
					
					$presentation = $relation["presentation"];

					foreach ($presentation->relations as $presentationRelation) {
						?>
						<script type="text/javascript">
							Ext.onReady(function(){
			
								lookupGridColumnModelRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?> = new Ext.grid.ColumnModel([
			                    	{header: '<?= $presentationRelation["title"]; ?>', width: <?= abs($presentationRelation["lookupWindow"]["width"] - 45); ?>, dataIndex: '<?= $presentationRelation["name"]; ?>_display'}
			                    ]);
			                            			        
			                    lookupGridDataStoreRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?> = new Ext.data.Store({
			                    	proxy: new Ext.data.HttpProxy({
			                     		url: '<?= Extjs_renderer::ajaxurl();; ?><?= $this->controller->name; ?>/lookup_json_list'
			                     	}),
			                 		reader: new Ext.data.JsonReader({
			                 			root: 'items',
			                 			totalProperty: 'totalCount',
			                 			id: 'Itemid'
			                 		}, [
										{name: '<?= $presentationRelation["name"]; ?>_id', mapping: 'Itemid'},
			                 			{name: '<?= $presentationRelation["name"]; ?>_display', mapping: '<?= $presentationRelation["name"]; ?>_display'}
			                 		]),
									baseParams: {limit:30, relation:'<?= $presentationRelation["name"]; ?>', relationParent:'<?= $relation["name"]; ?>'}
								});
			
			                    lookupGridRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?> = new Ext.grid.GridPanel({
					        		id:'lookupGridRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>',
					        		width: 'auto',
					        		region: 'center',
							        cm: lookupGridColumnModelRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>,
							        ds: lookupGridDataStoreRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>,
							        trackMouseOver:false,
							        sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
							        loadMask: true,
							        bbar: new Ext.PagingToolbar({
							            store: lookupGridDataStoreRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>,
							            pageSize: 10,
							            displayInfo: true,
							            displayMsg: 'Topics {0} - {1} of {2}',
							            emptyMsg: "No topics to display"
							        })
							    });
			
							    lookupFilterRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?> = new Ext.form.TriggerField({
									id:'lookupFilterRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>',
									emptyText:'Filter',
									triggerClass:'x-form-search-trigger',
									width: <?= abs($presentationRelation["lookupWindow"]["width"] - 30); ?>,
			            			onTriggerClick: (function() {
			            				lookupGridDataStoreRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>.baseParams.searchVal = this.getValue();
			            				lookupGridDataStoreRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>.reload();
			            			})
							    });
			
							    lookupWindowTopPanelRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?> = new Ext.Panel({
							        border:false,
							        region: 'north',
							        bodyStyle:'background:transparent; padding-top: 3px;',
							        height: 30,
							        items: [lookupFilterRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>]
							    });
			
								lookupWindowRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?> = new Ext.Window({
									id		 	: 'lookupWindowRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>',
						            title    	: '<?= $presentationRelation["lookupWindow"]["title"]; ?>',
						            closable 	: true,
						            closeAction	: 'hide',
						            width    	: <?= $presentationRelation["lookupWindow"]["width"]; ?>,
						            height   	: <?= $presentationRelation["lookupWindow"]["height"]; ?>,
						            //border : false,
						            plain    	: true,
						            layout	 	: 'border',
						            items    : [
									        lookupWindowTopPanelRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>,
								        	lookupGridRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>
								        	],
									buttons: [{
							            text: 'OK',
							            handler: (function() {
							            	try {
								            	Ext.getCmp('editorWindowFormRelation_<?= $relation["name"]; ?>_Field_<?= $presentationRelation["name"]; ?>').setValue(Ext.getCmp('lookupGridRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>').getSelectionModel().getSelected().get('<?= $presentationRelation["name"]; ?>_id'));
									            Ext.getCmp('editorWindowFormRelation_<?= $relation["name"]; ?>_Field_<?= $presentationRelation["name"]; ?>_display').setValue(Ext.getCmp('lookupGridRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>').getSelectionModel().getSelected().get('<?= $presentationRelation["name"]; ?>_display'));
									        } catch (err) {}
								            <?php
								            if ($presentationRelation["search"]) {
								            	?>
								            	try {
									            	Ext.getCmp('searchWindowFormFieldRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>').setValue(Ext.getCmp('lookupGridRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>').getSelectionModel().getSelected().get('<?= $presentationRelation["name"]; ?>_id'));
										            Ext.getCmp('searchWindowFormFieldRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>_display').setValue(Ext.getCmp('lookupGridRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>').getSelectionModel().getSelected().get('<?= $presentationRelation["name"]; ?>_display'));
								            	} catch (err) {}
								            	<?php
											}
								            ?>
							            	lookupWindowRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>.hide();
							            })
							        },{
							            text: 'Cancel',
							            icon:'<?= $CI->config->item('base_url')."images/ppo/cancel.png"; ?>',
			        					cls:'x-btn-text-icon',
							            handler: (function() {
							            	lookupWindowRelation_<?= $relation["name"]; ?>_<?= $presentationRelation["name"]; ?>.hide();
							            })
							        }]
						        });
							});
						</script>
						<?php
					}
					break;
			}
		}
	}
	
	function renderSearchWindow() {
		$CI =& get_instance();
		
		?>
		<script type="text/javascript">
			Ext.onReady(function(){

				tabsSearch = new Ext.FormPanel({
					id:'editorSearchForm',
			        frame:true,
			        border:false,
			        width: 'auto',
			        bodyStyle:'background:transparent;',
			        items: [
						<?php
							
							$editors = "
							{
								xtype:'hidden',
							    name: 'tsr_dummy',
							    value: ''
							},";
							
							foreach ($this->controller->presentation->fields as $field) {
								if ($field["search"]) {
									switch (strtolower($field["type"])) {
										case "char":
										case "varchar":
											$editors .= "
												{
													id:'searchWindowFormField_".$field["name"]."',
													xtype:'textfield',
								                    fieldLabel: '".$field["title"]."',
								                    name: '".$field["name"]."',
								                    value: ''
								                },";
											break;
										case "int":
											$editors .= "
												{
													id:'searchWindowFormField_".$field["name"]."',
													xtype:'numberfield',
													allowDecimals:false,
								                    fieldLabel: '".$field["title"]."',
								                    name: '".$field["name"]."',
								                    value: ''
								                },";
											break;
										case "float":
											$editors .= "
												{
													id:'searchWindowFormField_".$field["name"]."',
													xtype:'numberfield',
													allowDecimals:true,
								                    fieldLabel: '".$field["title"]."',
								                    name: '".$field["name"]."',
								                    value: ''
								                },";
											break;
										case "date":
										case "datetime":
											$editors .= "
												{
													id:'searchWindowFormField_".$field["name"]."',
													xtype:'datefield',
								                    fieldLabel: '".$field["title"]."',
								                    name: '".$field["name"]."',
								                    value: '',
											        format: '".$field["format"]."'
								                },";
											break;
										case "tinyint":
											$editors .= "
												{
													id:'searchWindowFormField_".$field["name"]."',
													xtype:'checkbox',
								                    fieldLabel: '".$field["title"]."',
								                    name: '".$field["name"]."',
								                    value: ''
								                },";
											break;
									}
								}
							}
							foreach ($this->controller->presentation->relations as $relation) {
								if ($relation["search"]) {								
									switch ($relation["type"]) {
										case "OneOnOne":
											switch (strtolower($relation["control"])) {
												case "lookup":
													
													$editors .= "
														{
															xtype:'hidden',
															id:'searchWindowFormField_".$relation["name"]."',
														    name: '".$relation["name"]."',
														    value: ''
														},
														new Ext.form.TwinTriggerField(
																{
																	id:'searchWindowFormField_".$relation["name"]."_display',
												                    fieldLabel: '".$relation["title"]."',
												                    name: '".$relation["name"]."_display',
												                    trigger1Class:'x-form-clear-trigger',
												                    trigger2Class:'x-form-search-trigger',
												                    allowBlank:".$relation["allowBlank"].",
												                    value: '',
												                    hideMode: 'offsets',
												                    anchor: '-10',
												                    onTrigger1Click: (function() {
												                    	Ext.getCmp('searchWindowFormField_".$relation["name"]."').setValue('');
												                    	this.setValue('');
												                    }),
												                    onTrigger2Click: (function() {
												                    	Ext.getCmp('lookupGrid_".$relation["name"]."').getStore().reload();
										                    			Ext.getCmp('lookupWindow_".$relation["name"]."').show();
												                    })
												                }),";
													break;
												case "combo":
															
													$editors .= "
														{
															xtype:'combo',
															id:'searchWindowFormField_".$relation["name"]."',
										                    fieldLabel: '".$relation["title"]."',
										                    name: '".$relation["name"]."_display',
										                    allowBlank:".$field["allowBlank"].",
										                    value: '',
														    store: new Ext.data.Store({
										                    	proxy: new Ext.data.HttpProxy({
										                     		url: '".$CI->config->item('base_url').$CI->config->item('index_page').'/'.$this->controller->name."/lookup_json_list'
										                     	}),
										                 		reader: new Ext.data.JsonReader({
										                 			root: 'items',
										                 			totalProperty: 'totalCount',
										                 			id: 'Itemid'
										                 		}, [
																	{name: 'Itemid', mapping: 'Itemid'},
										                 			{name: '".$relation["name"]."_display', mapping: '".$relation["name"]."_display'}
										                 		]),
																baseParams: {limit:30, relation:'".$relation["name"]."'}
															}),
														    valueField: 'Itemid',
											                displayField: '".$relation["name"]."_display',
														    typeAhead: true,
														    mode: 'remote',
														    triggerAction: 'all',
														    selectOnFocus:true,
														    hiddenName: '".$relation["name"]."'
										                },";
		
													break;
											}
											break;
										case "OneToMany":
										
											break;
									}
								}	
							}
							$editors = substr($editors, 0, strlen($editors)-1);
							echo $editors;
						?>
			    	]
					
				});
				
				searchWindow = new Ext.Window({
					id		 	: 'searchWindow',
		            title    	: '<?= $this->controller->presentation->searchWindow["title"]; ?>',
		            closable 	: true,
		            closeAction	: 'hide',
		            width    	: <?= $this->controller->presentation->searchWindow["width"]; ?>,
		            height   	: <?= $this->controller->presentation->searchWindow["height"]; ?>,
		            //border : false,
		            plain    	: true,
		            items    	: [tabsSearch],
		            autoScroll:true,
					buttons: [{
			            text: 'Search',
			            icon:'<?= $CI->config->item('base_url')."images/ppo/search.png"; ?>',
						cls:'x-btn-text-icon',
			            handler: (function() {
	                		var params = '';
            				<?php
            					foreach($this->controller->presentation->fields as $field) {
            						if ($field["search"]) {
									?>
							if (Ext.getCmp('searchWindowFormField_<?= $field["name"]; ?>').getValue() != '') {
								params += '<?= $field["name"]; ?>: ' + Ext.getCmp('searchWindowFormField_<?= $field["name"]; ?>').getValue() + ',';
								Ext.getCmp('listViewGrid').getStore().baseParams.<?= $field["name"]; ?>= Ext.getCmp('searchWindowFormField_<?= $field["name"]; ?>').getValue();
							} else {
								Ext.getCmp('listViewGrid').getStore().baseParams.<?= $field["name"]; ?>= '';
							}
									<?php
            						}
            					}
            				?>
            				<?php
                					foreach($this->controller->presentation->relations as $relation) {
                						if ($relation["search"]) {
                							switch ($relation["type"]) {
												case "OneOnOne":
					    							?>
					    							if (Ext.getCmp('searchWindowFormField_<?= $relation["name"]; ?>').getValue() != '') {
					    								params += '<?= $relation["name"]; ?>: ' + Ext.getCmp('searchWindowFormField_<?= $relation["name"]; ?>').getValue() + ',';
					    								Ext.getCmp('listViewGrid').getStore().baseParams.<?= $relation["name"]; ?>= Ext.getCmp('searchWindowFormField_<?= $relation["name"]; ?>').getValue();
					    							} else {
					    								Ext.getCmp('listViewGrid').getStore().baseParams.<?= $relation["name"]; ?>= '';
					    							}
					    							<?php
					    							break;
												case "OneToMany":
													break;
                							}
                						}
                					}
               				?>
            				params = params.substring(0,params.length-1);
            				searchWindow.hide();
            				Ext.getCmp('listViewGrid').getStore().reload();
			            })
			        },{
			            text: 'Cancel',
			            icon:'<?= $CI->config->item('base_url')."images/ppo/cancel.png"; ?>',
			        	cls:'x-btn-text-icon',
			            handler: (function() {
			            	searchWindow.hide();
			            })
			        },{
			            text: 'Reset',
			            handler: (function() {
			            	Ext.getCmp('editorSearchForm').getForm().reset();
			            })
			        }]
		        });
			});
		</script>
		<?php
	}
	
	function renderSearchWindowRelation() {
		
		$CI =& get_instance();
		
		
		foreach ($this->controller->presentation->relations as $relation) {
			
			switch ($relation["type"]) {
				case "OneOnOne":
					break;
				case "OneToMany":
					
					$presentation = $relation["presentation"];
					$pageCount = $presentation->getPageCount();
					$idHiddenRendered = false;
					?>

					<script type="text/javascript">
						Ext.onReady(function(){
			
							tabsSearchRelation_<?= $relation["name"]; ?> = new Ext.FormPanel({
								id:'editorSearchFormRelation_<?= $relation["name"]; ?>',
						        frame:true,
						        border:false,
						        width: 'auto',
						        bodyStyle:'background:transparent;',
						        items: [
									<?php	
										$editors = "
										{
											xtype:'hidden',
										    name: 'tsr_dummy',
										    value: ''
										},";
										foreach ($presentation->fields as $field) {
											if ($field["search"]) {
												switch (strtolower($field["type"])) {
													case "char":
													case "varchar":
														$editors .= "
															{
																id:'searchWindowFormFieldRelation_".$relation["name"]."_".$field["name"]."',
																xtype:'textfield',
											                    fieldLabel: '".$field["title"]."',
											                    name: '".$field["name"]."',
											                    value: ''
											                },";
														break;
													case "int":
														$editors .= "
															{
																id:'searchWindowFormFieldRelation_".$relation["name"]."_".$field["name"]."',
																xtype:'numberfield',
																allowDecimals:false,
											                    fieldLabel: '".$field["title"]."',
											                    name: '".$field["name"]."',
											                    value: ''
											                },";
														break;
													case "float":
														$editors .= "
															{
																id:'searchWindowFormFieldRelation_".$relation["name"]."_".$field["name"]."',
																xtype:'numberfield',
																allowDecimals:true,
											                    fieldLabel: '".$field["title"]."',
											                    name: '".$field["name"]."',
											                    value: ''
											                },";
														break;
													case "date":
													case "datetime":
														$editors .= "
															{
																id:'searchWindowFormFieldRelation_".$relation["name"]."_".$field["name"]."',
																xtype:'datefield',
											                    fieldLabel: '".$field["title"]."',
											                    name: '".$field["name"]."',
											                    value: '',
											                    format: '".$field["format"]."'
											                },";
														break;
													case "tinyint":
														$editors .= "
															{
																id:'searchWindowFormFieldRelation_".$relation["name"]."_".$field["name"]."',
																xtype:'checkbox',
											                    fieldLabel: '".$field["title"]."',
											                    name: '".$field["name"]."',
											                    value: ''
											                },";
														break;
												}
											}
										}
										foreach ($presentation->relations as $relationPresentation) {
											if ($relationPresentation["search"]) {
												switch ($relationPresentation["type"]) {
													case "OneToOne":
														switch (strtolower($relationPresentation["control"])) {
															case "lookup":
																$editors .= "
																	{
																		xtype:'hidden',
																		id:'searchWindowFormFieldRelation_".$relation["name"]."_".$relationPresentation["name"]."',
																	    name: '".$relationPresentation["name"]."',
																	    value: ''
																	},
																	{
																		xtype:'trigger',
																		id:'searchWindowFormFieldRelation_".$relation["name"]."_".$relationPresentation["name"]."_display',
													                    fieldLabel: '".$relationPresentation["title"]."',
													                    name: '".$relationPresentation["name"]."_display',
													                    triggerClass:'x-form-search-trigger',
													                    value: '',
													                    onTriggerClick: (function() {
													                    	Ext.getCmp('lookupGrid_".$relationPresentation["name"]."').getStore().reload();
													                    	Ext.getCmp('lookupWindow_".$relationPresentation["name"]."').show();
													                    })
													                },";
																break;
															case "combo":
																		
																$editors .= "
																	{
																		xtype:'combo',
																		id:'searchWindowFormFieldRelation_".$relation["name"]."_".$relationPresentation["name"]."',
													                    fieldLabel: '".$relationPresentation["title"]."',
													                    name: '".$relationPresentation["name"]."_display',
													                    allowBlank:".$field["allowBlank"].",
													                    value: '',
																	    store: new Ext.data.Store({
													                    	proxy: new Ext.data.HttpProxy({
													                     		url: '".$CI->config->item('base_url').$CI->config->item('index_page').'/'.$this->controller->name."/lookup_json_list'
													                     	}),
													                 		reader: new Ext.data.JsonReader({
													                 			root: 'items',
													                 			totalProperty: 'totalCount',
													                 			id: 'Itemid'
													                 		}, [
																				{name: 'Itemid', mapping: 'Itemid'},
													                 			{name: '".$relationPresentation["name"]."_display', mapping: '".$relationPresentation["name"]."_display'}
													                 		]),
																			baseParams: {limit:30, relation:'".$relationPresentation["name"]."'}
																		}),
																	    valueField: 'Itemid',
														                displayField: '".$relationPresentation["name"]."_display',
																	    typeAhead: true,
																	    mode: 'remote',
																	    triggerAction: 'all',
																	    selectOnFocus:true,
																	    hiddenName: '".$relationPresentation["name"]."'
													                },";
					
																break;
														}
														break;
													case "OneToMany":
													
														break;
												}
											}	
										}
										$editors = substr($editors, 0, strlen($editors)-1);
										echo $editors;
									?>
						    	]
								
							});
							
							searchWindowRelation_<?= $relation["name"]; ?> = new Ext.Window({
								id		 	: 'searchWindowRelation_<?= $relation["name"]; ?>',
					            title    	: '<?= $presentation->searchWindow["title"]; ?>',
					            closable 	: true,
					            closeAction	: 'hide',
					            width    	: <?= $presentation->searchWindow["width"]; ?>,
					            height   	: <?= $presentation->searchWindow["height"]; ?>,
					            //border : false,
					            plain    	: true,
					            items    	: [tabsSearchRelation_<?= $relation["name"]; ?>],
								buttons: [{
						            text: 'Save',
						            icon:'<?= $CI->config->item('base_url')."images/ppo/save.png"; ?>',
			        				cls:'x-btn-text-icon',
						            handler: (function() {
				                		var params = '';
			            				<?php
			            					foreach($presentation->fields as $field) {
			            						if ($field["search"]) {
												?>
										if (Ext.getCmp('searchWindowFormFieldRelation_<?= $relation["name"]; ?>_<?= $field["name"]; ?>').getValue() != '') {
											params += '<?= $field["name"]; ?>: ' + Ext.getCmp('searchWindowFormField_<?= $field["name"]; ?>').getValue() + ',';
											Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getStore().baseParams.<?= $field["name"]; ?>= Ext.getCmp('searchWindowFormFieldRelation_<?= $relation["name"]; ?>_<?= $field["name"]; ?>').getValue();
										} else {
											Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getStore().baseParams.<?= $field["name"]; ?>= '';
										}
												<?php
			            						}
			            					}
			            				?>
			            				<?php
			                					foreach($presentation->relations as $relationPresentation) {
			                						if ($relationPresentation["search"]) {
			                							switch ($relationPresentation["type"]) {
															case "OneToOne":
									    						?>
									    							if (Ext.getCmp('searchWindowFormFieldRelation_<?= $relation["name"]; ?>_<?= $relationPresentation["name"]; ?>').getValue() != '') {
									    								params += '<?= $relationPresentation["name"]; ?>: ' + Ext.getCmp('searchWindowFormFieldRelation_<?= $relation["name"]; ?>_<?= $relationPresentation["name"]; ?>').getValue() + ',';
									    								Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getStore().baseParams.<?= $relationPresentation["name"]; ?>= Ext.getCmp('searchWindowFormFieldRelation_<?= $relation["name"]; ?>_<?= $relationPresentation["name"]; ?>').getValue();
									    							} else {
									    								Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getStore().baseParams.<?= $relationPresentation["name"]; ?>= '';
									    							}
									    						<?php
								    							break;
															case "OneToMany":
																break;
			                							}
			                						}
			                					}
			               				?>
			            				params = params.substring(0,params.length-1);
			            				searchWindowRelation_<?= $relation["name"]; ?>.hide();
			            				Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getStore().reload();
						            })
						        },{
						            text: 'Cancel',
						            icon:'<?= $CI->config->item('base_url')."images/ppo/cancel.png"; ?>',
			        				cls:'x-btn-text-icon',
						            handler: (function() {
						            	searchWindowRelation_<?= $relation["name"]; ?>.hide();
						            })
						        },{
						            text: 'Reset',
						            handler: (function() {
						            	Ext.getCmp('editorSearchFormRelation_<?= $relation["name"]; ?>').getForm().reset();
						            })
						        }]
					        });
						});
					</script>
					<?php
					break;
			}
		}
	}
	
	function renderListView() {
		$CI =& get_instance();
		$CI->load->library('session');
		
		?>
		<div id="listView"></div>
		<script type="text/javascript">
			
			function orderingUp(id, relation, datastore_id) {
				Ext.Ajax.request({
		
				   url: '<?= Extjs_renderer::ajaxurl();; ?><?= $this->controller->name; ?>/ordering_up',
				   success: (function(response, options) {
						Ext.getCmp(datastore_id).getStore().reload();
					}),
					params: { Itemid:id, relation: relation, parentid: Ext.getCmp('listViewGrid').getSelectionModel().getSelected().get('Itemid') }
				   
				});
				
			}
			
			function orderingDown(id, relation, datastore_id) {
				
				Ext.Ajax.request({
		
				   url: '<?= Extjs_renderer::ajaxurl();; ?><?= $this->controller->name; ?>/ordering_down',
				   success: (function(response, options) {
						Ext.getCmp(datastore_id).getStore().reload();
					}),
					params: { Itemid:id, relation: relation, parentid: Ext.getCmp('listViewGrid').getSelectionModel().getSelected().get('Itemid') }
				   
				});
				
			}
		
			Ext.onReady(function(){
				
				<?php
				//	render ordering column render function if any of fields are marked as ordering
				?>
				function rendererOrdering(data,cellmd,record,row,col,store) {
			    	return '<a href="#" onclick="orderingUp(' + record.get('Itemid') + ', \'\', \'listViewGrid\')"><img src="<?= $CI->config->item('base_url'); ?>images/ppo/ordering_up.png" alt="up" border="0"></a>&nbsp;<a href="#" onclick="orderingDown(' + record.get('Itemid') + ', \'\', \'listViewGrid\')"><img src="<?= $CI->config->item('base_url'); ?>images/ppo/ordering_down.png" alt="down" border="0"></a>';
			    }
			    
			    <?php
			    //	custom field renderers
			    foreach ($this->controller->presentation->fields as $field) {
			    	if ($field["gridRenderer"] != "") {
			    		?>
			    		function renderer_<?= $field["name"]; ?>(data,cellmd,record,row,col,store) {
					    	return <?= $field["gridRenderer"] ?>;
					    }
			    		<?php
			    	}
			    }
			    ?>
							
				listViewGridColumnModel = new Ext.grid.ColumnModel([
		        	<?php
			        	$_tmp = "";
			        	foreach ($this->controller->presentation->orderFormComponents() as $component) {
							switch ($component["type"]) {
								case "field":
									$field = $component["data"];
					        		if (!$field["hiddenGrid"]) {
					        			$widthGrid = ($field["widthGrid"] != "")?", width: ".$field["widthGrid"]:"";
		       			        		$titleGrid = ($field["titleGrid"] != "")?", header: '".$field["titleGrid"]."'":", header: '".$field["name"]."'";
		       			        		$renderer = ($field["ordering"])?", renderer: rendererOrdering":"";
		       			        		$renderer = ($field["gridRenderer"] != "")?", renderer: renderer_".$field["name"]:$renderer;
		       			        		$_tmp .= "{dataIndex: '".$field["name"]."'".$widthGrid.$titleGrid.$renderer."},\n";
					        		}
					        		break;
					        	
								case "relation":
									$relation = $component["data"];
					        		switch ($relation["type"]) {
					        			case "OneOnOne":
					        				if (!$relation["hiddenGrid"]) {
							        			if ($relation["widthGrid"] != "") {
				       			        			$widthGrid = ", width: ".$relation["widthGrid"];
				       			        		} else {
				       			        			$widthGrid = "";
				       			        		}
				       			        		if ($relation["titleGrid"] != "") {
				       			        			$titleGrid = ", header: '".$relation["titleGrid"]."'";
				       			        		} else {
				       			        			$titleGrid = ", header: '".$relation["name"]."'";
				       			        		}
				       			        		$_tmp .= "{dataIndex: '".$relation["name"]."_display'".$widthGrid.$titleGrid."},\n";
							        		}
		       								break;
					        			case "OneToMany":
					        				break;
					        			case "ManyToMany":
					        				break;
					        		}
					        		break;
					        		
							}
			        	}
			        	
			        	$_tmp = substr($_tmp, 0, strlen($_tmp)-2)."\n";
			        	echo $_tmp;
			        ?>
		        ]);
		        
		        listViewGridDataStore = new Ext.data.Store({
			        id:'listViewGridDataStore',
			        proxy: new Ext.data.HttpProxy({
			           url: '<?= Extjs_renderer::ajaxurl();; ?><?= $this->controller->name; ?>/json_list'
			        }),
			        reader: new Ext.data.JsonReader({
			            root: 'items',
			            totalProperty: 'totalCount',
			            id: 'Itemid'
			        }, [
			        <?php
			        	$_tmp = "{name: 'Itemid', mapping: 'Itemid'},\n";
			        	foreach ($this->controller->presentation->fields as $field) {
			        		if (!$field["hiddenGrid"]) {
			        			$_tmp .= "{name: '".$field["name"]."', mapping: '".$field["name"]."'},\n";
			        		}
			        	}
			        	foreach ($this->controller->presentation->relations as $relation) {
			        		switch ($relation["type"]) {
			        			case "OneOnOne":
			        				if (!$relation["hiddenGrid"]) {
				        				$_tmp .= "{name: '".$relation["name"]."', mapping: '".$relation["name"]."'},\n";
				        				$_tmp .= "{name: '".$relation["name"]."_display', mapping: '".$relation["name"]."_display'},\n";
			        				}
			        				break;
			        			case "OneToMany":
			        				break;
			        			case "ManyToMany":
			        				break;
			        		}
			        	}
			        	$_tmp = substr($_tmp, 0, strlen($_tmp)-2)."\n";
			        	echo $_tmp;
			        ?>
			        ]),
			
			        baseParams: {limit:<?= $this->listViewPageSize; ?>}
			        
			    });
	
				listViewGrid = new Ext.grid.GridPanel({
	        		id:'listViewGrid',
			        cm: listViewGridColumnModel,
			        ds: listViewGridDataStore,
			        trackMouseOver:false,
			        sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
			        loadMask: true,
			        <?php
			        	if ($this->controller->presentation->listView["title"] != '') {
			        		?>
			        title:'<?= $this->controller->presentation->listView["title"]; ?>',		
			        		<?php
			        	}
			        ?>
			        <?php
			        	if ($this->controller->presentation->listView["width"] != '') {
			        		?>
			        width:<?= $this->controller->presentation->listView["width"]; ?>,		
			        		<?php
			        	}
			        ?>
			        <?php
			        	if ($this->controller->presentation->listView["height"] != '') {
			        		?>
			        height:<?= $this->controller->presentation->listView["height"]; ?>,		
			        		<?php
			        	}
			        ?>
			        tbar:[			        
			        	new Ext.Toolbar.Button({
			        		text:'New', 						        		
			        		icon:'<?= $CI->config->item('base_url')."images/ppo/add.png"; ?>',
			        		cls:'x-btn-text-icon',
			        		handler: function(){
			        			
			        			<?php
			        			//	clear previous temp relations
			        			?>
			        			Ext.Ajax.request({
								   url: '<?= Extjs_renderer::ajaxurl();; ?><?= $this->controller->name; ?>/clear',
								   params: {},
								   disableCaching: false
								});

			        			Ext.getCmp('editorWindowForm').getForm().reset();
			        			Ext.getCmp('editorWindow').show();
			        			<?php
				            		//	clear all relation datastores		
		            				foreach ($this->controller->presentation->relations as $relation) {
										switch ($relation["type"]) {
											case "OneOnOne":
												break;
											case "OneToMany":
												$undefined = ($CI->session->userdata('user') > 0)?($CI->session->userdata('user') * -1):-1;
												?>
												Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getStore().baseParams.Itemid = <?= $undefined; ?>;
												Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getStore().reload();
												<?php
												break;
											case "ManyToMany":
												break;
										}
		            				}
	            				
	            				?>
			        		}
			        	}),
			        	new Ext.Toolbar.Button({
			        		text:'Edit',
			        		icon:'<?= $CI->config->item('base_url')."images/ppo/edit.png"; ?>',
						    cls:'x-btn-text-icon',
			        		handler: function(){
				        		if (Ext.getCmp('listViewGrid').getSelectionModel().getCount() > 0) {
				        			Ext.getCmp('editorWindow').show();
				        			Ext.getCmp('editorWindowForm').getForm().load({
					                	url:'<?= Extjs_renderer::ajaxurl();; ?><?= $this->controller->name; ?>/load', 
					                	waitMsg:'Loading',
					                	disableCaching: true,
					                	success:function(form, action) {			        					
				            				Ext.getCmp('editorWindow').show();
				            				<?php
				            				
					            				foreach ($this->controller->presentation->relations as $relation) {
													switch ($relation["type"]) {
														case "OneOnOne":
															break;
														case "OneToMany":
															?>
															Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getStore().baseParams.Itemid = Ext.getCmp('listViewGrid').getSelectionModel().getSelected().get('Itemid');
															Ext.getCmp('listViewOneToManyGrid_<?= $relation["name"]; ?>').getStore().reload();
															<?php
															break;
														case "ManyToMany":
															break;
													}
					            				}
				            				
				            				?>
					                	},
					                	failure:function(action) {
					                		var jsonData = Ext.util.JSON.decode(action.response.responseText);
					                		Ext.MessageBox.alert('Error', jsonData.errors.reason);
										},
										params:{ Itemid: Ext.getCmp('listViewGrid').getSelectionModel().getSelected().get('Itemid') }
					                });
				        			
			        			} else {
			        				Ext.Msg.show({
										   title:'Edit record',
										   msg: 'Please select record to edit!',
										   buttons: Ext.Msg.OK
			        				});
				        		}
			        		}
			        	}),
			        	new Ext.Toolbar.Button({
			        		text:'Delete',
			        		icon:'<?= $CI->config->item('base_url')."images/ppo/delete.png"; ?>',
						    cls:'x-btn-text-icon',
			        		handler: (function(){
				        		if (Ext.getCmp('listViewGrid').getSelectionModel().getCount() > 0) {
			        				Ext.Msg.show({
									   title:'Delete record?',
									   msg: 'You are about to delete selected record. Are you shure?',
									   buttons: Ext.Msg.YESNO,
									   fn: (function(btn, text){
									    if (btn == 'yes'){
									    	
									        Ext.Ajax.request({
											   url: '<?= Extjs_renderer::ajaxurl();; ?><?= $this->controller->name; ?>/delete',
											   success: (function(response, options) {
													Ext.getCmp('listViewGrid').getStore().reload();
												}),
											   failure: (function(response, options) {
												   var jsonData = Ext.util.JSON.decode(action.response.responseText);
							                	   Ext.MessageBox.alert('Error', jsonData.errors.reason);
											   }),
											   params:{ Itemid: Ext.getCmp('listViewGrid').getSelectionModel().getSelected().get('Itemid') }
											});
									    }
									    
									   }),
									   animEl: 'elId',
									   icon: Ext.MessageBox.QUESTION
									});
			        			} else {
			        				Ext.Msg.show({
										   title:'Delete record?',
										   msg: 'Please select record to delete!',
										   buttons: Ext.Msg.OK
			        				});
				        		}
			        		})
			        	}),
			        	{xtype: 'tbseparator'},
			        	new Ext.Toolbar.Button({
			        		text:'Search',
			        		icon:'<?= $CI->config->item('base_url')."images/ppo/search.png"; ?>',
						   	cls:'x-btn-text-icon',
			        		handler: function() {
			        			Ext.getCmp('searchWindow').show();
			        		}
			        	})
			        	<?php
			        	
			        	if (count($this->controller->customToolButtons) > 0) {
			        		?>
			        		,{xtype: 'tbseparator'}
			        		<?php
			        		foreach ($this->controller->customToolButtons as $customToolButton) {
			        			?>
			        			,new Ext.Toolbar.Button({
					        		text:'<?= $customToolButton->text; ?>',
					        		icon:'<?= $customToolButton->icon; ?>',
			        				cls:'x-btn-text-icon',
					        		handler: function() {
					        			<?php 
					        				
					        				$customToolButton->handler = str_replace("{!Itemid}", "Ext.getCmp('listViewGrid').getSelectionModel().getSelected().get('Itemid')", $customToolButton->handler);
					        				echo $customToolButton->handler;
					        				
					        			?>
					        		}
					        	})
			        			<?php
			        		}
			        	}
			        	
			        	?>
			        ],
			        bbar: new Ext.PagingToolbar({
			            store: listViewGridDataStore,
			            pageSize: <?= $this->listViewPageSize; ?>,
			            displayInfo: true,
			            displayMsg: 'Topics {0} - {1} of {2}',
			            emptyMsg: "No topics to display"
			        })
			    });
			    
			    new Ext.Panel({
			        title: '<?= $this->title; ?>',
			        renderTo: 'listView',
			        items:[
			        	listViewGrid
			        ]
			    });

			    Ext.getCmp('listViewGrid').getStore().reload();

			});
		</script>
		
		
		<?php
		
	}
	
	function renderExtAddons() {
		
		$CI =& get_instance();
		
		
		?>
		
		<style>
		
			.x-form-file-wrap {
			    position: relative;
			    height: 22px;
			}
			.x-form-file-wrap .x-form-file {
				position: absolute;
				right: 0;
				-moz-opacity: 0;
				filter:alpha(opacity: 0);
				opacity: 0;
				z-index: 2;
			    height: 22px;
			}
			.x-form-file-wrap .x-form-file-btn {
				position: absolute;
				right: 0;
				z-index: 1;
			}
			.x-form-file-wrap .x-form-file-text {
			    position: absolute;
			    left: 0;
			    z-index: 3;
			    color: #777;
			}
			
			.upload-icon {
				background:transparent url(<?= $CI->config->item('base_path'); ?>images/ppo/image_add.png) no-repeat scroll 0 0 !important;
			}
			
		</style>
		
		<script type="text/javascript">
		Ext.onReady(function(){
			
			Ext.form.FileUploadField = Ext.extend(Ext.form.TextField,  {
		    buttonText: 'Browse...',
		    buttonOnly: false,
		    buttonOffset: 3,
		    readOnly: true,
		    autoSize: Ext.emptyFn,
		    initComponent: function(){
		        Ext.form.FileUploadField.superclass.initComponent.call(this);
		        this.addEvents(
		            'fileselected'
		        );
		    },
		    onRender : function(ct, position){
		        Ext.form.FileUploadField.superclass.onRender.call(this, ct, position);
		        
		        this.wrap = this.el.wrap({cls:'x-form-field-wrap x-form-file-wrap'});
		        this.el.addClass('x-form-file-text');
		        this.el.dom.removeAttribute('name');
		        
		        this.fileInput = this.wrap.createChild({
		            id: this.getFileInputId(),
		            name: this.name||this.getId(),
		            cls: 'x-form-file',
		            tag: 'input', 
		            type: 'file',
		            size: 1
		        });
		        
		        var btnCfg = Ext.applyIf(this.buttonCfg || {}, {
		            text: this.buttonText
		        });
		        this.button = new Ext.Button(Ext.apply(btnCfg, {
		            renderTo: this.wrap,
		            cls: 'x-form-file-btn' + (btnCfg.iconCls ? ' x-btn-icon' : '')
		        }));
		        
		        if(this.buttonOnly){
		            this.el.hide();
		            this.wrap.setWidth(this.button.getEl().getWidth());
		        }
		        
		        this.fileInput.on('change', function(){
		            var v = this.fileInput.dom.value;
		            this.setValue(v);
		            this.fireEvent('fileselected', this, v);
		        }, this);
		    },

		    getFileInputId: function(){
		        return this.id+'-file';
		    },

		    onResize : function(w, h){
		        Ext.form.FileUploadField.superclass.onResize.call(this, w, h);
		        
		        this.wrap.setWidth(w);
		        
		        if(!this.buttonOnly){
		            var w = this.wrap.getWidth() - this.button.getEl().getWidth() - this.buttonOffset;
		            this.el.setWidth(w);
		        }
		    },

		    preFocus : Ext.emptyFn,

		    getResizeEl : function(){
		        return this.wrap;
		    },

		    getPositionEl : function(){
		        return this.wrap;
		    },

		    alignErrorIcon : function(){
		        this.errorIcon.alignTo(this.wrap, 'tl-tr', [2, 0]);
		    }
		    
		});
		Ext.reg('fileuploadfield', Ext.form.FileUploadField);
		
		});
		</script>
		
		<?php
		
	}
	
	function renderRelationEditors() {
		
		$this->renderSearchWindowRelation();
		$this->renderEditorWindowRelation();
		$this->renderLookupWindowsRelation();	
		
	}
	
	function render() {

		$this->renderExtAddons();
		$this->renderLookupWindows();
		$this->renderEditorWindow();
		$this->renderSearchWindow();
		$this->renderListView();
		$this->renderRelationEditors();
		
	}
	
}