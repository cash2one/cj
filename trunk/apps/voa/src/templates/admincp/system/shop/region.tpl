{include file="$tpl_dir_base/header.tpl"}

<ul class="nav nav-tabs font12">
	<li class="active"><a href="{$add_region_url}"><i class="fa fa-plus"></i> 区域管理</a></li>
	<li><a href="#id-region-batch" data-toggle="tab"><i class="fa fa-cloud-upload"></i> 批量导入区域数据</a></li>
</ul>

<div class="tab-content">
	<div class="tab-pane active" id="id-region-manage">
		{include file="$tpl_dir_base/system/shop/region_list.tpl"}
	</div>
	<div class="tab-pane" id="id-region-batch">
		{include file="$tpl_dir_base/system/shop/region_batch.tpl"}
	</div>
</div>



{include file="$tpl_dir_base/footer.tpl"}