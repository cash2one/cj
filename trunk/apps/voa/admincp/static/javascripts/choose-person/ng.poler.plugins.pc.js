/**
 * Created by three on 15/11/30.
 */

angular.module('ng.poler.plugins.pc', ['ng.poler','ui.bootstrap']).config(['$httpProvider', function ($httpProvider) {
    // 拦截器配置

    // Use x-www-form-urlencoded Content-Type
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    $httpProvider.defaults.headers.put['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    $httpProvider.defaults.headers.patch['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    $httpProvider.defaults.withCredentials = true;

    /**
     * The workhorse; converts an object to x-www-form-urlencoded serialization.
     * @param {Object} obj
     * @return {String}
     */
    var param = function (obj) {
        var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

        for (name in obj) {
            value = obj[name];

            if (value instanceof Array) {
                for (i = 0; i < value.length; ++i) {
                    subValue = value[i];
                    fullSubName = name + '[' + i + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if (value instanceof Object) {
                for (subName in value) {
                    subValue = value[subName];
                    fullSubName = name + '[' + subName + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if (value !== undefined && value !== null)
                query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
        }

        return query.length ? query.substr(0, query.length - 1) : query;
    };

    // Override $http service's default transformRequest
    $httpProvider.defaults.transformRequest = [function (data) {
        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];
}]).run(['ApiUtil', 'app_config', function (ApiUtil, appConfig) {
    ApiUtil.config({
        URL_PREFIX: appConfig.API.BASE_URL
    })
}]);
angular.module("ng.poler.plugins.pc").run(["$templateCache", function($templateCache) {$templateCache.put("templates/choose-advanced.html","<div class=\"choose-person-plugin\" ng-controller=\"AdvancedChooseCtrl\"><div class=\"modal-head\"><ul class=\"choose-menu\"><li ng-if=\"params.person.isPerson\" ng-click=\"tabChange(1)\" ng-class=\"{\'active\':setShowWhat.person.isShow}\">选人</li><li ng-if=\"params.tag.isTags\" ng-click=\"tabChange(2)\" ng-class=\"{\'active\':setShowWhat.tag.isShow}\">标签</li><li ng-if=\"params.department.isDepartment\" ng-click=\"tabChange(3)\" ng-class=\"{\'active\':setShowWhat.department.isShow}\">部门</li></ul></div><div><div id=\"person-view\" ng-if=\"params.person.isPerson\" ng-show=\"setShowWhat.person.isShow\"><section class=\"modal-lr\"><div class=\"modal-l\"><div class=\"modal-search no-item\"><div class=\"dem-item-wrap\"><ul class=\"sel-dpm-item\"><li class=\"sel-item\">研发部</li><li class=\"sel-item\">产品部</li><li class=\"sel-item\">资源部</li><li class=\"sel-item\">运营部</li></ul></div><div class=\"search-wrap\"><span class=\"fadajing\"></span> <input type=\"text\" placeholder=\"搜索部门\" onfocus=\"select()\" data-ng-model=\"depQuery.keyword\" data-ng-keypress=\"checkSearchDep($event)\"> <a href=\"javascript:;\" class=\"chosePer-icon chosePer-close input-close\" data-ng-if=\"depQuery.keyword\" data-ng-click=\"depQuery.keyword = null\"></a></div></div><div class=\"tree department-tree\"><div class=\"chosePer-warning-p\"><i class=\"chosePer-icon chosePer-warning\"></i> <span class=\"ver-bottom\">当前部门不可选择</span></div><span class=\"clearSearch\" data-ng-if=\"isSearchDep\" data-ng-click=\"cancelSearchDep()\">清除搜索 <i class=\"fa fa-times\"></i></span><div><ul data-ng-repeat=\"dep in departmentListWithPerson\"><li><div class=\"tree-item-content\"><span class=\"cy-icon cy-arrow chosePer-icon\" data-ng-class=\"{\'cy-open\':dep.isOpen, \'cy-kongbai\':dep.noChild}\" data-ng-click=\"toggleDepWithPerson(dep.id)\"></span><span class=\"cy-icon cy-folder\"></span> <span class=\"dpm-name\" data-ng-bind=\"dep.name\" data-ng-click=\"clickDepartment(dep.id)\"></span></div><ul collapse=\"!dep.isOpen\" data-ng-repeat=\"dep1 in dep.childDep\" data-ng-include=\"\'app/plugins/choose-person-page-node.html\'\"></ul></li></ul></div></div></div><div class=\"modal-r\"><div class=\"personnal-search\" data-ng-class=\"{\'no-item\':selectedMembers.length==0}\"><div class=\"ps-special-wrap\"><div class=\"chosePer-warning-p\"><i class=\"chosePer-icon chosePer-warning\"></i> <span class=\"ver-bottom\">当前人员不可选择</span></div></div><div class=\"per-item-Wrap\"><ul class=\"sel-dpm-item\"><li class=\"sel-item\" data-ng-repeat=\"sm in selectedMembers\"><span class=\"sel-personal-name ellipsis\">{{sm.m_username}}</span> <a href=\"javascript:;\" class=\"sel-item-del chosePer-icon chosePer-delete\" data-ng-click=\"deleteMember($index)\"></a></li></ul></div><div class=\"search-wrap\"><span class=\"fadajing\"></span> <input type=\"text\" placeholder=\"搜索人员\" onfocus=\"select()\" data-ng-model=\"memberQuery.keyword\" data-ng-keypress=\"checkSearchMember($event)\"> <a href=\"javascript:;\" class=\"chosePer-icon chosePer-close input-close\" data-ng-if=\"memberQuery.keyword\" data-ng-click=\"cancelSearchMember()\"></a></div></div><div class=\"chosePer-options\" data-ng-show=\"members.length > 0 && !singleSelect\"><a href=\"javascript:void(0);\" class=\"chosePer-fn\" data-ng-click=\"selectAll()\">全选</a> <a href=\"javascript:void(0);\" class=\"chosePer-fn\" data-ng-click=\"selectReverse()\">反选</a> <a href=\"javascript:void(0);\" class=\"chosePer-fn\" data-ng-click=\"cancelAll()\">清空</a></div><div class=\"personnal-avatar-wrap\"><div class=\"per-list-wrap\" data-ng-if=\"noMembers\"><h3>没有员工</h3></div><div class=\"per-list-wrap\" data-ng-if=\"!noMembers\"><ul class=\"personnal-list\"><li class=\"personnal-item\" title=\"{{member.m_username}}\" data-ng-class=\"{\'check\':member.selected, \'disable\':false}\" data-ng-click=\"clickMembers($index)\" data-ng-repeat=\"member in members\"><div class=\"per-avatar\"><img data-ng-src=\"{{member.m_face}}\"><div class=\"isChecked\" data-ng-if=\"member.selected\"><i class=\"checked\"></i></div></div><span class=\"per-name ellipsis\" data-ng-bind=\"member.m_username\"></span></li></ul><div class=\"load-more\" data-ng-if=\"hasMore\" data-ng-click=\"moreMembers()\"><a href=\"javascript:void(0);\">加载更多...</a></div></div></div></div></section></div><div id=\"department-view\" ng-if=\"params.department.isDepartment\" ng-show=\"setShowWhat.department.isShow\"><div class=\"choose-body choose-body-department\"><div class=\"modal-search no-item\"><div class=\"dem-item-wrap\"><ul class=\"sel-dpm-item\"></ul></div><div class=\"search-wrap\"><span class=\"fadajing\"></span> <input type=\"text\" placeholder=\"搜索部门\" onfocus=\"select()\" data-ng-model=\"depQuery.keyword\" data-ng-keypress=\"checkSearchDep($event)\"> <a href=\"javascript:;\" class=\"chosePer-icon chosePer-close input-close\" data-ng-if=\"depQuery.keyword\" data-ng-click=\"depQuery.keyword = null\"></a></div></div><div class=\"tree choose-department\"><div class=\"chosePer-warning-p\"><i class=\"chosePer-icon chosePer-warning\"></i> <span class=\"ver-bottom\">当前部门不可选择</span></div><span class=\"clearSearch\" data-ng-if=\"isSearchDep\" data-ng-click=\"cancelSearchDep()\">清除搜索 <i class=\"fa fa-times\"></i></span><div><ul data-ng-repeat=\"dep in departmentList\" data-ng-if=\"dep.id != 0\"><li><div class=\"tree-item-content\"><span class=\"chosePer-icon cy-arrow\" data-ng-class=\"{\'cy-open\':dep.isOpen, \'cy-kongbai\':dep.noChild}\" data-ng-click=\"toggleDep(dep.id)\"></span> <span class=\"chosePer-icon chosePer-checkBox\" data-ng-click=\"selectDepartment(dep.id)\" data-ng-class=\"{checked: dep.isChecked}\"></span> <span class=\"chosePer-icon cy-folder\" data-ng-click=\"selectDepartment(dep.id)\"></span> <span class=\"dpm-name\" data-ng-bind=\"dep.name\" data-ng-click=\"selectDepartment(dep.id)\"></span></div><ul collapse=\"!dep.isOpen\" data-ng-repeat=\"dep1 in dep.childDep\" data-ng-include=\"\'app/plugins/choose-dep-page-node.html\'\"></ul></li></ul></div></div></div></div><div id=\"tags-view\" class=\"tags-view\" ng-if=\"params.tag.isTags\" ng-show=\"setShowWhat.tag.isShow\"><div class=\"tags-wrap\"><label class=\"tags-tip\">我的标签</label><ul class=\"tags-list\"><li ng-repeat=\"tag in tags track by $index\" ng-click=\"chooseTag($index)\"><div class=\"tags-left\"><div class=\"tags-name\">{{tag.name}}</div></div><div class=\"tags-right\"><span class=\"chosePer-icon chosePer-checkBox\" ng-class=\"{\'checked\':tag.selected}\"></span></div></li></ul><div class=\"load-more\" ng-show=\"hasMoreTags\" data-ng-click=\"moreTags()\"><a href=\"javascript:void(0);\">加载更多...</a></div></div></div></div><div class=\"modal-footer\"><a href=\"javascript:void(0);\" class=\"modal-btn btn-default btn-large\" data-ng-click=\"doCancel()\">取消</a> <a href=\"javascript:void(0);\" class=\"modal-btn btn-confrim btn-large\" data-ng-click=\"OK()\">确定</a></div></div><script type=\"text/ng-template\" id=\"app/plugins/choose-dep-page-node.html\"><li data-ng-init=\"dep1_temp=dep1\"> <div class=\"tree-item-content\"> <span class=\"chosePer-icon cy-arrow\" data-ng-class=\"{\'cy-open\':dep1.isOpen, \'cy-kongbai\':dep1.noChild}\" data-ng-click=\"toggleDep(dep1.id)\"></span> <span class=\"chosePer-icon chosePer-checkBox\" data-ng-class=\"{checked: dep1.isChecked}\" data-ng-click=\"selectDepartment(dep1.id)\"></span> <span class=\"chosePer-icon cy-folder\" data-ng-click=\"selectDepartment(dep1.id)\"></span> <span class=\"dpm-name\" data-ng-bind=\"dep1.name\" data-ng-click=\"selectDepartment(dep1.id)\"></span> </div> <ul data-ng-class=\"{\'collapse\':!$parent.dep1_temp.isOpen}\" data-ng-repeat=\"dep1 in dep1.childDep\" data-ng-include=\"\'app/plugins/choose-dep-page-node.html\'\"><!-- 三级 --> <!-- 迭代树输出 --> </ul> </li></script><script type=\"text/ng-template\" id=\"app/plugins/choose-person-page-node.html\"><li data-ng-init=\"dep1_temp=dep1\"> <div class=\"tree-item-content\"> <span class=\"cy-icon cy-arrow chosePer-icon\" data-ng-class=\"{\'cy-open\':dep1.isOpen, \'cy-kongbai\':dep1.noChild}\" data-ng-click=\"toggleDep(dep1.id)\"></span> <!--<span class=\"chosePer-icon chosePer-checkBox {{more_checked ? \'\': \'chosePer-cbdisable\'}}\" data-ng-click=\"\"></span>--> <span class=\"cy-icon cy-folder\"></span> <span class=\"dpm-name\" data-ng-bind=\"dep1.name\" data-ng-click=\"clickDepartment(dep1.id)\"></span> </div> <ul data-ng-class=\"{\'collapse\':!$parent.dep1_temp.isOpen}\" data-ng-repeat=\"dep1 in dep1.childDep\" data-ng-include=\"\'app/plugins/choose-person-page-node.html\'\"><!-- 三级 --> <!-- 迭代树输出 --> </ul> </li></script>");
$templateCache.put("templates/choose-department.html","<div class=\"choose-person-plugin\" data-ng-controller=\"ChooseDepartCtrl\"><div class=\"modal-head\"><span class=\"mod-title\">选择{{what1}}</span></div><div class=\"choose-body choose-body-department\"><div class=\"modal-search no-item\"><div class=\"dem-item-wrap\"><ul class=\"sel-dpm-item\"></ul></div><div class=\"search-wrap\"><span class=\"fadajing\"></span> <input type=\"text\" placeholder=\"搜索{{what1}}\" onfocus=\"select()\" data-ng-model=\"depQuery.keyword\" data-ng-keypress=\"checkSearchDep($event)\"> <a href=\"javascript:;\" class=\"chosePer-icon chosePer-close input-close\" data-ng-if=\"depQuery.keyword\" data-ng-click=\"depQuery.keyword = null\"></a></div></div><div class=\"tree choose-department\"><div class=\"chosePer-warning-p\"><i class=\"chosePer-icon chosePer-warning\"></i> <span class=\"ver-bottom\">当前{{what1}}不可选择</span></div><span class=\"clearSearch\" data-ng-if=\"isSearchDep\" data-ng-click=\"cancelSearchDep()\">清除搜索 <i class=\"fa fa-times\"></i></span><div><ul data-ng-repeat=\"dep in departmentList\" data-ng-if=\"dep.id != 0\"><li><div class=\"tree-item-content\"><span class=\"chosePer-icon cy-arrow\" data-ng-class=\"{\'cy-open\':dep.isOpen, \'cy-kongbai\':dep.noChild}\" data-ng-click=\"toggleDep(dep.id)\"></span> <span class=\"chosePer-icon chosePer-checkBox\" data-ng-click=\"selectDepartment(dep.id)\" data-ng-class=\"{checked: dep.isChecked}\"></span> <span class=\"chosePer-icon cy-folder\" data-ng-click=\"selectDepartment(dep.id)\"></span> <span class=\"dpm-name\" data-ng-bind=\"dep.name\" data-ng-click=\"selectDepartment(dep.id)\"></span></div><ul collapse=\"!dep.isOpen\" data-ng-repeat=\"dep1 in dep.childDep\" data-ng-include=\"\'app/plugins/choose-dep-page-node.html\'\"></ul></li></ul></div></div></div><div class=\"modal-footer\"><a href=\"javascript:void(0);\" class=\"modal-btn btn-default btn-large\" data-ng-click=\"doCancel()\">取消</a> <a href=\"javascript:void(0);\" class=\"modal-btn btn-confrim btn-large\" data-ng-click=\"OK()\">确定</a></div></div><script type=\"text/ng-template\" id=\"app/plugins/choose-dep-page-node.html\"><li data-ng-init=\"dep1_temp=dep1\"> <div class=\"tree-item-content\"> <span class=\"chosePer-icon cy-arrow\" data-ng-class=\"{\'cy-open\':dep1.isOpen, \'cy-kongbai\':dep1.noChild}\" data-ng-click=\"toggleDep(dep1.id)\"></span> <span class=\"chosePer-icon chosePer-checkBox\" data-ng-class=\"{checked: dep1.isChecked}\" data-ng-click=\"selectDepartment(dep1.id)\"></span> <span class=\"chosePer-icon cy-folder\" data-ng-click=\"selectDepartment(dep1.id)\"></span> <span class=\"dpm-name\" data-ng-bind=\"dep1.name\" data-ng-click=\"selectDepartment(dep1.id)\"></span> </div> <ul data-ng-class=\"{\'collapse\':!$parent.dep1_temp.isOpen}\" data-ng-repeat=\"dep1 in dep1.childDep\" data-ng-include=\"\'app/plugins/choose-dep-page-node.html\'\"><!-- 三级 --> <!-- 迭代树输出 --> </ul> </li></script>");
$templateCache.put("templates/choose-person.html","<div class=\"choose-person-plugin\" data-ng-controller=\"ChoosePersonCtrl\"><div class=\"modal-head\"><span class=\"mod-title\">选择{{what2}}</span></div><section class=\"modal-lr\"><div class=\"modal-l\"><div class=\"modal-search no-item\"><div class=\"dem-item-wrap\"><ul class=\"sel-dpm-item\"><li class=\"sel-item\">研发部</li><li class=\"sel-item\">产品部</li><li class=\"sel-item\">资源部</li><li class=\"sel-item\">运营部</li></ul></div><div class=\"search-wrap\"><span class=\"fadajing\"></span> <input type=\"text\" placeholder=\"搜索{{what1}}\" onfocus=\"select()\" data-ng-model=\"depQuery.keyword\" data-ng-keypress=\"checkSearchDep($event)\"> <a href=\"javascript:;\" class=\"chosePer-icon chosePer-close input-close\" data-ng-if=\"depQuery.keyword\" data-ng-click=\"depQuery.keyword = null\"></a></div></div><div class=\"tree department-tree\"><div class=\"chosePer-warning-p\"><i class=\"chosePer-icon chosePer-warning\"></i> <span class=\"ver-bottom\">当前{{what1}}不可选择</span></div><span class=\"clearSearch\" data-ng-if=\"isSearchDep\" data-ng-click=\"cancelSearchDep()\">清除搜索 <i class=\"fa fa-times\"></i></span><div><ul data-ng-repeat=\"dep in departmentList\"><li><div class=\"tree-item-content\"><span class=\"cy-icon cy-arrow chosePer-icon\" data-ng-class=\"{\'cy-open\':dep.isOpen, \'cy-kongbai\':dep.noChild}\" data-ng-click=\"toggleDep(dep.id)\"></span><span class=\"cy-icon cy-folder\"></span> <span class=\"dpm-name\" data-ng-bind=\"dep.name\" data-ng-click=\"clickDepartment(dep.id)\"></span></div><ul collapse=\"!dep.isOpen\" data-ng-repeat=\"dep1 in dep.childDep\" data-ng-include=\"\'app/plugins/choose-person-page-node.html\'\"></ul></li></ul></div></div></div><div class=\"modal-r\"><div class=\"personnal-search\" data-ng-class=\"{\'no-item\':selectedMembers.length==0}\"><div class=\"ps-special-wrap\"><div class=\"chosePer-warning-p\"><i class=\"chosePer-icon chosePer-warning\"></i> <span class=\"ver-bottom\">当前{{what2}}不可选择</span></div></div><div class=\"per-item-Wrap\"><ul class=\"sel-dpm-item\"><li class=\"sel-item\" data-ng-repeat=\"sm in selectedMembers\"><span class=\"sel-personal-name ellipsis\">{{sm.m_username}}</span> <a href=\"javascript:;\" class=\"sel-item-del chosePer-icon chosePer-delete\" data-ng-click=\"deleteMember($index)\"></a></li></ul></div><div class=\"search-wrap\"><span class=\"fadajing\"></span> <input type=\"text\" placeholder=\"搜索{{what2}}\" onfocus=\"select()\" data-ng-model=\"memberQuery.keyword\" data-ng-keypress=\"checkSearchMember($event)\"> <a href=\"javascript:;\" class=\"chosePer-icon chosePer-close input-close\" data-ng-if=\"memberQuery.keyword\" data-ng-click=\"cancelSearchMember()\"></a></div></div><div class=\"chosePer-options\" data-ng-show=\"members.length > 0 && !singleSelect\"><a href=\"javascript:void(0);\" class=\"chosePer-fn\" data-ng-click=\"selectAll()\">全选</a> <a href=\"javascript:void(0);\" class=\"chosePer-fn\" data-ng-click=\"selectReverse()\">反选</a> <a href=\"javascript:void(0);\" class=\"chosePer-fn\" data-ng-click=\"cancelAll()\">清空</a></div><div class=\"personnal-avatar-wrap\"><div class=\"per-list-wrap\" data-ng-if=\"noMembers\"><h3>没有{{what2}}</h3></div><div class=\"per-list-wrap\" data-ng-if=\"!noMembers\"><ul class=\"personnal-list\"><li class=\"personnal-item\" title=\"{{member.m_username}}\" data-ng-class=\"{\'check\':member.selected, \'disable\':false}\" data-ng-click=\"clickMembers($index)\" data-ng-repeat=\"member in members\"><div class=\"per-avatar\"><img data-ng-src=\"{{member.m_face}}\"><div class=\"isChecked\" data-ng-if=\"member.selected\"><i class=\"checked\"></i></div></div><span class=\"per-name ellipsis\" data-ng-bind=\"member.m_username\"></span></li></ul><div class=\"load-more\" data-ng-if=\"hasMore\" data-ng-click=\"moreMembers()\"><a href=\"javascript:void(0);\">加载更多...</a></div></div></div><div class=\"per-btnGroup\"><a href=\"javascript:void(0);\" class=\"modal-btn btn-default btn-large\" data-ng-click=\"doCancel()\">取消</a> <a href=\"javascript:void(0);\" class=\"modal-btn btn-confrim btn-large\" data-ng-click=\"OK()\">确定</a></div></div></section></div><script type=\"text/ng-template\" id=\"app/plugins/choose-person-page-node.html\"><li data-ng-init=\"dep1_temp=dep1\"> <div class=\"tree-item-content\"> <span class=\"cy-icon cy-arrow chosePer-icon\" data-ng-class=\"{\'cy-open\':dep1.isOpen, \'cy-kongbai\':dep1.noChild}\" data-ng-click=\"toggleDep(dep1.id)\"></span> <!--<span class=\"chosePer-icon chosePer-checkBox {{more_checked ? \'\': \'chosePer-cbdisable\'}}\" data-ng-click=\"\"></span>--> <span class=\"cy-icon cy-folder\"></span> <span class=\"dpm-name\" data-ng-bind=\"dep1.name\" data-ng-click=\"clickDepartment(dep1.id)\"></span> </div> <ul data-ng-class=\"{\'collapse\':!$parent.dep1_temp.isOpen}\" data-ng-repeat=\"dep1 in dep1.childDep\" data-ng-include=\"\'app/plugins/choose-person-page-node.html\'\"><!-- 三级 --> <!-- 迭代树输出 --> </ul> </li></script>");}]);
/**
 * Created by Administrator on 2015/10/29 0029.
 */
angular.module('ng.poler.plugins.pc')
    .constant('app_config', {
        API: {
            BASE_URL: '/',
            REQUEST_TIMEOUT: 10000,
            CHECK_LOGIN_DELAY: 2 * 1000,
            CHECK_CHAT_GROUP: 1000,
            MSG_NOTICE: 1000 * 60,
            CHECK_SUCCESS: function (data) {
                try {
                    return data['errcode'] == this.RES_CODE.SUCCESS;
                } catch (e) {
                    return false;
                }
            },
            RES_CODE: {
                SUCCESS:'0',
                UNAUTHENTICATED:'10004',
                UNAUTHENTICATED2:'5025'
            }
        }
    });
/**
 * Created by Administrator on 2015/11/4 0004.
 */
(function (app,window) {

    /**
     * 通过 Dom 元素启动angular
     *
     * @demo：
     * <link rel="stylesheet" href="css/ng.poler.plugins.pc.min.css">
     *  <script src="js/ng.poler.min.js"></script>
     *  <script src="js/ng.poler.plugins.pc.min.js"></script>
     *
     *  <div id='angular-area' data-ng-controller="ChooseShimCtrl">
     *      <h1>angularJs</h1>
     *      <button data-ng-click="selectPerson('s','selectedPersonCallBack')">选人！</button>
     *      <button data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')">选部门！</button>
     *  </div>
     *
     * <script>
     *      angular.bootstrap(document.getElementById('angular-area'),['ng.poler.plugins.pc']);
     * </script>
     */
    app.controller('ChooseShimCtrl', ['$scope', 'PersonChooser','DepartmentChooser', function ($scope, PersonChooser, DepartmentChooser) {
        /**
         * 解析用户的参数
         * @param paramName
         * @returns {{selected: Object}}
         */
        function calShimParams(paramName) {
            var shimParams = eval(paramName);
            var selected = [];
            var config = {
                singleSelect:false
            };
            if(shimParams instanceof Array) {
                selected = shimParams;
            } else {
                for(var k in config) {
                    config[k] = shimParams.hasOwnProperty(k)?shimParams[k]:config[k];
                }
                selected = shimParams.hasOwnProperty('selected')?shimParams['selected']:[];
            }
            return {
                selected:selected,
                config: config
            };
        }
        /**
         * 选人ctrl
         */
        $scope.selectPerson = function(selectedPerson, callback, customParams) {
            var shimParams = calShimParams(selectedPerson);
            PersonChooser.choose(shimParams.selected,null,shimParams.config).result.then(function (data) {
                var fun = eval(callback);
                fun(data,customParams);
            });
        };
        /**
         * 选部门ctrl
         */
        $scope.selectDepartment = function(selectedDepartment, callback,customParams) {
            var shimParams = calShimParams(selectedDepartment);
            DepartmentChooser.choose(shimParams.selected,null,shimParams.config).result.then(function (data) {
                var fun = eval(callback);
                fun(data,customParams);
            });
        };
    }]);


    /**
     * 使用原始事件调用
     * @type {null}
     */
    var ShimApp = (function () {
        var app = null;
        return function () {
            if(!app) {
                app = angular.bootstrap(angular.element('<div></div>'),['ng.poler.plugins.pc']);
                //angular.injector(['ng', 'ng.poler.plugins.pc']);
            }
            return app;
        }
    })();

    window.shimChoosePerson = function (selectedPerson,config, callbackFun) {
        ShimApp().invoke(['$q', 'PersonChooser', function ($q, PersonChooser) {
            PersonChooser.choose(selectedPerson,null,config).result.then(function (data) {
                    callbackFun(data);
                });
        }]);
    };

    window.shimChooseDepartment = function (selectedDepartment,config, callbackFun) {
        ShimApp().invoke(['$q','DepartmentChooser', function ($q, DepartmentChooser) {
            DepartmentChooser.choose(selectedDepartment,null,config).result.then(function (data) {
                callbackFun(data);
            });
        }]);
    };


    window.advancedChoose = function(selecteds, config, callbackFun) {
        ShimApp().invoke(['AdvancedChooser', function(AdvancedChooser) {
            AdvancedChooser.choose(selecteds, null, config).result.then(function (data) {
                callbackFun(data);
            });
        }]);
    };

    /**
     * 配置接口url地址
     */
    window.ChooseApiConfig = function (conf) {
        ShimApp().invoke(['ApiUtil', function (ApiUtil) {
            ApiUtil.config({
                URL_PREFIX: conf.hostPath
            })
        }]);
    }
})(angular.module('ng.poler.plugins.pc'),window);
/**
 * Created by Administrator on 2015/11/10 0010.
 */
(function(app) {
    app.factory('ChoosePublicMethod', [function() {
        return {
            /**
             * 动态设置弹窗title
             * @param params  Array
             */
            _bindChooseWhat: function(params) {
                var obj = {},
                    defaultTitle= [
                        { what1: '部门'},
                        { what2: '人员'}
                    ];
                for(var i = 0; i < defaultTitle.length; i++) {
                    for (var k in defaultTitle[i]) {
                        obj[k] = defaultTitle[i][k];
                    }
                }
                if(params) {
                    for(var i=0; i < params.length; i++) {
                        for (var k in params[i]) {
                            obj[k] = params[i][k];
                        }
                    }
                }else {
                    return obj;
                }
                return obj;
            }
        }
    }]);
    app.factory('ChoosePersonService',['$http', '$q', 'ApiUtil','ChoosePublicMethod', function($http, $q, ApiUtil, ChoosePublicMethod){
        return {
            department: function (p) {
                return ApiUtil.get('PubApi/Apicp/Addressbook/ListDepartment', p);
            },
            member: function (p) {
                return ApiUtil.get('PubApi/Apicp/Addressbook/ListMember', p);
            },
            tag: function(p) {
                return ApiUtil.get('PubApi/Apicp/Clabel/List', p);
            },
            bindChooseWhat: function(p) {
                return ChoosePublicMethod._bindChooseWhat.call(null, p);
            }
        }
    }]);
})(angular.module('ng.poler.plugins.pc'));

/**
 * Created by Administrator on 2015/10/29 0029.
 */

(function (app) {
    /**
     * 选人组件控制器
     */
    app.controller('ChoosePersonCtrl',['$scope','ApiUtil','ChoosePersonService','app_config',function ($scope,ApiUtil,ChoosePersonService,app_config) {
        $scope.allDep = {};
        $scope.noMembers = false;
        var depTree = [];

        var getWhat = ChoosePersonService.bindChooseWhat($scope.chooseWhat);
        for(var k in getWhat) {
            $scope[k] = getWhat[k];
        }

        var init = function initFun(){
            $scope.departmentList = ChoosePersonService.department({}).then(function (data) {
                if(app_config.API.CHECK_SUCCESS(data)) {
                    depTree = $scope.departmentList = data.result.departments;
                    for(var index in data.result.departments) {
                        var dep = data.result.departments[index];
                        dep.noChild = false;
                        if(dep.id == 0) { // 全部人员做为单独部门处理
                            dep.noChild = true;
                        }
                        $scope.allDep[dep.id] = dep;
                    }
                    if($scope.departmentList.length>0) {
                        $scope.memberQuery.cd_id = $scope.departmentList[0].id;
                    }
                    refreshMember();
                }
            }, function (error) {
                console.log("选人组件.请求部分信息错误", error)
            });

            if(angular.isArray($scope.selectedList)) {
                $scope.selectedMembers = $scope.selectedList;
                for(var i in $scope.selectedMembers) {
                    $scope.selectedMembers[i].selected = true;
                }
            }
        };


        /**
         * 部分查询操作
         */
        $scope.depQuery = { };
        $scope.isSearchDep = false;
        // 显示隐藏子部门
        $scope.toggleDep = function (id) {
            // 已经加载过数据，不再加载
            var curDep = $scope.allDep[id];
            curDep.isOpen = !curDep.isOpen;

            // 检查是否需要请求
            if((curDep.childDep && curDep.childDep.length>0) || curDep.noChild) {
                return;
            }

            $scope.depQuery.cd_id = id;
            ChoosePersonService.department($scope.depQuery).then(function (data) {
                if(app_config.API.CHECK_SUCCESS(data)) {
                    var departments = data.result['departments'];
                    if(departments.length>0) {
                        for(var index in departments) {
                            var dep = departments[index];
                            dep.noChild = false;
                            $scope.allDep[dep.id] = dep;
                        }

                        curDep.childDep = departments;
                    } else {
                        curDep.noChild = true;
                    }
                }
            }, function (error) {
                console.log("选人组件.请求部分信息错误", error)
            });
        };
        // enter键搜索
        $scope.checkSearchDep = function (event) {
            if(!$scope.depQuery.keyword) {
                // 没有搜索内容
                return;
            }
            if(event.keyCode==13) { // 输入enter键
                ChoosePersonService.department($scope.depQuery).then(function (data) {
                    if(app_config.API.CHECK_SUCCESS(data)) {
                        $scope.departmentList = data.result.departments;
                        for(var i in $scope.departmentList) {
                            $scope.departmentList[i].noChild = true;
                        }
                    }
                    $scope.isSearchDep = true;
                }, function (error) {
                    console.log("选人组件.请求部分信息错误", error)
                });

            }
        };

        /**
         * 取消搜索部门
         */
        $scope.cancelSearchDep = function () {
            $scope.departmentList = depTree;
            $scope.depQuery.keyword = null;
            $scope.isSearchDep = false;
        };

        /**
         * 人员查询操作
         * @param dep
         */
        $scope.memberQuery = {
            page:1,
            limit:35
        };
        var refreshMember = function () {
            $scope.members = ChoosePersonService.member($scope.memberQuery).then(function (data) {
                $scope.members = data.result.members;
                $scope.hasMore = $scope.members.length<data.result.total;
                for(var index in data.result.members) {
                    for(var j in $scope.selectedMembers) {
                        if($scope.selectedMembers[j].m_uid == data.result.members[index].m_uid) {
                            data.result.members[index].selected = true;
                            break;
                        }
                    }
                }
                if($scope.members.length==0){
                    $scope.noMembers = true;
                } else {
                    $scope.noMembers = false;
                }
            }, function (error) {
                console.log("选人组件.请求人员信息错误", error)
            })
        };

        // 加载更多人员
        $scope.moreMembers = function () {
            $scope.memberQuery.page += 1;
            ChoosePersonService.member($scope.memberQuery).then(function (data) {
                for(var index in data.result.members) {
                    $scope.members.push(data.result.members[index]);
                    for(var j in $scope.selectedMembers) {
                        if($scope.selectedMembers[j].m_uid == data.result.members[index].m_uid) {
                            data.result.members[index].selected = true;
                            break;
                        }
                    }
                }
                $scope.hasMore = $scope.members.length<data.result.total;
            }, function (error) {
                console.log("选人组件.请求人员信息错误", error)
            })
        };
        // 弹击部分
        $scope.clickDepartment = function (dep_id) {
            $scope.memberQuery.page = 1;
            $scope.memberQuery.cd_id = dep_id;
            refreshMember();
        };
        //
        $scope.checkSearchMember = function (event) {
            if(event.keyCode==13) { // 输入enter键
                $scope.memberQuery.page = 1;
                refreshMember();
            }
        };
        $scope.cancelSearchMember = function () {
            $scope.memberQuery.page = 1;
            $scope.memberQuery.keyword = null;
            refreshMember();
        };
        /**
         * 选择人员操作
         */
        $scope.selectedMembers = [];
        $scope.fatherDom = $(".per-item-Wrap");
        $scope.delDomItem = $(".per-item-Wrap .sel-dpm-item");
        $scope.cumulationWidth = 0;
        $scope.initWidth = 65;

        /**
         * 增加宽度(内容宽度超出父容器宽度出现滚动条)
         */
        $scope.setContentWidth = function(width){

            var contentWidth = width;
            $scope.delDomItem.css({
                width:contentWidth + 10
            });
            if( contentWidth >= 465 ){
                $scope.fatherDom.css({
                    width : 465
                });
            }
            if( contentWidth < 465 ){
                $scope.fatherDom.css({
                    width : contentWidth + 10
                });
            }

        }

        if($scope.selectedList){
            $scope.cumulationWidth = $scope.selectedList.length * $scope.initWidth;
            $scope.setContentWidth($scope.cumulationWidth);
        }

        $scope.clickMembers = function (index) {
            var sm = $scope.members[index];
            sm.selected = !sm.selected;
            if(sm.selected) { //
                if($scope.singleSelect) {
                    $scope.selectedMembers.forEach(function (obj) {
                        obj.selected = false;
                    });
                    $scope.selectedMembers.length = 0;
                }
                $scope.selectedMembers.push(sm);
            } else {  // 取消
                for(var i in $scope.selectedMembers) {
                    if(sm.m_uid==$scope.selectedMembers[i].m_uid) {
                        $scope.selectedMembers.splice(i, 1);
                        break;
                    }
                }
            }
            $scope.cumulationWidth = $scope.initWidth * $scope.selectedMembers.length;
            $scope.setContentWidth($scope.cumulationWidth);
        };
        $scope.deleteMember = function (index) {
            $scope.selectedMembers[index].selected = false;
            $scope.cumulationWidth -= $scope.initWidth;
            $scope.setContentWidth($scope.cumulationWidth);
            if($scope.members) {
                for(var j in $scope.members) {
                    if($scope.members[j].m_uid == $scope.selectedMembers[index].m_uid) {
                        $scope.members[j].selected = false;
                        break;
                    }
                }
            }

            $scope.selectedMembers.splice(index, 1);
        };
        /**
         * 判断是否选择人员
         * @param member
         * @returns {boolean}
         */
        function isSelected(member) {
            var len = $scope.selectedMembers.length;
            for(var i=0; i<len; i++) {
                var m = $scope.selectedMembers[i];
                if(m.m_uid == member.m_uid) {
                    return true;
                }
            }
            return false;
        }
        /**
         * 全部选择
         */
        $scope.selectAll = function () {
            //$scope.selectedMembers = [];
            for(var index in $scope.members) {
                $scope.members[index].selected = true;
                if(!isSelected($scope.members[index])) {
                    $scope.selectedMembers.push($scope.members[index]);
                }
            }
            $scope.cumulationWidth = $scope.selectedMembers.length * $scope.initWidth;
            $scope.setContentWidth($scope.cumulationWidth);
        };
        /**
         * 全部取消
         */
        $scope.cancelAll = function () {
            $scope.selectedMembers = [];
            $scope.cumulationWidth = 0;
            $scope.setContentWidth($scope.cumulationWidth);
            for(var index in $scope.members) {
                $scope.members[index].selected = false;
            }
        };
        /**
         * 反选
         */
        $scope.selectReverse = function () {
            $scope.selectedMembers = [];
            for(var index in $scope.members) {
                $scope.members[index].selected = !$scope.members[index].selected;
                if($scope.members[index].selected) {
                    $scope.selectedMembers.push($scope.members[index]);
                }
            }
            if($scope.selectedMembers.length){
                $scope.cumulationWidth = $scope.selectedMembers.length * $scope.initWidth;
            }else{
                $scope.cumulationWidth = 0;
            }
            $scope.setContentWidth($scope.cumulationWidth);
        };

        /**
         * 确认选择
         * @constructor
         */
        $scope.OK = function () {
            $scope.doOk($scope.selectedMembers);
        };

        init();
    }]);
    /**
     * 选人组件对外服务
     */
    app.factory('PersonChooser', ['$q','$modal',function ($q,$modal) {
        return {
            choose: function (selected,size,params) {
                var defer = $q.defer();
                var modalInstance = $modal.open({
                    templateUrl: 'templates/choose-person.html',
                    size: size || 'lg',
                    resolve: {
                        selected: function () {
                            return selected?JSON.parse(JSON.stringify(selected)):null;
                        },
                        params: function () {
                            return params || {};
                        }
                    },
                    controller:['$scope', '$modalInstance','selected','params',function ($scope, $modalInstance, selected,params) {
                        for(var k in params) {
                            $scope[k] = params[k];
                        }
                        $scope.selectedList = selected;

                        $scope.doOk = function (res) {
                            $modalInstance.close(res);
                        };
                        $scope.doCancel = function (res) {
                            $modalInstance.dismiss(res || 'cancel');
                        };
                    }]
                });

                modalInstance.opened.then(function(){//模态窗口打开之后执行的函数
                    //console.log('modal is opened');
                });
                modalInstance.result.then(function (result) {
                    defer.resolve(result);
                }, function (reason) {
                    defer.reject(reason);
                });

                return { result:defer.promise };
            }
        };
    }]);

})(angular.module('ng.poler.plugins.pc'));
/**
 * Created by Administrator on 2015/10/29 0029.
 */

(function (app) {

    /**
     * 选部门组件控制器
     */
    app.controller('ChooseDepartCtrl',['$scope','ApiUtil','ChoosePersonService','app_config',
        function($scope, ApiUtil, ChoosePersonService, app_config){

            var depTree = null;
            $scope.allDep = {};
            $scope.depQuery = {};

            var getWhat = ChoosePersonService.bindChooseWhat($scope.chooseWhat);
            for(var k in getWhat) {
                $scope[k] = getWhat[k];
            }

            /**
             * 判断id是否已经选择
             * @param id
             */
            function checkInitSelected(id) {
                for(var i=0; i<$scope.selectedList.length; i++){
                    if($scope.selectedList[i].id==id) {
                        return true;
                    }
                }
                return false;
            }

            /**
             * 用户取消选择时，情况已选择列表
             * @param id
             */
            function cleanInitSelected(id) {
                var i=0;
                for(; i<$scope.selectedList.length; i++){
                    if($scope.selectedList[i].id==id) {
                        break;
                    }
                }
                if(i<$scope.selectedList.length) {
                    $scope.selectedList.splice(i,1);
                }
            }

            /**
             * 初始化遍历部门list
             */
            var init = function initFun(){
                $scope.departmentList = ChoosePersonService.department({}).then(function (data) {
                    if(app_config.API.CHECK_SUCCESS(data)) {
                        depTree = $scope.departmentList = data.result.departments;
                        for(var index in data.result.departments) {
                            var dep = data.result.departments[index];
                            dep.isChecked = checkInitSelected(dep.id);
                            if(dep.id == 0) { // 全部人员做为单独部门处理
                                dep.noChild = true;
                            }
                            $scope.allDep[dep.id] = dep;
                        }
                    }
                }, function (error) {
                    console.log("选人组件.请求部门信息错误", error)
                });

            };


            /**
             * 部分查询操作
             */
            $scope.depQuery = { };
            $scope.isSearchDep = false;
            // 显示隐藏子部门
            $scope.toggleDep = function (id) {

                // 已经加载过数据，不再加载
                $scope.allDep[id].isOpen = !$scope.allDep[id].isOpen;
                if(($scope.allDep[id].childDep && $scope.allDep[id].childDep.length>0) || $scope.allDep[id].noChild) {
                    return;
                }

                $scope.depQuery.cd_id = id;
                $scope.allDep[id]['number'] = 0;
                ChoosePersonService.department($scope.depQuery).then(function (data) {
                    if(app_config.API.CHECK_SUCCESS(data)) {
                        if(data.result.departments.length>0) {
                            for(var index in data.result.departments) {
                                var dep = data.result.departments[index];
                                var have = $scope.allDep.hasOwnProperty(dep.id);
                                dep['parentDep_id'] = id;
                                dep['parent_id'] = id;
                                dep['isChecked'] = checkInitSelected(dep.id)  // 用户之前已经选择
                                    || $scope.allDep[id]['isChecked']         // 上级部门选中状态
                                    || (have && $scope.allDep[dep.id].isChecked); // 用户通过搜索过后的选中状态

                                $scope.allDep[dep.id] = dep;
                            }
                            $scope.allDep[id].childDep = data.result.departments;

                            if( $scope.allDep[id]['isChecked'] ){
                                var get_Child_len =  $scope.allDep[id].childDep.length;
                                $scope.allDep[id]['number'] = get_Child_len;
                            }

                        } else {
                            $scope.allDep[id].noChild = true;
                        }
                    }
                }, function (error) {
                    console.log("选人组件.请求部分信息错误", error)
                });
            };


            /**
             * 子部门 递归
             * @param node
             * @param isChecked
             */
            function childProcess(node, isChecked) {
                node['isChecked'] = isChecked;
                node.number = 0;
                if(node.childDep) {
                    for(var index in node.childDep) {
                        childProcess(node.childDep[index], isChecked);
                    }
                }
            }

            /**
             * 父部门递归
             * @param node
             * @param isChecked
             */
            function parentProcess(node, isChecked) {
                if (isChecked == true) {
                    node.number++;
                } else {
                    node.number--;
                }
                (node.number == node.childDep.length) ? node['isChecked'] = true : node['isChecked'] = false;
                if(node.parentDep_id) {
                    parentProcess($scope.allDep[node.parentDep_id], isChecked)
                }
            }

            /**
             * 点击部门，设置部门选择状态
             *
             * @param id
             */
            $scope.selectDepartment = function (id) {
                var dep = $scope.allDep[id];

                dep['isChecked'] = !dep.isChecked;
                if(!dep.hasOwnProperty('number')){
                    dep['number'] = 0;
                }

                if(!dep['isChecked']) {
                    cleanInitSelected(dep.id);
                }

                // 处理搜索操作时的对象
                if($scope.isSearchDep) {
                    for(var index=0;index<$scope.departmentList.length; index++) {
                        if($scope.departmentList[index].id == id) {
                            $scope.departmentList[index].isChecked = !$scope.departmentList[index].isChecked
                        } /*单选处理*/else if($scope.singleSelect) {
                            $scope.departmentList[index].isChecked = false;
                        }
                    }
                }

                /* 单选处理：清楚其它选择项 */
                if($scope.singleSelect && dep.isChecked) {
                    for(var k in $scope.allDep) {
                        if(k!=id) {
                            $scope.allDep[k].isChecked = false;
                            //$scope.allDep[k] = false;
                        }
                    }
                }

                if(dep.childDep && !$scope.isSearchDep)  {
                    // 递归子部门
                    for(var index in dep.childDep) {
                        childProcess(dep.childDep[index], dep['isChecked']);
                    }
                }
                // TODO [现在不要操作上级部门] 递归子部门
                //if(dep.parentDep_id) {
                //    parentProcess($scope.allDep[dep.parentDep_id], dep['isChecked']);
                //}

            };

            /*
             * 计算选中的部门
             */
            function computeSelectedDepartment(tree) {

                var list = [];

                for(var key in tree) {
                    var dep = tree[key];
                    if(dep['isChecked']) {
                        list.push(dep);
                    } /*else TODO 返回所有部门，现在需求是：选择部门并不一定会选中下面的子部门  if(dep.childDep) {
                        var childList = computeSelectedDepartment(dep.childDep);
                        childList.forEach(function (item) {
                            list.push(item);
                        })
                    }
                     */
                }

                return list;
            }

            /**
             * 确认选择
             * @constructor
             */
            $scope.OK = function () {
                $scope.doOk(computeSelectedDepartment($scope.allDep));
            };

            /**
             * 对于用户确定部门选择完成操作，需要在所有部门【$scope.allDep】中遍历查找
             * @param event
             */
            $scope.checkSearchDep = function (event) {
                if(!$scope.depQuery.keyword) {
                    // 没有搜索内容
                    return;
                }
                $scope.depQuery.cd_id = null;
                if(event.keyCode==13) { // 输入enter键
                    ChoosePersonService.department($scope.depQuery).then(function (data) {
                        if(app_config.API.CHECK_SUCCESS(data)) {
                            $scope.departmentList = data.result.departments;
                            for(var i in $scope.departmentList) {
                                var dep = $scope.departmentList[i];
                                var have = $scope.allDep.hasOwnProperty(dep.id);
                                dep.noChild = true;
                                dep.isChecked = checkInitSelected(dep.id) ||
                                    (have && $scope.allDep[dep.id].isChecked);
                                // 新对象保存到缓存中
                                if(!have) {
                                    $scope.allDep[dep.id] = JSON.parse(JSON.stringify(dep));
                                }
                            }
                        }
                        $scope.isSearchDep = true;
                    }, function (error) {
                        console.log("选人组件.请求部分信息错误", error)
                    });

                }
            };

            /**
             * 取消搜索部门
             */
            $scope.cancelSearchDep = function () {
                $scope.departmentList = depTree;
                $scope.depQuery.keyword = null;
                $scope.isSearchDep = false;
            };

            init();

        }]);
    /**
     * 选部门组件对外服务
     */
    app.factory('DepartmentChooser', ['$q','$modal',function ($q,$modal) {
        return {
            choose: function (selected,size,params) {
                var defer = $q.defer();
                var modalInstance = $modal.open({
                    templateUrl: 'templates/choose-department.html',
                    size: size || 'lg',
                    resolve: {
                        selected: function () {
                            return selected?JSON.parse(JSON.stringify(selected)):null;
                        },
                        params: function () {
                            return params || {};
                        }
                    },
                    controller:['$scope', '$modalInstance','selected','params',function ($scope, $modalInstance, selected,params) {
                        for(var k in params) {
                            $scope[k] = params[k];
                        }

                        $scope.selectedList = selected;

                        $scope.doOk = function (res) {
                            $modalInstance.close(res);
                        };
                        $scope.doCancel = function (res) {
                            $modalInstance.dismiss(res || 'cancel');
                        };
                    }]
                });

                modalInstance.opened.then(function(){//模态窗口打开之后执行的函数
                    //console.log('modal is opened');
                });
                modalInstance.result.then(function (result) {
                    defer.resolve(result);
                }, function (reason) {
                    defer.reject(reason);
                });

                return { result:defer.promise };
            }
        };
    }]);

})(angular.module('ng.poler.plugins.pc'));
/**
 * Created by Administrator on 2016/3/3 0003.
 */

(function (app) {

    app.controller('AdvancedChooseCtrl',['$scope', '$timeout', 'ChoosePersonService', 'app_config', 'TagsService',
        function($scope, $timeout, ChoosePersonService, app_config, TagsService) {

            var params = $scope.params;
            /**
             * 默认赋值
             */

            (function(){

                var temp_params = {
                    person: {
                        isShow: (params.person && params.person.isShow) || false,
                        isSingle: (params.person && params.person.isSingle) || false,
                        isPerson: (params.person && params.person.isPerson) || false
                    },
                    department: {
                        isShow: (params.department && params.department.isShow) || false,
                        isSingle: (params.department && params.department.isSingle) || false,
                        isDepartment: (params.department && params.department.isDepartment) || false
                    },
                    tag: {
                        isShow: (params.tag && params.tag.isShow) || false,
                        isSingle: (params.tag && params.tag.isSingle) || false,
                        isTags: (params.tag && params.tag.isTags) || false
                    }
                };
                $scope.params = temp_params;
            })();

            $scope.setShowWhat = {
                person: {
                    isShow: $scope.params.person.isShow
                },
                department: {
                    isShow: $scope.params.department.isShow
                },
                tag: {
                    isShow: $scope.params.tag.isShow
                }
            };

            /**
             * TAB选项切换
             * @param index
             */
            $scope.tabChange = function(index) {
                if( index == 1) {
                    $scope.setShowWhat.person.isShow = true;
                    $scope.setShowWhat.department.isShow = false;
                    $scope.setShowWhat.tag.isShow = false;
                }
                else if( index == 2 ) {
                    $scope.setShowWhat.department.isShow = false;
                    $scope.setShowWhat.person.isShow = false;
                    $scope.setShowWhat.tag.isShow = true;
                }
                else if( index == 3 ) {
                    $scope.setShowWhat.person.isShow = false;
                    $scope.setShowWhat.department.isShow = true;
                    $scope.setShowWhat.tag.isShow = false;
                }
            };


           /***********************************************选人**************************************************/
           var person_module = function(){

               $scope.allDepWithPerson = {};
               $scope.noMembers = false;
               $scope.depQuery = { };
               $scope.isSearchDep = false;
               $scope.selectedMembers = [];
               $scope.memberQuery = {
                   page:1,
                   limit:35
               };
               var depTree = [];


               /**
                *  初始化
                */
               var init = function initFun(){
                   $scope.departmentListWithPerson = ChoosePersonService.department({}).then(function (data) {
                       if(app_config.API.CHECK_SUCCESS(data)) {
                           depTree = $scope.departmentListWithPerson = data.result.departments;
                           for(var index in data.result.departments) {
                               var dep = data.result.departments[index];
                               dep.noChild = false;
                               if(dep.id == 0) { // 全部人员做为单独部门处理
                                   dep.noChild = true;
                               }
                               $scope.allDepWithPerson[dep.id] = dep;
                           }
                           if($scope.departmentListWithPerson.length>0) {
                               $scope.memberQuery.cd_id = $scope.departmentListWithPerson[0].id;
                           }
                           refreshMember();
                       }
                   }, function (error) {
                       console.log("选人组件.请求部分信息错误", error)
                   });
                   if(angular.isArray($scope.selectedList.selectedPersons)) {
                       $scope.selectedMembers = $scope.selectedList.selectedPersons;
                       for(var i in $scope.selectedMembers) {
                           $scope.selectedMembers[i].selected = true;
                       }
                   }
               };

               /**
                * 显示隐藏子部门
                * @param id
                */
               $scope.toggleDepWithPerson = function (id) {
                   // 已经加载过数据，不再加载
                   var curDep = $scope.allDepWithPerson[id];
                   curDep.isOpen = !curDep.isOpen;

                   // 检查是否需要请求
                   if((curDep.childDep && curDep.childDep.length>0) || curDep.noChild) {
                       return;
                   }

                   $scope.depQuery.cd_id = id;
                   ChoosePersonService.department($scope.depQuery).then(function (data) {
                       if(app_config.API.CHECK_SUCCESS(data)) {
                           var departments = data.result['departments'];
                           if(departments.length>0) {
                               for(var index in departments) {
                                   var dep = departments[index];
                                   dep.noChild = false;
                                   $scope.allDepWithPerson[dep.id] = dep;
                               }

                               curDep.childDep = departments;
                           } else {
                               curDep.noChild = true;
                           }
                       }
                   }, function (error) {
                       console.log("选人组件.请求部分信息错误", error)
                   });
               };

               /**
                * enter键搜索
                * @param event
                */
               $scope.checkSearchDep = function (event) {
                   if(!$scope.depQuery.keyword) {
                       // 没有搜索内容
                       return;
                   }
                   if(event.keyCode==13) { // 输入enter键
                       ChoosePersonService.department($scope.depQuery).then(function (data) {
                           if(app_config.API.CHECK_SUCCESS(data)) {
                               $scope.departmentListWithPerson = data.result.departments;
                               for(var i in $scope.departmentListWithPerson) {
                                   $scope.departmentListWithPerson[i].noChild = true;
                               }
                           }
                           $scope.isSearchDep = true;
                       }, function (error) {
                           console.log("选人组件.请求部分信息错误", error)
                       });

                   }
               };


               /**
                * 取消搜索部门
                */
               $scope.cancelSearchDep = function () {
                   $scope.departmentListWithPerson = depTree;
                   $scope.depQuery.keyword = null;
                   $scope.isSearchDep = false;
               };

               var refreshMember = function () {
                   $scope.members = ChoosePersonService.member($scope.memberQuery).then(function (data) {
                       $scope.members = data.result.members;
                       $scope.hasMore = $scope.members.length<data.result.total;
                       for(var index in data.result.members) {
                           for(var j in $scope.selectedMembers) {
                               if($scope.selectedMembers[j].m_uid == data.result.members[index].m_uid) {
                                   data.result.members[index].selected = true;
                                   break;
                               }
                           }
                       }
                       if($scope.members.length==0){
                           $scope.noMembers = true;
                       } else {
                           $scope.noMembers = false;
                       }
                   }, function (error) {
                       console.log("选人组件.请求人员信息错误", error)
                   })
               };

               /**
                * 加载更多人员
                */
               $scope.moreMembers = function () {
                   $scope.memberQuery.page += 1;
                   ChoosePersonService.member($scope.memberQuery).then(function (data) {
                       $scope.members.total = data.result.total;
                       for(var index in data.result.members) {
                           $scope.members.push(data.result.members[index]);
                           for(var j in $scope.selectedMembers) {
                               if($scope.selectedMembers[j].m_uid == data.result.members[index].m_uid) {
                                   data.result.members[index].selected = true;
                                   break;
                               }
                           }
                       }
                       $scope.hasMore = $scope.members.length<data.result.total;
                   }, function (error) {
                       console.log("选人组件.请求人员信息错误", error)
                   })
               };

               /**
                * 弹击部分
                */
               $scope.clickDepartment = function (dep_id) {
                   $scope.memberQuery.page = 1;
                   $scope.memberQuery.cd_id = dep_id;
                   refreshMember();
               };

               $scope.checkSearchMember = function (event) {
                   if(event.keyCode==13) { // 输入enter键
                       $scope.memberQuery.page = 1;
                       refreshMember();
                   }
               };
               $scope.cancelSearchMember = function () {
                   $scope.memberQuery.page = 1;
                   $scope.memberQuery.keyword = null;
                   refreshMember();
               };



                $timeout(function() {
                    /**
                     * 选择人员操作
                     */
                    $scope.fatherDom = $(".per-item-Wrap");
                    $scope.delDomItem = $(".per-item-Wrap .sel-dpm-item");
                    $scope.cumulationWidth = 0;
                    $scope.initWidth = 65;


                    /**
                     * 增加宽度(内容宽度超出父容器宽度出现滚动条)
                     */
                    $scope.setContentWidth = function(width){

                        var contentWidth = width;
                        $scope.delDomItem.css({
                            width:contentWidth + 10
                        });
                        if( contentWidth >= 465 ){
                            $scope.fatherDom.css({
                                width : 465
                            });
                        }
                        if( contentWidth < 465 ){
                            $scope.fatherDom.css({
                                width : contentWidth + 10
                            });
                        }

                    };

                    if($scope.selectedList.selectedPersons){
                        $scope.cumulationWidth = $scope.selectedList.selectedPersons.length * $scope.initWidth;
                        $scope.setContentWidth($scope.cumulationWidth);
                    }

                    $scope.clickMembers = function (index) {
                        var sm = $scope.members[index];
                        sm.selected = !sm.selected;
                        if(sm.selected) { //
                            if($scope.setShowWhat.person.isSingle) {
                                $scope.selectedMembers.forEach(function (obj) {
                                    obj.selected = false;
                                });
                                $scope.selectedMembers.length = 0;
                            }
                            $scope.selectedMembers.push(sm);
                        } else {  // 取消
                            for(var i in $scope.selectedMembers) {
                                if(sm.m_uid==$scope.selectedMembers[i].m_uid) {
                                    $scope.selectedMembers.splice(i, 1);
                                    break;
                                }
                            }
                        }
                        $scope.cumulationWidth = $scope.initWidth * $scope.selectedMembers.length;
                        $scope.setContentWidth($scope.cumulationWidth);
                    };
                    $scope.deleteMember = function (index) {
                        $scope.selectedMembers[index].selected = false;
                        $scope.cumulationWidth -= $scope.initWidth;
                        $scope.setContentWidth($scope.cumulationWidth);
                        if($scope.members) {
                            for(var j in $scope.members) {
                                if($scope.members[j].m_uid == $scope.selectedMembers[index].m_uid) {
                                    $scope.members[j].selected = false;
                                    break;
                                }
                            }
                        }

                        $scope.selectedMembers.splice(index, 1);
                    };
                    /**
                     * 判断是否选择人员
                     * @param member
                     * @returns {boolean}
                     */
                    function isSelected(member) {
                        var len = $scope.selectedMembers.length;
                        for(var i=0; i<len; i++) {
                            var m = $scope.selectedMembers[i];
                            if(m.m_uid == member.m_uid) {
                                return true;
                            }
                        }
                        return false;
                    }
                    /**
                     * 全部选择
                     */
                    $scope.selectAll = function () {
                        //$scope.selectedMembers = [];
                        for(var index in $scope.members) {
                            $scope.members[index].selected = true;
                            if(!isSelected($scope.members[index])) {
                                $scope.selectedMembers.push($scope.members[index]);
                            }
                        }
                        $scope.cumulationWidth = $scope.selectedMembers.length * $scope.initWidth;
                        $scope.setContentWidth($scope.cumulationWidth);
                    };
                    /**
                     * 全部取消
                     */
                    $scope.cancelAll = function () {
                        $scope.selectedMembers = [];
                        $scope.cumulationWidth = 0;
                        $scope.setContentWidth($scope.cumulationWidth);
                        for(var index in $scope.members) {
                            $scope.members[index].selected = false;
                        }
                    };
                    /**
                     * 反选
                     */
                    $scope.selectReverse = function () {
                        $scope.selectedMembers = [];
                        for(var index in $scope.members) {
                            $scope.members[index].selected = !$scope.members[index].selected;
                            if($scope.members[index].selected) {
                                $scope.selectedMembers.push($scope.members[index]);
                            }
                        }
                        if($scope.selectedMembers.length){
                            $scope.cumulationWidth = $scope.selectedMembers.length * $scope.initWidth;
                        }else{
                            $scope.cumulationWidth = 0;
                        }
                        $scope.setContentWidth($scope.cumulationWidth);
                    };


                });

               init();

           };

            /***********************************************选人**************************************************/

            /***********************************************部门**************************************************/
            var department_module = function(){
                var depTree = null;
                $scope.allDep = {};
                $scope.depQuery = {};

                /**
                 * 判断id是否已经选择
                 * @param id
                 */
                function checkInitSelected(id) {
                    for(var i=0; i<$scope.selectedList.selectedDepartment.length; i++){
                        if($scope.selectedList.selectedDepartment[i].id==id) {
                            return true;
                        }
                    }
                    return false;
                }

                /**
                 * 用户取消选择时，情况已选择列表
                 * @param id
                 */
                function cleanInitSelected(id) {
                    var i=0;
                    for(; i<$scope.selectedList.selectedDepartment.length; i++){
                        if($scope.selectedList.selectedDepartment[i].id==id) {
                            break;
                        }
                    }
                    if(i<$scope.selectedList.selectedDepartment.length) {
                        $scope.selectedList.selectedDepartment.splice(i,1);
                    }
                }

                /**
                 * 初始化遍历部门list
                 */
                var init = function initFun(){
                    $scope.departmentList = ChoosePersonService.department({}).then(function (data) {
                        if(app_config.API.CHECK_SUCCESS(data)) {
                            depTree = $scope.departmentList = data.result.departments;
                            for(var index in data.result.departments) {
                                var dep = data.result.departments[index];
                                dep.isChecked = checkInitSelected(dep.id);
                                if(dep.id == 0) { // 全部人员做为单独部门处理
                                    dep.noChild = true;
                                }
                                $scope.allDep[dep.id] = dep;
                            }
                        }
                    }, function (error) {
                        console.log("选人组件.请求部门信息错误", error)
                    });

                };


                /**
                 * 部分查询操作
                 */
                $scope.depQuery = { };
                $scope.isSearchDep = false;
                // 显示隐藏子部门
                $scope.toggleDep = function (id) {

                    // 已经加载过数据，不再加载
                    $scope.allDep[id].isOpen = !$scope.allDep[id].isOpen;
                    if(($scope.allDep[id].childDep && $scope.allDep[id].childDep.length>0) || $scope.allDep[id].noChild) {
                        return;
                    }

                    $scope.depQuery.cd_id = id;
                    $scope.allDep[id]['number'] = 0;
                    ChoosePersonService.department($scope.depQuery).then(function (data) {
                        if(app_config.API.CHECK_SUCCESS(data)) {
                            if(data.result.departments.length>0) {
                                for(var index in data.result.departments) {
                                    var dep = data.result.departments[index];
                                    var have = $scope.allDep.hasOwnProperty(dep.id);
                                    dep['parentDep_id'] = id;
                                    dep['parent_id'] = id;
                                    dep['isChecked'] = checkInitSelected(dep.id)  // 用户之前已经选择
                                        || $scope.allDep[id]['isChecked']         // 上级部门选中状态
                                        || (have && $scope.allDep[dep.id].isChecked); // 用户通过搜索过后的选中状态

                                    $scope.allDep[dep.id] = dep;
                                }
                                $scope.allDep[id].childDep = data.result.departments;

                                if( $scope.allDep[id]['isChecked'] ){
                                    var get_Child_len =  $scope.allDep[id].childDep.length;
                                    $scope.allDep[id]['number'] = get_Child_len;
                                }

                            } else {
                                $scope.allDep[id].noChild = true;
                            }
                        }
                    }, function (error) {
                        console.log("选人组件.请求部分信息错误", error)
                    });
                };


                /**
                 * 子部门 递归
                 * @param node
                 * @param isChecked
                 */
                function childProcess(node, isChecked) {
                    node['isChecked'] = isChecked;
                    node.number = 0;
                    if(node.childDep) {
                        for(var index in node.childDep) {
                            childProcess(node.childDep[index], isChecked);
                        }
                    }
                }

                /**
                 * 父部门递归
                 * @param node
                 * @param isChecked
                 */
                function parentProcess(node, isChecked) {
                    if (isChecked == true) {
                        node.number++;
                    } else {
                        node.number--;
                    }
                    (node.number == node.childDep.length) ? node['isChecked'] = true : node['isChecked'] = false;
                    if(node.parentDep_id) {
                        parentProcess($scope.allDep[node.parentDep_id], isChecked)
                    }
                }

                /**
                 * 点击部门，设置部门选择状态
                 *
                 * @param id
                 */
                $scope.selectDepartment = function (id) {
                    var dep = $scope.allDep[id];

                    dep['isChecked'] = !dep.isChecked;
                    if(!dep.hasOwnProperty('number')){
                        dep['number'] = 0;
                    }

                    if(!dep['isChecked']) {
                        cleanInitSelected(dep.id);
                    }

                    // 处理搜索操作时的对象
                    if($scope.isSearchDep) {
                        for(var index=0;index<$scope.departmentList.length; index++) {
                            if($scope.departmentList[index].id == id) {
                                $scope.departmentList[index].isChecked = !$scope.departmentList[index].isChecked
                            } /*单选处理*/else if($scope.setShowWhat.department.isSingle) {
                                $scope.departmentList[index].isChecked = false;
                            }
                        }
                    }

                    /* 单选处理：清楚其它选择项 */
                    if($scope.setShowWhat.department.isSingle && dep.isChecked) {
                        for(var k in $scope.allDep) {
                            if(k!=id) {
                                $scope.allDep[k].isChecked = false;
                                //$scope.allDep[k] = false;
                            }
                        }
                    }

                    if(dep.childDep && !$scope.isSearchDep)  {
                        // 递归子部门
                        for(var index in dep.childDep) {
                            childProcess(dep.childDep[index], dep['isChecked']);
                        }
                    }
                    // TODO [现在不要操作上级部门] 递归子部门
                    //if(dep.parentDep_id) {
                    //    parentProcess($scope.allDep[dep.parentDep_id], dep['isChecked']);
                    //}

                };

                /*
                 * 计算选中的部门
                 */
                $scope.computeSelectedDepartment = function(tree) {

                    var list = [];

                    for(var key in tree) {
                        var dep = tree[key];
                        if(dep['isChecked']) {
                            list.push(dep);
                        } /*else TODO 返回所有部门，现在需求是：选择部门并不一定会选中下面的子部门  if(dep.childDep) {
                         var childList = computeSelectedDepartment(dep.childDep);
                         childList.forEach(function (item) {
                         list.push(item);
                         })
                         }
                         */
                    }

                    return list;
                };

                /**
                 * 对于用户确定部门选择完成操作，需要在所有部门【$scope.allDep】中遍历查找
                 * @param event
                 */
                $scope.checkSearchDep = function (event) {
                    if(!$scope.depQuery.keyword) {
                        // 没有搜索内容
                        return;
                    }
                    $scope.depQuery.cd_id = null;
                    if(event.keyCode==13) { // 输入enter键
                        ChoosePersonService.department($scope.depQuery).then(function (data) {
                            if(app_config.API.CHECK_SUCCESS(data)) {
                                $scope.departmentList = data.result.departments;
                                for(var i in $scope.departmentList) {
                                    var dep = $scope.departmentList[i];
                                    var have = $scope.allDep.hasOwnProperty(dep.id);
                                    dep.noChild = true;
                                    dep.isChecked = checkInitSelected(dep.id) ||
                                        (have && $scope.allDep[dep.id].isChecked);
                                    // 新对象保存到缓存中
                                    if(!have) {
                                        $scope.allDep[dep.id] = JSON.parse(JSON.stringify(dep));
                                    }
                                }
                            }
                            $scope.isSearchDep = true;
                        }, function (error) {
                            console.log("选人组件.请求部分信息错误", error)
                        });

                    }
                };

                /**
                 * 取消搜索部门
                 */
                $scope.cancelSearchDep = function () {
                    $scope.departmentList = depTree;
                    $scope.depQuery.keyword = null;
                    $scope.isSearchDep = false;
                };

                init();
            };
            /***********************************************部门**************************************************/

            /***********************************************标签**************************************************/
            var tag_module = function() {

                var tagsQuery = {
                    page:1,
                    limit:10
                };
                var total = 0;

                $scope.tags =  ChoosePersonService.tag(tagsQuery).then(function(data) {
                    if(data.errcode == 0) {
                        $scope.tags = TagsService._tags = data.result.list;
                        $scope.hasMoreTags = TagsService.hasMore(data.result.count);
                        for(var index in data.result.list) {
                            for(var j in TagsService.checks) {
                                if(TagsService.checks[j].ccl_id == data.result.list[index].ccl_id) {
                                    data.result.list[index].selected = true;
                                    break;
                                }
                            }
                        }
                    }
                }, function() {
                });

                $scope.moreTags = function() {
                    tagsQuery.page += 1;
                    ChoosePersonService.tag(tagsQuery).then(function(data) {
                        total = data.result.total;
                        for(var index in data.result.list) {
                            TagsService._tags.push(data.result.list[index]);
                            $scope.tags.push(data.result.list[index]);
                        }
                        $scope.hasMoreTags = TagsService.hasMore(data.result.count);
                    });
                };

                /**
                 * 加载更多人员
                 */
               /* $scope.moreMembers = function () {
                    $scope.memberQuery.page += 1;
                    ChoosePersonService.member($scope.memberQuery).then(function (data) {
                        $scope.members.total = data.result.total;
                        for(var index in data.result.members) {
                            $scope.members.push(data.result.members[index]);
                            for(var j in $scope.selectedMembers) {
                                if($scope.selectedMembers[j].m_uid == data.result.members[index].m_uid) {
                                    data.result.members[index].selected = true;
                                    break;
                                }
                            }
                        }
                        $scope.hasMore = $scope.members.length<data.result.total;
                    }, function (error) {
                        console.log("选人组件.请求人员信息错误", error)
                    })
                };*/


                TagsService.init({
                    isSingle: $scope.params.tag.isSingle
                });

                $scope.chooseTag = TagsService.clickTag;
            };
            /***********************************************标签**************************************************/

            /**
             * 确认选择
             * @constructor
             */
            $scope.OK = function () {
                $scope.selectedList.selectedPersons = $scope.selectedMembers;
                $scope.selectedList.selectedDepartment =  $scope.computeSelectedDepartment($scope.allDep);
                $scope.selectedList.selectedTags = TagsService.checks;
                $scope.doOk($scope.selectedList);
            };


            (function (params) {
                if(params.hasOwnProperty('person')) {
                    person_module();
                }
                if(params.hasOwnProperty('department')) {
                    department_module();
                }
                if(params.hasOwnProperty('tag')) {
                    tag_module();
                }
            })(params);

    }]);

    app.service('TagsService',[function() {

        var tag = {
            isSingle: false,        //是否单选
            _tags : [],              //所有标签
            checks: []              //选中标签数据
        };

        tag.init = function(config) {
            for(var k in config) {
                tag[k] = config[k];
            }
        };

        tag.hasMore = function(total) {
          return tag._tags.length < total;
        };

        tag.clickTag = function(index) {
            var currentTag = tag._tags[index];
            currentTag.selected = !currentTag.selected;
            if(currentTag.selected) {
                if(tag.isSingle) {
                    tag.checks.forEach(function (obj) {
                        obj.selected = false;
                    });
                    tag.checks.length = 0;
                }
                tag.checks.push(currentTag);
            }
            else {
                for(var i in tag.checks) {
                    if(currentTag.id == tag.checks[i].id) {
                        tag.checks.splice(i, 1);
                        break;
                    }
                }
            }
        };
        return tag;

    }]);


    app.factory('AdvancedChooser', ['$q','$modal',function ($q,$modal) {
    return {
        choose: function (selected,size,params) {
            var defer = $q.defer();
            var modalInstance = $modal.open({
                templateUrl: 'templates/choose-advanced.html',
                size: size || 'lg',
                resolve: {
                    selected: function () {
                        //return selected?JSON.parse(JSON.stringify(selected)):null;
                        return selected;
                    },
                    params: function () {
                        return params || {};
                    }
                },
                controller:['$scope', '$modalInstance','selected','params',function ($scope, $modalInstance, selected,params) {

                    $scope.selectedList = selected;
                    $scope.params = params;

                    $scope.doOk = function (res) {
                        $modalInstance.close(res);
                    };
                    $scope.doCancel = function (res) {
                        $modalInstance.dismiss(res || 'cancel');
                    };
                }]
            });

            modalInstance.opened.then(function(){//模态窗口打开之后执行的函数
                //console.log('modal is opened');
            });
            modalInstance.result.then(function (result) {
                defer.resolve(result);
            }, function (reason) {
                defer.reject(reason);
            });

            return { result:defer.promise };
        }
    };
}]);

})(angular.module('ng.poler.plugins.pc'));
