/**
 * H5 微信接口上传图片
 * Create By Deepseath
 * $Author$
 * $Id$
 */

require(["zepto", "frozen", "jweixin"], function ($, fz, wx) {

    /**
     * 上传的图片数
     * @type {number}
     * @private
     */
    var _progress_image_count = 0;

    /**
     * 上传ID顺序号
     * @type {number}
     * @private
     */
    var _uploader_number = 0;

    /**
     * 上传失败的错误报告
     * @type {Array}
     * @private
     */
    var _uploader_error = new Array(1);

    /**
     * 上传失败的图片数
     * @type {number}
     * @private
     */
    var _uploader_error_num = 0;

    /**
     * 用于展示上传后的图片
     * @type {string}
     * @private
     */
    var _tpl_image_show = '<div class="ui-badge-wrap" style="background:#fff" id="<%=id%>">'
        + '<img src="<%=src%>" data-big="<%=src%>" alt="" border="0" class="_uploader_preview" data-aid="<%=src%>" />'
        + '<div class="ui-badge-cornernum _uploader_remove" id="<%=id%>-remove" data-aid="<%=src%>">-</div>'
        + '</div>';

    /**
     * 微信接口人性化错误提示文字解析
     * @private
     */
    var _error_tip = {
        "permission denied": "无权使用该接口",
        "function not exist": "您的微信版本太低，请升级到最新版本",
        "the permission value is offline verifying": "请求微信接口发生错误，请返回后重试",
        "fail": "微信接收上传图片失败，请返回后重试"
    };

    /**
     * 初始化微信js接口是否加载验证完毕
     * @type {boolean}
     * @private
     */
    var _wx_loaded = false;

    /**
     * 上传等待的提示信息对象
     * @type {{}}
     * @private
     */
    var _upload_loading = {};

    // 事件监听
    $(function () {

        // 微信接口加载完毕
        wx.ready(function () {
            _wx_loaded = true;
        });

        // 页面对象
        var $uploader_image = $('#cyoa-body');

        // 点击图片浏览大图
        $uploader_image.on('click', '._uploader_preview', function () {

            // loading
            var __loading = $.loading({
                "content": "请稍候……"
            });
            // 当前点击的图片对象
            var __t = this;
            // 循环检查微信接口是否加载
            var it = setInterval(function () {
                if (_wx_loaded) {
                    __loading.loading("hide");
                    // 预览图片
                    _uploader_preview(__t);
                    clearInterval(it);
                    return false;
                }
            }, 500);
        });

        // 上传图片
        $uploader_image.on('click', '._uploader_add', function () {
            // loading
            var __loading = $.loading({
                "content": "请稍候……"
            });
            // 上传按钮对象
            var __t = this;
            // 循环检查微信接口是否已加载
            var it = setInterval(function () {
                if (_wx_loaded) {
                    __loading.loading("hide");
                    // 上传图片
                    _uploader_add(__t);
                    clearInterval(it);
                    return false;
                }
            }, 500);
        });

        // 移除图片
        $uploader_image.on('click', '._uploader_remove', function () {
            _uploader_remove(this);
        });
    });

    /**
     * 图片预览
     * @param t 点击的对象
     * @private
     */
    function _uploader_preview(t) {

        // 当前点击的图片对象
        var $cur = $(t);
        // 当前点击的图片大图地址
        var cur_src = $cur.attr('data-big');
        // 当前图片上传容器内的所有图片大图列表
        var list_src_obj = $cur.parents('._uploader_box').find('img._uploader_preview').map(function () {
            // 大图地址，检查地址格式
            var _src = $(this).attr('data-big');
            if (_src && _src != 'undefined' && _src.indexOf('loading.gif') < 0) {
                return _src;
            }
        });

        if (typeof(list_src_obj.selector) == 'undefined') {
            return false;
        }

        // 调用微信接口预览图片
        wx.previewImage({
            current: cur_src,
            urls: list_src_obj.selector
        });
    }

    /**
     * 删除指定图片
     * @param t 点击的对象
     * @private
     */
    function _uploader_remove(t) {

        var dia = $.dialog({
            "content": "是否确认删除该图片？",
            "button": ["取消", "确认"]
        });
        dia.on("dialog:action", function (e) {
            if (e.index == 0) {
                // 点击“取消”
                return;
            }

            _uploader_delete($(t).prev('img'));
        });
    }

    /**
     * 移除指定图片而不提醒
     * @private
     */

    /**
     * 移除指定图片而不提醒
     * @param t 当前点击的图片对象
     * @private
     */
    function _uploader_delete(t) {
        //return;
        // 当前点击的图片对象
        var $cur = $(t);
        // 所在容器对象
        var $box = $cur.parents('._uploader_box');
        // 当前图片的附件a_id
        var a_id = $cur.attr('data-aid');
        $cur.parent().remove();
        if (a_id && a_id.match(/^\d+$/) != null) {
            // 附件id存在，请求删除
            $.getJSON('/frontend/attachment/delete/id/' + a_id, function (data) {
                //console.log(data);
            });
            _image_at_ids_change($box, a_id, '-');
        }

        // 已上传的图片数小于最大值，则显示上传按钮
        var uploaded_count = !$box.children('input').val() ? 0 : $box.children('input').val().split(',').length;
        if (uploaded_count < $box.data('max') && $box.find('._uploader_add').css('display') == 'none') {
            $box.find('._uploader_add').show();
        }
    }

    /**
     * 选图并上传
     * @param t 点击的对象
     * @private
     */
    function _uploader_add(t) {

        // 当前图片上传容器对象
        var $uploader_box = $(t).parents('._uploader_box');

        // 初始化当前图片数
        _progress_image_count = $uploader_box.find('img._uploader_preview').length;

        // 选择图片
        wx.chooseImage({
            "success": function (res) {
                /**
                 * 遍历选择的图片文件 ID ，然后通过接口一一上传到微信，获取到 serverid
                 * 利用本地接口通过 serverid 读取微信文件并下载回本地服务器，写入附件表，返回附件信息以及附件ID
                 * 显示图片缩略图（给出预览链接）并将附件ID写入到预设的隐藏文本框内 ……
                 */

                // 超出允许上传的图片数
                if ($(t).attr('data-max') > 0 && _progress_image_count + res['localIds'].length > $(t).attr('data-max')) {
                    $.dialog({
                        "content": "最多只能上传 [" + $(t).attr('data-max') + "] 张图片",
                        "button": ["关闭"]
                    });
                    return;
                }

                // 初始化错误信息
                _uploader_error = [];
                // 初始化上传出错个数
                _uploader_error_num = 0;
                _upload_loading = $.loading({
                    "content": "上传中，请稍候……"
                });

                __upload($uploader_box, t, res['localIds']);
                //console.log(res);
            },
            "fail": function (res) {
                $.dialog({
                    "content": "选择图片发生错误[" + _wx_error(res['errMsg']) + "]",
                    "button": ["关闭"]
                });
            },
            "complete": function (res) {
                //console.log(res);
            },
            "cancel": function (res) {
                //console.log(res);
            }
        });
    }

    /**
     * 具体的图片上传操作
     * @param box 上传容器对象
     * @param t 上传按钮对象
     * @param localIds 待上传的 local id 列表
     * @private
     */
    function __upload(box, t, localIds) {

        setTimeout(function () {

            var $t = $(t);
            // 上传按钮对象
            var $add_btn = $(t);
            // 是否显示微信的上传loading
            //var show_weixin_progress = $add_btn.attr('data-progress') ? true : false;
            // 当前进程待上传的图片ID（第一个图片）
            var local_id = localIds.shift();
            // 缩略图尺寸
            var thumbsize = $add_btn.attr('data-thumbsize');
            // 大图预览尺寸
            var bigsize = $add_btn.attr('data-bigsize');
            // 已上传图片数+1
            _progress_image_count++;

            // 顺序号+1
            _uploader_number++;
            // 当前的ID
            var _cur_id = '-' + _uploader_number;

            // 新增一个loading图片
            box.find('._uploader_image_box').before(_show_image({
                "src": "/misc/images/loading.gif",
                "box": box,
                "id": _cur_id
            }));

            // 当上传图片动作执行时 删除相关文字
            if (_uploader_number == 1) {
                $('.news-pic-state').html('');
            }

            // 当前上传的删除按钮对象
            var $remove = $('#' + _cur_id + '-remove');

            // 启动上传
            wx.uploadImage({
                "localId": local_id,
                "isShowProgressTips": false,//show_weixin_progress,
                "success": function (res) {
                    // 返回的微信媒体文件server_id（media_id）
                    var server_id = res.serverId;
                    // 待发送给本地上传接口的数据
                    var data = {
                        "serverid": server_id,
                        "thumbsize": thumbsize,
                        "bigsize": bigsize
                    };
                    //alert(JSON.stringify(res));
                    // 发送serverid到服务器转换为服务器本地附件信息获取附件ID
                    $.ajax({
                        "url": "/api/attachment/get/aid",
                        "dataType": "json",
                        "type": "GET",
                        "data": data,
                        "success": function (r) {
                            // 读取结果发生错误

                            if (typeof(r['errcode']) == 'undefined') {
                                // 设置出错
                                _set_uploader_error("上传到服务器发生未知网络错误");
                                return false;
                            }
                            // 上传发送错误
                            if (r['errcode'] != 0) {
                                _set_uploader_error(r['errmsg'] + '[Err: ' + r['errcode'] + ']');
                                return false;
                            }
                            // 接口返回的数据
                            var data = r.result;

                            //alert(_cur_id);

                            // 当前上传的图片所在容器
                            var $__cur = $('#' + _cur_id);

                            // 替换对应的图片变量
                            $__cur.find('img').attr({
                                "src": data['thumb'],
                                "data-big": data['big'] + (data['big'].indexOf('?') < 0 ? '?' : '&') + '_t=' + _progress_image_count,
                                "data-aid": data['id']
                            });
                            // 替换对应的删除变量
                            $__cur.find('._uploader_remove').attr({
                                "data-aid": data['id']
                            });


                            // 将aid写入到表单内
                            _image_at_ids_change(box, data['id'], '+');
                            // 已上传图片大于最大允许值则隐藏按钮
                            var uploaded_count = !box.children('input').val() ? 0 : box.children('input').val().split(',').length;
                            if (uploaded_count >= box.data('max')) {
                                $add_btn.hide();
                            }
                        },
                        "error": function () {
                            _uploader_delete($remove);
                        },
                        "complete": function () {

                        }
                    });
                },
                "fail": function (res) {
                    //console.log(res);
                    // 设置出错
                    //alert('faile:' + JSON.stringify(res));
                    _set_uploader_error(_wx_error(res.errMsg));
                    //_uploader_delete($remove);
                },
                "complete": function (res) {
                    //alert('complete:' + JSON.stringify(res));
                    //console.log(res);
                    var err_msg = res.errMsg;
                    // 如果上传不成功，则清理已传入的数据
                    if (err_msg.match(/:\s*ok$/i) == null) {
                        _uploader_delete($remove);
                    }
                    if (0 < localIds.length) {

                        // 延迟请求上传确保频率问题
                        __upload(box, t, localIds);
                    } else {

                        _upload_loading.loading("hide");

                        // 全部上传完毕，存在错误提示则输出
                        if (_uploader_error && _uploader_error_num > 0) {
                            $.dialog({
                                "content": '上传失败 ' + _uploader_error_num + ' 张，原因：<br />' + _uploader_error.join('<br />'),
                                "button": ["关闭"]
                            });
                        }
                    }
                },
                "cancel": function () {
                    //console.log(res);
                    _uploader_delete($remove);
                }
            });
        }, 571);
    }

    /**
     * 解析微信的错误返回代码为可读错误信息
     * @param str
     * @returns {*}
     * @private
     */
    function _wx_error(str) {
        var errcode = str.split(':')[1];
        if (errcode && typeof(_error_tip[errcode]) != 'undefined') {
            return _error_tip[errcode] + '(' + str + ')';
        }

        return str;
    }

    /**
     * 显示一个图片
     * @returns {string}
     * @private
     */
    function _show_image(data) {
        return $.tpl(_tpl_image_show, data);
    }

    /**
     * 改变附件ID
     * @param box
     * @param atid
     * @param method
     * @returns {string}
     * @private
     */
    function _image_at_ids_change(box, id, method) {

        var $input = box.find('input');
        // 原有的 at_ids 字符串
        var at_ids = $input.val();
        at_ids = ',' + at_ids + ',';
        if (method == '+') {
            // 新增一个id
            at_ids += ',' + id + ',';
        } else {
            // 移除一个id
            at_ids = at_ids.replace(',' + id + ',', '');
        }
        // 清理多余的“,”并整理数据
        at_ids = at_ids.replace(/\s+/, '');
        at_ids = at_ids.replace(/,{2,}/, ',');
        at_ids = at_ids.replace(/^,/, '');
        at_ids = at_ids.replace(/,$/, '');
        // 赋值
        $input.val(at_ids);

        return '';
    }

    function _set_uploader_error(errmsg) {

        if (errmsg) {
            _uploader_error_num++;
        }

        if (errmsg && $.inArray(errmsg, _uploader_error) < 0) {
            _uploader_error.push(errmsg);
        }
    }

});
