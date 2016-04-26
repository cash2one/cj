define(["views/base", "data/customer", "utils/render", "text!templates/customers_list.html", "underscore", 'jquery', "iscrollview"
         , "css!styles/customer_list.css"], function(base, customer, render, tpl, _, $){
	
    function view() {
    	base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype = $.extend(view.prototype, {
    	page_limit: -1,
    	goods_id: null,
    	callback: null,
        // 模板处理
        render: function(args) {
            var self = this;
            this.goods_id = args.goods_id;
            if (typeof args.callback == 'function') {
            	this.callback == args.callback;
            }
            customer.get_list({goods_id: this.goods_id, query: this.query, page: this.page_number, limit: this.page_limit}, function (ret) {
            	//self.count_page(ret.total);
                // new 械板渲染类  
                var r = new render();
                // 模板内容
                r.template = tpl;
                // 分配变量
                r.vars = {goods_id: self.goods_id, customers: ret};
                // 应用, 返回当前element节点
                self.page = r.apply();
                // 监听事件
                self.event(self.goods_id);
            });
        }, 
        // 数据业务处理处
        data: function () {
            //data/goods
            var data = {};
            //var goods = new goods();
            //data = goods.list();
            return data;
        },
        // 监听事件   
        event: function (goods_id) {
        	var self = this;
        	/*
        	$.mobile.activePage.find(".iscroll-wrapper").bind("iscroll_onafterrefresh" , function (event, data) {
        		setTimeout(300, function () {
        			self.click_event(el, goods_id);
        		});
        		//self.click_event(el, goods_id);
            });*/
            
            // 访问父类的方法
            //this.parent.event.call(this);
            //$( document ).on( "pageshow", el, function() {
        		// 单击事件
            	this.click_event(goods_id);
            	// 搜完毕
            	this.page.find("#js-search").bind( "change", function(event, ui) {
	            //this.page.find("#js-search").on('keyup', function () {
	            	if ($.trim($(this).val()) != '') {
	            		self.query = $(this).val();
	            		self.page.find('.iscroll-wrapper .ui-li-static').each(function () {
	            			var text = $(this).find('h3 span').text();
	            			var mobile = $(this).find('.tel').text();
	            			$(this).show();
	            			if (text != self.query ) {
		            			if (!text.match(self.query)) {
		            				if (mobile != self.query) {
			            				if (!mobile.match(self.query)) {
			            					$(this).hide();
			            					if (goods_id) {
			            						$(this).find('.ui-checkbox label').removeClass('ui-checkbox-on');
				            					if ($(this).find('.ui-checkbox label').hasClass( "ui-checkbox-off" )) {
				            						$(this).find('.ui-checkbox label').addClass( "ui-checkbox-off" );
				            					}
				            					$(this).find("input").attr('checked', false);
			            					}
			            					
			            				}
		            				}
		            				
		            			}
	            			}
	            		});
	                	//self.reload_data();
	            	} else {
	            		self.page.find('.iscroll-wrapper .ui-li-static').show();
	            	}
	            	
	            });
            //});
            
        },
        checkbox_taked: function (el) {
        	$(el).find('.ui-checkbox label').toggleClass( "ui-checkbox-on ui-checkbox-off" );
        	var el_box = $(el).find("input");
        },
        // 监听事件   
        click_event: function (goods_id) {
        	var self = this;
        	this.page.find('.iscroll-wrapper li').unbind('tap');
        	this.page.find('.iscroll-wrapper li').on('tap', function () {
        		if (self.goods_id) {
        			self.checkbox_taked(this);
        		} else {
        			location.href = "#/customer_detail/"+$(this).data('dataid');
        		}
        		
            });
        	//$('#js_btn_customer_add').unbind('click');
        	
        	this.page.find('#js_btn_customer_add').on('click', function () {
        		var customer_id = [];
        		
        		self.page.find('input').each(function () {
        			if ($(this).parents('.ui-checkbox').find('label').hasClass('ui-checkbox-on')) {
        				customer_id.push($(this).val());
        			}
        		});
                if (customer_id.length) {
                    customer.follow_goods({goods_id: goods_id, customer_id: customer_id.join(',')}, function(ret) {
                        if (ret) {
                            //location.href = "#/goods_list";
                        	history.go(-1);
                        }

                    });
                } else {
                	self.tips('#positionWindow', '请选择您要关联的客户!');
                }
                return false;

            });
        },
        
    });

    return view;
});
