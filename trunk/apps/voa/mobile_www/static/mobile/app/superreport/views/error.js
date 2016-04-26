define(["views/base",  "data/superreport", "utils/render", "text!templates/error.html", "jquery"
         ,"iscroll", "css!styles/common.css", "css!styles/superreport_view.css"], function(base, superreport, render, tpl, $){
	
    function view() {
    	base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype = $.extend(view.prototype, {
        // 模板处理
        render: function(args) {

            var self = this;
            var errcode = args.errcode;
            //检查权限

            var r = new render();
            // 模板内容
            r.template = tpl;
            // 分配变量
            r.vars = {errcode: errcode};

            // 应用, 返回当前element节点
            self.page = r.apply();
        }
    });
    
    return view;
});
