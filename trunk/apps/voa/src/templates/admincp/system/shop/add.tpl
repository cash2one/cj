{include file="$tpl_dir_base/header.tpl"}

<ul class="nav nav-tabs font12">
	<li class="active"><a href="#add-single" data-toggle="tab"><i class="fa fa-plus"></i> 添加一个门店</a></li>
	<li><a href="#add-batch" data-toggle="tab"><i class="fa fa-cloud-upload"></i> 多个门店批量导入</a></li>
</ul>

<div class="tab-content">
	<div class="tab-pane active" id="add-single">
		<div class="panel panel-default font12">
			<div class="panel-body">
				{include file="$tpl_dir_base/system/shop/editor_form.tpl"}
			</div>
		</div>
	</div>
	<div class="tab-pane" id="add-batch">
{include file="$tpl_dir_base/system/shop/add_batch.tpl"}
	</div>
</div>

{include file="$tpl_dir_base/footer.tpl"}