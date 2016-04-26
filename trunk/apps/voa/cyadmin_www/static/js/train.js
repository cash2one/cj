/**
 * Train js
 * @Author liyongjian
 */
(function($) {
    "use strict";
    $.train = {
        'dialogFormElem': null,
        formHash: null,
        setting_add: function() {

            var jqBtn = $("#js_train_setting_btn"),
                template = $('#js_train_setting_dialog').text();
            $.train.item_tpl = $("#js_train_item_tpl").text();
            $.train.item_box = $(".train-item-ul");

            jqBtn.on('click', function() {

                $.dialog({
                    'width': 400,
                    'id': 'train'
                }).title('添加报名项').content(template);

            });

            $('body').on('click', '#js_train_close', function() {

                $.dialog({
                    'id': 'train'
                }).close();

            });

            $("body").on('click', '#js_train_add', function() {

                $.train.formHash = $(this).data('hash');
                $.train.__settingForm();

            });

            $("body").on('click', '.js_train_item_del', function() {

                $.train.formHash = $(this).data('hash');
                var sid = $(this).data('value');
                $.train.__settingDel(sid);

            });

        },
        __settingForm: function() {

            var NewData = $("#js_train_setting_form").serializeArray();

            var data = {
                'action': 'add',
                'formhash': $.train.formHash,
                'data': NewData
            };

            function callback(result) {

                if (1 === result.status) {

                    var data = result['data'];

                    var append = $.train.item_tpl.replace(/fieldname/g, data['fieldname']).replace(/filed_id/g, data['sid']);
                    $.train.item_box.append(append);

                    $.dialog({
                        'id': 'train'
                    }).close();
                }
            }

            this.__ajax(data, callback);
        },
        __ajax: function(data, callback) {

            $.ajax({
                'url': '/content/train/setting',
                'data': data,
                'type': 'POST',
                'dataType': 'json'
            }).done(function(result) {

                callback(result);

            });

        },
        __settingDel: function(sid) {

            var data = {
                'formhash': $.train.formHash,
                'data': {
                    'sid': sid
                },
                'action': 'del'
            };

            function callback(result) {

                if (1 === result.status) {

                    var selector = '.train-item-ul__li[data-value="' + sid + '"]';

                    $(selector).remove();

                }

            };

            var delText = '<span>确定删除这条数据吗?</span>';
            $.dialog({
                'id': 'train',
                'okValue': '确定',
                'cancelValue': '取消',
                ok: function() {
                    $.train.__ajax(data, callback);
                },
                cancel: function() {

                }
            }).content(delText);

        },
        SelectField: function() {

            var jqSelectbtn = $(".js_train_item_select");

            jqSelectbtn.on('click', function() {

                var $this = $(this),
                    is_select = $this.data('select'); // 是否可选{true|false}

                if (is_select) {

                    if ($this.hasClass('train-item-ul__cur')) {

                        $this.removeClass('train-item-ul__cur');

                    } else {

                        $this.addClass('train-item-ul__cur');

                    }

                }


            });

            var signids = [],
                jqSignElem = $("#js_sign_fields"); // 存储已选择的报名信息

            $("#publish_btn,#draft_btn").on('click', function() {

                var hasSignFields = $(".train-item-ul__cur");

                hasSignFields.each(function() {

                    signids.push($(this).data('value'));

                });

                jqSignElem.val(signids.join(','));

            });


        },
        setting_edit: function() {

            var html = $("#js_train_setting_edit").html(),
                jqForm,
                formHash = $('.js_train_item_del').data('hash'),
                jqInput,
                jqSelf;

            function callback(result) {

                if (result['status']) {

                    window.location.reload();
                }

            }

            $(".js_train_item_link").on('click', function() {

                var $this = $(this),
                    text = $this.text(),
                    id = $this.data('id'),
                    required = $this.data('required'),
                    type = $this.data('type');

                jqSelf = $this;

                function okFunc() {

                    var form = {
                        'action': 'edit',
                        'data': jqForm.serializeArray(),
                        'formhash': formHash
                    };

                    $.train.__ajax(form, callback);
                }

                $.dialog({
                    'id': 'train',
                    'okVal': '确定',
                    'cancelVal': '取消',
                    'ok': okFunc,
                    'cancel': function() {

                    }
                }).content(html);

                jqForm = $("#js_train_setting_form");

                jqForm.find('input[type="text"]').val(text);
                jqForm.find('input[name="sid"]').val(id);

                if (required) {

                    jqForm.find('input[name="is_required"]').prop('checked', true);
                }

                if (type) {

                    jqForm.find('input[value="' + type + '"]').prop('checked', true);
                }

            });

        }
    };
})(jQuery);