define(["data/addressbook", "views/base", "utils/render", "text!templates/groups.html", 'jquery', "iscrollview"
        , "css!styles/common.css", "css!styles/groups.css"], function(addressbook, base, render, tpl, $){
	 function view() {
	    	base.call(this);
	    }
	    view.prototype = Object.create(base.prototype);
	    view.prototype.constructor = view;
	    
	    view.prototype = $.extend(view.prototype, {
	    	render: function(args) {
	    
		        var self = this;
		       
		        addressbook.get_departments(null, function (ret) {
	    			console.log(ret);
	    			var r = new render();
			        r.template = tpl;
			        this.page = r.apply();
	    		});
		        // 调用其它应用
		        /*
		        var c = new call;
		        c.app('sample', 'goods_detail', {only_return_element: true}, function (el) {
		            // el是elements 节点
		            console.log(el.html());
		            // el.find('.btn').click(function() {
		            // });
		            // $('#div').html(el);
		        });*/
	    	},
	    });
	   

	    return view;
	});
