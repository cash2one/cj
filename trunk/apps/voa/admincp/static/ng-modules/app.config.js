(function () {
    var BASE_URL = '/';

    angular.module('ng.poler.embed').run(['ApiUtil',function (ApiUtil) {
        ApiUtil.config({
            URL_PREFIX:BASE_URL
        })
    }]);
})();
