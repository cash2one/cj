
define(['underscore'], function(_) {

    // call 构造方法
    function commom() {
        // do something
    }

    commom.prototype = {
        /**
         * 时间格式化
         * @param  timestamp 时间戳
         * @return  string 格式化后的日期
         */
        dateformat: function(timestamp) {
            if (timestamp) {
                var d = new Date(timestamp *1000);
                return d.getUTCFullYear()+'-'+(d.getUTCMonth() + 1)+'-'+d.getUTCDate();
            }
            return timestamp;
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

    return commom;
});
