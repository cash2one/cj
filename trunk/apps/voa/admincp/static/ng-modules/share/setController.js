/**
 魏世超
 */

(function (app) {
    app.controller("shareSet", ["$scope", "ShareApi", function ($scope, ShareApi) {
            $scope.url = "";
            ShareApi.getUrl().then(function (data) {
                if (data.errcode == 0) {
                    $scope.url = data.result.url;
                }
            });
            var client = new ZeroClipboard(document.getElementById("copyurlx"));
            client.on("ready", function (readyEvent) {
                // alert( "ZeroClipboard SWF is ready!" );
                client.on( 'copy', function(event) {
                    event.clipboardData.setData('text/plain',$scope.url);
                } );
                client.on("aftercopy", function (event) {
                    // `this` === `client`
                    // `event.target` === the element that was clicked
                    alert("Copied text to clipboard: " + event.data["text/plain"]);
                });
            });
        }]);
})(angular.module('app.modules.shareSet'));