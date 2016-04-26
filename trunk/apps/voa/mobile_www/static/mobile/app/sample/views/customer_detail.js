define(["views/base", "utils/render", "text!templates/customer_detail.html", 'jquery'
         , "css!styles/customer_detail.css"], function(base, render, tpl, $){
	
	function view() {
    	base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    view.prototype = $.extend(view.prototype, {
    	detail: null,
    	customer_id: null,
        // 模板处理
        render: function(args) {
        	var self = this;
        	if (args.id) {
                // new 械板渲染类  
                var r = new render();
                // 模板内容
                r.template = tpl;
                // 分配变量
                r.assign('goods',  'safsdafasdfdsafdsafa');
                // 应用, 返回当前element 节点
                self.page = r.apply();
                
                
               
            }
        	
            
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
        event: function () {
        	var self = this;
        	$( document ).on( "pageshow", self.page, function() {
        		
        		//self.swipebox(self.page);
        		
        	});
        }
    });

    return view;
});
