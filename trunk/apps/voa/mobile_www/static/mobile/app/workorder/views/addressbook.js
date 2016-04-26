define(["data/addressbook", "utils/render", "text!templates/addressbook.html", 'jquery', 'utils/api'
         , "css!styles/addressbook.css"], function(addressbook, render, tpl, $, api){
	
    function view() {

    }

    view.prototype = {
        // 模板处理
        render: function(args) {
            var self = this;
            addressbook.get_list({}, function (ret) {
                // new 械板渲染类  
                var r = new render();
                // 模板内容
                r.template = tpl;
                // 分配变量
                r.vars = {data: ret};
                // 应用, 返回当前element节点
                var el = r.apply();

                //选中默认项
	            var addressbook = $('body').data('addressbook');
	            if(addressbook) {
	            	$('#mod_common_list input:checkbox').each(function (i, e){
	            		$.each(addressbook, function (i, a){
		            		if(a.id == e.value) {
		            			$(e).click();
		            		}
		            	});
	            	});
	            }
	            
	            $('title').html('选择接收人');
                 
	            /*setTimeout(function (){
               		scroll(0,0);
               	}, 300);*/
               	
                // 监听事件
                self.event(el, ret.list);
            });
        }, 
        // 监听事件   
        event: function (el, list) {
            
        	$('#customers_list li').unbind('click').click(function (){
        		var uid = $(this).attr('rel');
        		//缓存选中的地址簿
        		var u = {};
               $.each(list, function (){
               		if(uid == this.uid) {
               			u = {uid:this.uid,realname:this.realname,face:this.face};
            			return false;
            		}
            	})
                $('body').data('addressbook', u);
                location.href = "#/publish";
        	});
        }
    };

    return view;
});
