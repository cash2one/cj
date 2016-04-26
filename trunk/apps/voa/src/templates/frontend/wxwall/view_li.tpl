{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_wxwall_updated = '{$updated}';
</script>
{foreach $posts as $v}
{$v['m_username']} - {$v['wwp_message']}<br />
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}