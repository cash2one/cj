define(['require_router', "jquery"], function(require_router, $) {
    // 构造函数
    function Router() {
        // this.init();
    }

    Router.prototype = {
    	container: 'body',
        // 路由规则
        routes: {
            list: { path: '/list', moduleId: 'views/list' },
            view: { path: '/view/:id', moduleId: 'views/view' },
            customers_list: { path: '/customers_list', moduleId: 'views/customers_list' },
            customer_detail: { path: '/customer_detail/:id', moduleId: 'views/customer_detail' },
            customer_edit: { path: '/customers_edit/:id', moduleId: 'views/customers_edit' },
            goods_list: { path: '/goods_list', moduleId: 'views/goods_list' },
            goods_detail: { path: '/goods_detail/:id', moduleId: 'views/goods_detail' },
            goods_supply_list: { path: '/goods_supply_list', moduleId: 'views/goods_supply_list' },

            notFound: { path: '*', moduleId: 'views/list' }
        },

        // 载入模块
        load: function(module, routeArguments) {
            // jquery mobile page object
            var m = new module();
            // 生成页面
            m.render(routeArguments);
            // Programatically changes to the current page
        },
        // 初始化
        init: function() {
            var self = this;
            // 调用requirejs-router 路由
            require_router.registerRoutes(this.routes)
            .on('statechange', function(module, routeArguments) {
                if ($.mobile) {
                    $('.ui-page').removeClass('ui-page-keep');
                    $.mobile.loading( "show" );
                } else {
                    $(self.container).append('<div class="js-loading vco-loading"><div class="vco-loading-icon"></div><div class="vco-message"><p>正在加载...</p></div></div>');
                }
            })
            .on('routeload', function(module, routeArguments) {
                //history.pushState('', this.activeRoute.path, this.activeRoute.moduleId); // push a new URL into the history stack
                //history.go(0); // go to the current state in the history stack, this fires a popstate event
            	if (window.default_view && '*' == this.activeRoute.path) {
            		require(["views/" + window.default_view], function (defult_module) {
            			self.load(defult_module, window.default_arguments);
            			window.default_view = null;
                		window.default_arguments = null;
            		});
            	} else {
            		self.load(module, routeArguments);
            	}

            	$(self.container).find('.js-loading').remove();

            })
            .init();
        }
    };

    return Router;
});
