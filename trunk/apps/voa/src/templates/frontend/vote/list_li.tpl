{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_vote_updated = {$updated};
</script>

{foreach $list as $v}
<li><a href="/vote/view/{$v['v_id']}" class="m_link">
	<h1>{$v['v_subject']}</h1>
	<time>{if $v['v_endtime'] > $timestamp}{$v['_begintime']} 开始{else}{$v['_endtime']} 结束{/if}</time>
</a></li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}