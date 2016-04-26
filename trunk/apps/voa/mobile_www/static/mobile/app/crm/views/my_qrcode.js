define(["data/goods", "views/base", "utils/render", "text!templates/my_qrcode.html", "jquery"
         , "css!styles/common.css",  "css!styles/my_qrcode.css"], function(goods, base, render, tpl, $){
	
    function view() {
    	base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype = $.extend(view.prototype, {
    	page_limit: -1,
    	goods_id: null,
    	callback: null,
        // 模板处理
        render: function(args) {
            var self = this;
        
        	//self.count_page(ret.total);
            // new 械板渲染类  
            var r = new render();
            // 模板内容
            r.template = tpl;
            goods.get_qrcode({}, function (ret) {
                r.assign('qrcode', ret.qrcode_url);
                self.page = r.apply();

            });
        }
        
        
    });

    return view;
});
