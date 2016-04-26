define(['require_router', "jquery", "underscore"], function(router, $, _) {

    return {init: function () {
    	
    router
        .registerRoutes({
            config: { path: '/config', moduleId: 'views/config' },
            notFound: { path: '*', moduleId: 'views/config' }
        })
        .on('statechange', function(module, routeArguments) {
        	$('#container_main').html('<div class="vco-loading"><div class="vco-loading-icon"></div><div class="vco-message"><p>正在加载...</p></div></div>');
        })
        .on('routeload', function(module, routeArguments) {
        	var el_subnav = $('<div class="subnavgroup" />');
        	el_subnav.html('<div class="subnav"><ul class="nav nav-pills text-sm"></ul></div>');
        	
        	var url = window.location.href;
        	el_subnav.find(".nav").append('<li><a href="'+url.replace('config','list')+'"><i class="fa fa-list"></i> 数据列表</a></li>');
  //      	el_subnav.find(".nav").append('<li class="goods-list" ><a href="'+url.replace('config','template')+'"><i class="fa fa-plus"></i> 新建报表</a></li>');
        	el_subnav.find(".nav").append('<li class="goods-config"><a href="#/config"><i class="fa fa-gear"></i> 报表设置</a></li>');
        	if (this.activeRoute.moduleId == 'views/add') {
        		el_subnav.find('.goods-list').addClass('active');
        	} else if (this.activeRoute.moduleId == 'views/config') {
        		el_subnav.find('.goods-config').addClass('active');
        	} 

        	$('#sub-navbar .pull-right').html(el_subnav);
        	
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
