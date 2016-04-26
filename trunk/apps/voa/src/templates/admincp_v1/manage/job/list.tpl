{include file='admincp/header.tpl'}

{if $formActionUrl}
<form class="form-horizontal" role="form" method="post" action="{$formActionUrl}">
<input type="hidden" name="formhash" value="{$formhash}" />
{/if}
<table class="table table-striped table-hover font12">
	<colgroup>
		<col width="65" />
		<col width="100" />
		<col />
	</colgroup>
	<thead>
		<tr>
			<th><label class="vcy-label-none"><input type="checkbox" id="checkALL" onchange="javascript:checkAll(this,'delete');"{if !$formActionUrl} readonly="readonly" disabled="disabled"{/if} /> 删除</label></th>
			<th>显示顺序</th>
			<th>职务名称</th>
		</tr>
	</thead>
{if $jobList}
	<tfoot>
		<tr>
			<td colspan="3" class="text-right"><button type="submit" class="btn btn-primary"{if !$formActionUrl} readonly="readonly" disabled="disabled"{/if}>提交更新</button></td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $jobList as $_cj_id => $_cj}
		<tr>
			<td><input type="checkbox" name="delete[{$_cj_id}]" value="{$_cj_id}"{if !$formActionUrl} disabled="disabled"{/if} /></td>
			<td><input type="text" class="form-control" name="update[{$_cj_id}][cj_displayorder]" placeholder="0到99整数" value="{$_cj['cj_displayorder']}" maxlength="2"{if !$formActionUrl} readonly="readonly"{/if} /></td>
			<td>
				<input type="text" class="form-control" name="update[{$_cj_id}][cj_name]" placeholder="填写职务名称，不可重名" value="{$_cj['cj_name']|escape}" maxlength="30" required="required"{if !$formActionUrl} readonly="readonly"{/if} />
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="3" class="warning">暂无职务信息，请添加新职务。</td>
		</tr>
{/foreach}
	</tbody>
</table>
{if $formActionUrl}
</form>
{/if}
{include file='admincp/footer.tpl'}