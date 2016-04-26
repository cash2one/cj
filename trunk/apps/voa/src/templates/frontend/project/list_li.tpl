{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
	var _project_updated = '{$updated}';
	var _page = {$page};
	var _count = {$count};
</script>

{foreach $list as $v}
<li>
	<a href="/project/view/{$v['p_id']}" class="m_link">
		<h1><span>{$v['p_subject']}</span></h1>
		<h2>{$v['m_username']} {$v['_updated']} {if voa_d_oa_project::STATUS_CLOSED == $v['p_status']}<font color="#990000">已关闭</font>{elseif 100 == $v['p_progress']}<font color="#000099">已完成</font>{else}总进度: <font color="#009900">{$v['p_progress']}%</font>{/if}</h2>
	</a>
</li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}