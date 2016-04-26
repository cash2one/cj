{include file="$tpl_dir_base/header.tpl"}

<div class="table-light">
	<div class="table-header">
		<div class="row">
			<div class="col-sm-2 table-current-postion"></div>
			<div class="col-sm-8 actionBar pull-right text-right">
			   <form class="form-inline vcy-from-search" role="form" method="post" action="{$searchActionUrl}">
		           	<a href="?ac=style#/config" class="btn btn-default "> <i class="fa fa-gear"></i>&nbsp;客户配置</a>
		           	<span class="space"></span>
				    <input type="hidden" name="issearch" value="1" />
			        <input type="text" class="form-control form-small" id="truename" name="truename" placeholder="姓名" value="" maxlength="30">
			        <button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
		       </form>
           	</div>
		</div>
	</div>
	<form class="form-horizontal" role="form" method="post" action="{$deleteUrlBase}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<table class="table table-striped table-bordered table-hover font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-10" />
		<col class="t-col-10" />
		<col class="t-col-10" />
		<col class="t-col-10"/>
		<col class="t-col-10" />
		<col class="t-col-10" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
            <th>客户所属</th>
            <th>姓名</th>
            <th>手机</th>
            <th>年龄</th>
            <th>创建时间</th>
            <th>操作</th>
		</tr>
	</thead>
	{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">{if $deleteUrlBase}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="5" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
	{/if}
	<tbody>
	{foreach $list as $_id=>$_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_id}]" class="px" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td>{$_data['username']}</td>
			<td>{$_data['truename']}</td>
			<td>{$_data['mobile']}</td>
			<td>{$_data['ages']}</td>
			<td>{$_data['_created']}</td>
			<td>
				{$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
				{$base->linkShow($viewUrlBase, $_id, '详情', 'fa-eye', '')}
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="7" class="warning">{if $issearch}未搜索到指定条件的客户信息{else}暂无任何客户信息{/if}</td>
		</tr>
	{/foreach}
	</tbody>
</table>
</form>
</div>

{include file="$tpl_dir_base/footer.tpl"}
