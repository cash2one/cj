{include file="$tpl_dir_base/header.tpl"}

<div class="table-light">
    <div class="table-header">
        <div class="table-caption font12">红包发送记录列表</div>
    </div>
    <form class="form-horizontal" role="form" method="post" action="{$form_del_url}">
        <input type="hidden" name="formhash" value="{$formhash}" />
        <table class="table table-striped table-bordered table-hover font12">
            <colgroup>
                <col class="t-col-5" />
                <col class="t-col-9" />
                <col class="t-col-14" />
                <col class="t-col-12" />
                <col />
                <col class="t-col-9" />
                <col class="t-col-9" />
                <col class="t-col-18" />
                <col class="t-col-12" />
            </colgroup>
            <thead>
            <tr>
                <th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');" /><span class="lbl">全选</span></label></th>
                <th>发送者</th>
                <th>分配方式</th>
                <th>总金额(单位:元)</th>
                <th>已领取金额(单位:元)</th>
                <th>总个数</th>
                <th>已领取个数</th>
                <th>祝福语</th>
                <th>操作</th>
            </tr>
            </thead>
            {if !empty($multi)}
            <tfoot>
            <tr>
                <td colspan="9" class="text-right vcy-page">{$multi}</td>
            </tr>
            </tfoot>
            {/if}
            <tbody>
            {foreach $list as $_id => $_v}
            <tr>
                <td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete[{$_id}]" value="{$_id}" /><span class="lbl"> </span></label></td>
                <td>{$_v['m_username']}</td>
                <td>{$_v['_type']}</td>
                <td>{$_v['_total']}</td>
                <td>{$_v['_left']}</td>
                <td>{$_v['redpacks']}</td>
                <td>{$_v['times']}</td>
                <td class="text-left">{$_v['wishing']}</td>
                <td>
                    <!-- {$base->linkShow($del_url_base, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | -->
                    {$base->linkShow($view_url_base, $_id, '详情', 'fa-eye')}
                </td>
            </tr>
            {foreachelse}
            <tr>
                <td colspan="9" class="warning">{if $issearch}未搜索到指定条件的红包信息{else}暂无红包信息{/if}</td>
            </tr>
            {/foreach}
            </tbody>
        </table>

    </form>
</div>

{include file="$tpl_dir_base/footer.tpl"}