/**
 * Created by three on 15/12/24.
 *
 * 分页组件
 * TODO 用户输入：总条数，当前页，总页数
 */
(function (app) {
    app.directive('polerPagination',[function () {
        return {
            restrict:'A',
            templateUrl:'/admincp/static/ng-modules/plugins/pagination/pagination-tpl.html',
            scope:{
                polerPaginationCtrl:'=polerPagination',
                asyncInit:"&",
                downloadData:'&download'
            },
            link: function ($scope, element, attrs) {

                /**
                 * 分页外部控制器接口
                 * @type {{restInfo: Function}}
                 */
                $scope.polerPaginationCtrl = {
                    /**
                     * 重新设置分页信息
                     * @param pageInfo
                     * {
                     *      total: <number>,   总条数
                     *      curPage: <number>, 当前页码
                     *      pages: <number>    总页数
                     * }
                     */
                    reset: function (pageInfo) {
                        restPagination(pageInfo)
                    },
                    /**
                     * 获取当前分页状态信息
                     * @returns {{total: *, curPage: (*|number), pages: *}}
                     */
                    paginationInfo: function () {
                        return {
                            total:$scope.totalNumber,
                            curPage: $scope.curPage,
                            pages: $scope.pages
                        }
                    }
                };

                /**
                 * 异步等待初始化
                 */
                if($scope.asyncInit()) {
                    $scope.asyncInit().then(function (pageInfo) {
                        restPagination(pageInfo);
                    });
                }


                /**
                 * 1、计算分页按钮数据
                 * @param pages 总页数
                 * @param curPage 当前页
                 * @returns {*}
                 */
                function computePaginationList(pages, curPage){
                    var paginationList = null;
                    // 最大页数>=7的时候
                    if(pages>=7) {

                        // 判断最小
                        if(curPage<=3) {
                            curPage = 4;
                        }
                        // 判断最大
                        if(curPage>=pages-2) {
                            curPage = pages-3;
                        }
                        paginationList = [
                            curPage-2,
                            curPage-1,
                            curPage,
                            curPage+1,
                            curPage+2
                        ];

                        if(paginationList[0]>2) {
                            paginationList[0] = -1;
                        }
                        if(paginationList[4]<pages-1) {
                            paginationList[4] = -2;
                        }
                    } else if(pages>1){
                        paginationList = [];
                        for(var pi=2; pi<pages; pi++) {
                            paginationList.push(pi);
                        }
                    }

                    return paginationList;
                }

                /**
                 * 设置分页数据，显示到页面
                 *
                 * @param pageInfo
                 * {
                 *      total: <number>,   总条数
                 *      curPage: <number>, 当前页码
                 *      pages: <number>    总页数
                 * }
                 */
                function restPagination(pageInfo){
                    if(pageInfo) {
                        $scope.totalNumber = pageInfo.total;
                        $scope.curPage = pageInfo.curPage;
                        $scope.pages = pageInfo.pages;
                        if($scope.curPage>$scope.pages) {
                            $scope.curPage = $scope.pages;
                        }
                    } else {
                        pageInfo = {
                            total:$scope.totalNumber,
                            curPage: $scope.curPage,
                            pages: $scope.pages
                        };
                    }

                    $scope.paginationList = computePaginationList(pageInfo.pages,$scope.curPage);
                }

                /**
                 * 通知加载页面数据，根据返回值重新计算分页组件
                 * @param page
                 */
                function loadPage(page) {

                    var result = $scope.downloadData({page: page});
                    if(result) {
                        result.then(function (pageInfo) {
                            pageInfo.curPage = $scope.curPage;

                            restPagination(pageInfo);
                        });
                    }

                }

                /***************** 响应用户操作 *********************/
                /**
                 * 上一页
                 */
                $scope.prevPage = function () {
                    $scope.curPage--;
                    if($scope.curPage<1) {
                        $scope.curPage=1;
                    }
                    restPagination();
                    loadPage($scope.curPage);
                };
                /**
                 * 下一页
                 */
                $scope.nextPage = function () {
                    $scope.curPage++;
                    if($scope.curPage>$scope.pages) {
                        $scope.curPage=$scope.pages;
                    }
                    restPagination();
                    loadPage($scope.curPage);
                };
                /**
                 * 跳转到指定页
                 * @param page
                 */
                $scope.goPage = function (page) {
                    if(page>0) {
                        $scope.curPage = page;
                        restPagination();
                        loadPage($scope.curPage);
                    }
                };
                /***************** 响应用户操作 *********************/
            }
        }
    }]);
})(angular.module('ng.poler.plugins.pagination',[]));