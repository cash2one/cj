{foreach $formCustomFields as $fs}
	{if $fs['id'] == 'types'}
			<div class="form-group font12">
				<label for="types" class="col-sm-3 control-label text-right">{$fs['title']}</label>
				<div class="col-sm-9">
					<input type="hidden" name="_types_displayorder[0]" value="-1" />
					<input type="hidden" name="_types_name[0]" value="" />
					<table id="types-cp" class="table table-condensed">
						<colgroup>
							<col class="t-col-10" />
							<col class="t-col-20" />
							<col />
							<col class="t-col-10" />
						</colgroup>
						<thead>
							<tr>
								<th><label class="vcy-label-none"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'_types_delete');" />删除</label></th>
								<th>显示顺序</th>
								<th>类型名称</th>
								<th>类型编号</th>
							</tr>
						</thead>
						<tbody>
			{$max_key = 0}
			{foreach $fs['value'] as $type_key => $type_name}
							<tr>
								<td><label class="checkbox-inline"><input type="checkbox" name="_types_delete[{$type_key}]" value="{$type_key}" /><span class="space"></span></label></td>
								<td><input type="number" class="form-control form-small" name="_types_displayorder[{$type_key}]" value="{$type_name@iteration}" min="0" max="99" /></td>
								<td><input type="text" class="form-control form-small" name="_types_name[{$type_key}]" value="{$type_name|escape}" maxlength="10" /></td>
								<td><input type="text" class="form-control form-small" value="{$type_key}" disabled="disabled" /></td>
							</tr>
				{if $type_key > $max_key}{$max_key = $type_key}{/if}
			{/foreach}
			{$max_key = $max_key + 1}
						</tbody>
						<tfoot>
							<tr>
								<td><a href="javascript:;" onclick="javascript:_add_types();" role="button" class="btn btn-info btn-xs">新增</a></td>
								<td><input type="number" class="form-control form-small" name="_types_displayorder[]" value="99" min="0" max="99" /></td>
								<td colspan="2"><input type="text" class="form-control form-small" name="_types_name[]" value="" maxlength="10" /></td>
							</tr>
						</tfoot>
					</table>
					<p class="help-block">{$fs['comment']}</p>
				</div>
			</div>
	{/if}
{/foreach}

<script type="text/javascript">
var max_key = {$max_key};
var last_key = {$max_key}
function _add_types() {
	if (jQuery('input[name^="_types_name"]').size() >= 15) {
		alert('最多允许添加 15 个请假类型');
		return false;
	}
	max_key++;
	var tr = jQuery("#types-cp tfoot tr:last").clone(true, true);
	tr = (tr.html()).replace(/<a[^>]+>[^>]+<\/a>/i, '');
	jQuery('<tr>'+tr+'</tr>').insertBefore("#types-cp tfoot tr:last");
}
</script>