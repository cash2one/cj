{if !$inajax}
{include file='frontend/header.tpl'}
<body id="mod_notice_page">
{else}{include file='frontend/header_ajax.tpl'}{/if}

{if !$inajax}
	<h1 class="success">{$title}</h1>
	<h2>{$message}</h2>
	{if $url}
	<footer><a href="{$url}" class="mod_button2">{lang key=message_forward}</a></footer>
	{/if}
{else}{$message}<ajaxOk>{/if}

{if !$inajax}
	{include file='frontend/footer.tpl'}
{else}
	{include file='frontend/footer_ajax.tpl'}
{/if}