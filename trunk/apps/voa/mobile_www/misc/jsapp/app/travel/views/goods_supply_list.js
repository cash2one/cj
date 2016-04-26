define(["views/base", "data/goods", "utils/render", "text!templates/goods_supply_list.html", "text!templates/goods_supply_list_data.html", 'jquery', "iscrollview"
        , "css!styles/goods_supply_list.css"], function( base, goods, render, tpl, tpl_list_data, $){
	
	
    function view() {
    //	base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype.render = function(args) {
        var self = this;
        // 数据业务处理处
        goods.get_supply_list({page: this.page_number, limit: this.page_limit}, function (ret) {
        	
        	self.count_page(ret.total);

        	// new 械板渲染类  
            var r = new render();
            // 模板内容
            r.template = tpl;
            // 分配变量
            r.vars = {'data': r.parse_template(tpl_list_data, {goods: ret})};
            // 应用, 返回当前element 节点
            var el = r.apply();

            // 监听事件
            self.event(el);
        })
        
    };
    
    // 监听事件   
    view.prototype.event = function (el) {
    	var self = this;
    	$.mobile.activePage.find(".iscroll-wrapper").bind("iscroll_onrefresh" , function (event, data) {
    		setTimeout(300, function () {
    			self.click_event();
    		});
        });
        this.click_event();
        // 访问父类的方法
        this.parent.event.call(this);
        
    };
    
    view.prototype.click_event = function () {
    	$('.js-supply-add').click(function () {
        	var el_add_btn = $(this);
            //console.log();
        	$('#confirm').popup('open');/*
            goods.add_from_supply($(this).data('dataid'));
            //$(this).remove();
            location.href = "#/goods_list";*/
        	var id = $(this).data('dataid');
        	$('#yes').click(function() {
        		$.mobile.loading( "show" );
            	goods.add_from_supply(id, function (ret) {
            		$.mobile.loading( "hide" );
            		if (ret.errcode == '0') {
            			el_add_btn.remove();
            			//location.href = "#/goods_list";
            		} else {
            			alert(ret.errmsg);
            		}
            	});
            	
            });
        	
            return false;
        });
    };
    
    view.prototype.gotPullUpData = function (event, data, callback) {
    	goods.get_supply_list({page: this.page_number, limit: this.page_limit}, function (ret) {
    		var r = new render();
            $('.iscroll-wrapper ul.ui-listview').append(r.parse_template(tpl_list_data, {goods: ret}));
    		callback();
    		
    	});
    };
    

    return view;
});
