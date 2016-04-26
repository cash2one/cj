define([ "utils/common", "data/goods", "views/base", "utils/render", "text!templates/mycart.html", "jquery"
         , "css!styles/common.css", "css!styles/mycart.css"], function(common, goods, base, render, tpl, $){
    
    function view() {
        base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype = $.extend(view.prototype, {
        callback: null,
        goods_id: null,
        mode: 'buy', //or del
        tpl: null,
        // 模板处理
        render: function(args) {

            if (args.goods_id) {
                this.goods_id = args.goods_id;
            }

            var self = this;

            if (!this.tpl) {
                this.tpl = tpl;
            }

            goods.get_cart_list({dataid: this.goods_id}, function (ret) {

                var r = new render();
                r.template = self.tpl;
                r.assign("goods_id", self.goods_id);
                r.assign("goods", ret);

                var el = r.apply();
                self.page = el;
                self.event(el);
            });

        },
        event: function(el) {
            var self = this;
            // init
            self.count_amounts();

            $('.js-cart-edit').on('click', function() {
                if (self.mode == 'buy') {
                    self.mode = 'del';
                    $(this).text('完成');
                    $('.js-goods-num').hide();
                    $('#js_del').show();
                    $('#js_buy').hide();
                    $('#js_total').hide();
                } else {
                    self.mode = 'buy';
                    $(this).text('编辑');
                    $('.js-goods-num').show();
                    $('#js_buy').show();
                    $('#js_del').hide();
                    $('#js_total').show();
                }

            });
            $('#js_buy').click(function () {
                var cids = [];
                $('.js-card-select').each(function () {
                    if ($(this).hasClass('btn-select')) {
                        cids.push($(this).parents('.js-item').data('cartid'));
                    }
                });
                var urlparams = '';
                if (cids.length) {
                    urlparams = cids.join('_');
                } else {
                    return false;
                }
                location.href = common.makeurl('goods_order', urlparams);
            });
            $('#js_del').click(function(){
                $('.js-card-select').each(function () {
                    if ($(this).hasClass('btn-select')) {
                        goods.cart_del_product({cartid: $(this).parents('.js-item').data('cartid')}, function() {
                            // none
                        });
                        $(this).parents('.js-item').remove();
                    }
                });
                $('.js-cart-num').text(0);
                $('#js_cart_amounts').html(0);
                return false;
            });

            $('.js-card-select').on('click', function () {
                $(this).toggleClass(' btn-noselect btn-select');
                if ($('#mycart').find('.btn-select').length > 0) {
                    $('.js-all-select').addClass('btn-select');
                    $('.js-all-select').removeClass('btn-noselect');
                } else {
                    $('.js-all-select').removeClass('btn-select');
                    $('.js-all-select').addClass('btn-noselect');
                }

                self.count_amounts();
                return false;
            });
            $('.js-minus-num').on('click', function () {
                var goods_num = self.get_num($(this).parent());
                if (goods_num > 1) {
                    self.set_num($(this).parent(), goods_num-1);
                    goods.cart_update_product({cartid: $(this).parents('.js-item').data('cartid'), num: goods_num-1}, function() {
                        // none
                    });
                }
                self.count_amounts();
            });
            $('.js-all-select').on('click', function () {
                $(this).toggleClass(' btn-noselect btn-select');
                $('.js-card-select').trigger('click');
            });
            $('.js-plus-num').on('click', function () {
                var goods_num = self.get_num($(this).parent())+1;
                if (goods_num <= $(this).parent().find('[name=goods_num]').data('amount')) {
                    self.set_num($(this).parent(), goods_num);
                    goods.cart_update_product({cartid: $(this).parents('.js-item').data('cartid'), num: goods_num}, function() {
                        // none
                    });
                }
                self.count_amounts();
            });

        },
        count_amounts: function () {
            var amounts = 0;
            var total = 0;
            $('.js-card-select').each(function () {
                if ($(this).hasClass('btn-select')) {
                    total ++;
                    var $input = $(this).parent().find('[name=goods_num]');
                    amounts = amounts + (parseInt($input.val()) * parseFloat($input.data('price')));
                }


            });
            $('.js-cart-num').text(total);
            $('#js_cart_amounts').html(amounts);

            return amounts;
        },
        get_num: function (p) {
            var goods_num = p.find('[name=goods_num]').val();
            if (goods_num.length > 0) {
                goods_num = parseInt(goods_num);
            } else {
                goods_num = 1;
            }
            return goods_num;
        },
        set_num: function(p, num) {
            p.find('[name=goods_num]').val(num);
        }

        
        
    });

    return view;
});
