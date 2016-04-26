{include file='admincp/header.tpl'}

<table class="table table-striped table-hover font12">
	<colgroup>
		<col />
		<col width="18%" />
		<col width="13%" />
		<col width="10%" />
		<col width="15%" />
		<col width="15%" />
		<col width="18%" />
	</colgroup>
	<thead>
		<tr>
			<th>手机号</th>
			<th>Email</th>
			<th>真实姓名</th>
			<th>帐号状态</th>
			<th>管理组名称</th>
			<th>上次登录</th>
			<th>操作</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="7"></td>
		</tr>
	</tfoot>
	<tbody>
{foreach $adminerList as $_ca_id=>$_ca}
		<tr>
			<td>{$_ca['ca_mobilephone']|escape}</td>
			<td>{$_ca['ca_email']|escape}</td>
			<td>{$_ca['ca_username']|escape}</td>
			<td>{$_ca['_locked']}</td>
			<td>{$_ca['_grouptitle']|escape}<br />({$_ca['_groupenable']})</td>
			<td>{$_ca['_lastlogin']}<br />{$_ca['ca_lastloginip']}</td>
			<td>
		{if $_ca['ca_locked'] != $systemadminer}
				{$base->linkShow($deleteUrlBase, $_ca_id, '删除', 'fa-times')}
		{else}
				{$base->linkShow(false, $_ca_id, '删除', 'fa-times')}
		{/if} | 
				{$base->linkShow($editUrlBase, $_ca_id, '编辑', 'fa-edit')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="7" class="warning">
				暂无管理成员，请添加
			</td>
		</tr>
{/foreach}
	</tbody>
</table>

{include file='admincp/footer.tpl'}