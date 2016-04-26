define(["views/base", "data/goods", "utils/call", "utils/render", "text!templates/goods_list.html", "text!templates/goods_list_data.html", 'jquery', "iscrollview"
    , "css!styles/common.css", "css!styles/goods_list.css", "jquery-lazyload"], function(base, goods, call, render, tpl, tpl_list_data, $){
	
    function view() {
    	//this.parent.hide_weixin_menu.call(this);
    	base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    view.prototype.parent = base.prototype;
    view.prototype.render =  function(args) {
        var self = this;
        document.title = "产品列表";
        var goodsclass = goods.get_goodsclass_list();
        goods.get_list({page: this.page_number, classid: args.classid, limit: this.page_limit}, function (ret) {
        	// init page total
        	self.count_page(ret.total);
            var r = new render();
            r.template = tpl;
            r.assign("goodsclass", goodsclass);
            r.assign('more_data', 0);
            if (ret.total > self.page_limit) {
                r.assign('more_data', 1);
            }
            r.assign('no_data', 0);
            if (ret.data.length <= 0) {
                r.assign('no_data', 1);
            }

            r.assign("goods", ret);
            r.assign("timestamp", ret.timestamp);
            r.assign('data', r.parse_template(tpl_list_data, {goods: ret}));

            $(window).lazyLoadXT();

            var el = r.apply();
            self.page = el;

            self.event(el);
        });
       
    };

    view.prototype.event = function () {
        this.parent.event.call(this);

        // 置顶
        $('.js-top').on("click", function () {
            if ($(this).hasClass('notop')) {
                // 更改样式
                $(this).removeClass('notop');

                // 插到前面
                var item = $(this).parents('.js-item');
                item.clone(true, true).insertBefore($('.js-item:first'));
                item.remove();
            } else {
                $(this).addClass('notop');
                // 插到前面
                var item = $(this).parents('.js-item');
                item.clone(true, true).insertAfter($('.js-item:last'));
                item.remove();
            }
            goods.top({goodsid: $(this).data('id')}, function () {

            });
        });
    };

    view.prototype.got_pull_up_data = function (event, data, callback) {
        var self = this;
        goods.get_list({page: this.page_number, limit: this.page_limit}, function (ret) {
            var r = new render();
            $('.iscroll-wrapper ul.ui-listview').append(r.parse_template(tpl_list_data, {goods: ret}));
            //$('.js-container').trigger('create');

            $('.iscroll-wrapper ul.ui-listview').listview("refresh");
            self.event();
            callback();

        });
    };
   

    return view;
});
