{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_minutes_updated = {$updated};
</script>

{foreach $list as $v}
<li>
	<a href="/minutes/view/{$v.mi_id}">
		<time datetime="{$v['_ymd']}">
			<h2>{$v['_created_fmt']['y']}年{$v['_created_fmt']['m']}月</h2>
			<h1>{$v['_created_fmt']['d']}</h1><h3>{$weeknames[$v['_created_fmt']['w']]}</h3>
		</time>
		<h1>{$v['_subject']}</h1>
		<time datetime="{$v['_hi']}">{$v['_hi']}</time>
	</a>
</li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}
