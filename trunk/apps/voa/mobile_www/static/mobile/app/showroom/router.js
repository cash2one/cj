define(["utils/router"], function(router) {
    var router = new router();
    router.routes = {
        /*customers_list: { path: '/customers_list/:goods_id', moduleId: 'views/customers_list' },
        customer_detail: { path: '/customer_detail/:id', moduleId: 'views/customer_detail' },
        customer_add_to_goods: { path: '/customer_edit/goods_id/:goods_id', moduleId: 'views/customer_edit' },
        customer_edit: { path: '/customer_edit/:dataid', moduleId: 'views/customer_edit' },
        goods_list: { path: '/goods_list', moduleId: 'views/goods_list' },
        goods_detail: { path: '/goods_detail/:id', moduleId: 'views/goods_detail' },
        goods_supply_list: { path: '/goods_supply_list', moduleId: 'views/goods_supply_list' },*/
        list: { path: '/list/:id', moduleId: 'views/list' },
        detail: { path: '/detail/:id', moduleId: 'views/detail' },    
        notFound: { path: '*', moduleId: 'views/cat' }
    };
    return router;
});
