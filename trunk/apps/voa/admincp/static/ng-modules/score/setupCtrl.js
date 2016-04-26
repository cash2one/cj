/**
 * Created by Chan on 2016/4/12.
 */
(function(app) {
    app.controller("setupCtrl", ["$scope", "$timeout", "ScoreApi", function($scope, $timeout, ScoreApi) {
        var response = {
            open: "1",
            close: "0"
        },
        require = {
            open: 1,
            close: 0
        };

        $scope.isOpen = null;

        ScoreApi.getSwitch().then(function(data) {
            var result = null;

            if (data.errmsg !== "ok") {
                return;
            }

            result = data.result;

            if (result.score_config == 'true') {
                $scope.isOpen = true;
            }
        });

        $scope.isCloseSwitch = function(event) {
            if ($scope.isOpen === true) {
                requireSwitchStatus(require.open);
            }

            if ($scope.isOpen === false) {
                $scope.showTip();
            }
        };

        $scope.showTip = function() {
            var tipInfo = {
                message: "*关闭积分功能后将会使积分统计规则失效，但会保留之前的积分数值。",
                title: "关闭积分功能",
                cancelBtn: "暂不关闭",
                okBtn: "仍要关闭"
            };

            $scope.showDialog(tipInfo);
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
                            $timeout(function() {
                                $scope.isOpen = !$scope.isOpen;
                            });
                        }
                    },
                    OK: {
                        label: obj.okBtn,
                        className: "btn-primary",
                        callback: function() {
                            requireSwitchStatus(require.close);
                        }
                    }
                }
            });
        };

        function requireSwitchStatus(postStatus) {
            ScoreApi.changeSwitch({ switch: postStatus }).then(function(data) {
                if (data.errmsg === "ok") {
                    var result = data.result;

                    if (result.status === response.open) {
                        $scope.isOpen = true;
                        return;
                    }

                    $scope.isOpen = false;
                }
            }, function() {
                $scope.isOpen = !$scope.isOpen;
            });
        }
    }]);
})(angular.module("app.modules.setUp"));
