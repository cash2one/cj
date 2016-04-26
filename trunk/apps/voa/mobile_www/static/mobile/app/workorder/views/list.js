define(["data/workorder", "utils/call", "utils/render", "text!templates/list.html", 'jquery', 'utils/api'
        , "css!styles/workorder.css", "css!styles/common.css"], function(workorder, call, render, tpl, $, api){
	
    function view() {
        
    }

    view.prototype = {
        render: function(args) {
            var self = this;
            workorder.get_list(args, function (ret) {
                var r = new render();
                r.template = tpl;
                r.vars = {ls: ret, type:args.type, res:args.res};
                var el = r.apply();
                self.event(el);
            });
        }, 
        data: function () {
            //data/goods
            //return {goods: goods.get_list()};
        },
        event: function (el) {
            //$(el).find('.btn').click(function () {
            //});
        }
    };

    return view;
});
