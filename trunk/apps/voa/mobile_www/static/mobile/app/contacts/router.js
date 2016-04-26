define(["utils/router"], function(router) {
    var router = new router();
    router.routes = {
    	contacts: { path: '/contacts', moduleId: 'views/contacts' },
    	favorites: { path: '/favorites', moduleId: 'views/favorites' },
    	groups: { path: '/groups', moduleId: 'views/groups' },
        notFound: { path: '*', moduleId: 'views/contacts' }
    };
    return router;
});
