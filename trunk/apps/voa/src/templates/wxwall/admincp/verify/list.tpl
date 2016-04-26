{include file='wxwall/admincp/header.tpl'}

<ul class="nav nav-tabs font-12">
{foreach $post_status_descriptions as $_id => $_n}
	<li{if $_id == $viewStatus} class="active"{/if}><a href="{$updateStatusUrlBase}{$_id}"><strong>{$_n}</strong></a></li>
{/foreach}
</ul>

<table class="table table-striped table-hover font-12">
	<colgroup>
		<col class="col-w-12" />
		<col />
		<col class="col-w-12" />
		<col class="col-w-7" />
		<col class="col-w-7" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-center">用户名</th>
			<th>消息内容</th>
			<th class="text-center">发布时间</th>
			<th class="text-center">删除</th>
			<th class="text-center">审核</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="5" class="text-right">{$multi}&nbsp;</td>
		</tr>
	</tfoot>
	<tbody>
{foreach $msgList as $_wwp_id => $_wwp}
		<tr>
			<td class="text-center">
				<img src="{$_wwp['_face']}" alt="{$_wwp['m_username']|escape}" class="img-circle wxscreen-avator" />
				{$_wwp['m_username']|escape}
			</td>
			<td>{$_wwp['_message']}</td>
			<td class="text-center">{$_wwp['_created']}</td>
			<td class="text-center"><a href="{$deleteUrlBase}{$_wwp_id}" class="_op btn btn-danger btn-sm">删除</a></td>
			<td class="text-center"><a href="{$setStatusUrlBase[$_wwp['wwp_status']]['url']}{$_wwp_id}" class="_op btn btn-{$setStatusUrlBase[$_wwp['wwp_status']]['classname']} btn-sm">{$setStatusUrlBase[$_wwp['wwp_status']]['name']}</a></td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="5" class="warning">
				暂无消息数据
			</td>
		</tr>
{/foreach}
	</tbody>
</table>

{include file='wxwall/admincp/footer.tpl'}