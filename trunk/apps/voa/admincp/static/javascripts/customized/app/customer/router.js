define(['require_router', "jquery", "underscore"], function(router, $, _) {

    return {init: function () {
    	
    router
        .registerRoutes({
            config: { path: '/config', moduleId: 'views/config' },
            add: { path: '/add', moduleId: 'views/edit' },
            view: { path: '/view/:id', moduleId: 'views/view' },
            edit: { path: '/edit/:id', moduleId: 'views/edit' },
            cate: { path: '/cate', moduleId: 'views/cate' },
            cate_add: { path: '/cate_edit', moduleId: 'views/cate_edit' },
            cate_edit: { path: '/cate_edit/:id', moduleId: 'views/cate_edit' },
            cate_edit_pid: { path: '/cate_edit/pid/:pid', moduleId: 'views/cate_edit' },

            customer: { path: '/customer/*', moduleId: 'customer/customerView' },
            regex: { path: /^\/\w+\/\d+$/i, moduleId: 'regex/regexView' },
            list: { path: '/list', moduleId: 'views/list' },
            notFound: { path: '*', moduleId: 'views/list' }
        })
        .on('statechange', function(module, routeArguments) {
        	$('#container_main').html('<div class="vco-loading"><div class="vco-loading-icon"></div><div class="vco-message"><p>正在加载...</p></div></div>');
        })
        .on('routeload', function(module, routeArguments) {
        	/**r el_subnav = $('<div class="subnavgroup" />');
        	el_subnav.html('<div class="subnav"><ul class="nav nav-pills text-sm"></ul></div>');
        	
        	//el_subnav.find(".nav").append('<li><a href="#/add"><i class="fa fa-list"></i>添加产品</a></li>');
        	el_subnav.find(".nav").append('<li><a href="/admincp/office/travel/sale/pluginid/'+window._plusinid+'/"><i class="fa fa-cloud"></i> 服务号与企业号打通</a></li>');
        	el_subnav.find(".nav").append('<li><a href="/admincp/office/travel/order/pluginid/'+window._plusinid+'/"><i class="fa fa-cloud"></i> 订单列表</a></li>');
        	el_subnav.find(".nav").append('<li class="goods-list" ><a href="#/list"><i class="fa fa-cloud"></i> 客户列表</a></li>');        	
        	el_subnav.find(".nav").append('<li><a href="?act=main"><i class="fa fa-list"></i> 产品列表</a></li>');
        	//el_subnav.find(".nav").append('<li class="goods-cate"><a href="#/cate"><i class="fa fa-sitemap"></i> 产品分类</a></li>');
        	//el_subnav.find(".nav").append('<li class="goods-config"><a href="#/config"><i class="fa fa-gear"></i> 客户配置</a></li>');
        	//el_subnav.find(".nav").append('<li class=""><a href="?act=main#/config"><i class="fa fa-gear"></i> 产品配置</a></li>');
        	if (this.activeRoute.moduleId == 'views/list' || this.activeRoute.moduleId == 'views/view'  || this.activeRoute.moduleId == 'views/edit') {
        		el_subnav.find('.goods-list').addClass('active');
        	} else if (this.activeRoute.moduleId == 'views/config') {
        		el_subnav.find('.goods-config').addClass('active');
        	} else if (this.activeRoute.moduleId == 'views/cate' || this.activeRoute.moduleId == 'views/cate_edit') {
        		el_subnav.find('.goods-cate').addClass('active');
        	}

        	$('#sub-navbar .pull-right').html(el_subnav);*/
            window._appFacade = {
                curr_router: arguments,
                data: {}
            };
           $(function () {
        	   module.render(routeArguments, $('#container_main'));
           })
            
        })
        .init();
    }}
    
});
