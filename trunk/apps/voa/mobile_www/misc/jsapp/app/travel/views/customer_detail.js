define(["utils/render", "text!templates/customer_detail.html", 'jquery', 'data/customer'
         ], function(render, tpl, $, api, customer){
	
    function view() {

    }

    view.prototype = {
        // 模板处理
        render: function(args) {
            // new 械板渲染类  
            var r = new render();
            // 模板内容
            r.template = tpl;
            // 分配变量
            r.vars = this.data();
            // 应用, 返回当前element 节点
            var el = r.apply();

            // 监听事件
            this.event(el);
        }, 
        // 数据业务处理处
        data: function () {
            //data/goods  <%=a%>
            var data = {a: 1, b:2};
            //var goods = new goods();
            //data = goods.list();
            return data;
        },
        // 监听事件   
        event: function (el) {
            $(el).find('.btn').click(function () {
            });
        }
    };

    return view;
});
