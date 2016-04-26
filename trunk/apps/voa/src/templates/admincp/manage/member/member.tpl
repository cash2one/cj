
<!-- 批量选择用户弹出框 -->
<div id="div_member_select" data-is-display="0" class="contacts-img ui-follow" style="display:none;position: absolute;top:180px;left:4000px;z-index:3;">
    <div class="modal-content">
        <div class="modal-header" style="background-color: #f5f5f5;">
            <button type="button" class="close">×</button>
            <h4 class="modal-title">批量选择</h4>
        </div>
        <div class="modal-body padding-sm" style="background-color:#fdfdfd;">
            <div class="row">
            </div>
        </div>
        <div class="modal-footer text-left" style="padding-top: 9px;background-color: #f5f5f5;">
            <button type="button" id="btn_member_select_invite" class="btn btn-info">邀请关注</button>
            <button type="button" id="btn_member_select_delete" class="btn btn-danger" data-toggle="modal" data-target="#modal-delete-member">删除</button>
        </div>
    </div>
</div>
<!-- 批量选择用户弹出框 -->

<!-- 用户详细信息弹出框 -->
<div id="div_member_detail" data-is-display="0" class="ui-follow" style="display:none;position: absolute;top:180px;left:4000px;z-index: 3;">
    <div class="modal-content">
        <div class="modal-header" style="background-color: #f5f5f5;">
            <button type="button" class="close">×</button>
            <h4 class="modal-title">成员资料</h4>
        </div>
        <div class="modal-body no-padding" id="div_member_detail_tpl" style="background-color:#fdfdfd;">
        </div>
        <div class="modal-footer text-left" style="padding-top: 9px;background-color: #f5f5f5;">
            <button type="button" class="btn btn-info" name="btn_member_detail_invite">邀请关注</button>
            <!--<button type="button" class="btn btn-default">设为负责人</button>-->
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal-add-member" name="btn_member_detail_edit">修改</button>
            <button type="button" class="btn btn-info" name="btn_member_detail_active">启用</button>
            <button type="button" class="btn btn-danger" name="btn_member_detail_delete">删除</button>
        </div>
    </div>
</div>


<!-- 设置用户属性弹出框 -->
<div id="modal-edit-fields" class="modal fade" role="dialog" style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">设置成员属性</h4>
            </div>

            <form name="form_member_fields" id="form_member_fields" method="post" action="{$member_fields_url}">
                <div class="panel widget-tasks">

                    <div class="panel-body no-padding-vr" id="div_member_fields">

                        {foreach from=$fields key=k item=field}
                            <div class="task {if $field.status != 2}ext{/if}">
                                <input type="hidden" name="fields[{$k}][priority]" value="" />
                                {if $field.status != 2}
                                    <span class="fa fa-trash-o pull-right"></span>
                                {/if}
                                <div class="fa fa-arrows-v task-sort-icon"></div>
                                <div class="action-checkbox">
                                    <label class="px-single">
                                        <input type="checkbox" {if $field.status == 1}checked="checked"{/if}{if $field.status == 2}checked="checked" disabled="disabled"{/if} name="fields[{$k}][status]" value="1" class="px">
                                        <span class="lbl"></span>
                                    </label>
                                </div>
                                <div class="col-xs-8">
                                    {if $field.status == 2}
                                        {$field.desc}
                                    {else}
                                        <input type="text" class="form-control" value="{$field.desc}" name="fields[{$k}][desc]">
                                    {/if}
                                </div>
                            </div>
                        {/foreach}
                        <a href="javascript:;" id="a_member_field_add">
                            <i class="fa fa-plus"></i>
                            添加
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    <button type="submit" name="btn_submit" class="btn btn-info">提交</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- 设置用户属性弹出框 -->

<script type="text/javascript">
    init.push(function () {
        jQuery('#scroll-box').slimScroll({ height: 450 });
        jQuery('#department_select').slimScroll({ height: 450 });
        jQuery('#div_member_detail .modal-body').slimScroll({ height: 449 });
    });
</script>
<!-- 添加用户弹出框 -->
<div id="modal-add-member" class="modal fade" role="dialog" style="display: none;">
    <div class="modal-dialog modal-sm" style="width:500px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">添加员工</h4>
            </div>
            <form name="form_member" id="form_member_edit" method="post">
                <div id="scroll-box">
                    <div class="modal-body tab-content-padding">
                        <input type="hidden" name="id" value="" />
                        <table class="table" id="inputs-table">
                            <tbody id="table_member_edit">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    <button type="submit" name="btn_submit" class="btn btn-info">提交</button>
                </div>
            </form>

        </div>
    </div>
</div>
<!-- 添加用户弹出框 -->

<!-- 选择部门弹出层 -->
<div id="modal-select-department" style="display: none; position: absolute;left:200px;top:100px;z-index: 10000;">
    <div class="modal-dialog" style="width:350px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close btn_close">×</button>
                <h4 class="modal-title">选择部门</h4>
            </div>
            <div class="category">
                <div class="padding-sm border-b text-bg">
                    {$department.cd_name}
                </div>
                <div class="border-t"></div>
                <div id="department_select">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 添加用户弹出框 -->