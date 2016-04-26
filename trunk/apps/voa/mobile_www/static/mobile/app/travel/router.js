define(["utils/router"], function(router) {
    var router = new router();
    router.routes = {
        data_statistics:{ path: '/edit_receiveinfo', moduleId: 'views/edit_receiveinfo' },
        data_statistics:{ path: '/data_statistics', moduleId: 'views/data_statistics' },
        customers_payment:{ path: '/customers_payment', moduleId: 'views/customers_payment' },
        customers_payment_count:{ path: '/customers_payment_count', moduleId: 'views/customers_payment_count' },
        my_qrcode: { path: '/my_qrcode', moduleId: 'views/my_qrcode' },
        mycart: { path: '/mycart', moduleId: 'views/mycart' },
        mycart2: { path: '/mycart/:goods_id', moduleId: 'views/mycart' },
        my: { path: '/my', moduleId: 'views/my' },
        results: { path: '/results', moduleId: 'views/results' },
        customers_list: { path: '/customers_list/:goods_id', moduleId: 'views/customers_list' },
        customers_list_clean: { path: '/customers_list', moduleId: 'views/customers_list' },
        customer_detail: { path: '/customer_detail/:id', moduleId: 'views/customer_detail' },
        customer_remarks: { path: '/customer_remarks/:id', moduleId: 'views/customer_remarks' },
        customer_add_to_goods: { path: '/customer_edit/goods_id/:goods_id', moduleId: 'views/customer_edit' },
        customer_edit: { path: '/customer_edit/:dataid', moduleId: 'views/customer_edit' },
        customer_add: { path: '/customer_edit', moduleId: 'views/customer_edit' },
        customer_remark: { path: '/customer_remark/:id', moduleId: 'views/customer_remark' },
        goods_list: { path: '/goods_list', moduleId: 'views/goods_list' },
        goods_list2: { path: '/goods_list/:classid', moduleId: 'views/goods_list' },
        goods_detail: { path: '/goods_detail/:id', moduleId: 'views/goods_detail' },
        goods_selected: { path: '/goods_selected', moduleId: 'views/goods_selected' },
        goods_selected2: { path: '/goods_selected/:goods_id', moduleId: 'views/goods_selected' },
        goods_order: { path: '/goods_order', moduleId: 'views/goods_order' },
        goods_order2: { path: '/goods_order/:goods_id/:goods_num', moduleId: 'views/goods_order' },
        goods_order3: { path: '/goods_order/:cartids', moduleId: 'views/goods_order' },
        goods_order_manage: { path: '/goods_order_manage', moduleId: 'views/goods_order_manage' },
        goods_order_detail: { path: '/goods_order_detail/:order_id', moduleId: 'views/goods_order_detail' },
        goods_order_detail_slae: { path: '/goods_order_detail_slae', moduleId: 'views/goods_order_detail_slae' },
        goods_order_list: { path: '/goods_order_list', moduleId: 'views/goods_order_list' },
        goods_detail_promotion: { path: '/goods_detail/:id/promotion/:sig/:timestamp', moduleId: 'views/goods_detail' },
        goods_detail_from_customer: { path: '/goods_detail/from_customer/:from_customer/:id', moduleId: 'views/goods_detail' },
        goods_detail_supply: { path: '/goods_detail/:id/:sid/:pulled', moduleId: 'views/goods_detail' },
        goods_supply_list: { path: '/goods_supply_list', moduleId: 'views/goods_supply_list' },

        //customer: { path: '/customer/*', moduleId: 'customer/customerView' },
        //regex: { path: /^\/\w+\/\d+$/i, moduleId: 'regex/regexView' },
        notFound: { path: '*', moduleId: 'views/goods_list' }
    };
    // 从微信过来的链接设置默认路由。  类似的问题： 缺陷 #351
    if (window.default_view) {
        router.routes.notFound = {path: '*', moduleId: "views/"+window.default_view};
    }

    return router;
});
