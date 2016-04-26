{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_vnote_updated = {$updated};
</script>

{foreach $list as $v}
<li><a href="/vnote/view/{$v.vn_id}" class="m_link"><span>{$v['_subject']}</span><time>{$v['_created_md']}</time></a></li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}