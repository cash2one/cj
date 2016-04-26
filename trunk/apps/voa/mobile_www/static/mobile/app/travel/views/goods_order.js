define(["underscore", "data/goods", "views/goods_selected", "utils/render", "text!templates/goods_order.html", "jquery"
        , "css!styles/common.css" , "css!styles/goods_order.css"], function(_, goods, base, render, tpl, $){

    function view() {
        base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    view.prototype.parent = base.prototype;
    view.prototype = $.extend(view.prototype, {
        goods_num: null,
        address_info: null,
        goods_id: null,
        cartids: [],
        price: 0,
        styleid: null,
        amount: 0,
        // 模板处理
        render: function(args) {

            if (!args.cartids) {
                if (args) {
                    var params = args[0].split('_');
                    this.cartids = params;
                    /*
                    this.goods_id = params[0];
                    this.goods_num = params[1];
                    this.styleid = params[2];
                    this.price = params[3];
                    this.amount = this.price * this.goods_num;*/
                }
            } else {
                if (!_.isNumber(args.cartids)) {
                    this.cartids = args.cartids.split('_');
                } else {
                    this.cartids.push(args.cartids);
                }
                /*
                this.goods_num = args.goods_num;
                this.goods_id = args.goods_id;*/
            }
            this.tpl = tpl;

            var self = this;

            var r = new render();
            r.template = self.tpl;
            /*
            if (this.goods_num) {
                r.assign('goods_num', this.goods_num);
            }*/
            this.address_info = goods.get_order_address();
            if (this.address_info.address) {
                r.assign("adr", this.address_info.address);
            } else {
                r.assign("adr", {name: '', adr: '', phone: ''});
            }
            goods.get_cart_list({}, function (ret) {
                if (self.cartids) {
                    ret = _.filter(ret, function(item){
                        return _.find(self.cartids, function(id){return id ==  item.cartid});
                    });
                }
                $.each(ret, function (k, item) {
                    self.price += (item.price * item.num);
                });

                r.assign("goods", ret);
                r.assign("price", self.price);

                var el = r.apply();
                self.page = el;
                self.event(el);
            });
                    //goods.cart_to_order({})
            //this.parent.render.call(this, args);
/*
            goods.get_detail({dataid: this.goods_id}, function (ret) {

                r.assign("goods", ret);
                var style = null;
                if (!_.isEmpty(ret.styles)) {
                    style = _.first(ret.styles);
                }
                r.assign("style_first", style);
                var el = r.apply();
                self.page = el;
                self.event(el);

                for(k3 in self.address_info) {
                    $('#debug').append(k3 + ':' + self.address_info[k3] + '<br/>');
                }
                for(k4 in self.address_info.address) {
                    $('#debug').append(k4 + ':' + self.address_info.address[k4] + '<br/>');
                }
                for(k2 in ret) {
                   // $('#debug').append(k2 + ':' + ret[k2] + '<br/>');
                }
            });*/

        },
        event: function(el) {
            var self = this;
            // 文件分享
            $('.js-share-out', el).click(function () {
                $('#js-share-out', el).popup('open');

            })
            $('#js-share-out', el).on('tap', function () {
                $(this).popup('close');
            })

            if (!_.isEmpty(this.address_info.address)) {
                $('.js-weixin-address.js-yes').show();
                $('.js-weixin-address.js-no').hide();
            } else {
                $('.js-weixin-address.js-no').show();
                $('.js-weixin-address.js-yes').hide();
            }
            $('#js_weixin_pay').on('click', function() {

                if ($('#js_adr_name').text() == '' || $('#js_adr_phone').text() == '' || $('#js_adr_address').text()  == '') {
                    alert('请填写收货人信息');

                    return false;
                }

                var that = this;
                if ($(that).data('disabled')) {
                    return false;
                }
                $(that).data('disabled', true);
                $(that).text('请求支付中...');
                goods.cart_to_order({cartids: self.cartids, name: $('#js_adr_name').text(),
                    phone: $('#js_adr_phone').text(), adr: $('#js_adr_address').text()
                    }, function (pay_params) {
                    $(that).text('开始支付...');
                    if(typeof pay_params == 'object') {
                        $('#debug').append('<br/><br/>准备发起请求<br/>');
                        for(k in pay_params) {
                            $('#debug').append(k + ':' + pay_params[k] + '<br/>');
                        }
                        for(k1 in pay_params.result) {
                            $('#debug').append(k1 + ':' +  pay_params.result[k1] + '<br/>');
                        }
                        for(k2 in pay_params.result.pay_params) {
                            $('#debug').append(k2 + ':' +  pay_params.result.pay_params[k2] + '<br/>');
                        }

                        WeixinJSBridge.invoke(
                            'getBrandWCPayRequest',
                            pay_params.result.pay_params,
                            function(res){
                                WeixinJSBridge.log(res.err_msg);
                                $('#debug').append('<br/>回調信息:<br/>');
                                if(res.err_msg == 'get_brand_wcpay_request:ok') {
                                    $('#debug').append('支付成功');
                                    /*
                                    goods.order_pay_status({orderid: self.goods_id}, function (result, status) {
                                        if (status.errcode != '0') {
                                            alert('支付出现异常情况，请联系客服');
                                        }
                                        location.href = $(that).prop('href');
                                    });
                                    */
                                    location.href = $(that).prop('href');

                                }else if(res.err_msg == 'get_brand_wcpay_request:cancel') {
                                    $('#debug').append('支付已取消');
                                    location.href = $(that).prop('href');
                                }else{
                                    $('#debug').append(res.err_msg);
                                }
                                $(that).text('重新支付');
                                $(that).data('disabled', false);
                            }
                        );

                    }else{
                        $('#debug').append('<br/>获取支付参数错误');
                    }
                });
                return false;
            });
            $('.js-weixin-address').on('click', function() {
                /*
                for(k1 in self.address_info.ads_params) {
                    //alert(k1+':'+self.address_info.ads_params[k1]);
                    $('#debug').append(k1 + ':' + self.address_info.ads_params[k1] + '<br/>');
                }
                $('#debug').append("self.address_info.ads_params" + ':' + self.address_info.ads_params + '<br/>');
                $('#debug').append("WeixinJSBridge" + ':' + typeof  WeixinJSBridge + '<br/>');
                for(k2 in self.address_info.ads_params) {
                    //alert(k1+':'+self.address_info.ads_params[k1]);
                    $('#debug').append(k2 + ':' + self.address_info.ads_params[k2] + '<br/>');
                }*/
                if (typeof WeixinJSBridge != "undefined"){
                    WeixinJSBridge.invoke('editAddress', self.address_info.ads_params, function(res){
                        /*
                        for(k1 in res) {
                            //alert(k1+':'+self.address_info.ads_params[k1]);
                            $('#debug').append(k1 + ':' + res[k1] + '<br/>');
                        }*/
                        if (res.userName) {
                            $('#js_adr_name').text(res.userName);
                            $('#js_adr_phone').text(res.telNumber);
                            $('#js_adr_address').text(res.addressCitySecondStageName + " " + res.addressCountiesThirdStageName + " " + res.addressDetailInfo);
                            $('.js-weixin-address.js-no').hide();
                            $('.js-weixin-address.js-yes').show();
                        }

                    });
                } else {
                    alert('没有调用到微信JS接口');
                }
            });
        }


    });

    return view;
});
