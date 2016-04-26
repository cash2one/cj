define(["utils/customized_form", "utils/api", "text!templates/view.html", "underscore", 'jquery',
        ], function(customized_form, api, tpl, _, $){
    return {render: function (args, container) {
    	var options_data = api.get('goodstablecolopt');
    	var result_columntype_list = api.get('goodstablecol');
    	// 是否货源
    	//var src_field = _.find(result_columntype_list, function(item) {return item.field == 'src_field'});
    	// 过滤掉隐藏的字段
        //result_columntype_list = _.filter(result_columntype_list, function(item) {return item.isuse == '1' || item.field == 'src_field'});// 是否货源 src_field 不隐藏 
        result_columntype_list = _.filter(result_columntype_list, function(item) {return item.isuse == '1'});

    	var goods = api.get('goodsdetail', {src_field: 1, dataid: args.id}, true);
    	var classname = '';
    	if (goods.classid != '0') {
    		classname = api.get('goodsclass', {classid: goods.classid}, true);
    		if (!_.isEmpty(classname.data)) {
    			classname = classname.data[0].classname;
    		} else {
    			classname = '';
    		}
    		
    	}
    	// 自定义表单数据处理类
    	var customized = new customized_form();
		customized.col_opts_list = options_data;
    	// 列
		$.each(result_columntype_list, function(k3, col2) {
			if (col2.field == 'src_field') {//货源标识, 1: 货源; 2: 用户自己的产品
				goods["_"+col2.tc_id] = goods[col2.field];
				if (goods["_"+col2.tc_id] == '1') {
					goods["_"+col2.tc_id] = '货源';
				} else {
					goods["_"+col2.tc_id] = '非货源';
				}
			} else {
				
				goods = customized.get_col_value(col2, goods);
			}
		});
		
		
        var template = _.template(tpl);
        var html = template({columnlist: result_columntype_list, goods: goods, classname: classname});
        //var div = $("<div/>").html( html);
        var div = $(container).html(html);
        div.find('.js-btn-del').click(function () {
        	if (confirm("确定删除？")) {
        		api.delete("goods", {dataid: args.id, url: 'goods', field: "dataid", value: args.id} );
            	//api.set('goodsdetail?dataid='+args.id,  null);
            	location.href = "#";
        	}
        	
        });
        
        return div;
    }};
});
