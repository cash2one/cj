{if $form_delete_action_url}

<form class="form-horizontal" role="form" method="get" action="{$form_delete_action_url}">
<!--
<input type="hidden" name="formhash" value="{$formhash}" />
-->
{/if}
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			记录列表
		</div>
	</div>
<table class="table table-striped table-hover table-bordered font12">
	<colgroup>
		<col />
		<col class="t-col-20"/>
		<col class="t-col-20"/>
		<col class="t-col-20"/>
		<col class="t-col-15" />
		<col class="t-col-10" />
	</colgroup>
	<thead>
		<tr>
			<th>姓名</th>
			<th>微信号</th>
			<th>手机号码</th>
			<th>邮箱地址</th>
			<th>部门</th>
			<th>职位</th>
		</tr>
	</thead>
{if $member_list}
	<tfoot>
		<tr>
			<td colspan="6" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $member_list as $_m}
		<tr>
			<td>{$_m['_realname']|escape}</td>
			<td>{$_m['mf_weixinid']}</td>
			<td>{$_m['m_mobilephone']}</td>
			<td>{$_m['m_email']}</td>
			<td>{$_m['_department']|escape}</td>
			<td>{$_m['_job']|escape}</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="6" class="warning">{$emptyResultTipMessage}</td>
		</tr>
{/foreach}
	</tbody>
</table>
{if $form_delete_action_url}
</form>
{/if}
</div>