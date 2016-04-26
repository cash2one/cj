{if !$inajax}
{include file='mobile/header.tpl'}
<body id="mod_notice_page">
{else}{include file='mobile/header_ajax.tpl'}{/if}

{if !$inajax}
	<h1 class="success">{$title}</h1>
	<h2>{$message}</h2>
	{if $url}
	<footer><a href="{$url}" class="mod_button2">{lang key=message_forward}</a></footer>
	{/if}
{else}{$message}<ajaxOk>{/if}

{if !$inajax}
	{include file='mobile/footer.tpl'}
{else}
	{include file='mobile/footer_ajax.tpl'}
{/if}