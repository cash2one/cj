{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_wxwall_updated = {$updated};
</script>
{foreach $fin_list as $v}
<li>
	<a href="/wxwall/view/{$v['ww_id']}" class="m_link"><h1>{$v['ww_subject']}</h1><time>{$v['_endtime']} 结束</time></a>
</li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}