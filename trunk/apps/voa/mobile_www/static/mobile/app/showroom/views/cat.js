define(["views/base", "data/showroom", "utils/render", "text!templates/cat.html", "text!templates/cat_data.html", 'jquery', "iscrollview"
        , "css!styles/showroom.css"], function( base, showroom, render, tpl, tpl_list_data, $){
	
	
    function view() {
    	base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    view.prototype.page_limit = 10;
    
    view.prototype.render = function(args) {
        var self = this;
        // 数据业务处理处
        showroom.get_cat({page: this.page_number, limit: this.page_limit}, function (ret) {
        	
        	self.count_page(ret.total);

        	// new 械板渲染类  
            var r = new render();
            // 模板内容
            r.template = tpl;
            
           //解析子模板
            var son = r.parse_template(tpl_list_data, {ls: ret});	
            r.vars = {'data': son};
            
            // 应用, 返回当前element 节点
            self.page = r.apply();
			if(ret.total > 0) {
        		$('.empty').remove();
        	}else{
        		$('.ui-listview, .ui-field-footer').remove();
        	}
        	if(ret.limit * ret.page >= ret.total) {
    			$('.ui-field-footer').hide();
    		}
        	
            // 监听事件
            self.event(self.page);
        })
        
    };
    
    // 监听事件   
    view.prototype.event = function (el) {
    	var self = this;
        this.click_event();
        // 访问父类的方法
        this.parent.event.call(this);
        
    };
    
    view.prototype.click_event = function () {
    	var self = this;
    	$( document ).on( "pageshow", self.page, function() {
    		$('.js-goods-item', self.page).on('tap', function () {
    			location.href = $(this).data('href');
    		});
	    	$('.js-supply-add', self.page).on('tap', function () {
	        	var el_add_btn = $(this);
	        	$('#confirm').popup('open');
	        	var id = $(this).data('dataid');
	        	$('#yes', self.page).on('tap', function() {
	        		$.mobile.loading( "show" );
	            	showroom.add_from_supply(id, function (ret) {
	            		$.mobile.loading( "hide" );
	            		if (ret.errcode == '0') {
	            			el_add_btn.remove();
	            		} else {
	            			alert(ret.errmsg);
	            		}
	            	});
	            	
	            });
	        	
	            return false;
	        });
    	});
    	$('#list a').click(function (){
    		$('body').data('title', $(this).text());
    	});
    	$("#js-search").bind( "change", self.search);
    };
    
    view.prototype.got_pull_up_data = function (event, data, callback) {
    	var self = this;
    	showroom.get_cat({page: this.page_number, limit: this.page_limit}, function (ret) {
    		var r = new render();
    		var son = r.parse_template(tpl_list_data, {ls: ret});
            $('ul.ui-listview').append(son).listview("refresh");
            self.click_event();
    		callback();
    		if(ret.limit * ret.page >= ret.total) {
    			$('.ui-field-footer').hide();
    		}
    		self.search();
    	});
    };
    

    return view;
});
