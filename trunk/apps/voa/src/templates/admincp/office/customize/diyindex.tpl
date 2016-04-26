{include file="$tpl_dir_base/header.tpl"}

<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			记录列表
			<a href="{$index_url}?act=update" class="pull-right btn btn-xs btn-primary">添加自定义首页</a>
		</div>
	</div>
	
	<form class="form-horizontal" id="soform" role="form" method="get" action="{$index_url}?act=del">
	<table class="table table-striped table-bordered table-hover font12">
	<colgroup>
		<col class="t-col-5">
		<col class="t-col-20">
		<col class="t-col-10">
		<col class="t-col-15">
		<col class="t-col-15">
	</colgroup>
	<thead>
	<tr>
		<th>
			<label class="checkbox">
				<input type="checkbox" class="px" id="delete-all" onchange="javascript:checkAll(this, 'tiid');">
				<span class="lbl">全选</span>
			</label>
		</th>
		<th>(首页名称) 首页会直接与服务号关联</th>
		<th>关联状态</th>
		<th>创建时间</th>
		<th>操作</th>
	</tr>
	</thead>
	{if !empty($multi)}
    <tfoot>
        <tr>
            <td colspan="5" class="text-right vcy-page">{$multi}</td>
        </tr>
    </tfoot>
	{/if}
	<tbody>
	{foreach $list as $_id => $_v}
	<tr>					
		<td><input type="checkbox" name="tiid[]" value="{$_v['tiid']}" /></td>
		<td><a href="{$index_url}?act=update&tiid={$_v['tiid']}">{$_v['subject']|escape}</a></td>
		<td>{if 0 == $_v['related']}未关联{else}已关联{/if}</td>
        <td>{$_v['_updated']}</td>
		<td>
			<a href="{$index_url}?act=update&tiid={$_v['tiid']}"><i class="fa fa-edit"></i> 编辑</a>
			{if 1 == $_v['tiid']}
	        {else}
	        |
	        <a href="{$index_url}?act=del&tiid={$_v['tiid']}" class="text-danger _delete"><i class="fa fa-times"></i> 删除</a>
	        {/if}
		</td>
	</tr>	
	{foreachelse}
    <tr>
        <td colspan="5" class="warning">{if $issearch}未搜索到指定条件的首页信息{else}暂无对应数据{/if}</td>
    </tr>
	{/foreach}			
	</tbody>
	</table>
	</form>
</div>

<script type="text/javascript">
$(function() {
	// 全选
	$('#chkall').on('click', function(e) {
		var self = $(this);
		self.parents("form").find("input[name='tiid[]']").each(function(ipt) {
			$(this).prop('checked', self.prop('checked'));
		});
	});
});
</script>

{include file="$tpl_dir_base/footer.tpl"}
