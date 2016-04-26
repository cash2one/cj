{if !$inajax}
{include file='mobile/header.tpl'}
<body id="mod_notice_page">
{else}{include file='mobile/header_ajax.tpl'}{/if}

{if !$inajax}
<section class="ui-notice ui-notice-nopersonnel" style="padding-bottom: 80px;">
	<i></i>
	<!-- {$title} -->
	<p>{$message}</p>
</section>
{if $url}
	<footer><a href="{$url}" class="mod_button2">{lang key=message_forward}</a></footer>
{/if}
{else}{$message}<ajaxOk>{/if}

	{if !$inajax}
	{include file='mobile/footer.tpl'}
	{else}
	{include file='mobile/footer_ajax.tpl'}
{/if}