define(["text!templates/cate_edit.html", "underscore", 'utils/api',  'jquery'], function (tpl, _, api, $) {
    function view () {
	}
	view.prototype = {
		id: 0,
		pid: 0,
		render: function (args, container) {
			var self = this;
	    	pcate =  api.get('goodsclass', {pid: 0}, true);
	    	this.id = args.id ? args.id : 0;
	    	if (args.pid) {
	    		this.pid = args.pid;
	    	}
	    	
	    	
	    	
	    	//item.options_data = _.filter(options_data, function (opt) {return opt.tc_id == item.tc_id});
	    	//console.log(window._appFacade);
	    	
	        var template = _.template(tpl);
	        var html = template({pcate: pcate.data, classid: this.id});
	        //var div = $("<div/>").html( html);
	        div = $(container).html(html);
	        if (this.id) {
	        	/*
	    		var classitem = _.find(pcate.data, function(item) {return item.classid == this.pid});
	    		if (classitem) {
	    			if (this.pid) {
	    				div.find('select[name=pid] option[value='+this.pid+']').attr('selected', true);
	    	        }
	    		}*/
	    		var result = api.get('goodsclass', {classid:this.id}, true);
	    		div.find('[name=classname]').val(result.data[0].classname);
	    		this.pid = result.data[0].pid;
	    		
	    	}
	        if (this.pid) {
	        	div.find('[name=pid] option[value='+this.pid+']').attr('selected', 'selected');
	        }
    		

	        $('.btn-primary').click(function () {
	        	api.save('goodsclass', $('#form-adminer-edit').serialize(), function () {
	        		//$('#form-adminer-edit')[0].reset();
	        		//console.log(bootgrid);
	        		//div.find("table").bootgrid('reload');
	        		//self.render(args, container);
	        		///location.reload();
	        		location.href = "#/cate";
	        	});
	        	
	        	return true;
	        });
		}
	}
	return new view();
});