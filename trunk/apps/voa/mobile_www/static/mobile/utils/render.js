define([ "underscore", 'jquery'], function(_, $) {

    // 构造方法
    function Render() {
        // do something.
    }

    Render.prototype = {
        // 模板
        template: '',
        // 容器 对像
        container: null,
        // page
        page: null,
        // 模板变量
        vars: {},
        only_return_element: null,
        /**
         * 模板解析
         * @param {string} template 模板文件
         * @param {object} vars 变量
         * @returns {*}
         */
        parse_template: function(template, vars) {
        	var tpl = _.template(template);
	        return tpl(vars);
        },
        // 分配变量
        assign: function(key, val) {
        	if (key) {
        		this.vars[key] = val;
        	}
        },
        // 操作
        apply: function(vars, template) {
            var footer;

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

            if (window._app == 'travel' || window._app == 'crm') {
                footer = '<p class="copyright" style="text-align: center;color:#999;font-size:12px;padding: 10px;margin: 0;">由 畅移云工作 <span style="color:#aaa;">提供技术支持</span></p>';
                this.template = this.template + footer;
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
            } else { // 只返回结构
                this.page = $('<div />').html(html);
            }
			//每个页面添加年度总结链接,2015-01-15号之后可删
			if(window.year2014_url && $('#float2014').length == 0) {
				var html = '<div id="float2014" style="position:fixed;top:20%;right:6px;"><a href="'+year2014_url+'"><img style="width:60px;height:60px;" src="/static/images/year2014/year2014.png"/></a></div>';
				$('body').append(html);
			}

	        return this.page;
        },
        change: function(html) {

            // 把html插入到容器里
            this.page = $('<div data-role="page" />').html(html);

            this.container.append(this.page);
            // $.mobile.changePage(this.page, {reverse: false, changeHash: false, transition: "slide"});
            $.mobile.changePage(this.page, {reverse: false, changeHash: false});

            // 页面切换时, 新页面显示之后
            $(document).on('pageshow', this.page, function(){
            	$.mobile.silentScroll(0);
            });

            // 页面加载失败处理
            $(document).on("pageloadfailed", this.page, function() {
            	$('body').html('<h1>pageloadfailed<h1>');
            });

            // 页面更新失败处理
            $(document).on("pagechangefailed", this.page, function() {
            	$('body').html('<h1>pagechangefailed<h1>');
            });

            // 页面切换时, 旧页面隐藏之后
            $(document).on("pagehide", this.page, function() {
                // 删除重复的page
                $('.ui-page').each(function() {
                    if (!$(this).hasClass("ui-page-active") && !$(this).hasClass("ui-page-keep")) {
                        $(this).remove();
                    }
                });
            });

            return this.page;
        }

    };

    return Render;
});
