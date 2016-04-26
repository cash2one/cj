{include file='cyadmin/header.tpl'}
<script>
    $(function() {
        $('#sandbox-container .input-daterange').datepicker({
            todayHighlight: true
        });
    });
</script>
<div class="panel panel-default">
    <div class="panel-heading">列表</div>

    <div class="panel-body">
        <form class="form-horizontal" action="{$form_url}" method="get">
            <div class="form-group "><label class="control-label col-sm-1">企业名称</label>
                <div class="col-sm-2">
                    <input type="text" name="company" value="{$condi['company']|escape}" placeholder="" class="input-sm form-control"> <span class="help-block"></span>
                </div>
                <label class="col-xs-2" style="text-align: right; margin-top: 0px; margin-bottom: 0px; padding-top: 7px;">联系人姓名</label>
                <div class="col-sm-2">
                    <input type="text" name="fullname" value="{$condi['fullname']|escape}" placeholder="" class="input-sm form-control"> <span class="help-block"></span>
                </div>
            </div>
            <div class="form-group "><label class="control-label col-sm-1"
                                            for="title">时间</label>
                <div class="col-md-4" id="sandbox-container">
                    <div class="input-daterange input-group" id="datepicker"><input
                                type="text" class="input-sm form-control"
                                value="{$condi['created_begintime']|escape}" name="created_begintime"> <span
                                class="input-group-addon">to</span> <input type="text"
                                                                           class="input-sm form-control" value="{$condi['created_endtime']|escape}"
                                                                           name="created_endtime"></div>
                </div>

                <input type="hidden" name="issearch" value="1"/>
                <button name="submit" value="1" type="submit"
                        class="btn btn-primary  input-sm">搜 索</button>
                <button name="export" value="export" type="submit"
                        class="btn btn-primary  input-sm">导出</button>
            </div>
        </form>
        <form action="{$form_delete_url}" method="post">
            <table class="table table-striped table-hover font12">
                <colgroup>
                    <col class="t-col-2" />
                    <col class="t-col-10" />
                    <col class="t-col-6" />
                    <col class="t-col-6" />
                    <col class="t-col-6" />
                    <col class="t-col-6" />
                    <col class="t-col-6" />
                    <col class="t-col-10" />
                    <col class="t-col-15" />

                </colgroup>
                <thead>
                <tr>
                    <th><input type="checkbox" class="px" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$form_delete_url && !$total} disabled="disabled"{/if} />
                        <span class="lbl">全选</span></th>
                    <th>联系人姓名</th>
                    <th>联系人电话</th>
                    <th>邮箱</th>
                    <th>代理区域</th>
                    <th>公司名称</th>
                    <th>提交时间</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <td colspan = '1' class= "text-right">{if $form_delete_url}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
                    <td colspan="9" class="text-right">{$multi}</td>
                </tr>
                </tfoot>
                <tbody>
                {if $list}
                    {foreach $list as $val}
                        <tr>
                            <td><input type="checkbox" class="px" name="delete[{$val['aid']}]" value="{$val['aid']}" /></td>
                            <td>{$val['fullname']}</td>
                            <td>{$val['telephone']}</td>
                            <td>{$val['email']}</td>
                            <td>{$val['region']}</td>
                            <td>{$val['company']}</td>
                            <td>{$val['created']}</td>
                            <td>
                                {$base->show_link($view_url, $val['aid'], '详情', 'fa-edit')}
                                {$base->show_link($delete_url, $val['aid'], '删除', 'fa-delete')}
                            </td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="10" class="warning">{if $issearch}未搜索到指定条件的{$module_plugin['cp_name']|escape}数据{else}暂无任何{$module_plugin['cp_name']|escape}数据{/if}</td>
                    </tr>
                {/if}
                </tbody>
            </table>
            <div class="control-label col-sm-1">

        </form>
    </div>
</div>
</div>
{include file='cyadmin/footer.tpl'}
