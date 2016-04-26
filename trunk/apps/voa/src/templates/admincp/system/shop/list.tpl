{include file="$tpl_dir_base/header.tpl"}

{include file="$tpl_dir_base/system/shop/search_form.tpl"}

<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			门店列表
		</div>
	</div>
	<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}">
	<input type="hidden" name="formhash" value="{$formhash}" />
{include file="$tpl_dir_base/system/shop/list_data.tpl"}
	</form>
</div>

{include file="$tpl_dir_base/footer.tpl"}