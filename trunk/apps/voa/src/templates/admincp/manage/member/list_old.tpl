{include file="$tpl_dir_base/header.tpl"}

<link href="/admincp/static/stylesheets/contacts.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/admincp/static/javascripts/jquery.select3.js" ></script>

<div class="stat-panel">
    <div class="stat-row">
        <div class="stat-cell col-sm-3 padding-sm-hr bordered no-border-r valign-top no-padding" style="background-color:#f5f5f5;">
            <div class="category">
                <div class="padding-sm border-b" style="height:42px;">
                    <div id="top_department" class="pull-left">{$department.cd_name}</div>

                    <button class="add-sub-cate btn btn-info pull-right btn-col" id="add_top_department" title="添加" href="javascript:;" data-toggle="modal" data-target="#modal-add-department">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
                <div class="border-t"></div>
                <div id="dl_department_{$department.cd_id}" style="margin-left:-18px;margin-top:15px;  padding-bottom: 15px;">

                </div>
            </div>

        </div>
        <div class="stat-cell col-sm-9 bordered no-padding">
            <div class="panel-heading" style="background-color: #f5f5f5;">
                <div class="border-b">
                    <a id="a_add_member" class="btn btn-info" data-toggle="modal" data-target="#modal-add-member"><i class="fa fa-plus"></i> 添加员工</a>&nbsp;
                    {if $member_impmem_url}<a class="btn btn-warning"  href="/admincp/manage/member/impmem">批量导入</a>{/if}&nbsp;

                    <a class="btn btn-warning"  href="{$member_impmem_url}"> 批量导出</a>&nbsp;
                    <div class="panel-heading-controls" style="width:30%;margin-top: 0em;">
                        <form onsubmit="return false;">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" placeholder="搜索姓名、手机号" name="kw" id="txt_search_kw">
                            <span class="input-group-btn">
                                <button class="btn" type="button" id="btn_search_member">
                                    <span class="fa fa-search"></span>
                                </button>
                            </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <table class="table table-striped table-hover font12 margin-0">
                <colgroup>
                    <col class="t-col-3">
                    <col class="t-col-20">
                    <col class="t-col-8">
                    <col class="t-col-12">
                    <col class="t-col-15">
                    <col class="t-col-12">
                    <col class="t-col-10"></colgroup>
                <thead>
                <tr class="title">
                    <td class="text-left">
                        <label class="checkbox">
                            <input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');">
                            <span class="lbl lbl-width">全选</span>
                        </label>
                    </td>
                    <td>姓名</td>
                    <td>性别</td>
                    <td>职位</td>
                    <td>手机</td>
                    <td>邮箱</td>
                    <td><div class="btn-group btn-group-xs">
                            <button type="button" class="btn">状态</button>
                            <button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><i class="fa fa-caret-down" style="line-height: 1"></i></button>
                            <ul id="ul_member_status" class="dropdown-menu" style="min-width:70px;">
                                <li><a href="javascript:;" data-status="1">已关注(11)</a></li>
                                <li><a href="javascript:;" data-status="4">未关注(11)</a></li>
                                <li><a href="javascript:;" data-status="4">禁用(11)</a></li>
                            </ul>
                        </div></td>
                </tr>
                </thead>

                <tbody id="table_member_list">
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="7" class="text-right vcy-page" id="table_pages"></td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div id="div_first_tips" class="modal fade">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <p>畅移通讯录全新改版</p>
                <p>现在可以直接在畅移后台添加员工啦</p>
                <p>用户不需要再去微信企业号后台进行设置</p>
                <!--<p>Tips:为了避免重复添加造成的不变，若你已在企业号后台添加了通讯录，点击右上角进行同步按钮进行同步</p>-->
            </div>
            <div class="modal-footer">
                <button type="button" id="btn_close_tips" class="btn btn-primary" data-dismiss="modal">知道了</button>
            </div>
        </div>
    </div>
</div>


{include file="$tpl_dir_base/manage/member/js_tpl.tpl"}
{include file="$tpl_dir_base/manage/member/member.tpl"}
{include file="$tpl_dir_base/manage/member/member_confirm.tpl"}
{include file="$tpl_dir_base/manage/member/department_tpl.tpl"}

