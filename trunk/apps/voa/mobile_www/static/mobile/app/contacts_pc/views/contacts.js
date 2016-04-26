define(["data/addressbook", "views/base", "utils/render", "text!templates/contacts.html", "text!templates/contacts_form.html", 'jquery'
        , "css!styles/common.css", "css!styles/contacts.css", "jquery-lazyload"], function(addressbook, base, render, contacts_selector_tpl, contacts_form_tpl, $){
	
	function view() {
	    	//base.call(this);
	}
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype = $.extend(view.prototype, {
    	page: null,
    	last_page: null,
    	callback: null,	//渲染完的回调
    	sct_callback: null,	//选人结束回调
    	remove_callback: null, // 删除人员的回调
    	container: null,
    	range_limit: null,
    	contacts_default_data: [],
    	range_limit_contacts_data: null,
		deps_enable: true,
		contacts_enable: true,
    	input_type: 'checkbox',
    	input_name_deps: "deps[]",
    	input_name_contacts: "contacts[]",
    	
    	render: function (args) {
    		if (window.default_arguments) {
    			args = window.default_arguments;
    		}
    		
    		var self = this;
    		var r = new render();
    		r.vars = [];
	        r.template = contacts_form_tpl;
	        if (args.contacts_default_data) {
	        	this.contacts_default_data = args.contacts_default_data;
	        }
	        r.assign('contacts', this.contacts_default_data);

			if (typeof args.deps_enable   != "undefined") {
				this.deps_enable = args.deps_enable;
			}
			r.assign('deps_enable', this.deps_enable);
			if (typeof args.contacts_enable != "undefined") {
				this.contacts_enable = args.contacts_enable;
			}
			r.assign('contacts_enable', this.contacts_enable);
	        if (args.range_limit_contacts_data) {
	        	self.range_limit_contacts_data = args.range_limit_contacts_data;
	        } else {
	        	self.range_limit_contacts_data = null;
	        }
	        if (args.range_limit != undefined) {
	        	 this.range_limit = args.range_limit;
	        }
	        // callback 返回本模块对像
	        if (typeof args.callback == "function") {
	        	args.callback(this);
	        }
	        // callback 返回本模块对像
	        if (typeof args.sct_callback == "function") {
	        	this.sct_callback = args.sct_callback;
	        }
	        if (typeof(args.remove_callback) == 'function') {
	        	this.remove_callback = args.remove_callback;
	        }
	        
	        if (args.only_return_element == undefined) {
	        	 r.only_return_element = true;
	        } else {
	        	 r.only_return_element = args.only_return_element;
	        }
	        this.page = r.apply();
	        if (args.input_name_contacts) {
	        	this.input_name_contacts = args.input_name_contacts;
	        }
	        if (args.input_name_deps) {
	        	this.input_name_deps = args.input_name_deps;
	        }
	        if (args.input_type) {
	        	this.input_type = args.input_type;
	        }
	        if (args.container) {
	        	this.container = args.container;
	        }
	        if (this.container) {
	        	$(this.container).html(this.page);
	        	$(self.container).find('.js-contacts-form-row a').on("click", function () {
	        		if(self.remove_callback) {
	            		self.remove_callback($(self.container));
	            	}
	        		$(this).remove();
	        	});
	        	$(self.container).find(".js-call-contacts").on('click', function () {
 	        		self.selector({only_return_element: true, callback: function (ret) {
 	        			$(self.container).find(".js-contacts-window .modal-body").html(ret.page);
 	        			$(self.container).find(".js-contacts-window").modal('show');
 	        			$(self.container).find('.js-contact-close').on('click', function () {
 	        				$(self.container).find(".js-contacts-window").modal('hide');
          	            });
          	            // 同步到 表单上
 	        			$(self.container).find('.js-contact-save').unbind('click').on('click', function () {
 	        				$(self.container).find('.js-contacts-form-row a').remove();
 	        				$(self.container).find('.js-contacts-form-row input').remove();
          	            	var input_name = '';
          	            	$(self.container).find('.js-current-checked a').each(function () {
          	            		
          	            		input_name = $(this).data('input').data('type');
          	            		
          	            		var a = $(this).clone(true, true);
          	            		a.append('<input type="hidden" name="'+input_name+'" value="'+$($(this).data('input')).val()+'" />');
          	            		$(self.container).find('.js-contacts-form-row').append(a);
          	            		if(self.sct_callback) self.sct_callback($(self.container));
              	            });
          	            	
          	            	$(self.container).find(".js-contacts-window").modal('hide');
          	            });
          	            
          	            ret.events(function (contact_list) {
          	            	
          	            });
      	            	// 把已经选择的同步到选择器上
          	            
          	            if (self.range_limit_contacts_data) {
          	            	$(self.container).find('.js-input-tr input').attr('disabled', "disabled");
          	            	$.each(self.range_limit_contacts_data, function(k, item) {
          	            		if (item.input_name && item.id) {
          	            			var name = item.input_name.replace('[', '\\[');
                  	            	name = name.replace(']', '\\]');
                  	            	$(self.container).find('input[value='+item.id+'][data-type='+name+']').removeAttr("disabled");
          	            		}
          	            	});
          	            }
          	            $(self.container).find('.js-contacts-form-row a').each(function () {
          	            	var name = $(this).find('input').attr('name');
							name = name.replace('[', '\\[');
							name = name.replace(']', '\\]');
          	            	$(this).data('input', $(self.container).find('input[value='+$(this).find('input').val()+'][data-type='+name+']'));
          	            	if (self.range_limit_contacts_data) {
	          	            	if ($(this).data('input')) {
	          	            		$(this).data('input').removeAttr('disabled');
	          	            		$(this).parents('.js-input-tr').show();
	          	            	}
          	            	}
          	            	$(this).data('input').attr('checked', true);
          	            	//$(this).data('input').checkboxradio('refresh');
          	            	var a = $(this).clone(true, true);
          	            	a.off('click').on('click', function () {
          	            		if(self.remove_callback) {
          	            			self.remove_callback($(self.container));
          	            		}
          	            		$(this).data('input').prop('checked', false);
          	            		$(this).remove();
          	            	});
          	            	a.find('input').remove();
          	            	$(self.container).find('.js-current-checked').append(a);
          	            });
          	            $(self.container).find('.js-input-tr input').each(function () {
          	            	if ($(this).attr('disabled')) {
          	            		$(this).parents('.js-input-tr').hide();
          	            	}
          	            });
	          	       
 	        		}});
 	        		
 	        	});
	        }
	        return this;
    	},
    	selector: function(args) {
    		var self = this;
    		if (args.callback) {
    			this.callback = args.callback;
    		}
    		if (args.input_type) {
    			this.input_type = args.input_type;
    		}    		
    		addressbook.get_list(null, function (contacts) {
    			addressbook.get_departments(null, function (deps) {
	    		
	    			if (contacts.list.length) {
	    				// 按字母排序
	    				contacts.list = _.sortBy(contacts.list, function (item) {return item.alphaindex;});
	    			}
	    			
	    			var r = new render();
					r.assign('deps_enable', self.deps_enable);

					r.assign('contacts_enable', self.contacts_enable);

	    			r.assign('contacts', contacts.list);
	    			r.assign('deps', deps.lists);
	    			r.assign('input_type', self.input_type);
	    			r.assign('input_name_deps', self.input_name_deps );
	    			r.assign('input_name_contacts', self.input_name_contacts );
			        r.template = contacts_selector_tpl;
			        if (args.only_return_element) {
			        	r.only_return_element = true;
			        }
			        self.page = r.apply();

			        if (!args.only_return_element) {
			        	self.events();
			        }
			        if (self.callback) {
			        	self.callback(self);
			        }
			        
			        
    			});
    		});
	        // 调用其它应用
	        /*
	        var c = new call;
	        c.app('sample', 'goods_detail', {only_return_element: true}, function (el) {
	            // el是elements 节点
	            console.log(el.html());
	            // el.find('.btn').click(function() {
	            // });
	            // $('#div').html(el);
	        });*/
    	},
    	events: function () {
    		var self = this;

			$(window).lazyLoadXT({"scrollContainer": self.page.find('#js-contacts')});
			self.page.find('#js-contacts').animate({scrollTop:1},150);

    		this.event_tabs_switch();

    		this.page.find('[type=checkbox], [type=radio]').on('change', function () {
				self.event_checked(this);
    		});
    		this.page.find('.js-input-tr .js-select-input').on('click', function () {
				//$(this).data('selected', true);
				if (!$(this).parents('.js-input-tr').find('input').attr('disabled')) {
					$(this).parents('.js-input-tr').find('input').prop('checked', !$(this).parents('.js-input-tr').find('input').prop('checked'));
					$(this).parents('.js-input-tr').find('input').trigger( "change" );

				}

    			
    		});
    			// 完成callback;
    		this.page.find('#search').on('keyup', function () {
    			var keyword = $(this).val().toLowerCase();
    			$(self.container).find('.js-input-tr').each(function () {
    				var text = $(this).text().toLowerCase();
    				if (text.match(keyword)) {
    					$(this).show();
    				} else {
    					$(this).hide();
    				}
    			});
    			$(self.container).find('.js-index').each(function () {
    				var index = $(this).text().trim().toUpperCase();
    				if (index) {
    					if ($(self.container).find('[data-index='+index+']:visible').length) {
    						$(this).show();
    					} else {
    						$(this).hide();
    					}
    				}
    			});
			});
    	},
    	event_tabs_switch: function () {
    		var self = this;
    		this.page.find('.js-nav a').on('click', function () {
				self.page.find('.js-nav li').removeClass('active');
				
				self.page.find('[data-role="listview"]').hide();
				self.page.find('#'+$(this).data('for')).show();
				//self.page.find('#'+$(this).data('for')).listview('refresh');
				$(this).parents('li').addClass('active');
			});
    	},
    	event_checked: function (input) {
    		var self = this;
			var box =  $(self.container).find(".js-contacts-window .modal-body");
    		//this.page.find('[type=checkbox], [type=radio]').on('change', function () {
				//if ($(input).parents('.js-input-tr').data('selected'))
				//{
					if ($(input).is(':checked')) {
						var $a = $('<a />');
						$a.attr('id', self.parse_input_name_to_id($(input).data('type'))+'_' + $(input).val());

						var $span = $('<span />');
						$span.text($(input).data('name'));
						$a.append($span);


						$a.data('input', $(input));
						$a.on('click', function () {
							$(this).data('input').attr('checked', false);
							//$(this).data('input').checkboxradio('refresh');
							$(this).remove();
						});
						if ($(input).attr('type') == 'radio') {
							$('.js-current-checked', box).html('');
						}
						$('.js-current-checked', box).append($a);
						if (self.input_type == 'radio') {
							$(self.container).find('.js-contact-save').trigger('click');
						} else {
							$(".js-current-checked", box).animate({scrollLeft:30000},150);
						}




					} else {

						$("#"+self.parse_input_name_to_id($(input).data('type'))+'_' + $(input).val(), box).remove();


					}
					//$(input).parents('.js-input-tr').data('selected', false);
				//}
			//});
    	},
		parse_input_name_to_id: function (name) {
			if (name) {
				name = name.replace('[', '');
				name = name.replace(']', '');
				return name;
			} else {
				return '';
			}
		}
    });
   

    return view;
});
