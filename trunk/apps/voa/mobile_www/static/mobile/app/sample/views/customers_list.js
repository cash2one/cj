define(["views/base", "utils/render", "text!templates/customers_list.html", "jquery"
         , "css!styles/customer_list.css"], function(base, render, tpl, $){
	
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
            // 分配变量
            //r.vars = {goods_id: self.goods_id, customers: ret};
            // 应用, 返回当前element节点
            self.page = r.apply();
            // 监听事件
        },
        
        
    });

    return view;
});
