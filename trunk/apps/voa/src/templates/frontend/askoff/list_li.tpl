{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_askoff_updated = {$updated};
</script>

{foreach $list as $v}
<li>
    <a class="m_link" href="/askoff/view/{$v['ao_id']}">
        <time>{$v['_begintime_md']}-{$v['_endtime_md']}</time>
        <b>{$types[$v['ao_type']]}</b>
        <span>{$v['_timespace']}</span>
        <i class="{$v['_status_class']}">{$v['_status_tip']}</i>
    </a>
</li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}