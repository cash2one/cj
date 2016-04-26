define(["widgets/customized_form_config", "utils/api", "underscore", 'jquery', 'text!templates/config.html', "jqueryui"
        ], function(customized_form_config, api, _, $, tpl){
	function view() {
		
	}
	
    view.prototype = {
    	render: function (args, container) {
    		var options_data = api.get('goodstablecolopt', null, true);
	    	var column_type = api.get("/api/common/get/columntype", null, true);
	    	// 获取默认数据
	        var result_columntype_list = api.get('goodstablecol', null, true);
	        
	        
	    	// 主模板
	        $(container).html(tpl);
	        
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
	        
	    }
     };
    return new view();
});
