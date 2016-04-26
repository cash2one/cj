define([], function(){
	
    function view() {
    }
    
    view.prototype = {
    	page_number: 1,
    	page_limit: 3,
    	page_total: 1,
    	// current page element;
    	page: null,
    	query: '', 
    
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
