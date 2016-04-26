define(["text!widgets/templates/sample.html", "underscore", 'jquery', 'utils/api', "css!widgets/styles/sample.css"
        ], function(tpl, _, $, api){
	
    function widget() {

    }

    widget.prototype = {
        render: function(args) {
	     
	        var template = _.template(tpl);
	        var html = template({act: "list"});
	       	var div = $('<div />').html(html);
	        
	        return div;
        }
    };

    return widget;
});
