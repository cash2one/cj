
define(["data/showroom", "utils/call", "utils/render", "text!templates/detail.html", 'jquery', 'utils/api'
        , "css!styles/showroom.css", "swipebox"], function(showroom, call, render, tpl, $, api){
    
    function view() {
        
    }

    view.prototype = {
        render: function(args) {
            var self = this;
            showroom.get_detail({ta_id: args.id}, function (ret) {
                var r = new render();
                r.template = tpl;
                
                r.vars = {d: ret};
                var el = r.apply();
                
                self.event(el);
            });
        },
        event: function (el) {
            this.swipebox(el);
        },
        swipebox: function (el) {
        	// 图片预览
    		$('.ui-body p img', el).on('tap', function () {
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

    return view;
});
