define(["views/base",  "data/superreport", "utils/render", "text!templates/edit.html", "jquery"
         ,"iscroll", "css!styles/common.css", "css!styles/superreport_list.css"], function(base, superreport, render, tpl, $, html5){
	
    function view() {
    	base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    
    view.prototype = $.extend(view.prototype, {
        // 模板处理
        render: function(args) {
            var self = this;
            var dr_id = args.dr_id;
            var csp_id = null;
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
                    location.href = "#/error/3";
                    return;
                } else {                                    //店长
                    csp_id = ret.placeid;
                }

                superreport.get_view({dr_id: dr_id}, function(ret){

                    // new 模板渲染类
                    var r = new render();
                    // 模板内容
                    r.template = tpl;
                    // 分配变量
                    r.vars = {list_int:ret.int, list_text: ret.text, dr_id: dr_id, csp_id: csp_id};
                    // 应用, 返回当前element节点
                    self.page = r.apply();
                    // 监听事件
                    self.event(self.page);
                });
            });

        }
    });

    // 监听事件   
    view.prototype.event = function (el) {
    	var self = this;
    	el.find('#send').bind('click', function (){
            var errmsg = '';
            //验证所有表单元素
            el.find("#report_form").find('input,textarea').each(function(index,element){
                var val = el.find(element).val();
                var name = el.find(element).attr('name');
                var required = el.find(element).attr('need');
                var fieldname = el.find(element).attr('fieldname');
                if (required == 1 && val == '') {
                    errmsg += fieldname + '不能为空 \n\r';
                }
            });
            if (errmsg != '') {  //如果验证不通过
                alert(errmsg);
                return;
            }
            superreport.edit_report(el.find("#report_form").serialize(), function(ret){

                if (ret.errcode == 0) {
                    location.href = '#/daily/'+ret.result.csp_id+'/'+ret.result.date;
                } else {
                    alert(ret.errmsg);
                    return;
                }
            });

    		$(this).unbind('click');
    	});
    	
    };
    
    return view;
});
