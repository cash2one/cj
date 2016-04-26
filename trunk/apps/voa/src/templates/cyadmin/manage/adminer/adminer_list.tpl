{include file='cyadmin/header.tpl'}

<table class="table table-striped table-hover font12">
	<colgroup>
		<col />
		<col class="t-col-12" />
		<col class="t-col-12" />
		<col class="t-col-11" />
		<col class="t-col-16" />
		<col class="t-col-17" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th>登录名</th>
			<th>真实姓名</th>
			<th>手机号</th>
			<th>帐号状态</th>
			<th>管理组名称</th>
			<th>上次登录</th>
			<th>操作</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="7" class="text-right">{$adminer_multi}</td>
		</tr>
	</tfoot>
	<tbody>
{foreach $adminer_list as $_ca_id=>$_ca}
		<tr{if $_ca['ca_locked'] == $system_adminer} class="text-danger"{/if}>
			<td>{$_ca['ca_username']|escape}</td>
			<td>{$_ca['ca_realname']|escape}</td>
			<td>{$_ca['ca_mobilephone']|escape}</td>
			<td>{$_ca['_locked']}</td>
			<td>{$_ca['_cag_title']|escape}<br />({$_ca['_cag_enable']})</td>
			<td>{$_ca['_lastlogin']}<br />{$_ca['ca_lastloginip']}</td>
			<td>
		{if $_ca['ca_locked'] != $system_adminer}
				{$base->show_link($delete_url_base, $_ca_id, '删除', 'fa-trash-o', 'class="_delete"')}
		{else}
				{$base->show_link(false, $_ca_id, '删除', 'fa-trash-o')}
		{/if} | 
				{$base->show_link($edit_url_base, $_ca_id, '编辑', 'fa-edit')}
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

{include file='cyadmin/footer.tpl'}