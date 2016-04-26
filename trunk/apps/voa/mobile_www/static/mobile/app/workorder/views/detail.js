function goback(state,is_sent)
{
	var map = {1:'wait_confirm',2:'refused',3:'wait_complete',4:'completed',99:'canceled'};
	var type = map[state] ? map[state] : 'wait_confirm';
	var res = is_sent ? 'sent' : 'received';
	location.href = '#/list/'+type+'/'+res;
}
define(["data/workorder", "utils/render", "text!templates/detail.html", 'jquery', 'underscore'
         , "swipebox", "css!styles/common.css","jquery.fileupload-validate"],
         function(workorder, render, tpl, $, _){
	
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
    	// 模板处理
        render: function(args) {
            var self = this;
            
            //获取详情
            workorder.get_detail({id: args.id}, function (ret) {
            	var r = new render();
                r.template = tpl;
                
                //判断是否"我发送的"
                if(ret.workorder.sender_info == null) {
                	ret.workorder.sender_info = {};
                }
                
                var is_sent = ret.role.roleid == 1;	//发送者
                var is_oper = ret.role.roleid == 2 || ret.role.roleid == 3;	//执行者
                
                //定义可执行操作
                var action = {};
                action.can_cancel = ret.allow_action.indexOf('cancel') != -1;			//可撤回
                action.can_refuse = ret.workorder.wostate == 1 && is_oper;	//可拒绝,可接受
                action.can_complete = ret.allow_action.indexOf('complete') != -1;		//可完成(提交)
                
                var state = ret.workorder.wostate;
                $('title').html('派单详情');
                 
                //state = 3;
                //图片上传组件
                if(state == 3 && is_oper) {
	                $( document ).on( "pagecreate", this.page, function() {
	                    $('.fileinput-button').fileupload({
	                        // Uncomment the following to send cross-domain cookies:
	                        //xhrFields: {withCredentials: true},
	                        dataType: 'json',
	                        url: '/api/attachment/post/upload/',
	                        maxFileSize: 5000000,
	                        maxNumberOfFiles : 1,
	                        //acceptFileTypes: /(\.|\/)(csv|jpe?g|png)$/i,
	                        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
	                        progressall: function (e, data) {
	                            var progress = parseInt(data.loaded / data.total * 100, 10);
	                            $(this).find('.fileinput-button').find('span').eq(0).text('正在上传中，进度：'+progress + '%');
	                            if (progress == 100) {
	                                $(this).find('.fileinput-button').find('span').eq(0).text('上传完成，正在处理请稍等。。。');
	                            }
	                        },
	                        done: function (e, data) {
	                            var result = data.result;
	                            if (result.errcode == 0) {
	                                result = result.result;
	                                var tc_id = 'at_ids';
	                                self.show_attach(result, tc_id, $(this).parents('.ui-field-contain'));
	                            } else {
	                                tip(result.errmsg);
	                            }
	                        }
	                    });
	                });
                }
               
                r.vars = {row: ret, state: state, is_sent: is_sent, is_oper: is_oper, action: action};
                var el = r.apply();
                self.event(el);
            });
           
        }, 
        // 监听事件   
        event: function (el) {
        	var self = this;
        	//撤回
    		$('#cancel').click(function(){
    			var id = $('#woid').val();
    			$.mobile.loading( "show" );
    			var btn = $(this);
    			btn.attr('disabled', true);
    			workorder.cancel({id: id}, function (ret) {
	            	$.mobile.loading( "hide" );
	            	btn.attr('disabled', false);
    				if(ret.errcode == 0) {
	            		tip('撤回派单成功', 1);
	            		setTimeout(function (){
	            			location.href = "#/list/canceled/received";
	            		}, 2000);
	            	}else {
	            		tip(ret.errmsg);
	            	}
	            });
            });
            
            //接受派单
    		$('#confirm').click(function(){
    			var id = $('#woid').val();
    			$.mobile.loading( "show" );
    			var btn = $(this);
    			btn.attr('disabled', true);
    			workorder.confirm({id: id}, function (ret) {
	            	$.mobile.loading( "hide" );
	            	btn.attr('disabled', false);
    				if(ret.errcode == 0) {
	            		tip('确认派单成功', 1);
	            		setTimeout(function (){
	            			location.href = "#/list/wait_complete/received";
	            		}, 2000);
	            	}else {
	            		tip(ret.errmsg);
	            	}
	            });
            });
            
            //拒绝派单
    		$('#refuse').click(function(){
    			$('#require_memo').hide();
    			$('#refuse_memo').popup('open');
    			$('.refuse_memo').off('click', '#refuse_save');
    			$('#refuse_memo').on( "click", '#refuse_save', function(){
            		var memo = $('textarea[name=refuse_memo]').val();
    				var id = $('#woid').val();
    				if(!memo) {
    					$('textarea[name=refuse_memo]').focus();
    					return $('#require_memo').show();
    				}
    				//保存
    				var btn = $(this);
    				btn.attr('disabled', true);
    				workorder.refuse({id: id, reason:memo}, function (ret) {
		            	$.mobile.loading( "hide" );
		            	btn.attr('disabled', false);
	    				if(ret.errcode == 0) {
		            		tip('拒绝派单成功', 1);
		            		setTimeout(function (){
		            			location.href = "#/list/refused/received";
		            		}, 2000);
		            	}else {
		            		tip(ret.errmsg);
		            	}
		            });
    			});
            });
            
            //完成派单
    		$('#complete').click(function(){
				var id = $('#woid').val();
				var memo = $('textarea[name=caption]').val();
				if(!memo) {
					$('textarea[name=caption]').focus();
					return $('#caption_memo').show();
				}
				var ids = [];
				$('input[name="_at_ids[]"]').each(function (i, e){
					ids.push(e.value);
				});
				ids = ids.length > 0 ? ids.join(',') : '';
				var btn = $(this);
				btn.attr('disabled', true);
				workorder.complete({id: id, caption:memo, at_ids: ids}, function (ret) {
	            	$.mobile.loading( "hide" );
	            	btn.attr('disabled', false);
    				if(ret.errcode == 0) {
	            		tip('派单已完成', 1);
	            		setTimeout(function (){
	            			location.href = "#/list/completed/received";
	            		}, 2000);
	            	}else {
	            		tip(ret.errmsg);
	            	}
	            });
            });
            self.swipebox(el);
        },
        //显示图片
        show_attach: function (result, tc_id, el) {
            var self = this;
            if (typeof result.value == "object") {
                if (_.isArray(result.value)) {
                    $.each(result.value, function (k, v) {
                        self.show_attach(v, tc_id);
                    });
                }
                return ;
            }
            if (result && typeof result.url != "undefined") {
                var input = $('<input/>');
                input.attr('name', "_"+tc_id+"[]");
                input.val(result.id);
                input.attr('type', "hidden");
                if (result.isimage == 1) {
                    var image = $('.js-image-sample').clone();
                    image.find('img').attr('src', result.url + '_45');
                    image.append(input);
                    image.removeClass('js-image-sample');
                    image.width(45).height(45);
                    $(image).insertAfter($('.js-image-sample')).show();
                } else {
                    var image = $('.js-media-sample').clone();
                    image.find('.media-heading').text(result.filename);
                    image.find('small i').text(result.filesize);
                    image.append(input);
                    image.removeClass('js-media-sample');
                    console.log(image);
                    $(image).insertAfter($('.js-media-sample')).show();
                }
                $('.js-attach-close').click(function () {
                    $(this).parents('.item').remove();
                    return false;
                });
                self.swipebox($('.mod_photo_uploader'));
            }
        },
        swipebox: function (el) {
        	// 图片预览
    		$('img', el).on('tap', function () {
    			var p = $(this).attr('org');
    			if (!p) {
    				p = $(this).attr('src');
    			}
    			var photo = [];
        		$( 'img', el).each(function () {
        			var pic = $(this).attr('org');
        			if (!pic) {
        				pic = $(this).attr('src');
        			}
        			if (pic) {
        				photo.push({href: pic});
        			}
        			
        			
        		});
        		
    			if (p) {
    				photo = _.filter(photo, function(item){return item.href != p});
    				photo.unshift({href:p});
    				$.swipebox(photo);
    			}
    			
    		});
        }
    };

    return view;
});
