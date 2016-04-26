{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_secret_updated = {$updated};
</script>

{foreach $list as $v}
<li><a href="/secret/view/{$v.st_id}" class="m_link"><h1><span>{$v.st_subject}</span></h1><h2>{$v._created}</h2></a></li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}