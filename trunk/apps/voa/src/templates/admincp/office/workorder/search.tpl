{include file="$tpl_dir_base/header.tpl"}
{include file="$tpl_dir_base/office/workorder/search_form.tpl"}

{if $submit_search}
	<div class="table-light">
		<div class="table-header">
			<div class="table-caption font12">搜索结果</div>
		</div>
	{$var_is_search = true}
	{include file="$tpl_dir_base/office/workorder/search_list.tpl"}
</div>
{/if}

{include file="$tpl_dir_base/footer.tpl"}