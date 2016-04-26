define(["jquery", "underscore"], function ($, _){
    if ( null == window._cache) {
        window._cache = {};
    }
    function cache() {
    }
    cache.prototype = {
        key: function() {
        }
        set: function (key, value) {
            window._cache[key] = value;
        },
        get: function (key) {
            return window._cache[key];
        },
        clean: function(key) {
            if (key) {
                window._cache[key] = null;
            } else {
                window._cache = null;
            }
        }
    };

    return new cache();
}
