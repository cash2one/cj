(function (app) {
    app.directive('dateTimePicker',['$filter',function($filter) {
        return {
            restrict : 'EA',
            scope:{
                dateplaceholder:"@",
                timeplaceholder:"@"
            },
            require:"ngModel",
            template:'<div class="input-group">' +
            '<input type="text" class="form-control date-input"' +
            'placeholder="{{dateplaceholder}}"  style="width: 50%;" value=""> ' +
            '<input type="text" class="form-control col-md-offset-1 time-input"' +
            'placeholder="{{timeplaceholder}}"  style="width: 40%;" value="">' +
            '</div>',
            link : function($scope, element, attrs,ctrl) {
                var dateInput = element.find(".date-input") ;
                var timeInput = element.find(".time-input") ;
                dateInput.change(function(){
                    ctrl.$setViewValue(dateInput.val()+" "+timeInput.val()) ;
                });
                timeInput.change(function(){
                    ctrl.$setViewValue(dateInput.val()+" "+timeInput.val()) ;
                });
                ctrl.$render = function() {
                    var date = null;
                    if(ctrl.$viewValue==""||ctrl.$viewValue==undefined
                        ||ctrl.$viewValue==null){
                        date = new Date();
                    }else{
                        date = new Date(ctrl.$viewValue);
                    }
                    var dd = $filter('date')(date,'yyyy-MM-dd');
                    var tt = $filter('date')(date,'HH:mm');
                    dateInput.val(dd);
                    timeInput.val(tt);
                    dateInput.datepicker({
                        dateFormat: 'yy-mm-dd',
                        todayHighlight:true
                    });
                    timeInput.timepicker({
                        showMeridian: false,
                        secondStep:5
                    });
            };


            }
        }
    }]).directive('ueditor',[function() {
        return {
            restrict: 'EA',
            require:"ngModel",
            scope:{
                editor:"=ngModel"
            },
            link:function($scope,ele,attrs,ctrl){
                var opt={"toolbars":[["source","|","bold","italic","underline","removeformat","|","forecolor","backcolor",
                    "insertorderedlist","insertunorderedlist","fontfamily","fontsize","|","justifyleft",
                    "justifycenter","justifyright","justifyjustify","|","link","unlink","insertimage",
                    "insertvideo"]],
                    "textarea":"content",
                    "initialFrameHeight":300,
                    "initialContent":"",
                    "elementPathEnabled":false,
                    "serverUrl":"/admincp/ueditor/",
                    "charset":"utf-8","lang":"zh-cn",
                    "autoClearinitialContent":false,
                    "emotionLocalization":true,
                    "pageBreakTag":"ueditor_page_break_tag"}
                $scope.editor = UE.getEditor(attrs.id, opt);

            }
        }
    }]);

})(angular.module('ng.poler.plugins.campaignsPlugins',[]));