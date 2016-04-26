(function (app) {
    app.directive('cycpUploadMore',['$window',function($window) {
        return {
            restrict : 'EA',
            scope:{
                text:"@",
                name:"@",
                url:"@",
                emText:"@",
                limitNum : "=",
                initLoad : "=",
                model:"=ngModel"
            },
            require:"ngModel",
            template:'<div class="uploader_box clearfix">'+
                '<input type="hidden" class="_input" name="{{name}}" value="{{model}}"/>'+
                '<input type="hidden" class="pic_id" value="{{model}}"/>'+
                '<span class="btn btn-success fileinput-button" style="float:left;">'+
                '<i class="glyphicon glyphicon-plus"></i>'+
                '<span>{{text}}</span>'+
                '<input class="cycp_uploader" type="file" name="file" data-url="{{url}}" ' +
                    'data-callback="callbackaa" data-callbackall="" ' +
                    'data-hidedelete="1" data-imgs-del="delImgs" data-limit-num="{{limitNum}}" data-showimage="true" multiple="multiple" />'+
                '<em style="font-size:12px; font-style:normal">{{emText}}</em>'+
                '</span>'+
                '<span class="_showdelete" data-ng-show="false">' +
                '<a href="javascript:;" class="btn btn-danger cycp_uploader_delete">删除</a></span>'+
                '<span class="_showimage"><a data-ng-repeat="item in initLoad" target="_blank" style="position:relative; float:left; margin-left:5px; width:33px; height:33px;"><img src="{{item.url}}" border="0" style="width : 33px; height:33px;" /><span class="glyphicon glyphicon-remove" aria-hidden="true" style="color:red; position:absolute; right:-4px; top:-6px; cursor:pointer" data-ng-click="delImg(item.id)"></span></a></span>'+
                '</div>',
            controller : ["$scope","$timeout",function($scope,$timeout){
                $scope.delImg = function(id){
                    $timeout(function(){
                        for(var i=0; i<$scope.model.length; i++){
                            if($scope.model[i] == id){
                                $scope.model.splice(i,1);
                            }
                        }
                        console.log($scope.initLoad)
                        for(var j=0; j<$scope.initLoad.length; j++){
                            if($scope.initLoad[j].id == id){
                                $scope.initLoad.splice(j,1);
                            }
                        }
                    })
                }

                $window.callbackaa = function(res,t,data){
                    $timeout(function(){
                        $scope.model.push(res.id);
                        for(var i=0; i<$scope.initLoad.length; i++){
                            if($scope.initLoad[i].files){
                                if($scope.initLoad[i].files[0]===data.files[0]){
                                    $scope.initLoad[i] = {"id":res.id,"url":res.file[0].url};
                                }
                            }
                        }
                    })
                }
            }],
            link : function($scope, element, attrs,ctrl) {
                $scope.text = "上传图片";
                $scope.emText = "";
                $scope.url="/admincp/api/attachment/upload/?file=file&thumbsize=45";
                $scope.$watch('initLoad',function(){
                    element.find('.cycp_uploader').data('initLoad',$scope.initLoad);
                },true);
                
               // var $input = element.find('.uploader_box').find('input._input');
                
            }
        }
    }]);

})(angular.module('ng.poler.plugins.cycpupload',[]));