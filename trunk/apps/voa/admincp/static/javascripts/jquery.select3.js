/**
 * Created by luckwang on 15/5/12.
 */


(function($) {

    /*
     <div id="select2-layer" data-name="cd_ids[] 表单控件名称" data-value="48,6 默认值" style="width:400px;">
     </div>

     $('#select2-layer').select3({
     params...
     });
     */
    $.fn.select3 = function(options) {

        options = options || {};

        var settings = $.extend({
            placeholder : "请选择",
            format_no_matches : '没有匹配到数据',
            format_result : '',
            //format_selection : 0,
            max : 0,                                //最大选中项数量 0为不限制
            ajax : '',            //数据请求url 数据优先使用 data参数
            default_data : '',      //默认数据 优先data-value 属性
            data : null,             //json数据 [{ id:id, text:text},{ id:id, text:text},{ id:id, text:text}]
            denied_top_selected : false  //不允许最顶层选择， true 不允许, false 允许
        }, options);

        //当前dom的jquey对象
        var self = $(this);
        //当前值
        var value = new Array();
        self.addClass('select2-container select2-container-multi');

        //优先使用属性data-value为默认值
        if (self.attr('data-value')) {
            settings.default_data = self.attr('data-value');
        }

        //字符串转数组
        if (settings.default_data) {
            settings.default_data = settings.default_data.split(',');
        }

        var container_width = self.width();

        //创建html标签
        self.add_tag = function (type, css) {
            return jQuery(document.createElement(type)).addClass(css);
        };

        //添加值隐藏域
        var hidden = self.add_tag('input', '').attr('type', 'hidden').attr('name', self.attr('data-name'));
        self.append(hidden);

        //基础输入框
        var ul_container = self.add_tag('ul', 'select2-choices');
        var input_li = self.add_tag('li', 'select2-search-field');
        var input_text = self.add_tag('input', 'select2-input').
            attr('type', 'text').attr('autocomplete', 'off').
            attr('autocorrect', 'off').attr('autocapitalize', 'off').
            attr('spellcheck', false).css('width', container_width).
            attr('placeholder', settings.placeholder);

        self.append(ul_container.append(input_li.append(input_text)));

        //搜索结果结构
        var search_drop = self.add_tag('div', 'select2-drop select2-drop-multi select2-display-none select2-drop-active select2-drop-above');
        search_drop.width(container_width);
        var search_drop_ul = self.add_tag('ul', 'select2-results');
        search_drop.append(search_drop_ul);
        $(document.body).append(search_drop);

        //树形列表结构
        var tree_drop = self.add_tag('div', 'select2-drop select2-drop-multi select3-drop-multi select2-display-none select2-drop-active select2-drop-above');
        tree_drop.width(container_width);
        var tree_drop_ul = self.add_tag('ul', 'select3-results select2-results');
        tree_drop_ul.css({position: 'relative'});
        tree_drop.append(tree_drop_ul);
        $(document.body).append(tree_drop);

        //添加透明遮罩
        var mask = self.add_tag('div', 'select2-drop-mask');//.attr('id', '#select3-drop-mask');
        mask.hide();
        $(document.body).append(mask);

        //设置文本框的宽度
        self.reset_input_width = function () {
            //计算文本框输入时的宽度
            var width = 0;
            ul_container.find('.select2-search-choice').each(function(e, li) {
                var li_width = $(li).outerWidth(true);
                width += li_width;
                if (width > container_width) {
                    width = li_width;
                }
            });
            var input_width = container_width - width - 8;
            //最小宽度80
            if (input_width < 80) {
                input_width = container_width;
            }
            input_text.css('width', input_width);
        };

        //文本框焦点事件
        input_text.on('focus', function () {
            self.reset_input_width();
            self.show_tree();
        });

        //ul点击事件
        ul_container.on('click',function() {
            input_text.trigger('focus');
            self.addClass('select2-container-active');
        });

        //文本失去焦点
        mask.on('click', function() {
            self.removeClass('select2-container-active');
            input_text.css('width', '16px');
            input_text.val('');
            self.hide_result();
            self.hide_tree();
            mask.hide();
            ul_container.find('.select2-search-choice-focus').removeClass('select2-search-choice-focus');
        });

        //文本键入事件
        input_text.on('keyup', function(e) {
            switch (e.keyCode) {
                //删除
                case 8:
                    if (input_text.val() == '') {
                        var last = ul_container.find('.select2-search-choice').last();
                        if (last.hasClass('select2-search-choice-focus')) {
                            last.find('.select2-search-choice-close').trigger('click');
                        } else {
                            last.addClass('select2-search-choice-focus');
                        }
                        self.hide_result();
                        self.show_tree();
                    } else {
                        self.show_result(input_text.val());
                    }
                    break;
                //向上移动
                case 38:
                    var highlighted = search_drop_ul.find('.select2-highlighted');
                    var prev = highlighted.prev();
                    if (prev.get(0) != undefined) {
                        highlighted.removeClass('select2-highlighted');
                        prev.addClass('select2-highlighted');
                        search_drop_ul.scrollTop(search_drop_ul.scrollTop() - prev.height());
                    }
                    input_text.val(input_text.val());
                    break;
                //向下
                case 40:
                    var highlighted = search_drop_ul.find('.select2-highlighted');
                    var next = highlighted.next();
                    if (next.get(0) != undefined) {
                        highlighted.removeClass('select2-highlighted');
                        next.addClass('select2-highlighted');
                        search_drop_ul.scrollTop(search_drop_ul.scrollTop() + next.height());
                    }
                    break;
                //搜索
                default:
                    ul_container.find('.select2-search-choice-focus').removeClass('select2-search-choice-focus');
                    self.show_result(input_text.val());
            }
        });

        //文本框回车事件
        input_text.on('keypress', function(e) {
            if (e.keyCode == 13) {
                search_drop_ul.find('.select2-highlighted').trigger('click');
                return false;
            }
        });

        //如果json数据为空请求ajax
        if (settings.data === null) {
            $.ajax(settings.ajax, {
                type : 'GET',
                cache : true,
                data : {},
                dataType : 'json',
                success : function(response) {
                    if (response.result.list) {
                        settings.data = response.result.list;
                        self.init_tree();
                    }
                }
            });
        } else {
            self.init_tree();
        }

        //添加选中项
        self.add_selected = function(id, text) {
            if (settings.denied_top_selected &&
                    self.is_top(id)) {
                return ;
            }
            if (value.indexOf(id) < 0) {

                if(settings.max > 0 && settings.max <= value.length) {
                    return;
                }
                //添加值到隐藏文本域
                value.push(id);
                var ids = value.join(',');
                ids = ids.replace(/(^,)|(,$)/g, '');
                hidden.val(ids);

                //添加显示标签
                var li_div = self.add_tag('div').text(text);
                var li_item = self.add_tag('li', 'select2-search-choice');
                var li_close = self.add_tag('a', 'select2-search-choice-close').attr('href', '#');
                li_item.append(li_div);
                li_item.append(li_close);
                input_li.before(li_item);
                input_text.attr('placeholder', '');
                self.reset_input_width();

                //绑定删除事件
                li_close.on('click', function(e) {
                    li_item.remove();
                    if (value.indexOf(id) >= 0) {
                        value.splice(value.indexOf(id), 1);
                        var d_ids = value.join(',');
                        d_ids = d_ids.replace(/(^,)|(,$)/g, '');
                        hidden.val(d_ids);
                    }
                });

                tree_drop.css({left : self.offset().left, top : self.offset().top + self.height()});
                search_drop.css({left : self.offset().left, top : self.offset().top + self.height()});
            }
        };

        //初始化树形数据
        self.init_tree = function() {
            if (settings.data) {
                self.each_tree(settings.data, tree_drop_ul);
            }
        }

        //遍历树形数据
        self.each_tree = function(data, container) {
            if (data) {
                for (var i in data) {
                    self.add_tree_item(container, data[i]);
                }
            }
        }

        //显示树形列表
        self.show_tree = function() {
            tree_drop.css({left : self.offset().left, top : self.offset().top + self.height()});
            tree_drop.show();
            self.hide_result();
            mask.show();
        };

        //隐藏树形列表
        self.hide_tree = function() {
            tree_drop.hide();
            mask.hide();
        };

        //添加树形项
        self.add_tree_item = function(container, obj) {
            if (settings.default_data) {
                if (settings.default_data.indexOf(obj.id) >= 0) {
                    self.add_selected(obj.id, obj.text);
                }
            }
            var tree_drop_li = self.add_tag('li', 'select2-results-dept-0 select2-result select2-result-selectable');

            //点击选中事件
            tree_drop_li.on('click', function(e) {
                self.add_selected(obj.id, obj.text);
                e.stopPropagation();
                return false;
            });

            var tree_drop_li_div = self.add_tag('div', 'select2-result-label select3-result-label');
            var tree_drop_li_bg = self.add_tag('div');
            var i_open = self.add_tag('i', 'fa').css('padding', '8px');
            tree_drop_li_div.append(i_open, '&nbsp;<i class="fa fa-folder"></i>&nbsp;', obj.text);
            tree_drop_li.append(tree_drop_li_bg);
            tree_drop_li.append(tree_drop_li_div);
            //鼠标滑过选中状态
            tree_drop_li_div.hover(function(e){
                tree_drop_ul.find('.jstree-wholerow').removeClass('jstree-wholerow');
                tree_drop_ul.find('.select3-white').removeClass('select3-white');
                tree_drop_li_bg.addClass('jstree-wholerow');
                tree_drop_li_div.addClass('select3-white');

                e.stopPropagation();
                return false;
            });

            container.append(tree_drop_li);
            //添加下一级
            if (!$.isEmptyObject(obj.subs)) {
                i_open.addClass('fa-caret-right');
                //展开下级
                i_open.on('click', function(e){
                    i_open.parent().next().toggle();
                    e.stopPropagation();
                    return false;
                });
                var sub_ul = self.add_tag('ul', 'select2-results select3-results').css('max-height', '100%');
                //默认展开第一级
                if (container.parents('ul.select3-results').size() > 0) {
                    sub_ul.hide();
                }
                tree_drop_li.append(sub_ul);
                self.each_tree(obj.subs, sub_ul);
            }
        };

        //显示搜索结果框
        self.show_result = function(kw) {
            self.clear_result();
            if (kw != '') {
                self.hide_tree();
                search_drop.show();
                search_drop.css({left : self.offset().left, top : self.offset().top + self.height()});

                mask.show();
                search_drop_ul.scrollTop(0);
                self.each_result(kw, settings.data);

                //判断是否有匹配结果
                if (!search_drop_ul.is(':has(li)')) {
                    self.add_no_result();
                } else {
                    search_drop_ul.find('.select2-result').eq(0).addClass('select2-highlighted');
                }
            } else {
                self.hide_result();
            }
        };

        //隐藏搜索结果框
        self.hide_result = function() {
            self.clear_result();
            search_drop.hide();
            mask.hide();
        };

        //根据关键字匹配项
        self.each_result =  function(kw, data) {
            if (data) {
                for (var i in data) {
                    if (data[i].text.toLowerCase().indexOf(kw.toLowerCase()) >= 0) {
                        self.add_result_item(kw, data[i].id, data[i].text);
                    }
                    if (!$.isEmptyObject(data[i].subs)) {
                        self.each_result(kw, data[i].subs);
                    }
                }
            }
        };

        //添加匹配项
        self.add_result_item = function(kw, id, text) {

            if (value.indexOf(id) >= 0 || (
                    settings.denied_top_selected &&
                    self.is_top(id)
                )) {
                return;
            }
            var search_drop_li = self.add_tag('li', 'select2-results-dept-0 select2-result select2-result-selectable');

            //点击选中事件
            search_drop_li.on('click', function(){
                self.hide_result();
                self.add_selected(id, text);
                input_text.val('');
                input_text.trigger('focus');
            });

            //鼠标划过选中状态
            search_drop_li.hover(function(){
                search_drop_ul.find('.select2-result').removeClass('select2-highlighted');
                search_drop_li.addClass('select2-highlighted');
            });
            var search_drop_li_div = self.add_tag('div', 'select2-result-label');

            var t = text.replace(kw, '<span class="select2-match">' + kw + '</span>');
            //格式化结果
            if (settings.format_result) {
                t = settings.format_result(id, t);
            }
            search_drop_li_div.html(t);
            search_drop_li.append(search_drop_li_div);
            search_drop_ul.append(search_drop_li);
        };

        //添加没有匹配结果的项
        self.add_no_result = function() {
            var search_drop_li = self.add_tag('li', 'select2-no-results');
            search_drop_li.text(settings.format_no_matches);
            search_drop_ul.append(search_drop_li);
        };

        //清除匹配结果
        self.clear_result = function() {
            search_drop_ul.empty();
        };

        //判断是否为最顶层
        self.is_top = function (id) {

            for (var i in settings.data) {
                if (settings.data[i].id == id) {
                    return true;
                }
            }
            return false;
        }
        //self.reset_input_width();
    };
})($);