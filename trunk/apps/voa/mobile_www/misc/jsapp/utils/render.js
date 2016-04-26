define([ "underscore", 'jquery'], function(_, $){
	
    function render() {

    }

    render.prototype = {
        // 模板
        template: '',
        //容器 对像
        container: null,
        //page
        page: null,
        vars: {},
        only_return_element: null,
        parse_template: function (template, vars) {
        	var template = _.template(template);
	        return template(vars);
        },
        //操作 
        apply: function(vars, template) {
            if (vars) {
                this.vars = vars;
            }
            if (template) {
                this.template = template;
            }
            if (this.container == null) {
                this.container = $('body');
            }
            if (this.vars.only_return_element) {
                this.only_return_element = this.vars.only_return_element;
            }

	        var html = this.parse_template(this.template, this.vars);
            
            // 如果不只是返回结构则
            if (null == this.only_return_element) {
                if ($.mobile) {
                   this.change(html); 
                } else {
                    this.page = $('<div />').html(html);
                    this.container.html(this.page);
                }
            } else {// 只返回结构
                this.page = $('<div />').html(html);
            }
	        
	        return this.page;
        },
        change: function (html) {
            
            //把html插入到容器里
            this.page = $('<div data-role="page" />').html(html);
            this.container.append(this.page);
            $.mobile.changePage( this.page , { reverse: false, changeHash: false, transition: "slide" } );

            $( document ).on( "pageshow", this.page, function() {
            	setTimeout(function (){
		            //删除重复的page
		            $('.ui-page').each(function (){
		                if (!$(this).hasClass("ui-page-active")) {
		                    $(this).remove();
		                }
		            });
            	}, 1000);
            });
            
            return this.page;
        }
        
    };

    return render;
});
