{if $inajax}{include file='frontend/header_ajax.tpl'}{/if}

<script reload="1">
_reimburse_updated = {$updated};
</script>

{foreach $list as $v}
<li><a href="/reimburse/view/{$v.rb_id}">
	<h1>{$v['_subject']}</h1>
	<time>{$v['_updated_u']}</time><p>{$v['rb_username']}  最后更新</p>
	<i class="{$v['_status_class']}">{$v['_status_tip']}</i>
</a></li>
{/foreach}

{if $inajax}{include file='frontend/footer_ajax.tpl'}{/if}