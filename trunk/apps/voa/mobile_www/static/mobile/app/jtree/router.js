define(["utils/router"], function(router) {
    var router = new router();
    router.routes = {
        notFound: { path: '*', moduleId: 'views/default' }
    };
    return router;
});
