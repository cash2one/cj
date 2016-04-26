define(["utils/call", "widgets/customized_web_form", "utils/api", "text!templates/copy.html", "underscore", 'jquery'
        
        ], function(call, customized_web_form, api, tpl, _, $){
	function change_price(price_col) {
        var last_price = null;
        $(".js-style-price").each(function () {
            var price = $.trim($(this).val());
            if (price) {
                price = parseFloat(price);
            } else {
                price = 0;
            }
            if (last_price == null) {
                last_price = price;
            } else {
                if (price < last_price ) {
                    last_price = price;
                }
            }
        });
        $('[name=_'+price_col.tc_id+']').val(last_price);
        return last_price;
    }
    return {render: function (args, container) {
    	var options_data = api.get('goodstablecolopt', null, true);
    	// 获取默认数据
        var result_columntype_list = api.get('goodstablecol', null, true);
        result_columntype_list = _.filter(result_columntype_list, function(item) {return item.isuse == '1'});
        // 提成比列不显示
        //result_columntype_list = _.filter(result_columntype_list, function(item) {return item.field != 'percentage'});
        
        var price_col = _.find(result_columntype_list, function(item) {return item.field == 'price'});
        var goodsnum_col = _.find(result_columntype_list, function(item) {return item.field == 'goodsnum'});

        var goods = api.get('goodsdetail', {dataid: args.id, src_field:1, edit: 1}, true);
        
    	var cate = api.get('goodsclass', {limit: -1}, true);
        var template = _.template(tpl);
        var html = template({goods: goods, dataid: args.id, cate: cate.data, src_field: goods['src_field']});
        var div = $(container).html(html);

        var contacts_default_data = [];
        /*if (!_.isEmpty(goods.uids)) {
            for(var i in goods.uids) {
                var newu = goods.uids[i];
                newu.input_name = "uids[]";
                contacts_default_data.push(goods.uids[i]);
            }
        }*/

        // 联系人
        var cc = new call;
        cc.app('contacts_pc', "contacts", {container: "#contacts_container",
            input_name_contacts: "uids[]", contacts_default_data: contacts_default_data, deps_enable: false});//contacts_default_data: [{id:129, name: 'keithli', input_name: 'aaa'}]
        div.find('.js-btn-save').click(function () {
        	api.save('goods', $('#js-form').serialize(), function () {
        		location.href = "?#";
        	});
        	
        	return false;
        });
        
        if (goods.classid) {
        	$('[name=classid] option').each(function() {
        		if ($.trim($(this).val()) == $.trim(goods.classid)) {
        			$(this).attr('selected', 'selected');
        		}
        	});
        }

        // 规格事件
        // 删除
        $(".js-format-row .js-del-format").on('click', function () {
        	if ($(".js-format-row").length > 1) {
        		$(this).parents('.js-format-row').remove();
        	}
            change_price(price_col);
        	
        	return false;
        });
        // 添加
        $(".js-add-format").on('click', function() {
        	var new_row = $(".js-format-row:last").clone(true, true);
        	new_row.find('input').val('');
        	new_row.insertAfter($(".js-format-row:last"));
            new_row.find('input').removeProp('disabled');
            new_row.find('.js-del-format').show();
            new_row.show();
            change_price(price_col);

        });
        // 规格事件结束
        
        // 自定义表单
        var form = new customized_web_form();
        // 表单类型列表 不可为空
        form.columntype_list = result_columntype_list;
        // 表单类型选项数据 可为空
        form.options_data = options_data;
        // 表单数据 可为空
        form.goods = goods;
       
        // 表单生成容器
        form.container = '#js-form-customize tbody',
        // 生成表单
        form.render();

        // 取最小价格
        if (!_.isEmpty(price_col)) {
            $(".js-style-price").on('change', function () {
                change_price(price_col);

            });
            $('#_'+price_col.tc_id).on('change', function () {
                var v = $.trim($(this).val());
                var last_price = change_price(price_col);
            });
        }
        
        //编号赋值
        $('#_'+goodsnum_col.tc_id).val('');
        
        return div;
    }};
});