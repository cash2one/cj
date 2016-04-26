define(["views/base", "data/goods", "utils/render", "text!templates/goods_supply_list.html", "text!templates/goods_supply_list_data.html", 'jquery', "iscrollview"
        , "css!styles/goods_supply_list.css"], function( base, goods, render, tpl, tpl_list_data, $){
	
	
    function view() {
    	base.call(this);
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
			r.assign('more_data', 0);
			if (ret.total > self.page_limit) {
				r.assign('more_data', 1);
			}
			r.assign('no_data', 0);
			if (ret.data.length <= 0) {
				r.assign('no_data', 1);
			}
            // 分配变量
            r.assign('data', r.parse_template(tpl_list_data, {goods: ret}));
            // 应用, 返回当前element 节点
            self.page = r.apply();

            // 监听事件
            self.event(self.page);
        })
        
    };
    
    // 监听事件   
    view.prototype.event = function (el) {
    	var self = this;
    	/*
    	$.mobile.activePage.find(".iscroll-wrapper").bind("iscroll_onrefresh" , function (event, data) {
    		setTimeout(300, function () {
    			self.click_event();
    		});
        });*/
        this.click_event();
        // 访问父类的方法
        this.parent.event.call(this);
        
    };
    
    view.prototype.click_event = function () {
    	var self = this;
    	//$( document ).on( "pageshow", self.page, function() {
	    	//this.page.find('.js-supply-add').unbind( "click" );
    		$('.js-goods-item', self.page).off('tap').on('tap', function () {
    			location.href = $(this).data('href');
    		});
    		
	    	$('.js-supply-add', self.page).off('click').on('click', function () {
	        	var el_add_btn = $(this);
	        	$('#confirm').popup('open');
	        	var id = $(this).data('dataid');
	        	$('#yes', self.page).unbind('click');
	        	$('#yes', self.page).on('click', function() {
	        		$('#confirm').popup('close');
	        		$.mobile.loading( "show" );
	            	goods.add_from_supply(id, function (ret) {
	            		$.mobile.loading( "hide" );
	            		if (ret.errcode == '0') {
	            			$('.js-added', el_add_btn.parents('.js-row')).show();

	            			el_add_btn.remove();
	            		} else {
	            			self.tips('.js-tips-window', ret.errmsg);
	            		}
	            	});
	            	
	            });
	        	
	            return false;
	        });
    	//});
    };
    
    view.prototype.got_pull_up_data = function (event, data, callback) {
    	var self = this;
    	goods.get_supply_list({page: this.page_number, limit: this.page_limit}, function (ret) {
    		var r = new render();
            $('.iscroll-wrapper ul.ui-listview').append(r.parse_template(tpl_list_data, {goods: ret})).listview("refresh");
            self.click_event();
    		callback();
    		
    	});
    };
    

    return view;
});
