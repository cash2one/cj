define(["data/goods", "utils/render", "text!templates/goods_detail.html", 'jquery', 'underscore'
         , "css!styles/goods_detail.css", "swipebox"], function(goods, render, tpl, $, _){
	
    function view() {

    }

    view.prototype = {
        // 模板处理
        render: function(args) {
            var self = this;
            goods.get_detail({dataid: args.id, src_field: args.sid}, function (ret) {
                var r = new render();
                r.template = tpl;
                r.vars = {goods: ret};
                var el = r.apply();
                self.event(el);
            });
        }, 
        // 监听事件   
        event: function (el) {
        	$( document ).on( "pageshow", el, function() {
        		
        		$('img', el).click(function () {
        			var p = $(this).attr('org');
        			if (!p) {
        				p = $(this).attr('src');
        			}
        			var photo = [];
            		$( '.general_frame img', el).each(function () {
            			var pic = $(this).attr('org');
            			if (!pic) {
            				pic = $(this).attr('src');
            			}
            			photo.push({href: pic});
            			
            		});
            		
        			if (p) {
        				photo = _.filter(photo, function(item){return item.href != p});
        				photo.unshift({href:p});
        				$.swipebox(photo);
        			}
        			
        		});
        	});
        	
        }
    };

    return view;
});
