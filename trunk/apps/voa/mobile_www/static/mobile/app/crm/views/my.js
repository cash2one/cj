define(["views/base", "data/goods", "utils/call", "utils/render", "text!templates/my.html", 'jquery', "iscrollview"
        , "css!styles/common.css", "css!styles/my.css"], function(base, goods, call, render, tpl, $){
	
    function view() {
    	//this.parent.hide_weixin_menu.call(this);
    	base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    view.prototype.parent = base.prototype;
    view.prototype.render =  function(args) {
        var self = this;
        var sales_counts = goods.get_sales_counts();

        var r = new render();
        r.template = tpl;
        r.assign("sales_counts", sales_counts);
        var el = r.apply();
        self.page = el;
        self.event(el);

    };

   

    return view;
});
