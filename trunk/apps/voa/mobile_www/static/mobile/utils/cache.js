define([], function() {

    // 如果缓存属性未定义
    if (null == window._cache) {
        window._cache = {};
    }

    // 缓存类
    function Cache() {
        // do something.
    }

    Cache.prototype = {
        // 键名
        key: function() {
            // do something.
        },
        /**
         * 设置缓存
         * @param {string} key 缓存键值
         * @param {*} value 缓存详情
         */
        set: function(key, value) {
            window._cache[key] = value;
        },
        /**
         * 获取缓存
         * @param {string} key 缓存键值
         */
        get: function(key) {
            return window._cache[key];
        },
        /**
         * 清空缓存
         * @param {string} key 缓存键值
         */
        clean: function(key) {
            // 如果 key 有值, 则清空指定缓存
            if (key) {
                window._cache[key] = null;
            } else {
                window._cache = null;
            }
        }
    };

    return new Cache();
});
