define([ "utils/common", "data/goods", "views/base", "utils/render", "text!templates/goods_selected.html", "jquery"
         , "css!styles/goods_selected.css", "css!styles/common.css"], function(common, goods, base, render, tpl, $){
    
    function view() {
        base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype = $.extend(view.prototype, {
        callback: null,
        goods_id: null,
        goods: null,
        goods_num: 0,
        tpl: null,
        // 模板处理
        render: function(args) {

            if (args.goods_id) {
                this.goods_id = args.goods_id;
            } else {
                this.goods_id = args[0];
            }

            var self = this;

            if (!this.tpl) {
                this.tpl = tpl;
            }

            goods.get_detail({dataid: this.goods_id}, function (ret) {

                self.goods = ret;

                var r = new render();
                r.template = self.tpl;
                var style = null;
                if (!_.isEmpty(ret.styles)) {
                    var selected = false;
                    ret.styles = _.map(ret.styles, function (item){
                        item.current = false;
                        if (item.price == ret.price && selected == false) {
                            selected = true;
                            self.goods_num = item.amount;
                            item.current = true;
                        }
                        return item;
                    });
                    if (!selected) {
                        if (ret.styles.length) {
                            ret.styles[0].current = true;
                            self.goods_num = ret.styles[0].amount;
                        }
                    }
                    ret.goods_num = self.goods_num;
                    /*
                    style = _.find(ret.styles, function () {return true});
                    if (style.amount > 0) {
                        self.goods_num = style.amount;
                    }*/
                }
                //r.assign("style_first", style);
                r.assign("goods", ret);

                if (args.goods_num) {
                    r.assign('goods_num', args.goods_num);
                }
                var el = r.apply();
                self.page = el;
                self.event(el);
            });

        },
        event: function(el) {
            var self = this;

            // 初始化的时候
            //$('.js-style:first').addClass("selected");
            $('[name=goods_num]').data('amount', $('.js-style.selected').data('amount'));
            $('[name=goods_num]').data('styleid', $('.js-style.selected').data('styleid'));
            $('[name=goods_num]').data('price', $('.js-style.selected').data('price'));
            $('[name=goods_num]').on("change", function () {
                if ($(this).val() > $(this).data('amount')) {
                    $(this).val($(this).data('amount'));
                }
            });
           // goods_num
            $('.js-style').on("click", function () {
                $('.js-style').each(function () {
                    $(this).removeClass('selected');
                });
                $(this).addClass('selected');
                $('.js-warehouse').text('库存：'+$(this).data('amount'));
                $('.js-price').text("￥ "+$(this).data('price'));
                $('[name=goods_num]').val(1);
                $('[name=goods_num]').data('amount', $(this).data('amount'));
                $('[name=goods_num]').data('styleid', $(this).data('styleid'));
                $('[name=goods_num]').data('price', $(this).data('price'));
            });
            $('.js-minus-num').on('click', function () {
                var goods_num = self.get_num();
                if (goods_num > 1) {
                    self.set_num(goods_num-1);
                }
            });
            $('.js-plus-num').on('click', function () {
                var goods_num = self.get_num()+1;
                if (goods_num <= $('[name=goods_num]').data('amount')) {
                    self.set_num(goods_num);
                }
            });
            $('.js-btn-nextpage').on('click', function() {
                    if (self.get_num() > 0 && self.goods_num > 0) {
                        /*
                         var buy_url = common.makeurl('goods_order', self.goods_id+"_"+self.get_num()+"_"+$('[name=goods_num]').data('styleid')+
                         "_"+$('[name=goods_num]').data('price'), 'pay');*/
                        goods.add_to_cart({
                                goods_id: self.goods_id,
                                num: self.get_num(),
                                styleid: $('[name=goods_num]').data('styleid')
                            },
                            function (ret) {
                                if (ret.result) {
                                    location.href = common.makeurl('goods_order', ret['result']);
                                }

                            }
                        );
                    } else {
                        alert('库存为空，不能添加购买');
                    }
                return false;
            });
            // 加入购物车
            $('.js-btn-addtocart').on('click', function () {
                if (self.get_num() > 0 && self.goods_num > 0) {
                    goods.add_to_cart({
                            goods_id: self.goods_id,
                            num: self.get_num(),
                            styleid: $('[name=goods_num]').data('styleid')
                        },
                        function () {
                            //location.href = "#/mycart/"+self.goods_id;
                            //location.href = self.goods.detail_url;
                            history.go(-1);
                        }
                    );
                } else {
                    alert('库存为空，不能添加购物车');
                }
                return false;

            });
        },
        get_num: function () {
            var goods_num = $('[name=goods_num]').val();
            if (goods_num.length > 0) {
                goods_num = parseInt(goods_num);
            } else {
                goods_num = 1;
            }
            return goods_num;
        },
        set_num: function(num) {
            $('[name=goods_num]').val(num);
        }


        
    });

    return view;
});
