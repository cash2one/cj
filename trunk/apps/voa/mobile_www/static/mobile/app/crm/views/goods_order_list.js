define(["data/goods", "views/base", "utils/render", "text!templates/goods_order_list.html", "text!templates/goods_order_list_data.html", "jquery"
        , "css!styles/common.css" , "css!styles/goods_order_list.css"], function(goods, base, render, tpl, tpl_list_data, $){
    
    function view() {
        base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype = $.extend(view.prototype, {
        order_id: null,
        page_limit: 10,
        // 模板处理
        render: function(args) {
            var self = this;
        
            //self.count_page(ret.total);
            // new 械板渲染类  
            var r = new render();
            // 模板内容
            r.template = tpl;

            goods.get_order_list({type: 'client', page: this.page_number, limit: this.page_limit}, function (ret){
                self.count_page(ret.total);
                r.assign('more_data', 0);
                if (ret.total > self.page_limit) {
                    r.assign('more_data', 1);
                }
                r.assign('no_data', 0);
                if (ret.list.length <= 0) {
                    r.assign('no_data', 1);
                }

                // 分配变量
                r.assign('data', r.parse_template(tpl_list_data, {order_list: ret.list}));

                self.page = r.apply();
                self.event();
                // 取消订单
                self.page.find('.js-cancel-order').off('click').on('click', function () {
                    var that = this;
                    goods.del_order({orderid: $(this).data('orderid')}, function() {
                        $(that).parents('.js-item').remove();
                    });
                });
            });
        },
        event: function () {
            var self = this;
            //$('.js-lastpage').
            // 下一页
            self.page.find('.js-btn-nextpage').click(function (){
                var that_btn = this;
                self.next_page(function () {
                    $(that_btn).parent().hide();
                });
                $('.js-btn-lastpage').parent().show();
            });

            // 上一页
            self.page.find('.js-btn-lastpage').click(function () {
                //console.log('ddd');
                var that_btn = this;
                self.last_page(function () {
                    $(that_btn).parent().hide();
                });
                $('.js-btn-nextpage').parent().show();
            });

            // 继续支付
            self.page.find('.js-go-payment').on('click', function () {
                var that = this;
                if ($(that).data('disabled')) {
                    return false;
                }
                $(that).data('disabled', true);
                if ($(that).data('orderid')) {
                    self.order_id = $(that).data('orderid');
                }
                goods.order_pay_continue({orderid: self.order_id}, function (pay_params) {
                    WeixinJSBridge.invoke(
                        'getBrandWCPayRequest',
                        pay_params,
                        function(res){
                            WeixinJSBridge.log(res.err_msg);
                            $('#debug').append('<br/>回調信息:<br/>');
                            if(res.err_msg == 'get_brand_wcpay_request:ok') {
                                $('#debug').append('支付成功');
                                $('.js-order-status').text('支付成功');
                                $('.js-go-payment').remove();
                                $('.js-cancel-order').remove();

                                //location.href = $(that).prop('href');
                            }else if(res.err_msg == 'get_brand_wcpay_request:cancel') {
                                $('#debug').append('支付已取消');
                                //location.href = $(that).prop('href');
                            }else{
                                $('#debug').append(res.err_msg);
                            }
                            //$(that).text('重新支付');
                            $(that).data('disabled', false);
                        }
                    );
                });
            });



        },

        click_event: function () {
            var self = this;

        },
        got_pull_up_data: function (event, data, callback) {
            var self = this;
            //$('.js-list-box').html('');
            goods.get_order_list({type: 'client', page: this.page_number, limit: this.page_limit}, function (ret) {
                var r = new render();
                $('.js-list-box').append(r.parse_template(tpl_list_data, {order_list: ret.list}));
                $('.js-list-box').trigger('create');
                self.click_event();
                callback();

            });
        }
        
        
    });

    return view;
});
