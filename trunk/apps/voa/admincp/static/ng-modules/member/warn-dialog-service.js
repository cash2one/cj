/**
 * 弹窗提示
 * */
(function (app) {
    app.factory('warnDialog', ['DialogTool', function (DialogTool) {
        return function (title, text, tips, isBtn, fn) {
            DialogTool.open({
                templateUrl: '/admincp/static/ng-modules/member/views/main-tpl/warn-dialog.html',
                params:{
                    title: function(){
                        return title;
                    },
                    text: function(){
                        return text;
                    },
                    tips: function(){
                        return tips;
                    },
                    isBtn: function () {
                        return isBtn;
                    }
                }
            }).result.then(function ok() {
                fn();
            }, function () {
                // 取消不做任何操作
            });
        }
    }]);
})(angular.module('app.modules.member'));