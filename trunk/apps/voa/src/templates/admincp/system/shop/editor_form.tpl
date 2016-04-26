<form class="form-horizontal" role="form" action="{$form_submit_single_url}" method="post">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label class="control-label col-sm-2" for="id-name">{if $p_set['place_name_length_min'] > 0}*{/if}门店名称</label>
		<div class="col-sm-5">
			<input type="text" class="form-control" id="id-name" name="name" placeholder="最多输入{$p_set['place_name_length_max']}个字符" value="{$place['name']|escape}" maxlength="{$p_set['place_name_length_max']}"{if $p_set['place_name_length_min'] > 0} required="required"{/if} />
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-2">* 门店区域</label>
		<div class="col-sm-9">
{include 
	file="$tpl_dir_base/common_selector_placeregion.tpl"
	selector_name='placeregionid'
	placeregionid=$place['placeregionid']
	placetypeid=$place['placetypeid']
}
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-2" for="id-address">{if $p_set['place_address_length_min'] > 0}*{/if}门店地址</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="id-address" name="address" placeholder="最多输入{$p_set['place_address_length_max']}个字符" value="{$place['address']|escape}" maxlength="{$p_set['place_address_length_max']}"{if $p_set['place_address_length_min'] > 0} required="required"{/if} />
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-2">{if $p_set['place_master_count_min'] > 0}*{/if}绑定负责人</label>
		<div class="col-sm-5">
{if $p_set['place_master_count_max'] == 1}
{$master_input_type = 'radio'}
{else}
{$master_input_type = 'checkbox'}
{/if}
{include 
	file="$tpl_dir_base/common_selector_member.tpl"
	input_type=$master_input_type
	input_name='master_uid[]'
	selector_box_id='bind-master'
	default_data={$default_master}
	allow_member=true
	allow_department=false
}
		</div>
		<div class="col-sm-4">
			<span class="help-block">
			{if $p_set['place_master_count_min'] > 0 && $p_set['place_master_count_max'] > 0}
			要求绑定 {$p_set['place_master_count_min']} 到 {$p_set['place_master_count_max']} 个人
			{elseif $p_set['place_master_count_min'] > 0}
			最少要求绑定 {$p_set['place_master_count_min']} 个人
			{elseif $p_set['place_master_count_max'] > 0}
			最多只允许绑定 {$p_set['place_master_count_max']} 个人
			{/if}
			</span>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-2">{if $p_set['place_normal_count_min'] > 0}*{/if}绑定相关人</label>
		<div class="col-sm-5">
{if $p_set['place_normal_count_max'] == 1}
{$normal_input_type = 'radio'}
{else}
{$normal_input_type = 'checkbox'}
{/if}
{include 
	file="$tpl_dir_base/common_selector_member.tpl"
	input_type=$normal_input_type
	input_name='normal_uid[]'
	selector_box_id='bind-normal'
	default_data={$default_normal}
	allow_member=true
	allow_department=false
}
		</div>
		<div class="col-sm-4">
			<span class="help-block">
			{if $p_set['place_normal_count_min'] > 0 && $p_set['place_normal_count_max'] > 0}
			要求绑定 {$p_set['place_normal_count_min']} 到 {$p_set['place_normal_count_max']} 个人
			{elseif $p_set['place_normal_count_min'] > 0}
			最少要求绑定 {$p_set['place_normal_count_min']} 个人
			{elseif $p_set['place_normal_count_max'] > 0}
			最多只允许绑定 {$p_set['place_normal_count_max']} 个人
			{/if}
			</span>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-6">
			<button type="submit" class="btn btn-primary">{if $placeid}编辑{else}添加{/if}</button>
			<span class="space"></span>
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</form>
