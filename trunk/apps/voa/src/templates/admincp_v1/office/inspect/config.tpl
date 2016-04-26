{include file='admincp/header.tpl'}
        
<div class="panel panel-default">
<div class="panel-heading"><a href="{$subListUrl}">/根目录</a> {if $pid} > {$current['insi_name']}{/if}</div>

  <div class="panel-body">
  <form class="form-horizontal" role="form" method="post" action="{$deleteUrlBase}">
<table class="table table-striped table-hover font12">
    <colgroup>
        <col class="t-col-5" />
        <col />
        <col class="t-col-25" />
        
        <col class="t-col-8" />
        <col class="t-col-15" />
        <col class="t-col-15" />
        <col class="t-col-15" />
    </colgroup>
    <thead>
        <tr>
            <th><label class="vcy-label-none"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase} disabled="disabled"{/if} />删除</label></th>
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
            <span id="btn-add" url="{$editUrl}" class="btn btn-primary input-sm">
            <i class="fa fa-plus"></i>
            <botton>新增</botton>
            </span>
            </td>
            <td colspan="7" class="text-right vcy-page">{$multi}</td>
        </tr>
    </tfoot>

    <tbody>
{foreach $list as $_id=>$_data}
        <tr>
            <td class="text-left"><input type="checkbox" name="delete[{$_id}]" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /></td>
            {if !$pid}
            <td>{$_data['insi_name']|escape}</td>
            {/if}
            <td>{$_data['insi_describe']|escape}</td>
            {if $pid}
            <td>{$_data['insi_rules']|escape}</td>
            {/if}
            <td>{$_data['insi_ordernum']|escape}</td>
            <td>{$_data['insi_created']}</td>
            <td>{$_data['insi_updated']}</td>
            <td>
                {$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="_delete"')} | 
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
{include file='admincp/footer.tpl'}