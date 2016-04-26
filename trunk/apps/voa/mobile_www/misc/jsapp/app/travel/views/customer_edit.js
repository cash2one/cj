define(["utils/formvalidate", "widgets/customized_form", "data/customer", "utils/render", "text!templates/customer_edit.html", "underscore", 'jquery'
        , "css!styles/customer_edit.css"], function(formvalidate, customized_form, customer, render, tpl_edit, _, $){
		
    function view() {

    }

    view.prototype = {
        page: null,
        detail: null,
        // 模板处理
        render: function(args) {
            var dataid = '';
            if (args.dataid) {
                dataid = args.dataid;
                this.detail = customer.get_detail({dataid: dataid});
            }
            var goods_id = args.goods_id;
            var self = this;
            var cate = customer.get_cates_list({limit: -1});

            // 数据业务处理处
            // new 械板渲染类  
            var r = new render();
            // 模板内容
            r.template = tpl_edit;
            // 分配变量
            r.vars = {'goods_id': goods_id, cate: cate.data, dataid: dataid};
            r.only_return_element = true;
            // 应用, 返回当前element 节点
            this.page = r.apply();

            // 监听事件
            self.event();

            r.change(this.page.html());
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
            $( document ).on( "pagecreate", this.page, function() {
                $( document ).on( "click", '.js-btn-save', function(){
                	// 表单验证
                	var validate = new formvalidate();
                	validate.check($('#js-form').find('select, input, textarea'), function(input, error_type, msg) {
                		$(input).focus();
                		$(input).parents('.ui-field-contain').addClass('error');
                		//alert($(input).attr('fieldname')+msg);
                	}, function (input) {
                		$(input).parents('.ui-field-contain').removeClass('error');
                		//console.log(input, 'ok');
                	})
                	// 验证结束
                	if (!validate.error) {
	                	$.mobile.loading( "show" );
	                    customer.save($('#js-form').serialize(), function (ret) {
	                    	$.mobile.loading( "hide" );
	                    	if (ret.errcode == '0') {
	                    		location.href = "#/goods_list";
	                    	} else {
	                    		
	                    		alert(ret.errmsg);
	                    	}
	                    });
                	}
                    return false;
                });
            });

            //customer.get_table_col
            
        },
    };

    return view;
});
