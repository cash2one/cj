{include file="$tpl_dir_base/header.tpl"}

<div class="table-light">
	<div class="table-header">
		<div class="row">
			<div class="col-sm-2 table-current-postion"></div>
			<div class="col-sm-9 actionBar pull-right text-right">
			   <form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
				    <a href="?ac=style#/add" class="btn btn-default "><i class="fa fa-plus"></i>&nbsp;添加</a>
		           	<a href="?ac=express" class="btn btn-default "><i class="fa fa-list"></i>&nbsp;快递设置</a>
		           	<a href="?ac=cate" class="btn btn-default "> <i class="fa fa-sitemap"></i>&nbsp;产品分类</a>
		           	<a href="?ac=style#/config" class="btn btn-default "> <i class="fa fa-gear"></i>&nbsp;产品配置</a>
		           	<span class="space"></span>
				    <input type="hidden" name="issearch" value="1" />
			        <input type="text" class="form-control form-small" id="p_subject" name="p_subject" placeholder="标题" value="" maxlength="30">
			        <button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
			        <span class="space"></span>
			        <a href="?ac=putout" class="btn btn-default ">&nbsp;导出</a>
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
		<col class="t-col-8" />
		<col class="t-col-8" />
		<col  />
		<col class="t-col-14"/>
		<col class="t-col-10" />
		<col class="t-col-10" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
            <th>分类</th>
            <th>价格</th>
            <th>售出总数</th>
            <th>标题</th>
            <th>录入时间</th>
            <th>提成比例</th>
            <th>操作</th>
		</tr>
	</thead>
	{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">{if $deleteUrlBase}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="6" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
	{/if}
	<tbody>
	{if empty($list)}
    <tr>
		<td colspan="8" class="warning">{if $issearch}未搜索到指定条件的话题信息{else}暂无任何话题信息{/if}</td>
	</tr>
	{else}
	{foreach $list as $_id=>$_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_id}]" class="px" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td>{$classes[$_data['classid']]['classname']}</td>
			<td>{$_data['price']}</td>
			<td>{$_data['saledcount']}</td>
			<td class="text-left">{$_data['subject']|escape}</td>
			<td>{$_data['created']}</td>
			<td>{$_data['percentage']}</td>
			<td>
				{$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
				{$base->linkShow($viewUrlBase, $_id, '详情', 'fa-eye', '')}
			</td>
		</tr>
	{/foreach}
	{/if}

	</tbody>
</table>
</form>
</div>
{include file="$tpl_dir_base/footer.tpl"}
