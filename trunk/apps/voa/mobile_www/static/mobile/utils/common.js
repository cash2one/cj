
define(['underscore'], function(_) {

    // call 构造方法
    function common() {
        // do something
    }

    common.prototype = {
        /**
         * 时间格式化
         * @param  timestamp 时间戳
         * @return  string 格式化后的日期
         */
        dateformat: function(timestamp) {
            if (timestamp) {
                var d = new Date(timestamp *1000);
                return d.getUTCFullYear()+'-'+(d.getUTCMonth() + 1)+'-'+d.getUTCDate();
            } else {
                var d = new Date();
                return d.getUTCFullYear()+'-'+(d.getUTCMonth() + 1)+'-'+d.getUTCDate();
            }
        },

        /**
         * url 拼凑
         * @param  timestamp 时间戳
         * @return  string 格式化后的日期
         */
        makeurl: function(view, params, act) {

            var buy_url = window.location.origin+window.location.pathname;
            if (window.location.search) {
                var query = window.location.search;
                query=query.replace(/(&?)__view=([a-zA-Z0-9_]*)/, "");
                query=query.replace(/(&?)__params=([a-zA-Z0-9_]*)/, "");
                query=query.replace(/(&?)act=([a-zA-Z0-9_]*)/, "");
                query=query.replace(/(&?)logintype=([a-zA-Z0-9_]*)/, "");
                query=query.replace(/(&?)saleuid=([a-zA-Z0-9_]*)/, "");
                query=query.replace(/(&?)code=([a-zA-Z0-9_]*)/, "");
                buy_url = buy_url + ("?" == query ? query : query + "&") +"__view="+view+"&__params="+params;
            } else {
                buy_url = buy_url +"?__view="+view+"&__params="+params;
            }

            if (!_.isUndefined(act)) {
                buy_url += "&act="+act;
            }

            if (0 < window.saleuid) {
                buy_url += "&saleuid=" + window.saleuid;
            }

            return buy_url;
        },


        /**
         * 微信debug 日志显示
         * @param mixed vars   需要debug的数据 可以是string, array, objects
         * @param element $ele 显示日志的容器
         * @return  void
         */
        weixin_debug: function (vars, $ele) {

            if (_.isArray(vars)) {
                for (var i in vars) {
                    this.weixin_debug(vars[i]);
                }
            } else {
                $ele.append(i + ':  ' + vars[i]);
            }
        }
    };

    return new common;
});
