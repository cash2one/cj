/**
 * Created by three on 15/12/18.
 */

/**
 * 定义命民空间
 */
(function (window,angular) {
    window.PolerEmbed = {
        init: function ($dom, module) {
            angular.injector(['ng']).invoke(['$q',function ($q) {
                angular.bootstrap($dom, ['ng.poler.embed'].concat(module));
            }]);
        }
    };
})(window, angular);