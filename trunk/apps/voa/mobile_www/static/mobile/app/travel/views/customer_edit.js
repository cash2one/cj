define(["views/base", "utils/formvalidate", "widgets/customized_form", "data/customer", "utils/render", "text!templates/customer_edit.html", "underscore", 'jquery'
        , "css!styles/customer_edit.css", "swipebox"], function(base, formvalidate, customized_form, customer, render, tpl_edit, _, $){
		
    function view() {
    	base.call(this);
    }
    view.prototype = Object.create(base.prototype);
    view.prototype.constructor = view;
    view.prototype = $.extend(view.prototype, {
        page: null,
        detail: null,
        back_url: '#/customers_list',
        // 模板处理
        render: function(args) {
            var dataid = '';
            if (args.dataid) {
                dataid = args.dataid;
                this.detail = customer.get_detail({dataid: dataid, edit: 1});
                this.back_url = '#/customer_detail/'+dataid;
            }
            var goods_id = args.goods_id;
            if (goods_id) {
            	this.back_url = '#/goods_list';
            }
            var self = this;
            var cate = customer.get_cates_list({limit: -1});

            // 数据业务处理处
            // new 械板渲染类  
            var r = new render();
            // 模板内容
            r.template = tpl_edit;
            // 分配变量
            r.vars = {back_url: this.back_url, 'goods_id': goods_id, cate: cate.data, dataid: dataid};
            r.only_return_element = true;
            // 应用, 返回当前element 节点
            this.page = r.apply();

            // 监听事件
            self.event();

            this.page = r.change(this.page.html());
        }, 
        option_data: null,
        table_col: null,
        // 监听事件   
        event: function () {
            var self = this;
            var form = new customized_form();
            form.page = this.page;
            form.el_container_id = "#js-row-input";
            form.options_data = customer.get_table_col_opt();
            //this.options_data = customer.get_table_col_opt();
            this.table_col = customer.get_table_col();
            $.each(this.table_col, function (key, item) {
                item.value = '';
                if (self.detail) {
                    if (item.field) {
                        item.value = self.detail[item.field];
                    } else {
                        item.value = self.detail["_"+item.tc_id];
                    }
                }                
                form.render(item);
            });
            if (self.detail) {
                if (self.detail.classid) {
                	this.page.find('[name=classid] option[value='+self.detail.classid+']').attr('selected', 'selected');
                    /*this.page.find('[name=classid] option').each(function() {
                        if ($(this).val() == self.detail.classid) {
                            $(this).attr('selected', 'selected');
                        }
                    });*/
                }
            }
            $( document ).on( "pageshow", this.page, function() {
            	
            	//self.swipebox(self.page);
            	
            	$('.js-btn-save').unbind('click');
            	$('.js-btn-save').click(function(){
                	// 表单验证
                	var validate = new formvalidate();
                	validate.check($('#js-form').find('select, input, textarea'), function(input, error_type, msg) {
                		
                		self.tips('#form-error', $(input).attr('fieldname')+msg, function () {
                			$(input).focus();
                    		$(input).parents('.ui-field-contain').addClass('error').click(function() {
                    			$(this).removeClass('error');
                    		});
                		});
                	}, function (input) {
                		$(input).parents('.ui-field-contain').removeClass('error');
                	})
                	// 验证结束
                	if (!validate.error) {
	                	$.mobile.loading( "show" );
	                    customer.save($('#js-form').serialize(), function (ret) {
	                    	$.mobile.loading( "hide" );
	                    	if (ret.errcode == '0') {
	                    		location.href = self.back_url;
	                    	} else {
	                    		self.tips('#form-error', ret.errmsg);
	                    	}
	                    });
                	}
                    return false;
                });
            });

            //customer.get_table_col
            
        },
    });

    return view;
});
