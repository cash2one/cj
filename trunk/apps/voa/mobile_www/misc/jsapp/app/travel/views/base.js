define([], function(){
	
    function view() {
        
    }
    
    view.prototype = {
    	page_number: 1,
    	page_limit: 2,
    	page_total: 1,
    	query: '', 
    	/*
        render: function(args) {
            var self = this;
            goods.get_list({page: this.page_number, limit: this.page_limit}, function (ret) {
            	// init page total
            	self.count_page(ret.total);
                var r = new render();
                r.template = tpl;
                r.vars = {goods: ret};
                var el = r.apply();
                self.event(el);
            });
            // 调用其它应用
            //var c = new call;
            //c.app('sample', 'goods_detail', {only_return_element: true}, function (el) {
                // el是elements 节点
                //console.log(el.html());
                // el.find('.btn').click(function() {
                // });
                // $('#div').html(el);
           // });
        }, */
        data: function () {
            //data/goods
            //return {goods: goods.get_list()};
        },
        event: function (el) {
        	var self = this;
            $.mobile.activePage.find(".iscroll-wrapper").bind("iscroll_onpulldown", function (event, data) {
            	self.onPullDown(event, data);
            });
            
            $.mobile.activePage.find(".iscroll-wrapper").bind("iscroll_onpullup", function (event, data) {
            	self.onPullUp(event, data);
            });
        }, 
        count_page: function (total) {
        	if (total && total > this.page_limit) {
        		var page_total = Math.ceil(total/this.page_limit);
        		if (page_total != page_total) {
        			this.page_number = 1;
        		}
        		this.page_total = page_total;
        	}
        },
        onPullDown: function (event, data) {

        	this.page_number --;
        	if (this.page_number > 0) {
        		this.reload_data(event, data);
        	} else {
        		this.page_number = 1;
        	}
        	data.iscrollview.refresh(); 
        },
        reload_data: function (event, data) {
        	var lastpage = $('.iscroll-wrapper ul.ui-listview li');
    		this.gotPullUpData(event, data, function () {
    			lastpage.remove();
    		});
        },
        onPullUp: function (event, data) {
        	this.page_number ++;
        	if (this.page_total >= this.page_number) {
        		this.reload_data(event, data);
        	} else {
        		this.page_number --;
        	}
        	data.iscrollview.refresh(); 
        },
        gotPullUpData: function (event, data, callback) {
        	
        }
    };
    view.prototype.parent = view.prototype;
    
    return view;
});
