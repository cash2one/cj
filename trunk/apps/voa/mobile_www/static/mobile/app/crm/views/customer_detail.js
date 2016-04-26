define(["views/base", "utils/render", "text!templates/customer_detail.html", 'jquery', 'data/customer'
         , "css!styles/customer_detail.css", "jquery.fileupload-validate", "swipebox"], function(base, render, tpl, $, customer){
	
	function view() {
    	base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    view.prototype = $.extend(view.prototype, {
    	detail: null,
    	customer_id: null,
        // 模板处理
        render: function(args) {
        	var self = this;
        	if (args.id) {
        		this.customer_id = args.id;
                dataid = args.id;
                this.detail = customer.get_detail({dataid: dataid});
                // new 械板渲染类  
                var r = new render();
                // 模板内容
                r.template = tpl;
                // 分配变量
                r.assign('goods',  this.detail);
                r.assign('table_cols',  customer.table_cols);
                // 应用, 返回当前element 节点
                r.only_return_element = true;
                self.page = r.apply();
                customer.get_buy_hist({cid: dataid}, function (ret) {
                	$.each(ret.data, function (k, row) {
                		self.append_follow_list(row);
                	});
                });
                customer.get_remark({limit: 5, customer_id: dataid}, function (ret) {
                    
                	if (ret.data.length < 5) {
                		$('.js-remark-more', self.page).hide();
                	}
                	$.each(ret.data, function (k, row) {
                		self.append_remark(row);
                	});
                	self.page = r.change(self.page.html());
                    // 监听事件
                    self.event();
                });
                
               
            }
        	
            
        }, 
        append_follow_list: function (row) {
        	var self = this;
        	var item = $('.js-follow-list', self.page).find('.js-item').clone();
        	item.removeClass('js-item');
    		item.find('a').text(row.goods_name);
    		item.find('a').attr('href', row.detail_url);
    		item.show();
    		item.insertAfter($('.js-follow-list', self.page).find('.js-item'));
    		//item.insertAfter($('.js-remark-list .ui-field-contain:last-child', self.page));
        },
        append_remark: function (row) {
        	
        	var self = this;

        	if (row.crk_type == '2') {
        		var item = $('.js-remark-list', self.page).find('.js-image').clone(true, true);
        		item.removeClass('js-image');
        		if (row.attachs) {
        			if (row.attachs.length) {
        				item.find('img').attr('src', row.attachs[0].url+'/45');
						item.find('img').attr('data-src', row.attachs[0].url);
        			}
        			
        		} else {
        			item.find('img').attr('src', row.image+'/45');
					item.find('img').attr('data-src', row.image);
        		}
        		
        	} else {
        		var item = $('.js-remark-list', self.page).find('.js-item').clone();
        		item.find('.message').text(row.message);
        		item.removeClass('js-item');
        	}        	
        		
    		item.find('.updated_date').text(row.updated_date);
    		
    		
    		item.insertAfter($('.js-remark-list', self.page).find('.js-item'));
    		item.show();
			//self.swipebox(self.page);
    		//item.insertAfter($('.js-remark-list .ui-field-contain:last-child', self.page));
        },
        // 数据业务处理处
        data: function () {
            //data/goods  <%=a%>
            var data = {a: 1, b:2};
            //var goods = new goods();
            //data = goods.list();
            return data;
        },
        // 监听事件   
        event: function () {
        	var self = this;
        	$( document ).on( "pageshow", self.page, function() {
        		// 图片查看插件
        		self.swipebox(self.page);
        		
        		$('.js-add-comment', self.page).on('click', function () {
        			$('#js-add-comment-popup', self.page).popup('open');
        			
	            });
        		$('#remark_yes', self.page).unbind( "click" );
    			$('#remark_yes', self.page).click(function () {
    				var message = $('#remark').val();
    				if (message.length && self.customer_id) {
    					customer.remark_save({message: message, crk_type: 1, customer_id: self.customer_id}, function (ret) {
        					if (ret.errcode == '0') {
        						$('#js-add-comment-popup', self.page).popup('close');
        						var date = new Date();
        	                    var updated_date = date.getUTCFullYear()+'-'+(date.getUTCMonth()+1)+'-'+date.getUTCDate();
        						self.append_remark({message: message, updated_date: updated_date});
        						$('#remark').val('');
        					} else {
        						//显示错误
        					}
        				});
    				} else {
    				}
    				return false;
    			});

/* multiple 不支持 android
				$('.fileinput-button', self.page).on('click', function () {
					if (confirm('一次上传多张图片?')) {
						$('.fileinput-button', self.page).find('.fileupload').prop('multiple', true);
					} else {
						$('.fileinput-button', self.page).find('.fileupload').prop('multiple', false);
					}
				});

    			*/
    			$('.fileinput-button', self.page).fileupload({
                    // Uncomment the following to send cross-domain cookies:
                    //xhrFields: {withCredentials: true},
                    dataType: 'json',
                    url: '/api/attachment/post/upload/',
                    maxFileSize: 5000000,
                    maxNumberOfFiles : 1,
                     acceptFileTypes: /(\.|\/|)(gif|jpe?g|png|)$/i,
                    //acceptFileTypes: /(\.|\/)(xls)$/i,
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
                            customer.remark_save({attachids: result.id, crk_type: 2, customer_id: self.customer_id}, function (ret) {
	                            var date = new Date();
	    	                    var updated_date = date.getUTCFullYear()+'-'+(date.getUTCMonth()+1)+'-'+date.getUTCDate();
	    						self.append_remark({crk_type: 2, image: result.url, updated_date: updated_date});
	    						//self.swipebox(self.page); 有问题，暂时注释
                            });
                            //self.show_attach(result, item.tc_id, $(this).parents('.ui-field-contain'));
                        } else {
                            //alert(result.errmsg);
                        }
                    }
                });
        	});
        }
    });

    return view;
});
