define(["widgets/customized_web_form", "utils/api", "text!templates/edit.html", "underscore", 'jquery'
        
        ], function(customized_web_form, api, tpl, _, $){
	
    return {render: function (args, container) {
    	var options_data = api.get('goodstablecolopt', null, true);
    	var cate = api.get('goodsclass', {limit: -1}, true);
        var template = _.template(tpl);
        var html = template({dataid: args.id, cate: cate.data});
        var div = $(container).html(html);
        
        div.find('.js-btn-save').click(function () {
        	api.save('goods', $('#js-form').serialize(), function () {
        		location.href = "#";
        	});
        	
        	return false;
        });
        
        // 获取默认数据
        var result_columntype_list = api.get('goodstablecol', null, true);
        result_columntype_list = _.filter(result_columntype_list, function(item) {return item.isuse == '1'});
        var goods = api.get('goodsdetail', {dataid: args.id, edit: 1}, true);
        if (goods.classid) {
        	$('[name=classid] option').each(function() {
        		if ($(this).val() == goods.classid) {
        			$(this).attr('selected', 'selected');
        		}
        	});
        }
        
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
        
       
        return div;
    }};
});
