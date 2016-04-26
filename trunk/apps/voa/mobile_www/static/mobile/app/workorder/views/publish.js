define(["data/workorder", "utils/render", "text!templates/publish.html", 'jquery', 'utils/api', "utils/call", "css!styles/common.css"], function(workorder, render, tpl, $, api, call){
	
    
    function tip(msg, sync)
    {
    	$('#question').html(msg);
    	$('#popup').popup('open');
    	setTimeout(function (){
    		$('#popup').popup('close');
    	}, 2000);
    	if(sync) $.get('/frontend/qywxmsg/send/');
    }
    
    function view() {
        
    }
    view.prototype = {
        render: function(args) {
        	var r = new render();
            r.template = tpl;
            
            var el = r.apply();
            this.event(el);
            
            $('title').html('发起派单');
            
            //输出已选中的接收人
            var u = $('body').data('addressbook');
            if(u) {
            	$('input[name=receiver_uids]').val(u.uid);
        		var img = '<a class="photo" href="javascript:;"><img src="'+u.face+'" title="'+u.realname+'"/></a>';
        		$('#photoDiv').prepend(img);
            }
            
            var c = new call;
			c.app('contacts', 'contacts', {container: "#photoDiv", input_name_contacts: 'receiver_uids', input_name_deps: 'deps', input_type: 'radio'});
        }, 
        event: function (el) {
            //保存派单
    		$('#save').unbind('click').click(function(){
    			
    			//接收人处理
				var ids = [];
				$('input[name=receiver_uids]').each(function(i, e){
					ids.push(e.value);
				});
				if(!ids.length) {
					return tip('请选择接收人');
				}
				ids = ids.join(',');
				var params = $('#publish').serializeArray();
				for(k in params)
				{
					if(params[k].name == 'receiver_uids') {
						delete params[k];
					}
				}
				params.push({name : 'receiver_uids', value : ids});
				
				var btn = $(this);
				btn.attr('disabled', true).addClass('disabled');
				workorder.publish(params, function (ret) {
	            	$.mobile.loading( "hide" );
    				if(ret.errcode == 0) {
	            		tip('发布派单成功', 1);
	            		setTimeout(function (){
	            			location.href = "#/list/wait_confirm/sent";
	            		}, 2000);
	            	}else {
	            		btn.attr('disabled', false).removeClass('disabled');
	            		tip(ret.errmsg);
	            	}
	            });
            });
        }
    };

    return view;
});
