(function(app) {
    app.controller("exchangeApplicationCtrl", ["$rootScope","$scope", "$location", "ScoreApi", function($rootScope,$scope, $location, ScoreApi) {
        $scope.detaill = null;

        initList();

        $scope.isHideBtn = true;

        bootbox.setDefaults("locale", "zh_CN");

        $rootScope.scoreApplication = $rootScope.scoreAwardExchange;

        $scope.isSureExchange = function() {
            var tipInfo = {
                message: "确定兑换MacBook Pro这件商品吗？",
                title: "兑换确认",
                cancelBtn: "取消",
                okBtn: "确定"
            };

            $scope.showDialog(tipInfo);
        };

        $scope.fillReason = function() {
            var tipInfo = {
                title: "拒绝兑换",
                message: "message",
                cancelBtn: "取消",
                okBtn: "确定"
            };

            $scope.prompt(tipInfo);
        };

        $scope.prompt = function(obj) {
            bootbox.prompt({
                title: obj.title,
                message: obj.message,
                callback: function(result) {
                    if (result === null) {
                        chooseStatus(data.result.result);

                        return;
                    }

                    postReason(result);
                }
            });
        };

        $scope.showDialog = function(obj) {
            bootbox.dialog({
                message: obj.message,
                title: obj.title,
                buttons: {
                    Cancel: {
                        label: obj.cancelBtn,
                        className: "btn-default",
                        callback: function() {
                            $scope.isOpen = false;
                        }
                    },
                    OK: {
                        label: obj.okBtn,
                        className: "btn-primary",
                        callback: function() {
                            $scope.isOpen = true;

                            var params = {
                                order_id: sessionStorage.getItem("currentOrderId"),
                                status: 2,
                                reason: ""
                            };

                            ScoreApi.processAwardOrder(params).then(function(data) {
                                if (data.errcode === 0) {
                                    chooseStatus(2);

                                    return;
                                }

                                bootbox.alert("奖品兑换失败!");
                            }, function() {
                                bootbox.alert("奖品兑换失败!");
                            });
                        }
                    }
                }
            });
        };

        function initList() {
            var currentOrderId = $location.search().id;

            ScoreApi.getAwardDetaill({ order_id: currentOrderId }).then(function(data) {
                if (data.errmsg === "ok") {
                    $scope.detaill = data.result.order;

                    $scope.detaill.scr = parseInt($scope.detaill.score) / parseInt($scope.detaill.award_num);

                    chooseStatus(data.result.order.status);
                }
            }, function() {
                bootbox.alert("奖品兑换详情数据获取失败!");
            });
        }

        function chooseStatus(status) {
            status = parseInt(status);

            if (status === 1) {
                $scope.detaill.status = "受理中";

                $scope.isHideBtn = false;
            }

            if (status === 2) {
                $scope.detaill.status = "已同意";
                $scope.isHideBtn = true;
            }

            if (status === 3) {
                $scope.detaill.status = "已拒绝";
                $scope.isHideBtn = true;
            }
        }

        function postReason(reason) {
            var params = {
                order_id: sessionStorage.getItem("currentOrderId"),
                status: 3,
                reason: reason
            };

            ScoreApi.processAwardOrder(params).then(function(data) {
                if (data.errcode === 0) {
                    chooseStatus(3);
                    return;
                }

                bootbox.alert(data.errmsg);
            }, function() {
                bootbox.alert("拒绝理由提交失败!");
            });
        }
    }]);
})(angular.module("app.modules.awardExchange"));
