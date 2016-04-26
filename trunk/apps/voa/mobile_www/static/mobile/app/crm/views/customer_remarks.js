define(["views/customer_detail", "utils/render", "text!templates/customer_remarks.html", 'jquery', 'data/customer'
         , "css!styles/customer_remarks.css" , "swipebox"], function(customer_detail, render, tpl, $, customer){
	
	function view() {
		customer_detail.call(this);
    }
    view.prototype = Object.create(customer_detail.prototype);
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
                // new 械板渲染类  
                var r = new render();
                // 模板内容
                r.template = tpl;
                // 应用, 返回当前element 节点
                r.only_return_element = true;
                self.page = r.apply();
                
                customer.get_remark({customer_id: dataid}, function (ret) {
                	$.each(ret.data, function (k, row) {
                		self.append_remark(row);
                	});
                	self.page = r.change(self.page.html());
                    // 监听事件
                    self.event();
                });
                
               
            }
        	
            
        },
        // 监听事件   
        event: function () {
        	var self = this;
        	$( document ).on( "pageshow", self.page, function() {
        		self.swipebox(self.page);
        		$('.js-add-comment', self.page).on('click', function () {
        			$('#js-add-comment-popup', self.page).popup('open');
        			
	            });
        		$('#remark_yes', self.page).unbind( "click" );
    			$('#remark_yes', self.page).click(function () {
    				var message = $('#remark').val();
    				if (message.length && self.customer_id) {
    					customer.remark_save({message: message, customer_id: self.customer_id}, function (ret) {
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
        	});
        }
    });

    return view;
});
