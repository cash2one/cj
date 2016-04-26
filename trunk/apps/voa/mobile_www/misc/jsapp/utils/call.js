define(["jquery", "underscore"], function ($, _){
    function call() {
    }
    call.prototype = {
        // 调用其它应用
        app: function (name, view, args, callback) {
            require.config({
               baseUrl: window._root+'/app/'+name
            });
            
            require(['views/'+view], function (view) {
                var v = new view();
                var el = v.render(args);
                callback(el);
            });
        }
    };

    return call;
});
