(function (app) {
    app.directive('cycpUpload',['$window',function($window) {
        return {
            restrict : 'EA',
            scope:{
                text:"@",
                name:"@",
                url:"@",
                emText:"@",
                picUrl:"@"
            },
            require:"ngModel",
            template:'<div class="uploader_box">'+
                '<input type="hidden" class="_input" name="{{name}}" value="{{value}}"/>'+
                '<input type="hidden" class="pc_id" name="{{name}}" value="{{value}}"/>'+
                '<span class="btn btn-success fileinput-button">'+
                '<i class="glyphicon glyphicon-plus"></i>'+
                '<span>{{text}}</span>'+
                '<input class="cycp_uploader" type="file" name="file" data-url="{{url}}" ' +
                    'data-callback="callbackaa" data-callbackall="" ' +
                    'data-hidedelete="1" data-showimage="true" />'+
                '<em style="font-size:12px; font-style:normal">{{emText}}</em>'+
                '</span>'+
                '<span class="_showdelete" data-ng-show="false">' +
                '<a href="javascript:;" class="btn btn-danger cycp_uploader_delete">删除</a></span>'+
                '<span class="_showimage"></span>'+
                '</div>',
            link : function($scope, element, attrs,ctrl) {
                $scope.text = "上传图片";
                $scope.emText = "";
                $scope.url="/admincp/api/attachment/upload/?file=file&thumbsize=45";
                $window.callbackaa = function(res){
                    ctrl.$setViewValue(res.id);
                    ctrl.$render();
                }
                ctrl.$render = function(){
                   element.find(".pc_id").val(ctrl.$viewValue);
                }
                $scope.$watch('picUrl',function(newVal){
                    if($scope.picUrl){
                        var html = '<a href="' + $scope.picUrl + '" target="_blank">';
                        html += '<img src="' + $scope.picUrl+"/45" + '" border="0" style="max-width:64px;max-height:32px" />';
                        html += '</a>';
                        element.find('._showimage').html(html);
                    }
                })

            }
        }
    }]);

})(angular.module('ng.poler.plugins.cycpupload',[]));