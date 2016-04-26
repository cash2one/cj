/**
 * @param tab 切换按钮父级
 * @param con 切换内容父级
 * @param active 用于显示当前的类名
 * */
(function (app) {
    app.directive("cTab", function () {
        return {
            restrict : 'EA',
            link: function(scope,iEle,iAttr){
                var tab = iEle.find(iAttr.tab),  //切换按钮
                    con = iEle.find(iAttr.con),  //切换内容
                    active = iAttr.active;  //显示用类名
                tab.children().on("click", function () {
                    tab.children().removeClass(active);
                    $(this).addClass(active);
                    con.children().eq($(this).index()).addClass(active).siblings().removeClass(active);
                })
            }
        }
    });
})(angular.module('app.modules.member'));