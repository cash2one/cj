{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_page = {$page};
</script>
{foreach $list as $v}
<li>
	<a class="m_link" href="/frontend/productive/editem/pt_id/{$v['pt_id']}">
		<h1>{$shops[$v['csp_id']]['csp_name']}</h1>
		{if voa_d_oa_productive::STATUS_WAITING == $v['pt_status']}<span class="unevaluated">待巡店</span>{/if}
	</a>
</li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}