define([ "utils/common",  "data/goods", "views/base", "utils/render", "text!templates/goods_selected.html", "jquery"
         , "css!styles/goods_selected.css", "css!styles/common.css"], function(common, goods, base, render, tpl, $){
    
    function view() {
        base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype = $.extend(view.prototype, {
        callback: null,
        goods_id: null,
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

                var r = new render();
                r.template = self.tpl;

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
            $('.js-minus-num').on('click', function () {
                var goods_num = self.get_num();
                if (goods_num > 1) {
                    self.set_num(goods_num-1);
                }
            });
            $('.js-plus-num').on('click', function () {
                var goods_num = self.get_num();
                self.set_num(goods_num+1);

            });
            // 购买
            $('.js-btn-nextpage').on('click', function() {
                //$('#debuging').append("self.goods_id" + ':' +  self.goods_id + '<br/>');
                //$('#debuging').append("self.get_num()" + ':' +  self.get_num() + '<br/>');
                /*
                 var buy_url = common.makeurl('goods_order', self.goods_id+"_"+self.get_num()+"_"+$('[name=goods_num]').data('styleid')+
                 "_"+$('[name=goods_num]').data('price'), 'pay');*/
                goods.add_to_cart({goods_id: self.goods_id,
                        num: self.get_num()},
                    function (ret) {
                        /*
                        for(k1 in ret) {
                            $('#debuging').append(k1 + ':' +  ret[k1] + '<br/>');
                        }*/
                        if (ret.result) {
                            location.href = common.makeurl('goods_order', ret['result']);
                        }

                    }
                );
                return false;
            });
            // 加入购物车
            $('.js-btn-addtocart').on('click', function () {
                goods.add_to_cart({goods_id: self.goods_id,
                        num: self.get_num()},
                    function () {
                        //location.href = "#/mycart/"+self.goods_id;
                        //location.href = self.goods.detail_url;
                        history.go(-1);
                    }
                );
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
