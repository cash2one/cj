{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_askfor_updated = {$updated};
</script>

{foreach $list as $v}
<li>
	<a href="/askfor/view/{$v.af_id}" class="m_link">
		<p style="margin:0;padding:0;float:right;margin-right:30px;font-size:12px;color:#888">
			{$v._status}
		</p>
		<p style="margin:0;padding:0;float:left;">
			<h1><span>{$v.af_subject}</span></h1>
			<h2>{$v.m_username} {$v._created}</h2>
		</p>
	</a>
</li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}