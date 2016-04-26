define(["utils/customized_form", "utils/api", "text!templates/view.html", "underscore", 'jquery',
        ], function(customized_form, api, tpl, _, $){
    return {render: function (args, container) {
    	var options_data = api.get('goodstablecolopt');
    	var result_columntype_list = api.get('goodstablecol');
    	var goods = api.get('goodsdetail', {dataid: args.id}, true);
    	var classname = '';
    	if (goods.classid != '0') {
    		classname = api.get('goodsclass', {classid: goods.classid}, true);
    		if (!_.isEmpty(classname.data)) {
    			classname = classname.data[0].classname;
    		} else {
    			classname = '';
    		}
    	}
    	var customized = new customized_form();
		customized.col_opts_list = options_data;
    	// 列
		$.each(result_columntype_list, function(k3, col2) {
			goods = customized.get_col_value(col2, goods);
		});
		
		
        var template = _.template(tpl);
        var html = template({columnlist: result_columntype_list, goods: goods, classname: classname});
        //var div = $("<div/>").html( html);
        var div = $(container).html(html);
        div.find('.js-btn-del').click(function () {
        	api.delete("goods", {dataid: args.id, url: 'goods', field: "dataid", value: args.id} );
        	//api.set('goodsdetail?dataid='+args.id,  null);
        	location.href = "#";
        });
        
        return div;
    }};
});
