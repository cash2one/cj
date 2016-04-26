/*
* 审批流程js
* @@uthor liyongjian
* */
(function ($) {  
    'use strict';
    $.askfor = {
        init: function () {
            this.__visual();
            this.__person();
            this.__custom();
            this._addJob();

        },
        /**
        * 添加审批流程,左侧可视化
        * */
        __visual: function () {

            $('body').on('keyup click', '.js-askfor-input', function (event) {

                var $this = $(this),
                    visual = $this.attr('ask-visual'),
                    text = $this.val(),
                    type = $this.attr('type'),
                    index = $this.attr('ask-index'),
                    selector = $('.js-askfor-visual[ask-visual="' + visual + '"]'),                    
                    del_index = $this.attr('ask-del');

                // keyup事件
                (function () {
                    // 如果事件为keyup，input的type为text时，右边的input在输入时，同步到左边
                    if ('keyup' === event.type && 'text' === type) {                        

                        if (index) {

                            selector = $('.js-askfor-visual[ask-visual="' + visual + '"][ask-index="' + index + '"]');
                        }

                        selector.text(text);                        
                    }
                })();

                // click事件
                (function () {

                    if ('click' === event.type) {

                        // 当input的type为radio时,控制左侧上传图片是否显示
                        if ('radio' === type) {

                            var show_condition = selector.attr('ask-show');
                            if (show_condition == text) {
                                selector.css('display', 'block');
                            } else {
                                selector.hide();
                            }

                        }

                    }

                })();

            });
        },
        /**
        * 添加审批人,及删除审批人
        * */
        __person: function () {

            var box = $('.js-askfor-visual[ask-visual="person"]');
            $.askfor.person_box = box;
            function ClickAddPerson(val) {                
                $.askfor.nameList = {};
                 var nameArr = [],
                    personStr = null,
                    name;

                $('.js-askfor-approver input[type="hidden"]').each(function (index) {
                    var $this = $(this),
                        uid = $this.val();
                    var data = $this.parent().find('span').text();                    
                    
                    if (uid != val) {
                        $.askfor.nameList[data] = index
                    }
                });                
                for (name in $.askfor.nameList) {

                    if ($.askfor.nameList.hasOwnProperty(name)) {

                        nameArr.push(name);
                    }

                }

                personStr = nameArr.join('>');
                box.text(personStr);
            }
            $.askfor.personAddFn = ClickAddPerson;
            // 点击选择审批人，弹窗里的radio时,同步左侧添加审批人
            $('body').on('click', '.js-input-tr', function (event) {

                setTimeout(ClickAddPerson, 500);

            });

            function DelPerson() {
                /*
                 * 点击审批人名,进行删除审批人
                 * */
                $('body').on('mouseup', '.js-askfor-approver .js-contacts-form-row a', function (event) {

                    var $this = $(this),
                        val = $this.find('input[type="hidden"]').val();

                    ClickAddPerson(val);

                });
                /*
                 * 当点击审批人删除按钮，删除审批人
                 * */
                var PersonTime;
                $('body').on('click', '.js-person-del', function () {

                    var $this = $(this),
                    contact = $('.js-contact-radio:checked').val();
                    // 监听当点击,确定,删除审批人按钮时，同步删除审批人                    
                    function Del() {

                        if ($.askfor.delPerson) {    

                            if(1 == contact){
                                var val = $this.parents('.form-group').eq(0).find('input[type="hidden"]').val(); 
                                ClickAddPerson(val);      
                                                      

                            }else if(2 == contact){

                                $.askfor.jobEach();
                            }
                            
                            clearInterval(PersonTime);

                        }
                    }

                    PersonTime = setInterval(Del, 300);                     
                });

            }

            setTimeout(DelPerson, 100);

        },
        /**
        * 添加自定义字段
        * */
        __custom: function () {
            // 添加自定义字段按钮
            var jqBtn = $('.js-custom-field');

            // 获取最后一个,自定义字段的显示文本框
            function readDom() {

                return $('.js-custom-read:last');
            }
            // 根据已选择的,自定义字段文本,添加到,新增的自定义字段,做为显示文本
            function setText() {

                var jq = $('.js-custom-read[data-mark="' + $.askfor.custom_type + '"]');
                jq.val($.askfor.custom_text);
            }
            // 把已选择的,自定义字段类型,写入到属性中
            function setMark() {

                readDom().attr('data-mark', $.askfor.custom_type);
            }

            // 初始化
            (function () {

                // 获取默认,在select中选中的option
                var selector = $('.js-custom-select option:selected');
                
                /*var couple = Boolean(selector.attr('data-couple'));
                $.askfor.custom_couple = couple;*/
                // 获取在select中默认的option的文本
                $.askfor.custom_text = selector.text();
                // 获取在select默认的自定义字段的类型
                $.askfor.custom_type = selector.attr('data-type');
                // 获取在selet默认的自定义字段的value
                $.askfor.custom_val = selector.val();
                // 是否能继续添加自定义字段
                $.askfor.custom_is_add = true;
                // 是否添加了一对时间、日期、时间+日期类型的自定义字段
                $.askfor.is_addTime = false;
                /**
                * 好像没用了
                * */
                /*if ('edit' === $.askfor.page) {
                    return false;
                }
                setMark();
                setText();*/
            })();
            // 选择自定义字段类型
            $('.js-custom-select').on('change', function () {

                var $this = $(this),
                    chooesOption = $this.find('option:selected');

                /*var = couple = Boolean(chooesOption.data('couple'));
                $.askfor.custom_couple = couple;*/
                // 获取选择项的本文
                $.askfor.custom_text = chooesOption.text();                
                // 获取选择项的字段类型
                $.askfor.custom_type = chooesOption.attr('data-type');
                // 获取该类型最大添加个数值
                $.askfor.custom_length = chooesOption.attr('data-length');
                // 获取选择项的value
                $.askfor.custom_val = chooesOption.val();
                // 错误提示
                $.askfor.__customError(errorBox, jqBtn);
                /**
                * 如果已经添加了一对时间、日期、日期+时间，类型的字段,及当前选择项是类型为time时,可以进行,选项的联动
                * 在这时间、日期、日期+时间，类型的字段,选项改变的联动
                */
                if ($.askfor.is_addTime && 'time' === $.askfor.custom_type) {                    
                    setText();
                }
            });
            // 错误提示的dom
            var errorBox = $('#js-custom-error');
            // 统计,点击了几次,添加了时间类型的字段
            $.askfor.count = 0;
            jqBtn.on('click', function () {
                // 错误提示
                $.askfor.__customError(errorBox, jqBtn);
                // 是否可以添加自定义字段
                if ($.askfor.custom_is_add) {
                    // 插入自定义字段的dom
                    add_column.apply(this);
                    // 把选择的自定义类型的value写到新添加字段的隐藏域中,做为表单提交
                    $('.js-custom-hidden:last').val($.askfor.custom_val);
                    // 添加标记
                    setMark();
                    // 添加类型文本的显示
                    setText();

                    if ($.askfor.custom_type === 'time') {

                        var jq = $('.js-custom-read[data-mark="' + $.askfor.custom_type + '"]'),
                            parent = jq.eq(0).parents('.js-custom-group');
                        // 如果添加的类型为时间类型,自增,此时的值为1 
                        $.askfor.count++; 
                        /**
                        * 如果添加的时间类型字段,小于两个,进行一次
                        * 触发添加自定义按钮的click事件,来达到,添加一对时间类型的字段
                        * */
                        if ($.askfor.count < 2) {
 
                            jqBtn.trigger('click');
                            // true为已经添加了一对时间类型字段,可以进行,类型选项的联动改变
                            $.askfor.is_addTime = true; 
                        }
                        // 删除第一个时间类型字段的删除按钮
                        parent.find('.js-custom-del').parent().remove();

                    }

                }


            });
        },
        /**
        * 提示,时间,日期,时间+日期,类型不能再添加
        * */
        __customError: function (jq, btn) {

            var jqInput = $('.js-custom-read[data-mark="' + $.askfor.custom_type + '"]'),
                length = jqInput.length;

            if ($.askfor.custom_length > 0 && $.askfor.custom_type == 'time') {

                if (length >= $.askfor.custom_length) {

                    jq.show();
                    $.askfor.custom_is_add = false;
                } else {
                    jq.hide();
                    $.askfor.custom_is_add = true;
                }

            } else {
                jq.hide();
                $.askfor.custom_is_add = true;
            }
        },
        /**
        * 自定义字段删除
        * */
        __customDel: function (jq) {

            $.askfor.count = 0;

            // 隐藏添加时间,日期,日期+时间,不能添加的错误提示
            $('#js-custom-error').hide();

            // 是否能添加自定义字段,开关
            $.askfor.custom_is_add = true;

            var parent = $(jq).parents('.js-custom-group'),
                mark = parent.find('.js-custom-read').attr('data-mark');

            if ('time' === mark) {

                $.askfor.__delTimeType(mark);

            } else {

                $.askfor.__delOutherType(parent);
            }

        },
        /**
        * 删除自定义时间类型字段
        * */
        __delTimeType: function (mark) {

            var jqSelect = $('.js-custom-read[data-mark="' + mark + '"]'),
                timeParents = jqSelect.parents('.js-custom-group');

            timeParents.remove(); // 删除所有,时间类型自定义字段
            // 删除左侧,可视化,所有时间类型,字段
            timeParents.each(function () {

                var $this = $(this),
                    visualIndex = $this.find('.js-askfor-input').attr('ask-index');
                $.askfor.__delVisual(visualIndex);
            });
            $.askfor.is_addTime = false;
        },
        /**
        * 删除非时间类型,自定义字段
        * */
        __delOutherType: function (parent) {

            var visualIndex = parent.find('.js-askfor-input').attr('ask-index');
            parent.remove();
            $.askfor.__delVisual(visualIndex);
        },
        /**
        * 删除左侧可视化,自定义字段
        * */
        __delVisual: function (visualIndex) {

            // 根据,右侧自定义字段的索引,来删除左侧可视化,自定义字段
            var selector = '.js-visual-box .js-askfor-visual[ask-index="' + visualIndex + '"]',
                jqSelect = $(selector);                
            jqSelect.parent().remove();

        },
        editPageInit: function() {

            var timeTypeLength = $('.js-custom-read[data-mark="time"]').length;
            if (timeTypeLength == 2) {

                $.askfor.is_addTime = true;
            }
            
        },
        __jqClone:function(is_clone) {

                return $('.js-askfor-visual[ask-clone="' + is_clone + '"]:last');
        },
        InsertClone:$('.js-askfor-clone'),
        customClone: function(jq, index) {

            if ($.askfor.custom_is_add) {
                
                var self = $(jq),
                    is_clone = Boolean(self.attr('ask-clone')),
                    selector = $.askfor.__jqClone(is_clone),
                    cloneNode = selector.eq(0).clone();

                cloneNode.children().text(''); 
                cloneNode.find('.js-askfor-visual').attr('ask-index', index);
                $.askfor.InsertClone.append(cloneNode);
            }
        },
        // 添加职责
        _addJob:function(){

            var nameList = {},
                nameString = null,
                name = null;  

            function join(list){

                var newList=[];
                
                for(name in list){

                    newList.push(list[name]);
                }
                return newList.join('>');
            }

            function AddName(text,index,val){    

                if(val > 0){
                    nameList[index] = text;                       
                    nameString = join(nameList);                      
                    $.askfor.person_box.text(nameString);                
                }                
            }

            function getJobInfo(){                
                var $this = $(this),
                    chooes = $this.find('option:selected'),                    
                    text = chooes.text(),
                    index = $this.attr('ask-index'),
                    val = $this.val();                    
                    AddName(text,index,val);
            }

            $.askfor.jobEach = function(){
                nameList = {};
                $('.js-approver-job').each(function(){
                    getJobInfo.apply(this);
                });
            }
            $.askfor.jobEach();

            $('body').on('change','.js-approver-job',function(){                
                    getJobInfo.apply(this);                
            });            
        },
        clearPerson:function(){
            $.askfor.nameList = {};
            $.askfor.person_box.text('');        
        }
    };
    $.askfor.init();
})(jQuery);