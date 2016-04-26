define(["utils/common", "underscore", "data/goods", "views/goods_selected", "utils/render", "text!templates/goods_order_manage.html", "jquery"
        , "css!styles/common.css" , "css!styles/goods_order_manage.css"], function(common, _, goods, base, render, tpl, $){
    
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
            // 应用, 返回当前element节点

            goods.get_order_goods({today: 1}, function (ret) {
                r.assign('goods', ret.list);
                r.assign('today_date', common.dateformat());
                self.page = r.apply();
            });


            // 监听事件
        }
        
        
    });

    return view;
});
