/**
 * Created by three on 16/3/11.
 */
;(function (app) {
    /*app.factory('test', function(){
    	return {
    		CreateInputText : function(obj){
    			alert("我是测试服务");
    			alert(obj)
    		}
    	}
    });*/

    app.directive('assemblyInputBox',function(){
    	return {
    		restrict : "E",
    		templateUrl : '/admincp/static/ng-modules/ng.input.assembly/selInput-tpl.html',
    		replace : true,
    		scope : {
    			myData : "=",
    			myFn : "=",
                myLen : "=",
                enterLen : "="
    		},
    		controller : ["$scope","$timeout",function($scope,$timeout){
                /*$scope.$watch("myData",function(newVal){
                    if(typeof newVal == "undefined") return;
                    for(var i=0; i<newVal.length; i++){
                        var item = newVal[i];
                        if(item.title.length >=6&&/^(date|time|dateandtime|number)$/.test(item.type)){
                                item.title = item.title.substr(0,6);
                        }else if(item.title.length >= $scope.enterLen){
                            item.title = item.title.substr(0,$scope.enterLen);
                        }
                        if(item.type=="radio" ||　item.type=="checkbox"){
                            for(var j=0; j<item.value.length; j++){
                                if(item.value[j].name.length >= $scope.enterLen){
                                    item.value[j].name = item.value[j].name.substr(0,$scope.enterLen);
                                }
                            }
                        }
                    }
                    
                },true)*/

                $scope.changeVal = function(item){
                    if(item.title.length >=6&&/^(date|time|dateandtime|number)$/.test(item.type)){
                            $timeout(function(){
                                item.title = item.title.substr(0,6);
                            })
                    }else if(item.title.length >= $scope.enterLen){
                        $timeout(function(){
                            item.title = item.title.substr(0,$scope.enterLen);
                        })
                    }
                    if(item.type=="radio" ||　item.type=="checkbox"){
                        for(var j=0; j<item.value.length; j++){
                            if(item.value[j].name.length >= $scope.enterLen){
                                $timeout(function(){
                                    item.value[j].name = item.value[j].name.substr(0,$scope.enterLen);
                                })
                            }
                        }
                    }
                }
    		}],
    		link : function(scope,element,attr){
    			element.on('click','.Js_addTextAssembly',function(){
                    if(scope.myData.length == scope.myLen) return;
    				var type = $(this).attr('data-type');
    				var obj = {};
    				obj.type = type;
	    			obj.title = "标题：";
	    			if(type=="radio"){
	    				obj.value = [{name:"选项1"}];
	    			}else if(type=="checkbox"){
                        obj.value = [{name:"选项1"},{name:"选项2"}];
                    }else if(type=="img"){
	    				obj.value = [{max:1}];
	    			}else{
	    				obj.value = [""];
	    			}
	    			obj.is_null = false;
    				scope.myData.push(obj);
    			});
    			element.on('click','.Js_assmblyOrder',function(){
    				var index = $(this).parents('.Js_assemblyList').index(),
    					dt = scope.myData[index],
    					orderType = $(this).attr('data-order-type') == 0 ? -1 : 1;
    				scope.myData.splice(index,1);
    				scope.myData.splice(index+orderType,0,dt);
    			});
    			element.on('click','.Js_assemBlyItemDel',function(){
    				var index = $(this).parents('.Js_assemblyList').index(),
    					dt = scope.myData[index];
    				scope.myData.splice(index,1);
    			});
    			element.on('click','.Js_addAsseblyOptsItem',function(){
    				var index = $(this).parents('.Js_assemblyList').index();
    				scope.myData[index].value.push({name:"选项"+(scope.myData[index].value.length+1)});
    			});
    			element.on('click','.Js_radioAndChkDelBtn',function(){
    				var parentIndex = $(this).parents('.Js_assemblyList').index();
    				var index = $(this).parents('li').index();

                    if(scope.myData[parentIndex].type=="radio"){
                        if(scope.myData[parentIndex].value.length > 1){
                            scope.myData[parentIndex].value.splice(index,1);
                        }
                    }else if(scope.myData[parentIndex].type=="checkbox"){
                        if(scope.myData[parentIndex].value.length > 2){
                            scope.myData[parentIndex].value.splice(index,1);
                        }
                    }
                    
    			});
    		}
    	}
    });
})(angular.module('ng.input.assembly.pc',[]));