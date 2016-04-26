{if $form_delete_action_url}
<form class="form-horizontal" role="form" method="get" action="{$form_delete_action_url}">
<!--
<input type="hidden" name="formhash" value="{$formhash}" />
-->
{/if}
<table class="table table-striped table-hover font12">
	<colgroup>
		<col class="t-col-8" />
		<col class="t-col-9" />
		<col />
		<col class="t-col-15"/>
		<col class="t-col-10" />
		<col class="t-col-9" />
		<col class="t-col-10" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th><label class="vcy-label-none"><input type="checkbox" id="checkALL" onchange="javascript:checkAll(this,'id');"{if !$form_delete_action_url} disabled="disabled"{/if} /> 删除</label></th>
			<th>工号</th>
			<th>姓名</th>
			<th>手机号码</th>
			<th>性别</th>
			<th>部门</th>
			<th>职位</th>
			<th>操作</th>
		</tr>
	</thead>
{if $member_list}
	<tfoot>
		<tr>
			<td colspan="3"><button type="submit" class="btn btn-primary btn-sm"{if !$form_delete_action_url} disabled="disabled"{/if}>批量删除</button></td>
			<td colspan="5" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $member_list as $_m}
		<tr>
			<td><input type="checkbox" name="id[{$_m['m_uid']}]" value="{$_m['m_uid']}"{if !$form_delete_action_url} disabled="disabled"{/if} /></td>
			<td>{$_m['m_number']|escape}</td>
			<td>{$_m['_realname']|escape}</td>
			<td>{$_m['m_mobilephone']}</td>
			<td>{$_m['_gender']}</td>
			<td>{$_m['_department']|escape}</td>
			<td>{$_m['_job']|escape}</td>
			<td>
				{$base->linkShow($delete_url_base, $_m['m_uid'], '删除', 'fa-times', 'class="_delete"')} | {$base->linkShow($edit_url_base, $_m['m_uid'], '编辑', 'fa-edit')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="8" class="warning">{$emptyResultTipMessage}</td>
		</tr>
{/foreach}
	</tbody>
</table>
{if $form_delete_action_url}
</form>
{/if}