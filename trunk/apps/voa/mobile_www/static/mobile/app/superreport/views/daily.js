define(["views/base",  "data/superreport", "utils/render", "text!templates/daily.html", "jquery"
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
            var date = '';

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
                    if (args.csp_id == undefined){
                        location.href = "#/shops";
                        return;
                    }
                } else {                                    //店长
                    csp_id = ret.placeid;
                }

                if (args.date != undefined) {
                    date = args.date;
                }
                if (args.csp_id != undefined) {
                    csp_id = args.csp_id;
                }

                superreport.get_daily({csp_id: csp_id, date: date}, function(ret){

                    // new 模板渲染类
                    var r = new render();
                    // 模板内容
                    r.template = tpl;
                    // 分配变量
                    r.vars = ret;
                    r.vars.power = power;
                    if (ret.report == undefined) {
                        r.vars = {reporttime: ret.reporttime, report: []};
                    }
                    // 应用, 返回当前element节点
                    self.page = r.apply();

                    // 监听事件
                    self.event(self.page, {reporttime: ret.reporttime, csp_id: csp_id, dr_id: ret.dr_id});
                    $('title').html(ret.reporttime+' 日报');
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
            var current = ret.reporttime;
            var csp_id = ret.csp_id;
            var date = getDate({date: current, needle: 'backward'});
            location.href = '#/daily/'+csp_id+'/'+date;
        });
        //后一天
        el.find('#forward').bind('click', function(){
            var current = ret.reporttime;
            var csp_id = ret.csp_id;
            var date = getDate({date: current, needle: 'forward'});
            location.href = '#/daily/'+csp_id+'/'+date;
        });
        //日历选择
        el.find('#datepicker').bind('change', function(){
            var date = el.find(this).val();
            location.href = '#/daily/'+ret.csp_id+'/'+date;
        });


        el.find('#comment_submit').bind('click', function(){
            var comment = el.find('#comment').val();
            if (comment == '') {
                alert('评论内容不能为空！');
                return;
            }
            superreport.add_comment({dr_id: ret.dr_id, comment: comment}, function(data){
                if (data.errcode == 0) {
                    el.find('#comment').val('');
                    //el.find('#comment_input').hide();
                    superreport.get_comments({dr_id: ret.dr_id, limit: 1000}, function(comment_data){
                       comment_list = comment_data.list;
                        var str = '<h4>评论内容</h4>'
                            +'<ul class="ui-nodisc-icon ui-alt-icon comment-conten"  data-role="listview">';
                        for (i=0;i<comment_list.length;i++){
                            str += '<li>'
                              +'<p class="comment-title">'+comment_list[i].comment+'</p>'
                              +'<p>'+comment_list[i].username+'</p>'
                              +'<p class="ui-li-aside">'+comment_list[i].created_u+'</p>'
                              +'</li>'
                        };
                        str += '</ul>';
                        el.find('#comments_contain').html(str);
                        el.find('#comments_contain').trigger('create');
                    });
                } else {
                    alert(data.errmsg);
                }
            })
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