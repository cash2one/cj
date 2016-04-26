{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_askoff_updated = {$updated};
</script>

{foreach $list as $v}
<li>
    <a href="/askoff/view/{$v['ao_id']}">
		<h1>{$v['m_username']} {$types[$v['ao_type']]} {$v['_timespace']}</h1>
		<time>{$v['_begintime_md']}-{$v['_endtime_md']}</time>
        <i class="{$v['_status_class']}">{$v['_status_tip']}</i>
    </a>
</li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}