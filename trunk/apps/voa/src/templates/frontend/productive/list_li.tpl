{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_page = {$page};
</script>
{foreach $list as $v}
<li>
	<a class="m_link" href="/frontend/productive/view/pt_id/{$v['pt_id']}">
		<h1>{$shops[$v['csp_id']]['csp_name']}</h1>
		<p>{$v['m_username']}</p><time>{$v['_created_u']}</time>
	</a>
</li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}