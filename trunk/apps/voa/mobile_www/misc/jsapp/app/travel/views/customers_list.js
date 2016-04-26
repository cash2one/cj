define(["views/base", "data/customer", "utils/render", "text!templates/customers_list.html", "text!templates/customers_list_data.html", 'jquery', "iscrollview"
         , "css!styles/customer_list.css"], function(base, customer, render, tpl, tpl_list_data, $){
	
    function view() {

    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype = $.extend(view.prototype, {
        // 模板处理
        render: function(args) {
            var self = this;
            var goods_id = args.goods_id;
            customer.get_list({query: this.query, page: this.page_number, limit: this.page_limit}, function (ret) {
            	self.count_page(ret.total);
                // new 械板渲染类  
                var r = new render();
                // 模板内容
                r.template = tpl;
                // 分配变量
                r.vars = {goods_id: goods_id, 'data': r.parse_template(tpl_list_data, {customers: ret})};
                // 应用, 返回当前element节点
                var el = r.apply();

                // 监听事件
                self.event(el, goods_id);
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
        event: function (el, goods_id) {
        	var self = this;
        	$.mobile.activePage.find(".iscroll-wrapper").bind("iscroll_onafterrefresh" , function (event, data) {
        		setTimeout(300, function () {
        			self.click_event(el, goods_id);
        		});
        		//self.click_event(el, goods_id);
            });
            this.click_event(el, goods_id);
            // 访问父类的方法
            this.parent.event.call(this);
            $("#js-search").on('keyup', function () {
            	//console.log($(this).val());
            	if ($.trim($(this).val()) != '') {
            		self.query = $(this).val();
                	self.reload_data();
            	}
            	
            });
            
        },
        // 监听事件   
        click_event: function (el, goods_id) {
        	$('.iscroll-wrapper li').click(function () {
            	$(this).find('.ui-checkbox label').toggleClass( "ui-checkbox-on ui-checkbox-off" );
            	$(this).find("input").attr('checked', !$(this).find("input").attr('checked'));
            });
        	$('.js-btn-add').click(function () {

                var customer_id = [];
                $('input[name=customer]:checked').each(function() {
                	customer_id.push($(this).val());
                });
                if (customer_id.length) {
                    customer.follow_goods({customer_id: customer_id.join(','), goods_id: goods_id}, function(ret) {
                        if (ret) {
                            location.href = "#/goods_list";
                        }

                    });
                }
                return false;

            });
        },
        gotPullUpData: function (event, data, callback) {
        	var self = this;
        	customer.get_list({query: this.query, page: this.page_number, limit: this.page_limit}, function (ret) {
        		self.count_page(ret.total);
        		var r = new render();
                $('.iscroll-wrapper ul.ui-listview').append(r.parse_template(tpl_list_data, {customers: ret})).listview("refresh");
                $('.iscroll-wrapper').checkboxradio("refresh");
        		callback();
        		
        		
        	});
        }
    });

    return view;
});
