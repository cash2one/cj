define(["widgets/customized_form_config", "utils/api", "underscore", 'jquery', 'text!templates/config.html', "jqueryui"
        ], function(customized_form_config, api, _, $, tpl){
	function view() {
		
	}
	
    view.prototype = {
    	render: function (args, container) {
	    	var column_type = api.get("/api/common/get/columntype", null, true);
    		var options_data = api.get('goodstablecolopt', null, true);

	    	// 获取默认数据
	        var result_columntype_list = api.get('goodstablecol', null, true);
	        // 字段是否启用, 1: 启用; 2: 隐藏; 3: 未启用
	        result_columntype_list = _.filter(result_columntype_list, function(item) {return item.isuse == '1' || item.isuse == '3'});
			// 提成比列不显示
			//result_columntype_list = _.filter(result_columntype_list, function(item) {return item.field != 'percentage'});
	        
	    	// 主模板
	        $(container).html(tpl);
	        if (window._style == 'travel') {
	        	$('.js-travel-tpl').addClass('selected');
	        } else {
	        	$('.js-crm-tpl').addClass('selected');
	        }
	        // 模板更换
	        $(".js-travel-tpl").on('click', function () {
	        	if (confirm('更换模板会清除所有数据!确定更改？')) {
		        	api.save('goodstpl', {tpl: 'travel'}, function () {
		        		location.reload();
		        	});
	        	}
	        	
	        	return false;
	        });
	        $(".js-crm-tpl").on('click', function () {
	        	if (confirm('更换模板会清除所有数据!确定更改？')) {
		        	api.save('goodstpl', {tpl: 'crm'}, function () {
		        		location.reload();
		        	});
	        	}
	        	return false;
	        });
	        // 
	        var config = new customized_form_config();
	        config.tablecol_menu = column_type;
	        // 获取默认配置数据
	        config.tablecol = result_columntype_list;
	        config.tablecolopt = options_data;
	        config.tablecol_del_callback = function (params) {
	        	api.delete('goodstablecol', params);
	        }
	        config.tablecol_save_callback = function (params, callback) {
	        	api.save('goodstablecol', params, function (result) {
	        		callback(result);
	            });
	        };
	        config.tablecolopt_save_callback = function (act, params, callback) {
	        	if (act == 'delete') {
		    		api.delete('goodstablecolopt', params);
		    	} else {
		    		api.save('goodstablecolopt', params, function(ret) {
		    			callback(ret);
		    		});
		    	}
	        }
	        
	        config.render();
	        
	    },
     };
    return new view();
});
