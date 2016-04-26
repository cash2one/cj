define(["text!widgets/templates/customized_form.html", "underscore", 'jquery',
        "jquery.fileupload-validate"
        ], function(customized_form, _, $){
	
    function widget() {

    }

    widget.prototype = {
        page: null,
        options_data: null,
        el_container_id: '#js-form',
        render: function (item) {
            var self = this;
            // 查找本字段的选项
            item.options_data = _.filter(this.options_data, function (opt) {return opt.tc_id == item.tc_id});
            if (item.ct_type == 'checkbox') {
                if (item.value && typeof item.value == 'string') {
                    item.value = item.value.split(',');
                }
            }
            var template = _.template(customized_form);
            var html = template(item);
            var tr = $('<div class="ui-field-contain" />');
            tr.attr('id', '_tr_'+item.tc_id);
            tr.append(html);
            if (item.ct_type == 'text' && item.ftype == "2") {
                /*
                var ueobj = "_ue_"+item.tc_id;
             
                if (window[ueobj]) {
                    window[ueobj].destroy(); 
                }
                setTimeout(function () {
                window[ueobj] = UE.getEditor('_'+item.tc_id, {
                        //UE.getEditor('_'+item.tc_id, {
                            serverUrl: '/admincp/ueditor/',
                            textarea: '_'+item.tc_id,
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
                }, 300);*/
                
                //window[ueobj].destroy(); 
            }
            this.page.find(this.el_container_id).append(tr);
            
            if (item.ct_type == 'attach') {
                self.show_attach(item, item.tc_id, $('#_tr_'+item.tc_id));
                //$('.fileupload').fileupload();
                $( document ).on( "pagecreate", this.page, function() {
                    
                    $('#_tr_'+item.tc_id).find('.fileinput-button').fileupload({
                        // Uncomment the following to send cross-domain cookies:
                        //xhrFields: {withCredentials: true},
                        dataType: 'json',
                        url: '/api/attachment/post/upload/',
                        maxFileSize: 5000000,
                        maxNumberOfFiles : 1,
                         acceptFileTypes: /(\.|\/|)(gif|jpe?g|png|)$/i,
                        //acceptFileTypes: /(\.|\/)(xls)$/i,
                        progressall: function (e, data) {
                            //console.log(data);
                            /*
                            var progress = parseInt(data.loaded / data.total * 100, 10);
                            $(this).find('.fileinput-button').find('span').text('正在上传中，进度：'+progress + '%');
                            if (progress == 100) {
                                $(this).find('.fileinput-button').find('span').text('上传完成，正在处理请稍等。。。');
                            }*/
                        },
                        done: function (e, data) {
                            var result = data.result;
                            if (result.errcode == 0) {
                                result = result.result;
                                self.show_attach(result, item.tc_id, $(this).parents('.ui-field-contain'));
                            } else {
                                alert(result.errmsg);
                            }
                        }
                    });
                    //$('#_tr_'+item.tc_id).find('.fileinput-button').bind('fileuploadprogress', function (e, data) {
                        // Log the current bitrate for this upload:
                      //       console.log(data.bitrate);
                     //});
                });
            }
           
        },
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
                    image.addClass('item');
                    image.find('img').attr('src', result.url + '-100');
                    image.append(input);
                    image.removeClass('js-image-sample');
                    $(image).insertAfter(tr.find('.js-image-sample')).show();
                } else {
                    var image = tr.find('.js-media-sample').clone();
                    image.addClass('item');
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
        }
        

    };

    return widget;
});
