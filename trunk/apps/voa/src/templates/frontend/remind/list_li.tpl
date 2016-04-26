{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_remind_updated = {$updated};
</script>

{foreach $list as $v}
<li><a href="/remind/view/{$v.rm_id}" class="m_link"><h1><span>{$v.rm_subject}</span></h1><h2>{$v.m_username} {$v._created}</h2></a></li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}
