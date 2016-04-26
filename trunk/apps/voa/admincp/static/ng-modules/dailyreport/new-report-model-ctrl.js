(function(app){
    app.controller('newReportModelCtrl',['$scope', '$window' , '$timeout', '$location', 'DepartmentChooser', 'DailyReportApi' , function($scope,$window,$timeout,$location,DepartmentChooser,DailyReportApi){
        $('#sub-navbar').find("h1").html("<i class=\"fa fa-file-text-o page-header-icon\"><\/i>&nbsp;&nbsp;编辑报告模板");
        /*可见范围按钮样式及点击方法开始*/
        var modelId = $location.search().id;
        $scope.depList = [];
        /*清空所有部门*/
        $scope.rvrbAll = function(){
           $scope.rvrb_all_class = "btn btn-primary btn-lg reportVisibleRangeBtn";
           $scope.rvrb_part_class = "btn btn-default btn-lg active";
           $scope.depList = [];
           $scope.assemblyModel.drt_department=[];
        }
        /*获取排序*/
        DailyReportApi.sort().then(function(data){
            if(data.errcode==0){
                var cData = data.result.sort;
                $scope.assemblyModel.drt_sort = cData;

            }
        });

        $scope.rvrbPart = function(){
           DepartmentChooser.choose($scope.depList).result.then(function(data) {
                if(data){
                  var depArr = [];
                  var copyArr = [];
                  var resArr = [];
                  
                  for(var k=0; k<data.length; k++){
                    depArr.push(data[k]);
                    copyArr.push(data[k]);
                  }
                  var dLen = depArr.length;
                  var cLen = copyArr.length;
                  for(var i=0; i<cLen; i++){
                    var flag = false;
                    for(var j=0; j<dLen; j++){
                      if(depArr[j].id == copyArr[i].parentDep_id){
                        flag = true;
                      }
                    }
                    if(!flag){
                      resArr.push(copyArr[i]);
                    }
                  }
                   $scope.depList = resArr;
                   if(data.length > 0){
                      $scope.assemblyModel.drt_department = [];
                   }else{
                      $scope.assemblyModel.drt_department = "";
                   }
                   for(var i=0; i<data.length; i++){
                      var o = {};
                      o.dp_name = data[i].name;
                      o.dp_id = data[i].id;
                      o.dp_is_show = 0;
                      o.parent_id = data[i].parent_id ? data[i].parent_id : 0;
                      for(var j=0; j<resArr.length; j++){
                        if(data[i].id == resArr[j].id){
                          o.dp_is_show = 1;
                          break;
                        }
                      }
                      $scope.assemblyModel.drt_department.push(o);
                   }
                   if(data.length > 0){
                     $scope.rvrb_part_class = "btn btn-primary btn-lg";
                     $scope.rvrb_all_class = "btn btn-default btn-lg active reportVisibleRangeBtn";
                   }else{
                     $scope.rvrb_all_class = "btn btn-primary btn-lg reportVisibleRangeBtn";
                      $scope.rvrb_part_class = "btn btn-default btn-lg active";
                   }
                  
                }
           })
        }

        /*可见范围按钮样式及点击方法结束*/

        $scope.assemblyModel = {};
        $scope.assemblyModel.fnList = [
          {label : "单行文本",type : "text"},
          {label : "多行文本",type : "textarea"},
          {label : "数字输入",type : "number"},
          {label : "单项选择",type : "radio"},
          {label : "多项选择",type : "checkbox"},
          {label : "日期",type : "date"},
          {label : "时间",type : "time"},
          {label : "日期时间",type : "dateandtime"},
          {label : "上传图片",type : "img"},
        ];
        if(modelId){
            $('#sub-navbar').find("h1").html("<i class=\"fa fa-file-text-o page-header-icon\"><\/i>&nbsp;&nbsp;编辑模板");
          DailyReportApi.getReportTpl({drt_id:modelId}).then(function(data){
            var res = data.result.tpl;
            $scope.assemblyModel.drt_id = res.drt_id;
            $scope.assemblyModel.drt_name = res.drt_name;
            $scope.assemblyModel.drt_sort = res.drt_sort;
            $scope.assemblyModel.drt_switch = res.drt_switch;
            $scope.assemblyModel.drt_department = res.drt_departments.length > 0 ? res.drt_departments : "";
            $scope.assemblyModel.assemblyList = res.drt_module;
            for(var i=0; i<res.drt_departments.length; i++){
              if(res.drt_departments[i].dp_is_show=="1"){
                var o = {};
                o.id = res.drt_departments[i].dp_id;
                o.name = res.drt_departments[i].dp_name;
                o.parent_id = res.drt_departments[i].parent_id;
                $scope.depList.push(o);
              }
            }

            for(var j=0; j<$scope.assemblyModel.assemblyList.length; j++){
              var item = $scope.assemblyModel.assemblyList[j];
              if(item.value.length <= 0){
                item.value = "";
              }
            }
            if(res.drt_departments.length > 0){
              $scope.rvrb_part_class = "btn btn-primary btn-lg";
              $scope.rvrb_all_class = "btn btn-default btn-lg active reportVisibleRangeBtn";
            }else{
              $scope.rvrb_all_class = "btn btn-primary btn-lg reportVisibleRangeBtn";
              $scope.rvrb_part_class = "btn btn-default btn-lg active";
            }
            
            scopeWatch();
          });
        }else{
            $('#sub-navbar').find("h1").html("<i class=\"fa fa-file-text-o page-header-icon\"><\/i>&nbsp;&nbsp;新增模板");
          $scope.assemblyModel.drt_name = $scope.assemblyModel.drt_name || "";
          $scope.assemblyModel.drt_sort = $scope.assemblyModel.drt_sort || "";
          $scope.assemblyModel.drt_switch = $scope.assemblyModel.drt_switch == "0" ? "0" : "1";
          $scope.assemblyModel.drt_department = $scope.assemblyModel.drt_department || "";
          $scope.assemblyModel.assemblyList = [];
          $scope.rvrb_all_class = "btn btn-primary btn-lg reportVisibleRangeBtn";
          $scope.rvrb_part_class = "btn btn-default btn-lg active";
          scopeWatch();
        }
        

        function scopeWatch(){
          $scope.$watch('assemblyModel.assemblyList',function(oldVal,newVal){
            $timeout(function(){
              if(oldVal.length!=newVal.length)
                $('.iphoneContextBox').scrollTop($('#Js_iphoneContent').height())
            })
          },true);
        }

        

        $scope.save = function(){ //发送请求保存数据
          var param = {
                drt_switch : $scope.assemblyModel.drt_switch,
            drt_departments : $scope.assemblyModel.drt_department,
            drt_name : $scope.assemblyModel.drt_name,
            drt_sort : $scope.assemblyModel.drt_sort,
            drt_module : $scope.assemblyModel.assemblyList
          }
          var b_m_type={
              'textarea':'多行文本',
              'text':'单行文本',
              'date':'日期',
              'time':'时间',
              'dateandtime':'日期时间',
              'number':'数字',
              'radio':'单选框',
              'checkbox':'复选框',
              'img':'图片'
          }
          //验证标题是否为空并进行正确提示
          for(var i=0; i<param.drt_module.length; i++){
              //验证组件标题
              if(param.drt_module[i].title==''){
                  alert(b_m_type[param.drt_module[i].type]+'标题不能为空!');
                  return false;
              }
            delete param.drt_module[i].$$hashKey;
            var len = param.drt_module[i].value.length;
            for(var j=0; j<len; j++){
              delete param.drt_module[i].value[j].$$hashKey;
            }
          }
          if(modelId){
            param.drt_id = $scope.assemblyModel.drt_id;
            DailyReportApi.saveReportTpl(param).then(function(data){
              if(data.errcode==0){
                alert("模板修改成功");
                $window.history.back();
              }else{
                alert(data.errmsg);
              }
            });
          }else{
            DailyReportApi.addReportTpl(param).then(function(data){
              if(data.errcode==0){
                alert("模板创建成功");
                 $window.history.back();
              }else{
                alert(data.errmsg);
              }
            });
          }
        }

    }]);

})(angular.module('app.modules.dailyreporttemplate'));