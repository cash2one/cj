define(["utils/router"], function(router) {
    var router = new router();
    router.routes = {
        customers_list: { path: '/customers_list/:goods_id', moduleId: 'views/customers_list' },
        customer_detail: { path: '/customer_detail/:id', moduleId: 'views/customer_detail' },
        customer_add_to_goods: { path: '/customer_edit/goods_id/:goods_id', moduleId: 'views/customer_edit' },
        customer_edit: { path: '/customer_edit/:dataid', moduleId: 'views/customer_edit' },
        goods_list: { path: '/goods_list', moduleId: 'views/goods_list' },
        goods_detail: { path: '/goods_detail/:id', moduleId: 'views/goods_detail' },
        goods_detail_supply: { path: '/goods_detail/:id/:sid', moduleId: 'views/goods_detail' },
        goods_supply_list: { path: '/goods_supply_list', moduleId: 'views/goods_supply_list' },

        //customer: { path: '/customer/*', moduleId: 'customer/customerView' },
        //regex: { path: /^\/\w+\/\d+$/i, moduleId: 'regex/regexView' },
        notFound: { path: '*', moduleId: 'views/goods_list' }
    };
    return router;
});
