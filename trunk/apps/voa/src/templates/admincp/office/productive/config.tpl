{include file="$tpl_dir_base/header.tpl"}
<div class="table-light">
    <div class="table-header">
        <div class="table-caption font12">
            <a href="{$subListUrl}">/根目录</a> {if $pid} > {$current['pti_name']}{/if}              
            <div class="DT-lf-right">   
            <span id="btn-add" url="{$editUrl}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i>
            <botton>新增</botton>
            </span>
            </div>
        </div>  
    </div>
<form class="form-horizontal" role="form" method="post" action="{$deleteUrlBase}">
<table class="table table-striped table-hover font12 table-bordered">
    <colgroup>
        <col class="t-col-5" />
        <col class="t-col-20" />
        <col />        
        <col class="t-col-8" />
        <col class="t-col-15" />
        <col class="t-col-15" />
        <col class="t-col-15" />
    </colgroup>
    <thead>
        <tr>
            <th><label class="checkbox"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase} disabled="disabled"{/if} class="px" /><span class="lbl">全选</span></label></th>
            {if !$pid}
            <th>打分项名称</th>
            {/if}
            <th>打分项说明</th>
            {if $pid}
            <th>打分详细规则</th>
            {/if}
            <th>排序</th>
            <th>创建时间</th>
            <th>更新时间</th>
            <th>操作</th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <td colspan="2">{if $total > 0}{if $deleteUrlBase}<button type="submit" class="btn btn-danger">批量删除</button>{/if}{/if}            
            </td>
            <td colspan="7" class="text-right vcy-page">{$multi}</td>
        </tr>
    </tfoot>

    <tbody>
{foreach $list as $_id=>$_data}
        <tr>
            <td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_id}]" class="px" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
            {if !$pid}
            <td class="text-left">{$_data['pti_name']|escape}</td>
            {/if}
            <td class="text-left">{$_data['pti_describe']|escape}</td>
            {if $pid}
            <td class="text-left">{$_data['pti_rules']|escape}</td>
            {/if}
            <td>{$_data['pti_ordernum']|escape}</td>
            <td>{$_data['pti_created']}</td>
            <td>{$_data['pti_updated']}</td>
            <td>
                {$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
                {$base->linkShow($editUrl, $_id, '编辑', 'fa-edit', '')}
                {if !$pid}
                {$base->linkShow($subListUrl, $_id, '子项', 'fa-list', '')}
                {/if}
            </td>
        </tr>
{foreachelse}
        <tr>
            <td colspan="9" class="warning">{if $search}未搜索到指定条件的信息{else}暂无信息{/if}</td>
        </tr>
{/foreach}
    </tbody>
</table>
</form>
  </div>

<script>

{literal} 
$(function () {
$('#btn-add').click(function () {
        location.href = $(this).attr('url');
   });   
 });
{/literal}
</script>
{include file="$tpl_dir_base/footer.tpl"}