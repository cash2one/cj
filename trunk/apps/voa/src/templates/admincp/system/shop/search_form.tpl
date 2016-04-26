<div class="panel panel-default font12">
	<div class="panel-heading"><strong>门店搜索</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$form_search_url}">
			<input type="hidden" name="is_search" value="1" />
			<table style="width:100%">
				<colgroup>
					<col class="t-col-15" />
					<col class="t-col-15" />
					<col class="t-col-15" />
					<col class="t-col-55" />
				</colgroup>
				<tbody>
					<tr style="height:30px">
						<td class="text-right"><label class="vcy-label-none" for="id_shop_name">门店名称：</label></td>
						<td class="text-left"><input type="text" class="form-control form-small" id="id_shop_name" name="name" placeholder="输入门店名称关键词" value="{$search_by['name']|escape}" maxlength="255" /></td>
						<td class="text-right"><label class="vcy-label-none">门店区域：</label></td>
						<td class="text-left">
{include 
	file="$tpl_dir_base/common_selector_placeregion.tpl"
	selector_name='placeregionid'
	placeregionid=$search_by['placeregionid']
	placetypeid=1
}
						</td>
					</tr>
					<tr style="height:60px">
						<td class="text-right"><label class="vcy-label-none">绑定人员：</label></td>
						<td class="text-left" colspan="2">
{include 
	file="$tpl_dir_base/common_selector_member.tpl"
	input_type='radio'
	input_name='uid'
	selector_box_id='search-uid'
	default_data=$search_by['_user_selector']
	allow_member=true
	allow_department=false
}
						</td>
						<td class="text-left">
							<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
							<span class="space"></span>
							<a class="btn btn-default form-small form-small-btn" role="button" href="{$list_url}">全部门店</a>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
