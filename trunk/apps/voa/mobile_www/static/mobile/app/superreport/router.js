define(["utils/router"], function(router) {
    var router = new router();
    router.routes = {   
    	add: { path: '/add', moduleId: 'views/add' },//添加报表
        edit: { path: '/edit/:dr_id', moduleId: 'views/edit' },//编辑报表
        daily: { path: '/daily', moduleId: 'views/daily' },//查看日报
        daily1: { path: '/daily/:csp_id/:date', moduleId: 'views/daily' },//查看日报
        month: { path: '/month', moduleId: 'views/month' },//查看月报
        month1: { path: '/month/:csp_id/:year/:month', moduleId: 'views/month' },//查看月报
        shops: { path: '/shops', moduleId: 'views/shops' },//查看日报门店列表
        shops1: { path: '/shops/:date', moduleId: 'views/shops' },//查看日报门店列表
        monthshops: { path: '/monthshops', moduleId: 'views/monthshops' },//查看月报门店列表
        monthshops1: { path: '/monthshops/:year/:month', moduleId: 'views/monthshops' },//查看月报门店列表
        error: { path: '/error/:errcode', moduleId: 'views/error' },//编辑报表
        notFound: { path: '*', moduleId: 'views/daily' }
    };
    return router;
});
