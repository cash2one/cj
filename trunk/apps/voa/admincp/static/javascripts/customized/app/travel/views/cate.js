define(["text!templates/cate.html", "underscore", 'jquery', 'utils/api', "jquery-bootgrid", "bootstrap"
        ], function(tpl, _, $, api){
	//$.fn.collapse = function(){};
	//var result_columntype_list = null;
	var pid = 0;
	var option_data = null;
	var div = null;
	var goods_data = function (request, callback) {
		var sort = {field: '', type: ''};
		if (!_.isEmpty(request.sort)) {
			var key = _.keys(request.sort);
			sort.field = key[0];
			sort.type = request.sort[key[0]];
		}
		var params = {query: request.searchPhrase, page: request.current, limit: request.rowCount, sort_field: sort.field, sort_type: sort.type, pid: pid};
		api.get('goodsclass', params, true, function (result) {
	    	var data = {
    				"current": params.page,
    				"rowCount": params.limit,
    				"rows": result.data,
    				"total": result.total,
    		}
	    	callback(data);
		});
	}
	var pcate = null;
	function view () {
		
	}
	view.prototype = {
			render: function (args, container) {
				var self = this;
		    	pcate =  api.get('goodsclass', {pid: 0}, true);
		    	pid = pid ? pid : 0;
		    	
		    	//item.options_data = _.filter(options_data, function (opt) {return opt.tc_id == item.tc_id});
		    	//console.log(window._appFacade);
		    	
		        var template = _.template(tpl);
		        var html = template({pcate: pcate.data});
		        //var div = $("<div/>").html( html);
		        div = $(container).html(html);
		        /*
		        $('#myModal').find('.btn-primary').click(function () {
		        	api.save('goodsclass', $('#form-adminer-edit').serialize(), function () {
		        		//$('#form-adminer-edit')[0].reset();
		        		//console.log(bootgrid);
		        		//div.find("table").bootgrid('reload');
		        		self.render(args, container);
		        		///location.reload();
		        	});
		        	
		        	return true;
		        });*/
		        
		        div.find("table").bootgrid({
		        	ajax: true,
		        	data: function (request, callback) {
		        		goods_data(request, function (data) {
		        			callback(data);
		        		});
		        		
		        	},
		        	url: "#cate/",
		        	formatters: {
		        		commands: function (column, row) {
		        			var subcate = '';
		        			if (row.pid == "0") {
		            			//subcate = "<button type=\"button\"  class=\"btn btn-xs btn-default command-list \" data-row-classname=\""+ row.classname + "\" data-row-id=\"" + row.classid + "\"><span class=\"fa  fa-list-alt\"></span></button>&nbsp;";
		        			}

		            		return "<button type=\"button\"  class=\"btn btn-xs btn-default command-edit\"  data-row-id=\"" + row.classid + "\"><span class=\"fa fa-pencil\"></span></button> &nbsp;"+
		            		subcate +
		            		"<button type=\"button\" class=\"btn btn-xs btn-default command-delete\" data-row-id=\"" + row.classid + "\"><span class=\"fa fa-trash-o\"></span></button>";
		        
		        		}
		        	}
		        }).on("loaded.rs.jquery.bootgrid", function(){
		        	if (pid) {
			    		var classitem = _.find(pcate.data, function(item) {return item.classid == pid});
			    		if (classitem) {
			    			self.current(classitem.classname);
			    		}
			    		
			    	}
				    // Executes after data is loaded and rendered 
				    div.find(".command-edit").on("click", function(e)
				    {
				    	location.href = "#/cate_edit/"+$(this).data("row-id");
				    	return false;
				    	
				    }).end().find(".command-list").on("click", function(e)
				    {
				    	pid = $(this).data("row-id");
				    	self.current($(this).data("row-classname"));
				    	
				    	div.find("table").bootgrid('reload');
					}).end().find(".command-delete").on("click", function(e)
				    {
						if (confirm('确定删除吗？')) {
							api.delete('goodsclass', {'classid': $(this).data("row-id")});
					    	self.render(args, container);
						}
						
				    	
				    	//pcate =  api.get('goodsclass', {pid: pid}, true);
				    	//location.reload();
				    	//div.find("table").bootgrid('reload');
				    });
				    
				});
			    $('<a href="#/cate_edit" class="btn js-btn-add-cate btn-default"> <i class="fa fa-plus"></i>&nbsp;添加分类</a> ').insertBefore(div.find('.actionBar .search'));
			    
			    $('.js-btn-add-cate').click(function () {
			    	location.href = "#/cate_edit/pid/"+pid;
			    	return false;
			    });
			    
		        
		        
		        
		        //return div;
		    },
		    current: function (classname) {
		    	$('.table-current-postion').html('<a href="#" class="table-back">/根目录</a> -> '+classname)
		    	.find('.table-back').click(function () {
		    		pid = 0;
		    		div.find("table").bootgrid('reload');
		    		$('.table-current-postion').html('');
		    		return false;
		    	});
		    	if (pid) {
		        	//$('select[name=pid] option[value='+pid+']').attr('selected', true);
		        }
		    }
	};
    return new view();
});
