/**
 *
 * 日期插件 datetimepicker 分装指令
 */
(function (app) {
    app.directive('dateTimePicker',['$parse',function ($parse) {
        return {
            restrict : 'A',
            scope: false,
            link : function(scope, element, attrs) {
                var nowTime = new Date(),
                    lastTime = new Date(new Date().getTime()+86400000),
                    isControl = attrs.isControl,
                    startdate = attrs.startdate,
                    enddate = attrs.enddate,
                    starttime = attrs.starttime,
                    endtime = attrs.endtime;
                scope.$on('$stateChangeStart',function(){
                    element.datetimepicker('hide');
                });
                if(isControl == 'one'){
                    element.bind('click', function(e){
                        $(e.currentTarget).val('');
                        scope[starttime] = '';
                    });
                    (function(){
                        var startTime = nowTime.getFullYear()+'-'+(nowTime.getMonth()+1)+'-'+nowTime.getDate()+' 00:00';
                        var endTime = nowTime.getFullYear()+'-'+(nowTime.getMonth()+1)+'-'+nowTime.getDate()+' 23:59';
                        element.datetimepicker({
                            format: 'dd hh:ii',
                            startView: 1,
                            minView: 0,
                            startDate: startTime,
                            endDate: endTime,
                            autoclose: true,
                            pickerPosition: "bottom-left"
                        }).on('show',function(e){
                            $('.datetimepicker .datetimepicker-hours .switch').text('当天').bind('click',function(){
                                return false;
                            });
                            $('.datetimepicker .datetimepicker-minutes .switch').css({
                                opacity: 0
                            });
                        }).on('hide', function (e) {
                            var _that = $(e.currentTarget);
                            $(e.currentTarget).val(_that.val().slice(3));
                        });
                    })();
                }else if(isControl == 'two'){
                    element.bind('click', function(e){
                        $(e.currentTarget).val('');
                        scope[endtime] = '';
                    });
                    (function(){
                        var startTime = nowTime.getFullYear()+'-'+(nowTime.getMonth()+1)+'-'+nowTime.getDate()+' 00:00';
                        var endTime = lastTime.getFullYear()+'-'+(lastTime.getMonth()+1)+'-'+lastTime.getDate()+' 23:59';
                        element.datetimepicker({
                            format: 'dd hh:ii',
                            startView: 1,
                            minView: 0,
                            startDate: startTime,
                            endDate: endTime,
                            autoclose: true,
                            pickerPosition: "bottom-left"
                        }).on('show',function(e){
                            $('.datetimepicker .datetimepicker-hours .switch').text('当天').bind('click',function(){
                                return false;
                            });
                            $('.datetimepicker .datetimepicker-minutes .switch').css({
                                opacity: 0
                            });
                            $('.datetimepicker .datetimepicker-hours .next').bind('click',function(){
                                var _that = $(this);
                                $(this).siblings('.switch').css({
                                    opacity: 0
                                });
                                setTimeout(function(){
                                    _that.siblings('.switch').css({
                                        opacity: 1
                                    });
                                    _that.siblings('.switch').text('次日');
                                },200);

                            });
                            $('.datetimepicker .datetimepicker-hours .prev').bind('click',function(){
                                var _that = $(this);
                                $(this).siblings('.switch').css({
                                    opacity: 0
                                });
                                setTimeout(function(){
                                    _that.siblings('.switch').css({
                                        opacity: 1
                                    });
                                    _that.siblings('.switch').text('当天');
                                },200);
                            });
                        }).on('hide', function (e) {
                            var _that = $(e.currentTarget);
                            if(_that.val()){
                                if(Number(nowTime.getDate()) == _that.val().slice(0,2)){
                                    _that.val(_that.val().slice(3));
                                 }else{
                                    _that.val('次日' + _that.val().slice(3));
                                 }
                            }else{
                                _that.val('');
                                return false;
                            }

                        });
                    })();

                }else if(isControl == 'three'){
                    element.datetimepicker({
                        language: 'zh-CN',
                        format: 'yyyy-mm-dd',
                        startView: 2,
                        minView: 2,
                        autoclose: true
                    });
                    element.bind('click', function(){
                        element.datetimepicker('setStartDate', scope[startdate]);
                        element.datetimepicker('setEndDate', scope[enddate]);
                    });
                }
            }
        }
    }]);
})(angular.module('ng.poler.plugins.dateplugin',[]));