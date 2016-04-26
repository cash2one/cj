define(["text!widgets/templates/customized_form_tablecol.html", "underscore", 'jquery', 'ueditor', "jqueryui", "datetimepicker",
        "jquery.fileupload-validate"
        ], function(default_form_coltype_tpl, _, $){
	
	function customized_web_form() {
	}
	
	customized_web_form.prototype = {
		coltype_template: null,
		options_data: null,
		columntype_list: null,
		container: null,
		goods: null,
		show_attach: function (result, tc_id, tr) {
			var self = this;
			if (typeof result.value == "object") {
				if (_.isArray(result.value)) {
					$.each(result.value, function (k, v) {
						self.show_attach(v, tc_id, tr);
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
					var image = tr.find('.js-image-sample').clone();
					image.find('img').attr('src', result.url + '/100');
					image.append(input);
					image.removeClass('js-image-sample');
					$(image).insertAfter(tr.find('.js-image-sample')).show();
				} else {
					var image = tr.find('.js-media-sample').clone();
					image.find('.media-heading').text(result.filename);
					image.find('small i').text(result.filesize);
					image.append(input);
					image.removeClass('js-media-sample');
					$(image).insertAfter(tr.find('.js-media-sample')).show();
				}
				$('.js-attach-close').click(function () {
					$(this).parents('.item').remove();
					return false;
				});
			}
		},
		make_uedit: function (tc_id) {
			 var ueobj = "_ue_"+tc_id;
	       	 
	            if (window[ueobj]) {
	            	window[ueobj].destroy(); 
	            }
	            setTimeout(function () {
	            window[ueobj] = UE.getEditor('_'+tc_id, {
	                    //UE.getEditor('_'+item.tc_id, {
	                    	serverUrl: '/admincp/ueditor/',
	                    	textarea: '_'+tc_id,
	                    	UEDITOR_HOME_URL: '/misc/ueditor/',
	                        toolbars: [
	                                   ['fullscreen', 'source', '|', 'undo', 'redo', '|',
	                                    'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
	                                    'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
	                                    'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
	                                    'directionalityltr', 'directionalityrtl', 'indent', '|',
	                                    'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 
	                                    'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
	                                    'simpleupload', 'insertimage','scrawl', 'attachment', 'map', 'insertframe', 'background', '|',
	                                    'horizontal', 'spechars', 'snapscreen', 'wordimage', '|',
	                                    'inserttable', 'deletetable', 'charts', '|',
	                                     'preview', 'searchreplace']
	                               ],
	                               initialFrameWidth:"100%",
	                               autoHeightEnabled: true,
	                               autoFloatEnabled: true
	                           });
	            }, 300);
		},
		
		make_attach: function (item) {
			//console.log(item);
			// 文件类型 判断未加    item.ftype   1,文本文件 ， 2图片， 3 音频， 4 视频
			var self = this;
			this.show_attach(item, item.tc_id, $('#_tr_'+item.tc_id));
        	//$('.fileupload').fileupload();
			// 同时上传的最大的文件数
			$('#_tr_'+item.tc_id).data('max', item.max);
			// 已经上传的文件数。
			$('#_tr_'+item.tc_id).data('upload-process-total', 0);

        	$('#_tr_'+item.tc_id).find('.fileupload').fileupload({
                // Uncomment the following to send cross-domain cookies:
                //xhrFields: {withCredentials: true},
                dataType: 'json',
                url: '/api/attachment/post/upload/',
                maxFileSize: 5000000,
				//limitMultiFileUploads : item.max,
				//singleFileUploads: false,
				acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                //acceptFileTypes: /(\.|\/)(xls)$/i,
				// 开始上传时
				start: function (e) {
				},
				// 上传结束时 指是所有文件
				stop: function (e) {
					$('#_tr_'+item.tc_id).data('upload-process-total', 0);
				},
				add: function (e, data) {
					//console.log(data);
					// data.files[0] = {name, size, type: 'image/jpeg',
					var max = $('#_tr_'+item.tc_id).data('max');
					if (max > 0) {
						$('#_tr_'+item.tc_id).data('upload-process-total', $('#_tr_'+item.tc_id).data('upload-process-total') + 1);
						if ($('#_tr_'+item.tc_id).data('upload-process-total')  <= max ) {
							// 文件单个上传提交  each other
							data.submit();
						} else {
							alert("每次只能上传"+max+"个文件");
						}
					} else {
						alert("提示：该类型允许上传的文件为0，如要上传文件请更改配置的文件数量大于0。");
					}

				},
				submit: function (e, data) {
					console.log('submit', arguments);

					//return false;
				},
				fail: function (e, data) {
					console.log('fail', arguments);
					//return false;
				},
				progressall: function (e, data) {

                    var progress = parseInt(data.loaded / data.total * 100, 10);
					//console.log(progress);
					/*
                    $(this).find('.fileinput-button').find('span').eq(0).text('正在上传中，进度：'+progress + '%');
                    if (progress == 100) {
                        $(this).find('.fileinput-button').find('span').eq(0).text('上传完成，正在处理请稍等。。。');
                    }*/
                },
                done: function (e, data) {
                	var result = data.result;
                	if (result.errcode == 0) {
                		result = result.result;
						if (item.max) {
							if ($('#_tr_'+item.tc_id).find('.item:visible').length >= item.max) {
								$('#_tr_'+item.tc_id).find('.item:last').remove();
							}
						}
                		self.show_attach(result, item.tc_id, $(this).parents('tr'));
                	} else {
                		alert(result.errmsg);
                	}
                }
            });
		},
		
		make_form_row: function (item) {
			
			// 查找本字段的选项
			item.options_data = _.filter(this.options_data, function (opt) {return opt.tc_id == item.tc_id});
			if (item.ct_type == 'checkbox') {
				if (item.value.length) {
					if (typeof item.value == 'string') {
						item.value = item.value.split(',');
					}
				} else {
					item.value = '';
				}
			}
			var template = _.template(this.coltype_template);
	        var html = template(item);
	        var tr = $('<tr/>');
	        tr.attr('id', '_tr_'+item.tc_id);
	        tr.append(html);
	        
	        $('body').find(this.container).append(tr);

	        if (item.ct_type == 'text' && item.ftype == "2") {
	        	this.make_uedit(item.tc_id);
	       } else if (item.ct_type == 'attach') {
	        	this.make_attach(item);
	        } else {
	        	$('.input-time').datetimepicker({
	  	      	  datepicker:false,
	  	      	  lang:'ch',
	  	      	  format:'H:i',
	  	      	  mask:true
	  	      	});
	  	        $('.input-datetime').datetimepicker({
	  	        	  format:'Y-m-d H:i',
	  	        	  lang:'ch',
	  	        	  mask:true
	  	        	});
	  	        $('.input-date').datetimepicker({
	  	      	  format:'Y-m-d',
	  	      	  timepicker:false,
	  	      	  lang:'ch',
	  	      	  mask:true
	  	      	});
	        }
		},
		render: function () {
			//this.options_data = api.get('goodstablecolopt');
			//this.columntype_list = api.get('goodstablecol');

			if (!this.columntype_list) {
				this.error_msg =  '字段数据不能为空';
				
				return false;
			}
			if (null == this.coltype_template) {
				this.coltype_template = default_form_coltype_tpl;
			}
			
			var self = this;
	        $.each(this.columntype_list, function (key, item) {
	        	if (!_.isEmpty(self.goods)) {
	        		if (item.field) {
		        		item.value = self.goods[item.field];
					} else {
						item.value = self.goods["_"+item.tc_id];
					}
	        	} else {
	        		item.value = '';
	        	}
	        	
	        	
	        	self.make_form_row(item);
	    	});
		}
	        
	};

	return  customized_web_form;
});
