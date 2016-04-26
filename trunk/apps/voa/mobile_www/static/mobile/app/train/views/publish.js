define(["data/train", "utils/render", "text!templates/publish.html", 'jquery', 'utils/api'
        , "css!styles/train.css"], function(train, render, tpl, $, api){
	
    
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
            
            //输出缓存的表单数据
            $('input[name=contacter]').val($('body').data('contacter'));
            $('input[name=phone]').val($('body').data('phone'));
            $('input[name=address]').val($('body').data('address'));
            $('textarea[name=remark]').val($('body').data('remark'));
            
            $('title').html('发起派单');
            
            //输出已选中的接收人
            var u = $('body').data('addressbook');
            if(u) {
            	$('input[name=receiver_uids]').val(u.uid);
        		var img = '<a class="photo" href="javascript:;"><img src="'+u.face+'" title="'+u.realname+'"/></a>';
        		$('#photoDiv').prepend(img);
            }
        }, 
        event: function (el) {
            //保存派单
    		$('#save').unbind('click').click(function(){
				if(!$('input[name=receiver_uids]').val()) {
					return tip('请选择接收人');
				}
				var params = $('#publish').serialize();
				var btn = $(this);
				btn.attr('disabled', true).addClass('disabled');
				train.publish(params, function (ret) {
	            	$.mobile.loading( "hide" );
    				if(ret.errcode == 0) {
	            		tip('发布派单成功', 1);
	            		setTimeout(function (){
	            			location.href = "#/view/list/wait_confirm/sent";
	            		}, 2000);
	            	}else {
	            		btn.attr('disabled', false).removeClass('disabled');
	            		tip(ret.errmsg);
	            	}
	            });
            });
            $(document).off('click', '#mpuAdd');
    		$(document).on( "click", '#mpuAdd', function(){
				//选择接收人前,先缓存表单数据
				$('body').data('contacter', $('input[name=contacter]').val());
				$('body').data('phone', $('input[name=phone]').val());
				$('body').data('address', $('input[name=address]').val());
				$('body').data('remark', $('textarea[name=remark]').val());
				
				location.href = '#/addressbook';
            });
        }
    };

    return view;
});
