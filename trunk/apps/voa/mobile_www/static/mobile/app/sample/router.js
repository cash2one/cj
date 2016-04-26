define(["utils/router"], function(router) {
    var router = new router();
    router.routes = {
        customers_list: { path: '/customers_list', moduleId: 'views/customers_list' },
        customer_detail: { path: '/customer_detail/:id', moduleId: 'views/customer_detail' },

        //customer: { path: '/customer/*', moduleId: 'customer/customerView' },
        //regex: { path: /^\/\w+\/\d+$/i, moduleId: 'regex/regexView' },
        notFound: { path: '*', moduleId: 'views/customers_list' }
    };
    return router;
});
