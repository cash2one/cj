define(["utils/router"], function(router) {
    var router = new router();
    router.routes = {
        chat1: { path: '/chat_client/:goods_id/:sale_id', moduleId: 'views/chat_client'},	//客户聊天界面(产品ID,销售ID)
        chat2: { path: '/chat_sale/:goods_id/:tv_id', moduleId: 'views/chat_sale'},		//销售聊天界面(产品ID,客户ID)
        notFound: { path: '*', moduleId: 'views/list'}
    };
    return router;
});