<script type="text/javascript">
    var department_list_url = '{$department_list_url}';
    var department_add_url = '{$department_add_url}';
    var department_edit_url = '{$department_edit_url}';
    var department_delete_url = '{$department_delete_url}';
    var department_detail_url = '{$department_detail_url}';
    var member_list_url = '{$member_list_url}';
    var member_add_url = '{$member_add_url}';
    var member_edit_url = '{$member_edit_url}';
    var member_delete_url = '{$member_delete_url}';
    var member_invite_url = '{$member_invite_url}';
    var member_fields_url = '{$member_fields_url}';
    var member_detail_url = '{$member_detail_url}';
    var member_active_url = '{$member_active_url}';
    var top_id = {$department.cd_id};
    var tree_obj = null;
    {literal}
    (function(jQuery) {

        //左边部门树形结构
        jQuery.fn.show_tree = function(options) {

            options = options || {};

            var settings = jQuery.extend({
                request_type : "GET",        //发送请求方式 默认get
                layer : 10,                 //最多显示层级 最大10级
                url : '',                   //数据请求url (up_id 和params 同时发送过去)
                up_id : 0,                  //父级id(请求参数)
                params : {},                  //请求参数(扩展参数)
                delete_click : '',             //删除回调函数 有删除回调函数才会显示按钮
                add_click : '',                //添加回调函数    有添加回调函数才会显示按钮
                edit_click : '',                //编辑回调函数   有编辑回调函数才会显示按钮
                checkbox_name : '',             //复选框   复选框名称不为空时则显示
                checked_change : '',            //复选框       checked状态改变时回调函数
                checked_default : [],           //复选框       默认值
                item_click : ''
            }, options);

            var self = this;

            self.empty();

            //层级不能大于10级
            if (settings.layer > 10) {
                settings.layer = 10;
            }

            //层级不能小于10级
            if (settings.layer < 1) {
                settings.layer = 1;
            }

            if (settings.url == '') {
                console.log('请求url为空');
                return false;
            }

            //创建html标签
            self.add_tag = function (type, css) {
                return jQuery(document.createElement(type)).addClass(css);
            };

            //添加操作选项按钮
            self.add_option_button = function(ul, params, call_back, target, text) {
                if (call_back) {
                    var li_tag = self.add_tag('li');
                    var a_tag = self.add_tag('a').text(text).attr('data-toggle', 'modal').attr('data-target', target);
                    a_tag.on('click',params , call_back);
                    ul.append(li_tag.append(a_tag));
                }
            }

            //展开
            self.open = function (div, container, id, layer) {
                //添加展开按钮
                var i_open = self.add_tag('i','fa fa-caret-right i_open');
                //点击展开请求下级数据
                div.on('click', function () {

                    //切换点击icon
                    var $i_zk = $(this).find('.i_open');

                    //判断是否加载过数据
                    if ($(this).parent().siblings('dd').find('dl').size() > 0) {
                        //切换显示
                        $(this).parent().siblings('dd').find('dl').toggle();
                        if ($i_zk.is('.fa-caret-down')) {
                            $i_zk.removeClass('fa-caret-down').addClass('fa-caret-right');
                        } else if ($i_zk.is('.fa-caret-right')) {
                            $i_zk.removeClass('fa-caret-right').addClass('fa-caret-down');
                        }
                    } else {
                        //ajax请求数据
                        settings.params.up_id = id;
                        self.ajax_departments(settings.params, container, layer);
                        //$i_zk.removeClass('fa-caret-right').addClass(' fa-caret-down');
                    }
                });
                div.append(i_open);
            };

            //创建dl标签
            self.get_dl = function (id) {
                var dl_item = self.add_tag('dl', 'cate-item');
                return dl_item;
            };

            //创建dt标签
            self.get_dt = function (id) {
                var dt_item = self.add_tag('dt', 'cf');
                dt_item.hover(function(){
                            $(this).find('.dropdown-toggle').show();
                        },
                        function(){
                            $(this).find('.dropdown-toggle').hide();
                        });
                return dt_item;
            };

            self.get_dt_div = function (layer, dd_item, name, id) {
                var div = self.add_tag('div', 'name');
                //判断是否到了最大限制层
                if (layer > 0) {
                    self.open(div, dd_item, id, layer);
                }
                div.append(self.add_tag('i', 'fa fa-folder'), name);
                div.attr('data-id', id);

                if (settings.item_click) {
                    div.on('click',{id : id} , settings.item_click);
                }

                return div;
            }

            //获取复选框并绑定点击事件
            self.checkbox = function(container, dt, id) {
                if (settings.checkbox_name) {
                    var cb = self.add_tag('input');
                    cb.attr('type', 'checkbox').attr('name', settings.checkbox_name).val(id);
                    cb.on('click.self', function() {
                        var checkbox = jQuery(this);
                        var dl = checkbox.closest('dl');
                        dl.find('input[type=checkbox]').prop('checked', checkbox.prop('checked'));
                        //checked状态改变时调用回调函数
                        if (settings.checked_change) {
                            settings.checked_change(checkbox);
                            settings.checked_change(dl.find('input[type=checkbox]'));
                        }
                        self.change_checkbox(dl);
                    });

                    //得到上一级checkbox的状态，并赋值给子checkbox
                    var p_dt = container.siblings('dt');
                    if (p_dt.get(0)) {
                        if (p_dt.children('input').prop('checked')) {
                            cb.prop('checked', true);
                        }
                        //checked状态改变时调用回调函数
                        if (settings.checked_change) {
                            settings.checked_change(cb);
                        }
                    }

                    if (settings.checked_default.indexOf(id) > -1) {
                        cb.prop('checked', true);
                        //checked状态改变时调用回调函数
                        if (settings.checked_change) {
                            settings.checked_change(cb);
                        }
                    }
                    dt.append(cb);
                }
                return '';
            }

            //复选框改变checked时递归父级checkbox
            self.change_checkbox = function (dl) {
                var siblings = dl.siblings('dl');
                var siblings_size = siblings.size();
                var flag = dl.children('dt').children('input').prop('checked');
                for (var i = 0; i < siblings_size; i++) {
                    if (siblings.eq(i).children('dt').children('input').prop('checked') != flag) {
                        flag = false;
                        break;
                    }
                }
                //判断是否存在父级checkbox
                var p_dl = dl.parents('dl').eq(0);
                if (p_dl.get(0) &&
                        p_dl.children('dt').children('input').prop('checked') != flag) {

                    p_dl.children('dt').children('input').prop('checked', flag);
                    //checked状态改变时调用回调函数
                    if (settings.checked_change) {
                        settings.checked_change(p_dl.children('dt').children('input'));
                    }
                    self.change_checkbox(p_dl);
                }
            }

            //添加操作按钮
            self.add_button = function (dt_item, id, name, layer) {
                if (settings.add_click ||
                        settings.edit_click ||
                        settings.delete_click) {
                    //添加展示操作按钮
                    var a = self.add_tag('a', 'dropdown-toggle').append(self.add_tag('i', 'fa fa-caret-square-o-down')).hide().attr('data-toggle', 'dropdown');
                    a.on('click', function () {

                        //计算显示位置
                        var position = jQuery(this).position();
                        jQuery('.dropdown-menu-operation').hide();
                        var width = jQuery('.dropdown-menu-operation').width();
                        var height = jQuery('.dropdown-menu-operation').height();

                        var container_width = self.width();
                        var container_height = self.height();
                        var left = position.left;
                        var top = position.top + 15;
                        if (left + width >= container_width) {
                            left = left - width;
                        }
                        if (top + height >= container_height) {
                            top = top - height - 30;
                        }
                        jQuery('.menu' + id).css({left : left, top : top});
                        jQuery('.menu' + id).slideDown('fast');
                        if(jQuery(document).attr('is_bind_hide_menu') != 1){
                            jQuery(document).on('click', self.hide_menu);
                            jQuery(document).attr('is_bind_hide_menu', 1);
                        }
                    });
                    var ul = self.add_tag('ul', 'dropdown-menu-operation dropdown-menu menu' + id);
                    ul.css({
                        'min-width': '100px'
                    });
                    if (layer > 0) {
                        self.add_option_button(ul, {id : id}, settings.add_click, '#modal-add-department', '添加子部门');
                    }
                    self.add_option_button(ul, {id : id}, settings.edit_click, '#modal-add-department', '编辑');
                    self.add_option_button(ul, {id : id , name : name}, settings.delete_click, '#modal-delete-department', '删除');
                    self.add_option_button(ul, {id : id , name : name}, settings.delete_click, '#modal-delete-department', 'ID:00');
                    dt_item.append(a);
                    dt_item.append(ul);
                }
            };

            //隐藏操作选项菜单
            self.hide_menu = function () {
                jQuery('.dropdown-menu-operation').slideUp('fast');
                jQuery(document).off('click', self.hide_menu);
                jQuery(document).attr('is_bind_hide_menu', 0);
            }

            //添加dl项
            self.add_item = function (id, name, container) {
                var layer = parseInt(container.attr('data-layer')) - 1;
                var dl_item = self.get_dl();
                dl_item.attr('id', 'dl_department_' + id);
                var dt_item = self.get_dt(id);
                var dd_item = self.add_tag('dd');
                dd_item.attr('data-layer', layer);
                var div = self.get_dt_div(layer, dd_item, name, id);
                self.checkbox(container, dt_item, id);
                dt_item.append(div);
                self.add_button(dt_item, id, name, layer);
                dl_item.append(dt_item, dd_item);
                container.append(dl_item);
            };

            //ajax请求数据
            self.ajax_departments = function (params, container, layer) {
                //防止重复加载
                if (container.attr('data-loading') == 1) {
                    return;
                } else {
                    container.attr('data-loading', 1);
                }
                jQuery.ajax(settings.url, {
                    type : settings.request_type,
                    cache : true,
                    data : params,
                    dataType : 'json',
                    success : function(response) {
                        container.attr('data-loading', 0);
                        var items = response.result.list;
                        container.attr('data-layer', layer);
                        container.siblings('dt').find('.i_open').removeClass('fa-caret-right');
                        if (!jQuery.isEmptyObject(items)) {
                            container.siblings('dt').find('.i_open').addClass('fa-caret-down');
                            //遍历结果
                            for (var i in items) {
                                //添加列表项
                                self.add_item(items[i].id, items[i].name, container);
                            }
                        }
                    }
                });
            }
            //初始第一级数据
            settings.params.up_id = settings.up_id;
            //请求第一级部门数据
            self.ajax_departments(settings.params, self, settings.layer);
            if (settings.item_click) {
                //获取第一页成员数据
                settings.item_click({'data': {'id': settings.up_id}});
            }
            return self;
        };
    })(jQuery);

    {/literal}

    init.push(function () {
        jQuery('#dl_department_' + top_id).slimScroll({
            height: 490 ,
            overflow:'visible'
        });
    });
    //===============绑定事件=============
    jQuery(function() {

        {if $is_show_tips == 1}
        //=============第一次使用弹出层================
        jQuery('#div_first_tips').css({
            top : (jQuery(document.body).height() - 200) / 2
        });
        jQuery('#btn_close_tips').on('click', function() {
            var date = new Date();
            var ms = 24 * 3600 * 1000 * 90;
            date.setTime(date.getTime() + ms);
            var str = 'is_show_member_tips=1;expires=' + date.toGMTString();
            document.cookie = str;
        });
        jQuery('#div_first_tips').modal();
        //=============第一次使用弹出层================
        {/if}

        //左边部门树形结构
        tree_obj = jQuery('#dl_department_' + top_id).show_tree({
            url : department_list_url,
            up_id : top_id,
            layer : 10,
            add_click : add_department,
            delete_click : delete_department,
            edit_click : edit_department,
            item_click : member_list

        });

        //绑定模糊搜索事件
        jQuery('#btn_search_member').on('click', function() {
            var params = {
                data : {
                    id: jQuery('#current_department_id').val(),
                    kw: jQuery('#txt_search_kw').val()
                }
            }
            member_list(params);
            return false;
        });

        //绑定模糊搜索事件
        jQuery('#txt_search_kw').on('keyup', function(e) {
            if (e.keyCode == 13) {
                jQuery('#btn_search_member').click();
            }
        });

        //用户关注状态查询
        jQuery('#ul_member_status a').on('click', function(){
            var params = {
                data : {
                    id: jQuery('#current_department_id').val(),
                    kw: jQuery('#txt_search_kw').val(),
                    status: jQuery(this).attr('data-status')
                }
            };
            member_list(params);
        });

        //绑定(弹出层)删除部门点击事件
        jQuery('#modal-delete-department').find('button[name=btn_delete]').on('click', function() {
            jQuery.ajax(department_delete_url, {
                type : 'POST',
                data : { id : jQuery(this).attr('data-id')},
                dataType : 'json',
                success : function(response) {
                    if (response.result.errcode == 1) {
                        $('#dl_department_' + response.result.cd_id).remove();
                    } else {
                        alert(response.result.errmsg);
                    }
                }
            });
        });

        //绑定(弹出层)编辑部门提交事件
        jQuery('#form_department').on('submit', submit_department);

        //添加最顶层部门
        jQuery('#add_top_department').on('click', function() {
            jQuery('#form_department').get(0).reset();
			jQuery('#form_department input[type=hidden]').val('');
            //赋值到确认弹出层中
            jQuery('#form_department').find('input[name=up_id]').val(top_id);
        });

        //顶层部门点击
        jQuery('#top_department').on('click', function() {
            var params = {
                data : {
                    id: top_id,
                    kw: '',
                    status: ''
                }
            };
            member_list(params);
        });

        //绑定(弹出层)批量删除用户点击事件
        jQuery('#btn_delete_member').on('click', function() {
            return member_delete_invite(member_delete_url, jQuery(this).attr('data-id'));
        })

        //绑定批量邀请用户点击事件
        jQuery('#a_invite_member').on('click', function() {
            return member_delete_invite_confirm('invite');
        });

        //绑定(弹出层)批量邀请用户点击事件
        jQuery('#btn_invite_member').on('click', function() {
            member_delete_invite(member_invite_url, jQuery(this).attr('data-id'));
        });

        //用户详细信息弹出层的显示位置
        jQuery('#div_member_detail').css({ left: jQuery(window).width() + 100});
        //用户详细信息弹出层的显示位置
        jQuery('#div_member_detail').find('.close').click(function() {
            jQuery('#div_member_detail').animate({ left: jQuery(window).width() + 100}, 'fast').hide().attr('data-is-display', 0);
        });

        //批量选择用户弹出层的显示位置
        jQuery('#div_member_select').css({ left: jQuery(window).width() + 100});
        jQuery('#div_member_select').find('.close').click(function() {
            jQuery('#div_member_select').animate({ left: jQuery(window).width() + 100}, 'fast').hide().attr('data-is-display', 0);
            jQuery('#div_member_select .row').empty();
            jQuery('#table_member_list input[name=delete]').prop('checked', false);
        });

        //编辑用户弹出层
        jQuery('#a_add_member').on('click', function(){
            member_edit_tpl();
        });

        //隐藏选择部门弹出层
        jQuery('#modal-add-member').on('hide.bs.modal', function () {
            jQuery('#modal-select-department').fadeOut();
        })

        //编辑用户弹出层submit事件
        jQuery('#form_member_edit').on('submit', function() {
            if (jQuery('#form_member_edit').attr('data-loading') == 1) {
                return;
            } else {
                jQuery('#form_member_edit').attr('data-loading', 1)
            }
            jQuery.ajax(member_edit_url, {
                type : 'post',
                data : jQuery(this).serialize(),
                dataType : 'json',
                success : function(response) {
                    jQuery('#form_member_edit').attr('data-loading', 0);
                    if (response.result.errcode == 0) {
                        jQuery('#modal-add-member').modal('hide');
						var params = {
							data : {
								id: jQuery('#current_department_id').val(),
								kw: '',
								status: ''
							}
						};
						member_list(params);
                    }
                    alert(response.result.errmsg);
                }
            });
            return false;
        });

        //用户详细信息弹出层-编辑用户
        jQuery('#div_member_detail button[name=btn_member_detail_edit]').on('click', function(){
            member_edit_tpl(jQuery('#div_member_detail input[name=member_detail_uid]').val());
        });

        //选择部门弹出层-关闭事件
        jQuery('#modal-select-department .btn_close').on('click', function(){
            jQuery('#modal-select-department').fadeOut();
        });

        //绑定用户属性设置相关事件
        bind_member_fields_event();

        //绑定用户详情邀请关注事件
        jQuery('#div_member_detail button[name=btn_member_detail_invite]').on('click', function() {
            var uid = jQuery('#div_member_detail input[name=member_detail_uid]').val();
            return member_delete_invite(member_invite_url, uid);
        });

        //绑定用户详情删除事件
        jQuery('#div_member_detail button[name=btn_member_detail_delete]').on('click', function() {
            var uid = jQuery('#div_member_detail input[name=member_detail_uid]').val();
            return member_delete_invite(member_delete_url, uid);
        });

        //绑定用户详情编辑事件
        jQuery('#div_member_detail button[name=btn_member_detail_edit]').on('click', function() {
            var uid = jQuery('#div_member_detail input[name=member_detail_uid]').val();
        });

        //设置用户激活属性
        jQuery('#div_member_detail button[name=btn_member_detail_active]').on('click', function() {
            var active = jQuery('#div_member_detail input[name=member_detail_active]').val();
            var uid = jQuery('#div_member_detail input[name=member_detail_uid]').val();
            if (active == 1) {
                active = 0;
            } else {
                active = 1;
            }

            jQuery.ajax(member_active_url, {
                type : 'post',
                data : { id : uid, active : active},
                dataType : 'json',
                success : function(response) {
                    if (response.result.errcode == 0) {
                        jQuery('#div_member_detail input[name=member_detail_active]').val(active);
                        var text = '禁用';
                        if (active == 0) {
                            text = '启用';
                        }
                        jQuery('#div_member_detail button[name=btn_member_detail_active]').text(text);
                    } else {
                        alert(response.result.errmsg);
                    }
                }
            });
        })

        //批量选择层-邀请用户
        jQuery('#btn_member_select_invite').on('click', function(){
            jQuery('#a_invite_member').trigger('click');
        });

        //批量选择层-删除用户
        jQuery('#btn_member_select_delete').on('click', function(){
            return member_delete_invite_confirm('delete');
        });
    });
    //=======================绑定事件=======================

    //=======================编辑，添加，删除，邀请，列表用户=====================
    //设置部门选择值
    function set_department_value(checkboxs) {
        var member_edit_cd_ids = jQuery('#member_edit_cd_ids');
        var cd_ids = member_edit_cd_ids.val().split(',');
        checkboxs.each(function(k, checkbox){
            var cb = jQuery(checkbox);
            var index = cd_ids.indexOf(cb.val())
            if (cb.prop('checked')) {
                if (index < 0) {
                    cd_ids.push(cb.val());
                }
            } else {
                if (index > -1) {
                    cd_ids.splice(index, 1);
                }
            }
        });

        member_edit_cd_ids.val(cd_ids.join(','));
    }

    //编辑/添加用户弹出框
    function member_edit_tpl(uid) {
        if (!uid) {
            uid = 0;
        }
        jQuery('#form_member_edit input[name=id]').val(uid);
        //初始化用户信息
        init_member_detail({ id : uid, all_fields : 1}, function(response) {
            if (response.result.fields) {
                var tpl_member_edit = txTpl('tpl_member_edit', response.result);
                tpl_member_edit = jQuery(tpl_member_edit);
                //显示选择部门弹出层
                tpl_member_edit.find('button[name=btn_select_department]').on('click', function(){
                    jQuery('#modal-select-department').css({ left : (jQuery(window).width() / 2)});
                    jQuery('#modal-select-department').fadeIn();
                });
                jQuery('#table_member_edit').empty();
                jQuery('#table_member_edit').append(tpl_member_edit);

                jQuery('#table_member_edit .field_birthday').datepicker({
                    todayBtn: "linked"
                });

                var s3_params = {
                    format_result : function(id, text) {
                        return '<i class="fa fa-folder"></i>&nbsp;' + text;
                    },
                    placeholder : '请选择部门',
                    format_no_matches : '没有匹配的部门',
                    max : 1,
                    ajax : department_list_url + '?isall=1',
					denied_top_selected : false
                };
                //选择部门
                jQuery('#table_member_edit .department_select_layer').each(function(){
                    jQuery(this).select3(s3_params);
                });
                //选择职务
                jQuery('#table_member_edit ._select_positions').each(function(){
                    jQuery(this).select2();
                });
                //隐藏删除按钮
                jQuery('#table_member_edit ._delete').each(function(){
                    jQuery(this).on('click', member_add_delete_department);
                });
                if (jQuery('#table_member_edit ._delete').size() > 1) {
                    jQuery('#table_member_edit ._delete').show();
                }

                jQuery('#table_member_edit button[name=btn_member_add_department]').on('click', function(){

                    var tr_de = jQuery('#table_member_edit .member_tr_department').eq(0).clone();
                    tr_de.find('.department_select_layer').empty().attr('data-value','');
                    tr_de.find('div._select_positions').remove();
                    var tr_hr = jQuery('#table_member_edit .member_tr_hr').eq(0).clone();
                    var tr_cur = jQuery(this).closest('tr');
                    tr_cur.before(tr_de);
                    tr_cur.before(tr_hr);

                    tr_de.find('.department_select_layer').select3(s3_params);

                    tr_de.find('select._select_positions').select2();

                    tr_de.find('._delete').on('click', member_add_delete_department);
                    jQuery('#table_member_edit ._delete').show();
                });
            }
        });
    }

    function member_add_delete_department() {
        jQuery(this).closest('tr.member_tr_department').prev().remove();
        jQuery(this).closest('tr.member_tr_department').remove();
        if (jQuery('#table_member_edit .member_tr_department').size() < 2) {
            jQuery('#table_member_edit ._delete').hide();
        }
    }

    //删除或邀请用户确认框
    function member_delete_invite_confirm(type) {
        var checkboxs = jQuery('#table_member_list input[type=checkbox]:checked');
        if (checkboxs.size() > 0) {
            var ids = '';
            var names = '';
            checkboxs.each(function(k, v) {
                ids += v.value + ',';
                names += jQuery(v).attr('data-name') + '、';
            });
            ids = ids.substring(0, ids.length -1);
            names = names.substring(0, names.length -1);
            if (type == 'delete') {
                jQuery('#modal-delete-member .span_member').text(names);
                jQuery('#modal-delete-member .span_member_count').text(checkboxs.size());
                jQuery('#btn_delete_member').attr('data-id', ids);
            } else {
                jQuery('#modal-invite-member .span_invite_member').text(names);
                jQuery('#modal-invite-member .span_invite_count').text(checkboxs.size());
                jQuery('#btn_invite_member').attr('data-id', ids);
            }
            return true;
        } else {
            type == 'invite' ? alert('请选择需要邀请的同事') : alert('请选择需要删除的同事');
            return false;
        }
    }

    //删除或邀请用户请求
    function member_delete_invite(url, id) {
        jQuery.ajax(url, {
            type : 'post',
            data : { id : id},
            dataType : 'json',
            success : function(response) {
                if (response.result.errcode == 0) {
                    if (url == member_delete_url) {
                        if (id.indexOf(',') >= 0) {
                            id = id.split(',');
                            for (var i = 0; i < id.length; i++) {
                                jQuery('#row_member_' + id[i]).remove();
                                jQuery('#div_member_select .close').trigger('click');
                            }
                        } else {
                            jQuery('#row_member_' + id).remove();
                            jQuery('#div_member_detail .close').trigger('click');
                            jQuery('#div_member_select .close').trigger('click');
                        }
                    } else if(url == member_invite_url) {
                        alert('已成功邀请用户关注');
                    }
                } else {
                    /*
                    switch (response.result.errcode) {
                        case 60119:
                            response.result.errmsg = '用户已经关注过了';
                            break;
                        case 45025:
                            response.result.errmsg = '一个用户一周只能邀请一次';
                            break;
                        case 60118:
                            response.result.errmsg = '用户微信号和邮箱都不对';
                            break;
                    }*/
                    alert(response.result.errmsg);
                }
            }
        });
    }

    //ajax请求成员数据
    function member_list(event) {
        if (jQuery('#table_member_list').attr('data-loading') == 1) {
            return;
        } else {
            jQuery('#table_member_list').attr('data-loading', 1);
        }
        var kw = '';
        var page = '';
        var status = '';

        //关键词
        if (event.data.kw) {
            kw = event.data.kw;
        }
        //分页
        if (event.data.page) {
            page = event.data.page;
        }
        //用户关注状态
        if (event.data.status) {
            status = event.data.status;
        }
        jQuery.ajax(member_list_url, {
            type : 'get',
            cache : true,
            data : { cd_id : event.data.id, kw : kw, page : page, status : status },
            dataType : 'json',
            success : function(response) {
                jQuery('#table_member_list').attr('data-loading', 0);
                jQuery('#table_member_list').html(txTpl('tpl_member_list', response.result));
                bind_member_click();
                var pages = jQuery(response.result.pages);
                bind_page_a_click(pages);
                jQuery('#table_pages').html(pages);

                //更换用户列表数据时，关闭批量选择框和详细信息框
                jQuery('#div_member_detail .close').trigger('click');
                jQuery('#div_member_select .close').trigger('click');
            }
        });
        jQuery('#current_department_id').val(event.data.id);
    }

    //绑定用户行点击事件
    function bind_member_click() {

        jQuery('#table_member_list tr.row_member input[name=delete]').off('click');
        jQuery('#table_member_list tr.row_member').off('click');
        jQuery('#table_member_list tr.row_member .lbl').off('click');

        //隐藏多选框点击事件
        jQuery('#table_member_list tr.row_member input[name=delete]').on('click', function(e){
            e.stopPropagation();
            return false;
        });

        //显示多选框点击事件
        jQuery('#table_member_list tr.row_member .lbl').on('click', function(e){
            var input = jQuery(this).siblings('input[name=delete]');
            var member_select = jQuery('#div_member_select');
            var member_detail = jQuery('#div_member_detail');
            //取消多选框勾选
            if (input.prop('checked')) {
                input.prop('checked', false);
                jQuery('#div_select_col_' + input.val()).remove();
                //判断是否全部已取消勾选
                if (check_member_select()) {
                    member_select.find('.close').trigger('click');
                }
            } else {
                //勾选多选框
                input.prop('checked', true);
                //添加到批量选择框中
                insert_into_member_select(input);
                //判断批量选择框是否已显示
                if (member_select.attr('data-is-display') == 0) {
                    if (member_detail.attr('data-is-display') == 1) {
                        member_detail.find('.close').trigger('click');
                    }
                    jQuery('#div_member_select').animate({ left: jQuery(window).width() - 239}, 'fast').show().attr('data-is-display', 1);
                }
            }
            e.stopPropagation();
            return false;
        });

        //行row点击事件
        jQuery('#table_member_list tr.row_member').on('click', function() {

            if (jQuery('#div_member_select').attr('data-is-display') == 1) {
                jQuery(this).find('.lbl').trigger('click');
            } else {

                if (jQuery('#div_member_detail').attr('data-is-display') == 0) {
                    jQuery('#div_member_detail').animate({ left: jQuery(window).width() - 239}, 'fast').show().attr('data-is-display', 1);
                }
                var m_uid = jQuery(this).find('input[name=delete]').val();
                init_member_detail({ id : m_uid}, function(response) {
                    if (response.result.data) {
                        if (response.result.data.active == 1) {
                            jQuery('#div_member_detail button[name=btn_member_detail_active]').text('禁用');
                        } else {
                            jQuery('#div_member_detail button[name=btn_member_detail_active]').text('启用');
                        }
                        jQuery('#div_member_detail_tpl').html(txTpl('tpl_member_detail', response.result));
                    }
                });
            }
        });
    }

    //检查所有复选框是否已取消选择
    function check_member_select() {
        var checkboxs = jQuery('#table_member_list input[type=checkbox]');
        var checkboxs_size = checkboxs.size();
        for (var $i = 0; $i < checkboxs_size; $i++) {
            if (checkboxs.eq($i).prop('checked') == true) {
                return false;
            }
        }
        return true;
    }

    //插入成员到批量选择框中
    function insert_into_member_select(input) {
        var m_uid = input.val();
        var m_username = input.attr('data-name');
        var face = input.parents('.row_member').find('.td_name img').attr('src');
        var col = '<div id="div_select_col_' + m_uid + '" class="col-md-3"><div class="img-box"><img src="'+face+'" alt="'+m_username+'" class="">'+m_username+'<button type="button" class="close" data-id="'+m_uid+'"><i class="fa fa-minus-circle text-danger"></i></button></div></div>';
        col = jQuery(col);
        col.find('.close').on('click', function(){
            jQuery(this).parents('.col-md-3').remove();
            jQuery('#table_member_list input[type=checkbox][value='+jQuery(this).attr('data-id')+']').prop('checked', false);
            //判断是否全部已取消勾选
            if (check_member_select()) {
                jQuery('#div_member_select .close').trigger('click');
            }
        });
        jQuery('#div_member_select .row').append(col);
    }

    //初始化用户详细信息
    function init_member_detail(params, callback, type) {
        jQuery.ajax(member_detail_url, {
            type : 'get',
            data : params,
            dataType : 'json',
            success : callback
        });
    }

    //绑定成员列表分页事件
    function bind_page_a_click(pages) {
        pages.find('a').click(function() {
            var href = jQuery(this).attr('href');
            if (href.indexOf('?') >= 0) {
                var ev = {
                    data : {
                        id : get_querystring(href, 'cd_id'),
                        kw : get_querystring(href, 'kw'),
                        page : get_querystring(href, 'page'),
	                    status : get_querystring(href, 'status')
                    }
                }
                member_list(ev);
            }
            return false;
        });
    }

    //获取url中的qs参数
    function get_querystring(url, name) {

        var result = url.match(new RegExp("[\?\&]" + name+ "=([^\&]+)","i"));
        if(result == null || result.length < 1){
            return '';
        }
        return result[1];
    }
    //=======================编辑，添加，删除，邀请，列表用户=====================


    //=====================编辑部门===============
    //添加部门弹出层
    function add_department(event) {
        jQuery('#form_department')[0].reset();
		jQuery('#form_department input[type=hidden]').val('');
        //赋值到确认弹出层中
        jQuery('#form_department').find('input[name=up_id]').val(event.data.id);
    }

    //编辑部门弹出层
    function edit_department(event) {

        jQuery('#form_department')[0].reset();
        jQuery.ajax(department_detail_url, {
            type : 'get',
            data : { id : event.data.id},
            dataType : 'json',
            success : function(response) {
                if (response.result.data) {
                    //赋值到确认弹出层中
                    var form_department = jQuery('#form_department');
                    form_department.find('input[name=id]').val(response.result.data.id);
                    form_department.find('input[name=name]').val(response.result.data.name);
                    form_department.find('input[name=up_id]').val(response.result.data.up_id);
                    form_department.find('input[name=order]').val(response.result.data.order);
                    //form_department.find('input[name=order]').val(response.result.data.order);
                    form_department.find('select[name=purview] option[value='+response.result.data.purview+']').attr("selected", true);
                }
            }
        });
    }

    //提交部门
    function submit_department() {
        var form = jQuery('#form_department');
        if (form.attr('data-submiting') == 1) {
            return;
        } else {
            form.attr('data-submiting', 1);
        }
        jQuery.ajax(department_edit_url, {
            type : form.attr('method'),
            data : form.serialize(),
            dataType : 'json',
            success : function(response) {
                form.attr('data-submiting', 0);
                if (response.result.errcode == 0) {
                    jQuery('#modal-add-department').modal('hide');
                    var dl_obj = jQuery('#dl_department_' + response.result.id);
                    //更新右边部门树
                    if (dl_obj.get(0) != undefined) {
                        var i_open = dl_obj.find('div.name i.i_open').eq(0).clone();
                        var i_folder = dl_obj.find('div.name i.fa-folder').eq(0).clone();
                        dl_obj.find('div.name').eq(0).empty();
                        dl_obj.find('div.name').eq(0).append(i_open, i_folder, response.result.name);
                    } else {
                        //添加到右边部门树中
                        var parent_obj = jQuery('#dl_department_' + response.result.upid);
                        if (parent_obj.get(0).tagName == 'DIV') {
                            tree_obj.add_item(response.result.id, response.result.name, tree_obj);
                        } else {
                            tree_obj.add_item(response.result.id, response.result.name, parent_obj.find('dd').eq(0));
                        }
                    }
                } else {
                    alert(response.result.errmsg);
                }
            }
        });
        return false;
    }

    //删除部门弹出层
    function delete_department(event) {
        //赋值到确认弹出层中
        jQuery('#modal-delete-department').find('.span_department').text('"' + event.data.name + '"');
        jQuery('#modal-delete-department').find('button[name=btn_delete]').attr('data-id', event.data.id);
    }

    //=====================编辑部门（结束）===============

    //======================编辑用户属性字段=================
    //绑定用户属性相关事件
    function bind_member_fields_event() {

        //成员属性排序
        jQuery('#div_member_fields').sortable({ handle : '.task-sort-icon', opacity : 0.6, stop : function(){
            jQuery('#div_member_fields .task').each(function(i, task){
                jQuery(task).find('input[type=hidden]').val(i);
            });
        }});

        //新增一个成员属性
        jQuery('#a_member_field_add').on('click', function(){
            var a = jQuery(this);
            var i = jQuery('#div_member_fields .ext').size();
            var html='<div class="task ext">' +
                    '<input type="hidden" name="fields[' + i + '][priority]" value="'+(i+6)+'" />'+
                    '<span class="fa fa-trash-o pull-right"></span>'+
                    '<div class="fa fa-arrows-v task-sort-icon"></div>'+
                    '<div class="action-checkbox">'+
                    '<label class="px-single">'+
                    '<input type="checkbox" checked="checked"  name="fields[' + i + '][status]" value="1" class="px">'+
                    '<span class="lbl"></span>'+
                    '</label>'+
                    '</div>'+
                    '<div class="col-xs-8">'+
                    '<input type="text" class="form-control" value="" name="fields[' + i + '][desc]">'+
                    '</div>'+
                    '</div>';
            html = jQuery(html);
            html.find('.fa-trash-o').on('click', remove_member_field);
            a.before(html);
            //最多添加10个
            if (i >= 9) {
                a.hide();
            }
        });

        //用户属性提交事件
        jQuery('#form_member_fields').on('submit', function(){
            var form = jQuery(this);
            if (form.attr('data-submiting') == 1) {
                return;
            } else {
                form.attr('data-submiting', 1);
            }
            jQuery.ajax(form.attr('action'), {
                type : form.attr('method'),
                data : form.serialize(),   
                dataType : 'json',
                success : function(response) {
                    form.attr('data-submiting', 0);
                    if (response.result.errcode == 1) {
                        jQuery('#modal-edit-fields').modal('hide');
                    } else {
                        alert(response.result.errmsg);
                    }
                }
            });
            return false;
        });
        jQuery('#div_member_fields').find('.fa-trash-o').on('click', remove_member_field);
    }

    function remove_member_field() {
        jQuery(this).parents('.ext').remove();
        jQuery('#a_member_field_add').show();
        jQuery('#div_member_fields .ext').each(function(i, ext){
            jQuery(ext).find('input[type=hidden]').attr('name', 'fields[' + i + '][priority]');
            jQuery(ext).find('input[type=hidden]').val(i+6);
            jQuery(ext).find('input[type=checkbox]').attr('name', 'fields[' + i + '][status]');
            jQuery(ext).find('input[type=text]').attr('name', 'fields[' + i + '][text]');
        });
    }
    //======================编辑用户属性字段=================
</script>
{include file="$tpl_dir_base/footer.tpl"}