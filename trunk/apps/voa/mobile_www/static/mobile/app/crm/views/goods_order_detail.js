define(["data/goods", "views/goods_order_list", "utils/render", "text!templates/goods_order_detail.html", "jquery"
        , "css!styles/common.css" , "css!styles/goods_order_detail.css"], function(goods, base, render, tpl, $){
    
    function view() {
        base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype = $.extend(view.prototype, {
        page_limit: -1,
        goods_id: null,
        order_id: null,
        callback: null,
        // 模板处理
        render: function(args) {
            var self = this;
            this.order_id = args.order_id;
            //self.count_page(ret.total);
            // new 械板渲染类  
            var r = new render();
            // 模板内容
            r.template = tpl;
            goods.get_order_detail({orderid: this.order_id}, function (ret) {
                r.assign('order', ret);
                self.page = r.apply();
                self.event();
                // 取消订单
                self.page.find('.js-cancel-order').off('click').on('click', function () {
                    var that = this;
                    goods.del_order({orderid: $(this).data('orderid')}, function() {
                        location.href = "#/goods_order_list";
                    });
                });

            });
            // 分配变量
            //r.vars = {goods_id: self.goods_id, customers: ret};
            // 应用, 返回当前element节点

            // 监听事件
        }

        
        
    });

    return view;
});
