/**
 * input输入判断
 * */
(function (app) {
    app.directive("inputFocus",function () {
        return {
            restrict : 'A',
            link: function(scope,iEle,iAttr){
                var max = iAttr.max,
                    min = iAttr.min;
                if(max){
                    $(iEle).on('keyup',function(){
                        $(this).val($(this).val().replace(/[^0-9\.]/gi,''));
                        if($(this).val() && $(this).val().length > 0){
                            if(Number($(this).val()) > max){
                                $(this).val(max);
                            }
                            if(Number($(this).val()) < min){
                                $(this).val(min);
                            }
                        }
                    });
                }
            }
        }
    });
})(angular.module('app.modules.sign.class.main'));