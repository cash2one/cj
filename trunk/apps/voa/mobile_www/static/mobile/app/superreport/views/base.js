define(["data/superreport"], function(superreport){
	
    function view() {
    	// 微信右上角菜单开关
    	this.weixin_menu_switch();
    }
    
    view.prototype = {
    	page_number: 1,
    	page_limit: 3,
    	page_total: 1,
    	// current page element;
    	page: null,
    	query: '', 
    	// 微信右上角菜单开关 false为关 true为开
    	show_weixin_menu: false,
		rights: null,
    	// 微信右上角菜单
    	weixin_menu_switch: function () {
    		var self = this;
    		function onBridgeReady(){
    			if (self.show_weixin_menu) {
    				WeixinJSBridge.call('showOptionMenu');
    			} else {
    				WeixinJSBridge.call('hideOptionMenu');
    			}
    			
			}

			if (typeof WeixinJSBridge == "undefined"){
			    if( document.addEventListener ){
			        document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
			    }else if (document.attachEvent){
			        document.attachEvent('WeixinJSBridgeReady', onBridgeReady); 
			        document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
			    }
			}else{
			    onBridgeReady();
			}
    	},
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
            	self.on_pull_down(event, data);
            });
            /*
            $.mobile.activePage.find(".iscroll-wrapper").bind("iscroll_onpullup", function (event, data) {
            	self.on_pull_up(event, data);
            });*/
            $('.js-btn-nextpage').click(function (){
            	var that_btn = this;
            	self.next_page(function () {
            		$(that_btn).hide();
            	});
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
        on_pull_down: function (event, data) {
        	this.page_number = 1;
			var lastpage = $('.iscroll-wrapper ul.ui-listview li');
    		this.reload_data(event, data, function () {
    			lastpage.remove();
    			$('.js-btn-nextpage').show();
    		});
        	
        	data.iscrollview.refresh(); 
        },
        reload_data: function (event, data, callback) {
        	var self = this;
        	//var lastpage = $('.iscroll-wrapper ul.ui-listview li');
    		this.got_pull_up_data(event, data, function () {
    			if (typeof callback == 'function') {
    				callback();
        		}
    			
    			//lastpage.remove();
    		});
        },
        next_page: function (nopage_callback) {
        	this.page_number ++;
        	if (this.page_total >= this.page_number) {
        		this.reload_data();
        	} else {
        		this.page_number --;
        		if (typeof nopage_callback == 'function') {
        			nopage_callback();
        		}
        	}
        },
        on_pull_up: function (event, data) {
        	this.next_page();
        	data.iscrollview.refresh(); 
        },
        got_pull_up_data: function (event, data, callback) {
        	
        },
        tips: function (id, text, callback) {
        	$(id).find('p').text(text);
        	$(id).popup('open');
        	setTimeout( function () {
        		$(id).popup('close');
        		if (typeof callback == 'function') {
        			callback();
        		}
        	}, 2000);
        }, 
        swipebox: function (el) {
        	// 图片预览
    		$('img', el).on('tap', function () {
    			var p = $(this).attr('org');
    			if (!p) {
    				p = $(this).attr('src');
    			}
    			var photo = [];
        		$( 'img', el).each(function () {
        			var pic = $(this).attr('org');
        			if (!pic) {
        				pic = $(this).attr('src');
        			}
        			if (pic) {
        				photo.push({href: pic});
        			}
        			
        			
        		});
        		
    			if (p) {
    				photo = _.filter(photo, function(item){return item.href != p});
    				photo.unshift({href:p});
    				$.swipebox(photo);
    			}
    			
    		});
        }
    };
    view.prototype.parent = view.prototype;
    
    return view;
});
