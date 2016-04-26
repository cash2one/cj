define(["views/base", "data/goods", "utils/call", "utils/render", "text!templates/goods_list.html", 'jquery', "iscrollview"
        , "css!styles/goods_list.css"], function(base, goods, call, render, tpl, $){
	
    function view() {
        
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    view.prototype.parent = base.prototype;
    view.prototype.render =  function(args) {
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
        /*
        var c = new call;
        c.app('sample', 'goods_detail', {only_return_element: true}, function (el) {
            // el是elements 节点
            console.log(el.html());
            // el.find('.btn').click(function() {
            // });
            // $('#div').html(el);
        });*/
    };
    view.prototype.gotPullUpData = function (event, data, callback) {
    	goods.get_list({page: this.page_number, limit: this.page_limit}, function (ret) {
    		
    		$.each(ret.data, function(key, item) {
    			var iscrollview = $('.iscroll-wrapper').find('.js-item:last-child').clone();
    			iscrollview.find(".js-subject").text(item.subject);
    			iscrollview.find(".js-cover").attr('href', '#/goods_detail/'+item.dataid).find('img').attr('src', item.cover);
    			iscrollview.find(".js-price").text(item.price+item.price_unit);
    			iscrollview.find(".js-goodsct").text(item.goodsct);
    			iscrollview.find(".js-add-customer").attr('href', "#/customers_list/"+item.dataid);
    			//$('.iscroll-wrapper ul.ui-listview li:first-child').remove();
    			$('.iscroll-wrapper ul.ui-listview').append(iscrollview);//.listview("refresh");  
    		});
    		callback();
    		
    	});
    };
   

    return view;
});
