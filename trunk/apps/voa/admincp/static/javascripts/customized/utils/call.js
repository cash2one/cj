define([], function() {

    // call 构造方法
    function Call() {
        // do something
    }

    Call.prototype = {
        /**
         * 调用其它应用
         * @param {string} name 应用名称
         * @param {string} view 视图控制类
         * @param {*} args 参数
         * @param {function} callback 回调方法
         */
        app: function(name, view, args, callback) {
            // 引入应用配置
            //var org = require.toUrl('').split('?');
            var org_baseUrl = requirejs.s.contexts._.config.baseUrl;
            require.config({
                baseUrl: window._jsdir+'customized/app/'+name
            });

            // 调用视图控制
            require(['views/' + view], function(view) {

                // 视图初始化
                var v = new view();
                // 渲染
                var el = v.render(args);
                // 如果需要回调
                if ('function' == typeof callback) {
                	callback(el);
                }
                require.config({
                    baseUrl: org_baseUrl
                });
            });
        }
    };

    return Call;
});
