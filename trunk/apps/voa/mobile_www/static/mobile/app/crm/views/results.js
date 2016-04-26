define(["views/base", "data/goods", "utils/call", "utils/render", "text!templates/results.html", 'jquery', "iscrollview"
        , "css!styles/common.css", "css!styles/results.css"], function(base, goods, call, render, tpl, $){
    
    function view() {
        //this.parent.hide_weixin_menu.call(this);
        base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    view.prototype.parent = base.prototype;
    view.prototype.render =  function(args) {
        var self = this;
        var goodsclass = goods.get_goodsclass_list();
        goods.get_list({is_admin:1, classid: args.classid, page: this.page_number, limit: this.page_limit}, function (ret) {
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
            var el = r.apply();
            self.page = el;
            self.event(el);
        });
       
    };
    view.prototype.event = function () {
        this.parent.event.call(this);

        // 置顶
        $('.js-top').on("click", function () {
            // 更改样式
            $(this).removeClass('notop');


            goods.top({goodsid: $(this).data('id')}, function (){

            });
            // 插到前面
            var item = $(this).parents('.js-item');
            item.clone(true, true).insertBefore($('.js-item:first'));
            item.remove();

        });
    };
    
    view.prototype.got_pull_up_data = function (event, data, callback) {
        goods.get_list({page: this.page_number, limit: this.page_limit}, function (ret) {
            
            $.each(ret.data, function(key, item) {
                var iscrollview = $('.iscroll-wrapper').find('.js-item:last-child').clone();
                iscrollview.find(".js-subject").text(item.subject);
                iscrollview.find(".js-cover").attr('href', '#/goods_detail/'+item.dataid+'/promotion/'+item.sig+'/'+ret.timestamp).find('img').attr('src', item.cover);
                iscrollview.find(".js-price").text(item.price+item.price_unit);
                iscrollview.find(".js-goodsct").text(item.goodsct);
                iscrollview.find(".js-add-customer").attr('href', "#/customers_list/"+item.dataid);
                //$('.iscroll-wrapper ul.ui-listview li:first-child').remove();
                $('.iscroll-wrapper ul.ui-listview').append(iscrollview).listview("refresh");  
            });
            callback();
            
        });
    };
   

    return view;
});
