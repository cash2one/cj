define(["utils/customized_form", "text!templates/list.html", "underscore", 'jquery', 'utils/api', "jquery-bootgrid"
        ], function(customized_form, tpl, _, $, api){
	var result_columntype_list = null;
	var option_data = null;
	var cate = null;
	var goods_data = function(request, callback) {
		var sort = {
			field : '',
			type : ''
		};
		if (!_.isEmpty(request.sort)) {
			var key = _.keys(request.sort);
			sort.field = key[0];
			sort.type = request.sort[key[0]];
		}
		var params = {
			query : request.searchPhrase,
			page : request.current,
			limit : request.rowCount,
			sort_field : sort.field,
			src_field: 1,
			sort_type : sort.type
		};
		var customized = new customized_form();
		customized.col_opts_list = options_data;
		api.get('goods', params, true, function(result) {
			// 行
			goods = _.map(result.data, function(g, key) {
				if (g.classid != '0') {
					var gclass = _.find(cate.data, function (cv) {return cv.classid == g.classid});
					if (gclass) {
						g.classname = gclass.classname;
					}
				}
				// 列
				$.each(result_columntype_list, function(k3, col2) {
					g = customized.get_col_value(col2, g);
				});
				return g;
			});
			var data = {
				"current" : params.page,
				"rowCount" : params.limit,
				"rows" : goods,
				"total" : result.total,
			}
			callback(data);
		});
	}
    return {render: function (args, container) {
    	cate = api.get('goodsclass',  {limit: -1}, true);
    	options_data = api.get('goodstablecolopt' , null, true);
    	result_columntype_list = api.get('goodstablecol', null, true);
    	// 附件不显示到列表里
        result_columntype_list = _.filter(result_columntype_list, function(item) {return item.ct_type != 'attach'});
		// 提成比列不显示
		//result_columntype_list = _.filter(result_columntype_list, function(item) {return item.field != 'percentage'});
        // 多行文本跟富文本也不显示到列表
        result_columntype_list = _.filter(result_columntype_list, function(item) {return item.ct_type != 'text'});
        // 图片多选也不显示
        result_columntype_list = _.filter(result_columntype_list, function(item) {return !(item.ct_type == 'checkbox' && item.ftype == '2')});
        // 图片单选也不显示
        result_columntype_list = _.filter(result_columntype_list, function(item) {return !(item.ct_type == 'radio' && item.ftype == '2')});

        // message 也不显示
        result_columntype_list = _.filter(result_columntype_list, function(item) {return item.ct_type != 'message'});

        // 最好显示不隐藏的字段
        result_columntype_list = _.filter(result_columntype_list, function(item) {return item.isuse == '1'});

    	//item.options_data = _.filter(options_data, function (opt) {return opt.tc_id == item.tc_id});
    	//console.log(window._appFacade);
        var template = _.template(tpl);
        var html = template({columnlist: result_columntype_list});
        //var div = $("<div/>").html( html);
        var div = $(container).html(html);
        //div.find("table").colResizable();
        div.find("table").bootgrid({
        	ajax: true,
        	data: function (request, callback) {
        		goods_data(request, function (data) {

        			callback(data);
        			div.find('tbody tr').click(function () {
        	            var id = $(this).attr('key');
        	            location.href = "#/view/"+ data.rows[div.find('tbody tr').index($(this))].dataid;
        	        });
        		});

        	}
        });
	    $('<a href="#/add" class="btn btn-default "> <i class="fa fa-plus"></i>&nbsp;添加</a>&nbsp;').insertBefore(div.find('.actionBar .search'));
		$('<a href="#/config" class="btn btn-default "> <i class="fa fa-gear"></i>&nbsp;产品配置</a>&nbsp;').insertAfter(div.find('.actionBar .actions'));
		$('<a href="#/cate" class="btn btn-default "> <i class="fa fa-sitemap"></i>&nbsp;产品分类</a>&nbsp;').insertAfter(div.find('.actionBar .actions'));




        return div;
    }};
});
