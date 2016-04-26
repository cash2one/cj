define(["mobile-datepicker-datebox", "views/base",  "data/superreport", "utils/render", "text!templates/monthshops.html", "jquery"
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
            var year = '';
            var month = '';
            if (args.year != undefined) {
                year = args.year;
            }
            if (args.month != undefined) {
                month = args.month;
            }
            superreport.get_monthshops({year: year, month: month}, function(ret){

            	// new 模板渲染类  
                var r = new render();
                // 模板内容
                r.template = tpl;
                // 分配变量
                r.vars = ret;
                // 应用, 返回当前element节点
                self.page = r.apply();
                $('title').html('门店列表');
                // 监听事件
                self.event(self.page,{year: ret.year, month: ret.month});

            });        
        }
    });

    // 监听事件   
    view.prototype.event = function (el, ret) {
    	var self = this;
        var year = ret.year;
        var month = ret.month;
        //前一天
        el.find('#backward').bind('click', function(){
            var date = getDate({year: year, month: month, needle: 'backward'});
            location.href = '#/monthshops/'+date.year+'/'+date.month;
        });
        //后一天
        el.find('#forward').bind('click', function(){
            var date = getDate({year: year, month: month, needle: 'forward'});
            location.href = '#/monthshops/'+date.year+'/'+date.month;
        });

        //日历选择
        el.find('#datepicker').bind('change', function(){
            var date = el.find(this).val();
            var arr = date.split('-');
            location.href = '#/monthshops/'+arr[0]+'/'+arr[1];
        });

    	
    };


    return view;
});

/**
 * 获取前一月、后一月时间
 * @param parameters
 * @returns {string}
 */
function getDate(parameters)   {

    var year = parameters.year;
    var month = parameters.month;
    var needle = parameters.needle;

    if (needle == 'forward') {//前一月
        month = parseInt(month) + 1;
        if (month > 12) {
            month = 1;
            year = parseInt(year) + 1;
        }

    } else if (needle == 'backward') {//后一月
        month = parseInt(month) - 1;
        if (month < 1) {
            month = 12;
            year = parseInt(year) - 1;
        }
    } else {
        year = year;
        month = month;
    }

    if (month < 10) {
        month = '0'+month;
    }

    return {year:year,month:month};
}
