define(["views/base",  "data/superreport", "utils/render", "text!templates/month.html", "jquery"
         ,"iscroll", "css!styles/common.css", "css!styles/superreport_view.css","mobile-datepicker-datebox"], function(base, superreport, render, tpl, $){
	
    function view() {
    	base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype = $.extend(view.prototype, {
        // 模板处理
        render: function(args) {

            var self = this;
            var csp_id = null;
            var year = '';
            var month = '';

            //检查权限
            superreport.get_power(function(ret){
                if (ret == '') {
                    location.href = "#/error/1";
                    return;
                }
                var power = ret.power;
                if (power == 3){                          //相关人
                    location.href = "#/error/2";
                    return;
                } else if (power == 2){                     //负责人
                    if (args.csp_id == undefined) {
                        location.href = "#/monthshops";
                        return;
                    }
                } else {                                    //店长
                    csp_id = ret.placeid;
                }

                if (args.year != undefined) {
                    year = args.year;
                }
                if (args.month != undefined) {
                    month = args.month;
                }
                if (args.csp_id != undefined) {
                    csp_id = args.csp_id;
                }
                superreport.get_month({csp_id: csp_id, year: year, month: month}, function(ret){

                    // new 模板渲染类
                    var r = new render();
                    // 模板内容
                    r.template = tpl;
                    // 分配变量
                    r.vars = ret;
                    month = parseInt(ret.month);
                    if (month < 10) {
                        r.vars.month = month = '0'+ month;
                    }
                    // 应用, 返回当前element节点
                    self.page = r.apply();
                    $('title').html(ret.year+'-'+month+' 月报');
                    // 监听事件
                    self.event(self.page, {year: ret.year, month: ret.month, csp_id: ret.csp_id});

                });
            });


        }
    });

    // 监听事件   
    view.prototype.event = function (el, ret) {
    	var self = this;
        if (history.length < 2){
            el.find('.footer-btn').hide();
        }
        //前一天
        el.find('#backward').bind('click', function(){
            var year = ret.year;
            var month = ret.month;
            var csp_id = ret.csp_id;
            var date = getDate({year: year, month: month, needle: 'backward'});
            location.href = '#/month/'+csp_id+'/'+date.year+'/'+date.month;
        });
        //后一天
        el.find('#forward').bind('click', function(){
            var year = ret.year;
            var month = ret.month;
            var csp_id = ret.csp_id;
            var date = getDate({year: year, month: month, needle: 'forward'});
            location.href = '#/month/'+csp_id+'/'+date.year+'/'+date.month;
        });
        //日历选择
        el.find('#datepicker').bind('change', function(){
            var date = el.find(this).val();
            var arr = date.split('-');
            location.href = '#/month/'+ret.csp_id+'/'+arr[0]+'/'+arr[1];
        })

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