define(["data/addressbook", "views/base", "utils/render", "text!templates/contacts.html", "text!templates/contacts_form.html", 'jquery', "iscrollview"
        , "css!styles/common.css", "css!styles/contacts.css", "jquery-lazyload"], function(addressbook, base, render, contacts_selector_tpl, contacts_form_tpl, $){
	
	function view() {
	    	base.call(this);
	}
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype = $.extend(view.prototype, {
    	page: null,
    	callback: null,
    	container: null,
    	last_page: null,
    	// 是否屏蔽部门
    	disable_deps: true,
    	input_type: 'checkbox',
    	input_name_deps: "deps[]",
    	input_name_contacts: "contacts[]",
		contact_data: [],
		deps_data: [],
		current_page: 1,
		page_limit: 10,

    	
    	render: function (args) {
    		var self = this;
    		var r = new render();
			
	        r.template = contacts_form_tpl;
	        if (args.disable_deps) {
	        	this.disable_deps = args.disable_deps;

	        }

	        if (args.contacts_default_data) {
	        	r.assign('contacts', args.contacts_default_data);
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
	        	
	        	$(args.container).html(this.page).trigger('create');
	        	$('.js-contacts-form-row a').on("click", function () {
	        		this.remove();
	        	});
	        	$("#js-call-contacts").off('click').on('click', function () {
	        		
	        		$.mobile.loading( "show" );
	        		if (!self.last_page) {
	 	        		self.selector({only_return_element: true, callback: function (ret) {
	 	        			
	 	        			self.last_page = $.mobile.activePage;
		 	   	        	self.page = $('<div data-role="page" data-url="contacts" />').html(ret.page);
		 	   	        	$('body').append(self.page);
		 	   	        	$.mobile.activePage.addClass('ui-page-keep');
		 	   	        	$.mobile.changePage( self.page , { reverse: false, changeHash: false, transitions: "slide" } );
		 	   	        	$.mobile.activePage.addClass('ui-page-keep');
			 	   	        self.page.find('.js-contact-close').off('click').on('click', function () {
			 	   	        	
				 	   	        var from_page = self.last_page;
			 	            	var selector_page = self.page;
			 	            	$.mobile.changePage(from_page , { reverse: false, changeHash: false, transitions: "slide"} );
			 	            	self.last_page = selector_page;
			 	            	self.page = from_page;
			 	            	
			 	            	return false;
			 	            });
	
			 	            self.page.find('.js-contact-save').off('click').on('click', function () {
			 	            	var from_page = self.last_page;
			 	            	var selector_page = self.page;
			 	            	self.last_page.find('.js-contacts-form-row a').remove();
			 	            	var input_name = '';
			 	            	self.page.find('.js-current-checked a').each(function () {
			 	            		
			 	            		input_name = $(this).data('input').data('type');
			 	            		
			 	            		var a = $(this).clone(true, true);
			 	            		a.append('<input type="hidden" name="'+input_name+'" value="'+$($(this).data('input')).val()+'" />');
			 	            		self.last_page.find('.js-contacts-form-row').append(a);
			 	            	});
			 	            	$.mobile.changePage(from_page , { reverse: false, changeHash: false, transitions: "slide"} );
			 	            	self.last_page = selector_page;
			 	            	self.page = from_page;
			 	            	
			 	            	return false;
			 	            });
	          	            ret.events(function (contact_list) {
	          	            	
	          	            });
	      	            	self.formdata_to_selector();
		          	       
	 	        		}});
	        		} else {
	        			$.mobile.changePage(self.last_page , { reverse: false, changeHash: false, transitions: "slide"} );
	        			
	        			var current = self.last_page;
      	            	self.last_page = self.page;
      	            	self.page = current;
      	            	
	        			self.formdata_to_selector();
	        		}
 	        		
 	        	});
	        }
	        $.mobile.loading( "hide" );
	        return this;
    	},
    	
    	formdata_to_selector: function () {
    		var self = this;
    		self.page.find('.js-current-checked a').remove();
    		
    		// 把已经选择的同步到选择框上
            this.last_page.find('.js-contacts-form-row a').each(function () {
            	var name = $(this).find('input').attr('name');
            	name = name.replace('[', '\\[');
            	name = name.replace(']', '\\]');
            	$(this).data('input', $('input[value='+$(this).find('input').val()+'][data-type='+name+']'));
            	$(this).data('input').attr('checked', true);
            	$(this).data('input').checkboxradio('refresh');
            	var a = $(this).clone(true, true);
            	a.find('input').remove();
            	self.page.find('.js-current-checked').append(a);
            });
            $.mobile.loading( "hide" );
    	},
    	
    	//选择器 
    	selector: function(args) {
    		var self = this;
    		if (args.callback) {
    			this.callback = args.callback;
    		}
    		if (args.input_type) {
    			this.input_type = args.input_type;
    		}

			self.contact_data = addressbook.get_list();
			self.deps_data = addressbook.get_departments();
			if (self.contact_data.list.length) {
				// 按字母排序
				self.contact_data.list = _.sortBy(self.contact_data.list, function (item) {return item.alphaindex;});
			}
			var r = new render();
			r.assign('contacts', self.contact_data.list);
			r.assign('deps', self.deps_data.lists);
			r.assign('input_name_deps', self.input_name_deps );
			r.assign('disable_deps', self.disable_deps );
			r.assign('input_name_contacts', self.input_name_contacts );
			r.assign('input_type', self.input_type);
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
			/*
    		addressbook.get_list(null, function (contacts) {
    			addressbook.get_departments(null, function (deps) {
	    		
	    			if (contacts.list.length) {
	    				// 按字母排序
	    				contacts.list = _.sortBy(contacts.list, function (item) {return item.alphaindex;});
	    			}
	    			
	    			var r = new render();
	    			r.assign('contacts', contacts.list);
	    			r.assign('deps', deps.lists);
	    			r.assign('input_name_deps', self.input_name_deps );
					r.assign('disable_deps', self.disable_deps );
	    			r.assign('input_name_contacts', self.input_name_contacts );
	    			r.assign('input_type', self.input_type);
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
    		});*/
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
		pagination: function () {

		},
    	events: function (callback) {
    		var self = this;
    		this.event_tabs_switch();
    		this.event_checked(callback);
    			// 完成callback;
    		this.page.find('.js-btn-finish').on('click', function () {
    			if (typeof this.callback == 'function') {
    				this.callback();
    			}
				
			});
    	},
    	get_current_checked: function () {
    		$('.js-current-checked', self.page).each (function () {
    			
    		});
    	},
    	event_tabs_switch: function () {
    		var self = this;
    		this.page.find('.js-nav a').on('tap', function () {
				self.page.find('.js-nav a').removeClass('current');
				
				self.page.find('[data-role="listview"]').hide();
				self.page.find('#'+$(this).data('for')).show();
				//self.page.find('#'+$(this).data('for')).listview('refresh');
				$(this).addClass('current');
				return false;
			});
    	},
    	event_checked: function () {
    		this.page.find('[type=checkbox], [type=radio]').on('change', function () {
				if ($(this).is(':checked')) {
					var $a = $('<a />');
					$a.attr('id', 'people_'+$(this).val());
				
					var $span = $('<span />');
					$span.text($(this).data('name'));
					$a.append($span);
					
					
					$a.data('input', $(this));
					$a.on('click', function () {
						$(this).data('input').attr('checked', false);
						$(this).data('input').checkboxradio('refresh');
						$(this).remove();
					});
					if ($(this).attr('type') == 'radio') {
						$('.js-current-checked', self.page).html('');
					} 
					$('.js-current-checked', self.page).append($a);
					$(".js-current-checked", self.page).animate({scrollLeft:30000},150);
					
				} else {
					
					$('#people_'+$(this).val(), self.page).remove();
					
					
				}
			});
    	}
    });
   

    return view;
});
