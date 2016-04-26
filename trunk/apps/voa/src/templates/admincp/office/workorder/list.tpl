{include file="$tpl_dir_base/header.tpl"}
{include file="$tpl_dir_base/office/workorder/search_form.tpl"}

<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">工单列表</div>
	</div>
{$var_is_search = false}
{include file="$tpl_dir_base/office/workorder/search_list.tpl"}
</div>

{include file="$tpl_dir_base/footer.tpl"}