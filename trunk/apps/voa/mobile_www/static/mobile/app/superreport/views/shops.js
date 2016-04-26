define(["mobile-datepicker-datebox", "views/base",  "data/superreport", "utils/render", "text!templates/shops.html", "jquery"
         ,"iscroll", "css!styles/common.css", "css!styles/superreport_view.css"], function(datepicker, base, superreport, render, tpl, $){

    function view() {
    	base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype = $.extend(view.prototype, {
        // 模板处理
        render: function(args) {
            var self = this;

            var date = '';
            var ident = 1;
            if (args.date != undefined) {
                date = args.date;
            }
            if (args.ident != undefined){
                ident = args.ident;
            }
            superreport.get_shops({date: date, ident: ident}, function(ret){

            	// new 模板渲染类  
                var r = new render();
                // 模板内容
                r.template = tpl;
                // 分配变量
                r.vars = ret;
                r.vars.date = ret.date;
                // 应用, 返回当前element节点
                self.page = r.apply();
                $('title').html('门店列表');
                // 监听事件
                self.event(self.page, {current: ret.date});

            });        
        }
    });

    // 监听事件   
    view.prototype.event = function (el, ret) {
    	var self = this;
        //切换已提交门店、未提交门店
        el.find('.mod_list_nav').children().click(function(){
           $(this).addClass('current').siblings().removeClass('current');
            var index = $(this).attr('index');
           $('#index_'+index).show().siblings().hide();
        });
        //前一天
        el.find('#backward').bind('click', function(){
            var date = getDate({date: ret.current, needle: 'backward'});
            location.href = '#/shops/'+date;
        });
        //后一天
        el.find('#forward').bind('click', function(){
            var date = getDate({date: ret.current, needle: 'forward'});
            location.href = '#/shops/'+date;
        });

        //日历选择
        el.find('#datepicker').bind('change', function(){
            var date = el.find(this).val();
            location.href = '#/shops/'+date;
        });

    	
    };


    return view;
});

/**
 * 获取前一天、后一天时间
 * @param parameters
 * @returns {string}
 */
function getDate(parameters)   {

    var date = parameters.date;
    var needle = parameters.needle;
    var month_day = {1: 31, 2: 28,3: 31,4: 30, 5: 31, 6: 30, 7: 31, 8: 31, 9: 30, 10: 31, 11: 30, 12: 31};
    var	arr = date.split('-');
    if (arr.length != 3){
        alert('日期错误');
        return;
    }
    var year = parseInt(arr[0]);
    var month = parseInt(arr[1]);
    var day = parseInt(arr[2]);


    if (needle == 'backward') {//前一天
        if (day > 1) {
            day -= 1;
        } else {
            month -= 1;
            if (month < 1){
                month = 12;
                year -= 1;
            }
            day = month_day[month];
        }
    } else if (needle == 'forward') {//后一天
        if (day < month_day[month]) {
            day += 1;
        } else {
            day = 1;
            month += 1;
            if (month > 12){
                month =1;
                year += 1;
            }
        }
    }
    if (month < 10) {
        month = '0'+ month;
    }
    if (day < 10){
        day = '0'+ day;
    }
    return year+"-"+month+"-"+day;
}

