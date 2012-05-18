/*
 * Script from NETTUTS.com [by James Padolsey]
 * @requires jQuery($), jQuery UI & sortable/draggable UI modules
 */
 
(function($){
	spm_wf_editor.activeLayout = null;
	spm_wf_editor.iNettuts = {
	    settings : {
	    	layout_idx : 0,
	    	
	    	main : '#spm_wf_main',
	        layoutSelector: '.layout',
	        layoutHandleSelector: '.layout-head',
	        layoutContentSelector: '.layout-content',
	        columns : '.column',
	        widgetSelector: '.widget',
	        handleSelector: '.widget-head',
	        contentSelector: '.widget-content',
	        widgetDefault : {
	            movable: true,
	            removable: true,
	            collapsible: true,
	            editable: true,
	            colorClasses : ['color-yellow', 'color-red', 'color-blue', 'color-white', 'color-orange', 'color-green']
	        },
	        widgetIndividual : {
	        	wf_freetext : {
	                removable: false,
	                editable: false
	        	},
	            fixed : {
	                movable: false,
	                removable: false,
	                collapsible: false,
	                editable: false
	            },
	            gallery : {
	                colorClasses : ['color-yellow', 'color-red', 'color-white']
	            }
	        }
	    },
	
	    init : function () {
	//      this.attachStylesheet('inettuts.js.css');
	        this.addWidgetControls();
	    },
	    
	    getWidgetSettings : function (id) {
	        var settings = this.settings;
	        return (id&&settings.widgetIndividual[id]) ? $.extend({},settings.widgetDefault,settings.widgetIndividual[id]) : settings.widgetDefault;
	    },

		removeWidgetUI : function (widget) {
			widget.animate({
				opacity: 0
			},function () {
				$(this).wrap('<div/>').parent().slideUp(function () {
					$(this).remove();
				});
			});
		},
		removeWidget : function (widget) {
			var iNettuts = this;

			var jObj = $(".spm_wf_bound", widget);
			if(jObj.length > 0) {
				var fld_settings = spm_wf_editor.js.getFieldSettings( jObj );
				if(spm_wf_editor.view[fld_settings.view].js && spm_wf_editor.view[fld_settings.view].js.removeField){
					spm_wf_editor.view[fld_settings.view].js.removeField( jObj, fld_settings );
				} else {
					spm_wf_editor.js.removeFieldBase( jObj, fld_settings );
				}
			} else {
				// should be table
				// FIXME: hard code here
				$(".spm_table .spm_tablerow", widget).each(function(){
					jObj = $(this);
					var fld_settings = spm_wf_editor.js.getFieldSettings( jObj );
					if(spm_wf_editor.view[fld_settings.view].js && spm_wf_editor.view[fld_settings.view].js.removeField){
						spm_wf_editor.view[fld_settings.view].js.removeField( jObj, fld_settings );
					} else {
						spm_wf_editor.js.removeFieldBase( jObj, fld_settings );
					}
				});
			}
			
			iNettuts.removeWidgetUI(widget);
		},
		
		applyLayout : function(obj, thisSettings) {
		        var iNettuts = this,
	            	settings = this.settings,
	            	img_path = wgScriptPath + '/extensions/SemanticPageMaker/skins/wf_designer/img/';
				
				++ settings.layout_idx;
				
				$(settings.layoutHandleSelector, obj).css("visibility", "hidden");
				$(obj).mouseover(function(e){
					if(spm_wf_editor.activeLayout && spm_wf_editor.activeLayout == obj) return;
			if(spm_wf_editor.activeLayout ) {
				$(settings.layoutHandleSelector, spm_wf_editor.activeLayout ).css("visibility", "hidden");
			}
			$(settings.layoutHandleSelector, obj).css("visibility", "");
			spm_wf_editor.activeLayout = obj;
				});
				
				if (thisSettings.removable) {
	                $('<a href="#" class="remove">CLOSE</a>').mousedown(function (e) {
	                    e.stopPropagation();    
	                }).click(function () {
	                    if(confirm('This layout will be removed, \nas well as the widgets inside it, \nok?')) {
	                    	var layout = $(this).parents(settings.layoutSelector);
	                    	
	                    	// remove layout widgets
	                    	$(settings.widgetSelector, layout).each(function(){
	                        	iNettuts.removeWidget($(this));
	                        });
	                    
	                        layout.animate({
	                            opacity: 0    
	                        },function () {
	                            $(this).wrap('<div/>').parent().slideUp(function () {
	                                $(this).remove();
	                            });
	                        });

							spm_wf_editor.js.notifySave();
	                    }
	                    return false;
	                }).appendTo($(settings.layoutHandleSelector, obj));
	            }
	            
	            if (thisSettings.editable) {
	                $('<a href="#" class="edit">EDIT</a>').mousedown(function (e) {
	                    e.stopPropagation();    
	                }).toggle(function () {
	                    $(this).css({backgroundPosition: '-66px 0', width: '55px'})
	                        .parents(settings.layoutSelector)
	                            .find('.layout-edit-box').show().find('input').focus();
	                    return false;
	                },function () {
	                    $(this).css({backgroundPosition: '', width: ''})
	                        .parents(settings.layoutSelector)
	                            .find('.layout-edit-box').hide();
	                    return false;
	                }).appendTo($(settings.layoutHandleSelector,obj));
	                
	                var layid = $(settings.columns, $(settings.layoutContentSelector, obj)).attr('class').match(/column([123])/);
	                if(layid == null) {
	                	layid = 1;
	                } else {
	                	layid = layid[1];
	                }
	                var layout_edit = $('<div class="layout-edit-box" style="display:none;"/>')
	                    .append((function(){
	                    	var tablayout = '';
	                    	for(var i=1;i<=3;++i) {
	                    		tablayout = tablayout + '<div class="tablayout">' +
	                    			'<input type="radio" value="' + i + '" name="tab_selected_layout_' + settings.layout_idx + '"' + (layid == i? ' checked="true"':'') + '>' +
	                    			'<img src="' + img_path + 'tab_layout_' + i + 'column_1_highlight.gif" style="padding-right: 8px;">' +
	                    			'</div>';
	                    	}
	                    	return $('<div style="margin:10px;" class="buttons"></div>')
	                    		.append(tablayout)
	                    		.append('<a href="javascript:void(0);" style="padding: 10px;" class="spm_wf_editor_add_field">add new widget (default)</a>')
	                    		.append((function(){
		                    		var menu = $('<div style="display:inline;top:2px;" class="fg-button ui-widget ui-state-default ui-corner-all">&nbsp; <span class="ui-icon ui-icon-triangle-1-s"></span></div>')
		                    		menu.menu({ 
										content: $("#spm_wf_editor_widget_content").html(), 
										flyOut: true,
										keydownFunc: spm_wf_editor.event.keydownFunc,
										chooseItemFunc: function(item) {
											if($("ul", item).length > 0) return;
											var w = item.text();
											for(var str in spm_wf_editor.extra) {
												var eobj = spm_wf_editor.extra[str];
												if(eobj.list_str == w) {
													spm_wf_editor.activeLayout = obj;
													eobj.js.addNew();
													break;
												}
											}
										}
									});
									return menu;
		                    	})())
	                    })())
	                    .append('<div style="clear:both;"></div>')
	                    .insertAfter($(settings.layoutHandleSelector,obj));

		$(".spm_wf_editor_add_field", layout_edit).click(function(){
				spm_wf_editor.activeLayout = $(this).parents(settings.layoutSelector);

				spm_wf_editor.js.resetFieldSettings();
				$("#spm_wf_field").val('').removeAttr('readonly');
				$("#spm_wf_append_text").show();
			$("#fbpl_overlay").css({'height' : $(document).height()}).show();
			$("#fbpl").css({
				'top': $(document).scrollTop() + 20,
				'left': $(document).scrollLeft() + 20
			}).show('slow');
			$("#fbpl_accordion").accordion("activate", 0);

				var def_val = '';
				$("#spm_wf_editor_datatype_content li").each(function(){
					if($("ul", this).length == 0) {
						def_val = $(this).text();
						return false;
					}
				});
				$("#spm_wf_editor_datatype").val(def_val).change();
				$("#spm_wf_editor_view_content li").each(function(){
					if($("ul", this).length == 0) {
						def_val = $(this).text();
						return false;
					}
				});
				$("#spm_wf_editor_view").val(def_val).change();
		});

	                
					$("input:[name=tab_selected_layout_" + settings.layout_idx + "]:radio", layout_edit).change(function() {
						// FIXME: hard code here, the structure of layout
						var columnObj = $("> div.layout-content > .column", $(this).parents(settings.layoutSelector)),
							columnPattern = /\bcolumn([123])\b/,
							thisLayoutClass = columnObj.attr('class').match(columnPattern);
						
						if (thisLayoutClass) {
							var columns = parseInt( $(this).val() ), old_columns = parseInt( thisLayoutClass[1] );
							columnObj.removeClass(thisLayoutClass[0])
								.addClass("column" + columns);
							if (old_columns > columns) {
								var ins_parent = columnObj[ columns - 1 ],
									ins_obj = $(">li", ins_parent).last();
									
								for(var i = columns; i < old_columns; ++i) {
									$(">li", columnObj[i]).each(function(){
										if(ins_obj.length == 0) {
											ins_obj = $(this);
											$(ins_parent).append(ins_obj);
										} else {
											ins_obj = $(this).insertAfter(ins_obj);
										}
									});
								}
								for(var i = columns; i < old_columns; ++i) {
									$(columnObj[columns]).remove();
								}
							} else {
								for(var i = old_columns; i < columns; ++i) {
									$('<ul class="column column' + columns + '"></ul>').insertAfter(columnObj.last());
								}
								iNettuts.makeSortable(obj);
							}
		
		                	spm_wf_editor.js.notifySave();
						}
					});
	            }

                $('<a href="#" class="help">HELP</a>').mousedown(function (e) {
                    e.stopPropagation();    
                }).fancybox({
					'overlayShow'	: true,
					'transitionIn'	: 'elastic',
					'transitionOut'	: 'elastic',
					'titlePosition' : 'inside',
					'width'		  	: '90%',
					'height'	  	: '90%',
					'autoScale'		: false,
					'type'		  	: 'iframe',
					'title'         : 'Help - Widget Editor - Designer - Layout',
					'href'          : wgScriptPath + '/extensions/SemanticPageMaker/helps/help_SPM_designer.html#l2'
                }).appendTo($(settings.layoutHandleSelector,obj));

	            if (thisSettings.collapsible) {
	                $('<a href="#" class="collapse">COLLAPSE</a>').mousedown(function (e) {
	                    e.stopPropagation();    
	                }).toggle(function () {
	                    $(this).css({backgroundPosition: '-38px 0'})
	                        .parents(settings.layoutSelector)
	                            .find(settings.layoutContentSelector).hide();
	                    return false;
	                },function () {
	                    $(this).css({backgroundPosition: ''})
	                        .parents(settings.layoutSelector)
	                            .find(settings.layoutContentSelector).show();
	                    return false;
	                }).prependTo($(settings.layoutHandleSelector,obj));
	            }
	            
	            iNettuts.makeSortable(obj);
		},
		
		applyWidget : function(obj, thisWidgetSettings) {
	        var iNettuts = this,
	            settings = this.settings;

				$(settings.handleSelector, obj).css("visibility", "hidden");
				$(obj).mouseover(function(e){
					if(spm_wf_editor.activeWidget && spm_wf_editor.activeWidget == obj) return;
			if(spm_wf_editor.activeWidget) {
				$(settings.handleSelector, spm_wf_editor.activeWidget).css("visibility", "hidden");
			}
			$(settings.handleSelector, obj).css("visibility", "");
			spm_wf_editor.activeWidget = obj;
				});
				
				if (thisWidgetSettings.removable) {
	                $('<a href="#" class="remove">CLOSE</a>').mousedown(function (e) {
	                    e.stopPropagation();
	                }).click(function () {
	                    if(confirm('Remove this widget.\nSemantic data may lost, even if you do not save.\nOk?')) {
	                    	iNettuts.removeWidget($(this).parents(settings.widgetSelector));

							spm_wf_editor.js.notifySave();
	                    }
	                    return false;
	                }).appendTo($(settings.handleSelector, obj));
	            }
	            
	            if (thisWidgetSettings.editable) {
	            	var colorSel = $('<div class="colorSelector"><div style="background-color: #0000ff"></div></div>');
	            	var color = colorSel.parents(settings.widgetSelector).css('backgroundColor') ? 
	            		colorSel.parents(settings.widgetSelector).css('backgroundColor') : '#FFFFFF';
	            	colorSel.appendTo($(settings.handleSelector, obj)).ColorPicker({
						color: color,
						onShow: function (colpkr) {
							$(colpkr).fadeIn(500);
							return false;
						},
						onHide: function (colpkr) {
							$(colpkr).fadeOut(500);
							return false;
						},
						onSubmit: function (hsb, hex, rgb, el) {
							colorSel.parents(settings.widgetSelector)
									.css('backgroundColor', '#' + hex);
							spm_wf_editor.js.notifySave();
						}
					});

				    spm_wf_editor.view["table row"].js.applyTableEditor($(obj));
/*
	                $('<a href="#" class="edit">EDIT</a>').mousedown(function (e) {
	                    e.stopPropagation();    
	                }).toggle(function () {
	                    $(this).css({backgroundPosition: '-66px 0', width: '55px'})
	                        .parents(settings.widgetSelector)
	                            .find('.edit-box').show().find('input').focus();
	                    return false;
	                },function () {
	                    $(this).css({backgroundPosition: '', width: ''})
	                        .parents(settings.widgetSelector)
	                            .find('.edit-box').hide();
	                    return false;
	                }).appendTo($(settings.handleSelector,obj));
	                var edit_box = $('<div class="edit-box" style="display:none;"/>')
	                    .append('<ul>')
	                    .append((function(){
	                        var colorList = '<li class="item"><label>Available colors:</label><ul class="colors">';
	                        $(thisWidgetSettings.colorClasses).each(function () {
	                            colorList += '<li class="' + this + '"/>';
	                        });
	                        return colorList + '</ul>';
	                    })())
	                    .append('</ul>')
	                    .insertAfter($(settings.handleSelector,obj))
	                    .each(function () {
				            $('ul.colors li',this).click(function () {
				                var colorStylePattern = /\bcolor-[\w]{1,}\b/,
				                    thisWidgetColorClass = $(this).parents(settings.widgetSelector).attr('class').match(colorStylePattern),
				                    widget = $(this).parents(settings.widgetSelector);
				                if (thisWidgetColorClass) widget.removeClass(thisWidgetColorClass[0]);
				                widget.addClass($(this).attr('class').match(colorStylePattern)[0]);
				                return false;
				            });
				        });
				    
				    spm_wf_editor.view["table row"].js.applyTableEditor($(obj));
*/
	            }

                $('<a href="#" class="help">HELP</a>').mousedown(function (e) {
                    e.stopPropagation();    
                }).fancybox({
					'overlayShow'	: true,
					'transitionIn'	: 'elastic',
					'transitionOut'	: 'elastic',
					'titlePosition' : 'inside',
					'width'		  	: '90%',
					'height'	  	: '90%',
					'autoScale'		: false,
					'type'		  	: 'iframe',
					'title'         : 'Help - Widget Editor - Designer - Widget',
					'href'          : wgScriptPath + '/extensions/SemanticPageMaker/helps/help_SPM_designer.html#l3'
                }).appendTo($(settings.handleSelector,obj));

	            if (thisWidgetSettings.collapsible) {
	                $('<a href="#" class="collapse">COLLAPSE</a>').mousedown(function (e) {
	                    e.stopPropagation();    
	                }).toggle(function () {
	                    $(this).css({backgroundPosition: '-38px 0'})
	                        .parents(settings.widgetSelector)
	                            .find(settings.contentSelector).hide();
	                    return false;
	                },function () {
	                    $(this).css({backgroundPosition: ''})
	                        .parents(settings.widgetSelector)
	                            .find(settings.contentSelector).show();
	                    return false;
	                }).prependTo($(settings.handleSelector,obj));
	            }
	            
		        $(obj).find(settings.handleSelector).css({
		            cursor: 'move'
		        }).mousedown(function (e) {
		            $(obj).css({width:''});
		            $(this).parent().css({
		                width: $(this).parent().width() + 'px'
		            });
		        }).mouseup(function () {
		            if(!$(this).parent().hasClass('dragging')) {
		                $(this).parent().css({width:''});
		            } else {
		                $(settings.columns).sortable('disable');
		            }
		        });
		        
		        iNettuts.updateSortableItems();
		},

	    addWidgetControls : function () {
	        var iNettuts = this,
	            settings = this.settings;

			$(settings.layoutSelector, $(settings.main)).each(function () {
	            var thisSettings = iNettuts.getWidgetSettings(this.id);
	            iNettuts.applyLayout(this, thisSettings);
	        });
	        
	        $(settings.widgetSelector, $(settings.columns)).each(function () {
	            var thisWidgetSettings = iNettuts.getWidgetSettings(this.id);
	            iNettuts.applyWidget(this, thisWidgetSettings);
	        });
	    },
	    
	    attachStylesheet : function (href) {
	        return $('<link href="' + href + '" rel="stylesheet" type="text/css" />').appendTo('head');
	    },
	    
	    makeSortable : function (layout_obj) {
	        var iNettuts = this,
	            settings = this.settings,
	            wiki = '';
	            
	        $(settings.columns).sortable({
	            items: $(settings.columns + '> li'),
	            connectWith: $(settings.columns),
	            handle: settings.handleSelector,
	            placeholder: 'widget-placeholder',
	            forcePlaceholderSize: true,
	            revert: 300,
	            delay: 100,
	            opacity: 0.8,
	            containment: 'document',
	            start: function (e,ui) {
	                $(ui.helper).addClass('dragging');
	            },
	            stop: function (e,ui) {
	                $(ui.item).css({width:''}).removeClass('dragging');
	                $(settings.columns).sortable('enable');
	                
                	spm_wf_editor.js.notifySave();
	            }
	        });
	    },
	    
	    updateSortableItems : function () {
	        var settings = this.settings;
	        $(settings.columns).sortable( 'option', 'items', $(settings.columns + '> li') );
	    }
	};
		
	$(document).ready(function() {
		spm_wf_editor.iNettuts.init();
	});
})(jQuery);