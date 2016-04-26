{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_notice_updated = {$updated};
</script>

{foreach $list as $v}
<li>
	<a href="/notice/view/{$v.nt_id}">
		<h1>{$v.nt_subject}</h1>
		<time datetime="{$v._created_hi}">{$v._created}</time><p>{$v.nt_author}</p>
	</a>
</li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}
