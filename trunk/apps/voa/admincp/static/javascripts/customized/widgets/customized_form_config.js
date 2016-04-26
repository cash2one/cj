define([ "text!widgets/templates/customized_tablecol_menu.html", "underscore", 'jquery', 'text!widgets/templates/customized_tablecol.html', "jqueryui",
			"jquery.fileupload-validate"
        ], function(tablecol_menu_tpl, _, $, tablecol_tpl){
	
	function config () {
		
	}
		
    config.prototype = {
    	// 表单类型列表
    	tablecol: null,
    	tablecolopt: null,
    	tablecol_menu: null,
    	
    	tablecol_save_callback: null,
    	tablecolopt_save_callback: null,
    	tablecol_del_callback: null,

    	
    	// 最后一次打开的 box
    	last_open_box: null, 
    	// 菜单模板
    	tablecol_menu_tpl: null,
    	tablecol_tpl: null,
    	
    	// 菜单容器 对像/id
    	menu_container: '#draggable',
    	menu_item_class: '.dr',
    	
    	// main container
    	// 编辑区
    	main_container: '#sortable',
    	
    	// 保存属性数据
    	tablecol_save: function (div) {
    		var form_data = div.find('form').serializeArray();
    		/*
    		if (form.length == 2) {
    			var form_data = div.find('form').eq(0).serializeArray();
    		} else {
    			*/
    			//var form_data = form.serializeArray();
    		//}
	    	if (_.isEmpty(form_data)) {
	    		return ;
	    	}
        	var objects = {};
        	var tc_id = div.find('.page-header').attr('tc_id');
        	if (tc_id) {
        		objects['tc_id'] = tc_id;
        	}
        	
        	$.each(form_data, function (k, item) {
        		objects[item.name] = item.value;
        	});
        	if (typeof this.tablecol_save_callback == 'function') {
        		this.tablecol_save_callback(objects, function (result) {
            		if (tc_id == "0") {
                    	div.find('.js-options-values').attr('tc_id', result.tc_id);
                    	div.find('.page-header').attr('tc_id', result.tc_id);
                    }
            	});
        	}
        	
	    },
	    
	    // 属性选项删除或保存
	    // act 为delete则是删除
	    tablecolopt_save: function (opt, act) {
	    	var posts = {};
	    	posts.tc_id = opt.attr('tc_id');
	    	posts.tco_id = opt.attr('tco_id');
	    	posts.attachid = opt.attr('attachid');
	    	posts.value = opt.val();
	    	if (typeof this.tablecolopt_save_callback == 'function') {
        		this.tablecolopt_save_callback(act, posts, function (result) {
        			if (act != 'delete') {
        				if (result.tco_id) {
    		    			opt.attr('tco_id', result.tco_id);
    		    		}
        			}
        			
            	});
        	}
	    },
	    
	    // 图片上传控件
	    // object是控件 input object
	    make_images_upload: function (object) {
			var self = this;
			//this.show_images_opts(item, item.tc_id, div);
        	//$('.fileupload').fileupload();
        	object.fileupload({
                // Uncomment the following to send cross-domain cookies:
                //xhrFields: {withCredentials: true},
                dataType: 'json',
                url: '/api/attachment/post/upload/',
                maxFileSize: 5000000,
                maxNumberOfFiles : 1,
                acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                //acceptFileTypes: /(\.|\/)(xls)$/i,
                progressall: function (e, data) {
                	/*
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $(this).find('.fileinput-button').find('span').eq(0).text('正在上传中，进度：'+progress + '%');
                    if (progress == 100) {
                        $(this).find('.fileinput-button').find('span').eq(0).text('上传完成，正在处理请稍等。。。');
                    }*/
                },
                done: function (e, data) {
                	var result = data.result;
                	if (result.errcode == 0) {
                		result = result.result;                		
                		$(this).parents('.thumbnail').find("img").attr('src', result.url + '/100');
                		$(this).parents('.js-customize-option').find('input[type="text"]').attr('attachid', result.id);
                		//self.show_attach(result, item.tc_id, $(this).parents('tr'));
                		self.tablecolopt_save($(this).parents('.js-customize-option').find('input[type="text"]'));
                	} else {
                		alert(result.errmsg);
                	}
                }
            });
		},
		
		// 属性选项操作事件
		// div 为属性的父级容器
		tablecolopts_events: function (div) {
			var self = this;
			var on_option_minus = function (div) {
	            // 删除选项
	            div.find('.js-btn-option-minus').click(function() {
	            	//if (div.find('.js-customize-option').length > 1) {
	            		// 删除保存
	            		self.tablecolopt_save($(this).parents('.js-customize-option').find('.js-options-values'), 'delete');
	            		$(this).parents('.js-customize-option').fadeOut(500).remove();
	            	//}
	            });
	        } 
	        
	        div.find(".js-options-values").on("change", function () {
	        	self.tablecolopt_save($(this));
	        });	        
	        
	        // 添加选项
	        div.find('.js-btn-option-plus').click(function () {
	        	var firstoption = $(this).parents('.form-group').find('.js-customize-option-sample');
	        	//firstoption.find('input').attr('tc_id', vars.tc_id);
	            var option = firstoption.clone();
	            option.removeClass('js-customize-option-sample');
	            option.addClass('js-customize-option');
	            option.find('input[type="text"]').val('');
	            option.find('input[type="text"]').attr('name', 'options_values[]');
	            option.find('input[type="text"]').addClass('js-options-values');
	            option.find('input[type="text"]').attr('value', '');
	            //option.find('input').attr('tc_id', vars.tc_id);
	            option.find('input').attr('tco_id', '');
	            option.find('input').attr('tc_id', div.find('.page-header').attr('tc_id'));
	            
	            option.css('display', '');
	            if (option.find('.fileupload')) {
	            	self.make_images_upload(option.find('.fileupload'));
	            }
	            if ($(this).parents('.form-group').find('.js-customize-option').length) {
	            	$(option).insertAfter($(this).parents('.form-group').find('.js-customize-option').eq($(this).parents('.form-group').find('.js-customize-option').length-1));
	            } else {
	            	$(option).insertAfter(firstoption);
	            }
	            
	            $(this).parents('.form-group').find(".js-options-values").on("change", function () {
	            	self.tablecolopt_save($(this));
	            });
	            // 加添保存
	            // option_data_save(option.find("input"));
	            on_option_minus(div);
	        });
	        
	        on_option_minus(div);
	        // text options end
		},
		
	    // 表单类型创建
	    tablecol_create: function (vars) {
	        var self = this;
	    	if (vars.tc_id) {
	    		vars.options_data = _.filter(this.tablecolopt, function (opt) {return opt.tc_id == vars.tc_id});
	    	} else {
	    		vars.options_data = null;
	    	}
	        
	        
	        // 初始化模板
	        var template = _.template(this.tablecol_tpl);
	        var html = template(vars);
	        var div = $("<div/>").html( html);
	        if (vars.ct_type == 'select' || vars.ct_type == 'radio' || vars.ct_type == 'checkbox') {
	        	div.find('[name=ftype]').on('change', function () {

	        		if (div.find('.js-customize-option').length) {	        		
			        	if (confirm('切换后当前数据将被清除，确定切换选项类型？')){
			        		div.find('.js-customize-option').each(function () {
		        				//if (div.find('.js-image-text .js-customize-option:first-child'))
		        				//$(this).remove();
		        				self.tablecolopt_save($(this).find('.js-options-values'), 'delete');
			            		$(this).fadeOut(500).remove();
		        			});
			        		if ($(this).val() == '1') {
			        			
			        			$('.js-image-text').hide();
				        		$('.js-text').show();
			        		} else {
			        			
			        			$('.js-image-text').show();
				        		$('.js-text').hide();
			        		}
			        		
			        	} else {
			        		div.find('[name=ftype][type="radio"]').not(':checked').prop("checked", true);
			        	}
		        	} else {
		        		if ($(this).val() == '1') {
		        			$('.js-image-text').hide();
			        		$('.js-text').show();
		        		} else {
		        			
		        			$('.js-image-text').show();
			        		$('.js-text').hide();
		        		}
		        	}
	        		
	        		
		        	
		        });
	        }

	        // text options 
	        this.tablecolopts_events(div);
	        
	        div.find('.js-customize-option').each(function () {
	        	if ($(this).find('.fileupload')) {
	        		self.make_images_upload($(this).find('.fileupload'));
	        	}
	        });
            
	        
	        // 数据更改保存
	        div.find('input, select, textarea').on("change", function () {
	        	if (!$(this).hasClass('js-options-values')) {
	        		self.tablecol_save(div);
	        	}
	        });

			div.find('[name=ftype]').each(function () {
				if ($(this).val() == vars.ftype) {
					$(this).attr('checked', true);
				}
			});

	        div.find('[name=isuse]').each(function () {
	        	if ($(this).val() == vars.isuse) {
	        		$(this).attr('checked', true);
	        	}
	        });
	        
	        //点击时展开或缩小
	        div.find('.down').click(function() {
	            self.tablecol_box_toggle($(this), div);
	        });
	        
	        // 删除 tablecol item
	        div.find( ".close" ).click(function() {
	        	var tc_id = div.find('.page-header').attr('tc_id');
	        	var coltype = div.find('.page-header').attr('coltype');
	        	if (coltype == "1") {
	        		alert('这是系统字段不能删除，有特殊需求请联系管理员。');
	        		
	        		return false;
	        	}
	        	if (tc_id) {
	        		self.tablecol_del_callback({tc_id: tc_id, url: "goodstablecol", field: 'tc_id', value: tc_id});
	        	}
	            div.parents('li').fadeOut(500).remove();
	        });
			div.find( ".portlet-content" ).hide();
	        //初始时展开, 并缩回上一个展开的
			if (vars.coltype == 2) {
				this.tablecol_box_toggle( div.find('.down'), div);
			}

	        
	        return div;
	    },
	    
	    // 属性缩展
	    tablecol_box_toggle: function(icon, div) {
	        //var icon = $( this );
	        if (this.last_open_box && this.last_open_box != div) {
	            this.last_open_box.find('.glyphicon-toggle').addClass( "glyphicon-chevron-down" );
	            this.last_open_box.find('.glyphicon-toggle').removeClass( "glyphicon-chevron-up" );
	            this.last_open_box.find( ".portlet-content" ).fadeOut();
	        }
	        icon.find('.glyphicon-toggle').toggleClass( "fa-chevron-down fa-chevron-up" );
	        
	        div.find( ".portlet-content" ).fadeToggle();
	        this.last_open_box = div;
		},
				
	    // 添加属性自菜单
	    add_tablecol_from_menu: function (item) {
	    	var orderid = 0;
            if (item.prev()) {

            	if (item.prev().find('input[name=orderid]')) {
            		orderid = item.prev().find('input[name=orderid]').val();
            		orderid = parseInt(orderid)+1;
            	}
            }
            
            item.removeClass('dragging-menu-item');
            item.html('');
            var type = item.attr('columntype');
            columntype_item = _.find(this.tablecol_menu, function (type_val, type_key){ 
                return type_val.ct_type == type;
            }); 
            columntype_item.tc_id = 0;
            columntype_item.field = '';
            // 1系统字段, 2是自定义
            columntype_item.coltype = '2';
            columntype_item.orderid = orderid;
            columntype_item.tc_desc = '';
            columntype_item.fieldname = columntype_item.ct_name;
            columntype_item.required = 0;
            columntype_item.ftype = 1;
            columntype_item.unit = '';
            var box = this.tablecol_create(columntype_item);
            item.append(box).show('slow');
            item.attr('style', '');
            
            // 保存
            this.tablecol_save(item);
	    }, 
	    
	    // 生成
	    render: function () {
	    	var self = this;
	    	
	    	if (!this.tablecol_menu_tpl) {
	    		this.tablecol_menu_tpl = tablecol_menu_tpl;
	    	}
	    	if (!this.tablecol_tpl) {
	    		this.tablecol_tpl = tablecol_tpl;
	    	}
	        //生成配置菜单
	        $.each(this.tablecol_menu, function(key, val) {
	            var template = _.template(self.tablecol_menu_tpl);
	            var html = template(val);
	            $(self.menu_container).append(html);
	        });

	        // 初始化数据
	        $.each(this.tablecol, function (key, item) {
	    		var column = _.find(self.tablecol_menu, function (val, key) {
	 	            if (val.ct_type == item.ct_type) {
	 	                return true;
	 	            }
	 	        });
	 	        item.icon = column.icon;
	 	        var li = $('<li/>').append(self.tablecol_create( item ));
	 	        $(self.main_container).append(li);
	    	});
	        
	        // 初始化编辑区
	        $(this.main_container).sortable({
	            handle: ".page-header",
	            placeholder: "portlet-placeholder ui-corner-all",
	            over: function( event, ui ) {
	              $(ui.helper).css('cursor',"move");
	            },
	            stop: function( event, ui ) {
	              $(ui.item).css('cursor','auto');
	            },
	            out: function( event, ui ){
	              $(ui.helper).css('cursor','no-drop');
	            },
	            update: function () {
	            	$(self.main_container).find('li').each(function (order) {
	              	  $(this).find('input[name=orderid]').val(order);
	              	  self.tablecol_save($(this));
	                });
	            } 
	        });
	        //.disableSelection();
	      
	        // 菜单初始化
	        $( this.menu_item_class).draggable({
	            connectToSortable: this.main_container,
	            opacity: 0.75,
	            //helper: 'clone', 
	            placeholder: "portlet-placeholder ui-corner-all",
	            helper: function(){ 
	                // adjust width of li element at dragging ;
	                var item = $(this).clone();
	                item.addClass('dragging-menu-item');
	                //item.attr('style', 'width: 220px');
	                return item;
	            },
	            stop: function (e,m) {
	                // updating at dropping
	                var item = m.helper;
	                self.add_tablecol_from_menu(item);
	            }
	    
	        }).disableSelection();
	        
	        $(this.menu_container).droppable({ 
	            accept: ".da",
	            over: function( event, ui ) {
	                $(ui.helper).css('cursor',"alias");
	            },
	            out: function( event, ui ){
	                $(ui.helper).css('cursor','no-drop');  
	            }
	        });
	
	    }
	};
    
    return config;
});
