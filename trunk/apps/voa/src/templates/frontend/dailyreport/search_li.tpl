{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_dailyreport_updated = {$updated};
</script>

{foreach $list as $v}
<li><a href="/dailyreport/view/{$v.dr_id}" class="m_link">
	<i>{$v.m_username}</i>
	{$v['_reporttime_fmt']['m']}-{$v['_reporttime_fmt']['d']} {$weeknames[$v['_reporttime_fmt']['w']]}日报
</a></li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}