(function (app, window) {
    app.controller('listCtrl', ['$rootScope', '$scope', 'CampaignsApi', 'Page', function ($rootScope, $scope, CampaignsApi, Page) {
            $('#sub-navbar').find("h1").html("<i class=\"fa fa-list page-header-icon\"><\/i>&nbsp;&nbsp;活动列表");
            $scope.types = [];
            $scope.selectList = {};
            var hasSelected = [];
            bootbox.setDefaults("locale", "zh_CN");

            (function () {
                $scope.types = [{"title": "全部", "id": "0"}];
                if ($rootScope.campaignsEdit == undefined) {
                    $scope.classification = "0";
                    $scope.status = 2;
                    $rootScope.campaignsList = {};
                    fetchReportList({page: 1});
                } else {
                    $rootScope.campaignsList = $rootScope.campaignsEdit;
                    $scope.classification = $rootScope.campaignsList.classification == undefined ? "0" : $rootScope.campaignsList.status;
                    ;
                    $scope.status = $rootScope.campaignsList.status == undefined ? 2 : $rootScope.campaignsList.status;
                    $scope.name = $rootScope.campaignsList.subject;
                    $('#id_begin_time').val($rootScope.campaignsList.legalStartDate);
                    $('#id_end_time').val($rootScope.campaignsList.legalEndDate);

                    var params = {
                        page: 1,
                        start_date: isNaN(Date.parse(new Date($('#id_begin_time').val()))) ? "" : $('#id_begin_time').val(),
                        end_date: isNaN(Date.parse(new Date($('#id_end_time').val()))) ? "" : $('#id_end_time').val(),
                        typeid: $scope.classification,
                        subject: $scope.name || "",
                        status: $scope.status
                    }

                    fetchReportList(params);
                }
                getSettingList();
            })();

            function getSettingList() {
                CampaignsApi.CampaignSettingList().then(function (res) {
                    $scope.types = $scope.types.concat(res.result);
                })
            }


            $scope.getReportPage = function (page) {
                $scope.reportListData = [];
                $scope.reportQueryParams.page = page;
                fetchReportList($scope.reportQueryParams);
            };

            function fetchReportList(params) {
                $scope.reportQueryParams = params;
                CampaignsApi.list(params).then(function (data) {
                    if (data.errcode == 0) {
                        $scope.selectList = {};
                        $scope.chkall = false;
                        $scope.reportList = data.result.list;
                        $scope.resultCount = data.result.count;
                        // 分页
                        curPage = params.page;
                        $scope.polerPaginationCtrl.reset({
                            total: data.result.count,
                            pages: data.result.pages,
                            curPage: params.page
                        });
                    }
                    if (data.errcode > 0) {
                        alert(data.errmsg);
                    }
                }, function (error) {
                    console.log(error)
                })
            }

            $scope.search = function ($event) {
                if ($event.keyCode == 13 || $event.type == "click") {
                    var params = {
                        page: 1,
                        start_date: isNaN(Date.parse(new Date($('#id_begin_time').val()))) ? "" : $('#id_begin_time').val(),
                        end_date: isNaN(Date.parse(new Date($('#id_end_time').val()))) ? "" : $('#id_end_time').val(),
                        typeid: $scope.classification,
                        subject: $scope.name,
                        status: $scope.status
                    }

                    $rootScope.campaignsList['classification'] = $scope.classification;
                    $rootScope.campaignsList['status'] = $scope.status;
                    $rootScope.campaignsList['subject'] = $scope.name;
                    $rootScope.campaignsList['legalStartDate'] = isNaN(Date.parse(new Date($('#id_begin_time').val()))) ? "" : $('#id_begin_time').val();
                    $rootScope.campaignsList['legalEndDate'] = isNaN(Date.parse(new Date($('#id_end_time').val()))) ? "" : $('#id_end_time').val();

                    CampaignsApi.list(params).then(function (data) {
                        $scope.selectList = {};
                        $scope.chkall = false;
                        $scope.reportList = data.result.list;
                        $scope.resultCount = data.result.count;
                        curPage = params.page;
                        $scope.polerPaginationCtrl.reset({
                            total: data.result.count,
                            pages: data.result.pages,
                            curPage: params.page
                        });
                    })
                }
            }

            function getSelected() {
                hasSelected = [];
                for (var prop in $scope.selectList) {
                    if ($scope.selectList[prop] && $scope.selectList.hasOwnProperty(prop)) {
                        hasSelected.push(prop);
                    }
                }
                return hasSelected;
            }

            $scope.checkBoxAll = function () {
                for (var i in $scope.selectList) {
                    if ($scope.chkall == true) {
                        $scope.selectList[i] = true;
                    } else {
                        $scope.selectList[i] = false;
                    }
                }
            }
        $scope.delItem = function(id){
            bootbox.dialog({
                message: '是否删除活动？',
                title: '提示：',
                buttons: {
                    Cancel: {
                        label: '取消',
                        className: "btn-default"
                    },
                    OK: {
                        label: '删除',
                        className: "btn-primary",
                        callback: function() {
                            var ids = id==undefined ? getSelected().join(','):id;
                            CampaignsApi.dels({ids:ids}).then(function(data){
                                if(data.errcode == 0){
                                    fetchReportList($scope.reportQueryParams);
                                }
                            })

                            }
                        }
                    }
                });
            }
        }]);
})(angular.module('app.modules.campaignsList'), window);
