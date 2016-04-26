{include file='frontend/header.tpl'}

<body id="wbg_gg_detail">

<header>
	<h1>{$notice['nt_subject']}</h1>
	<h2>{$notice['nt_author']}    {$notice['_created']}</h2>
</header>

<article>
	{$notice['nt_message']}
</article>

<div class="foot numbtns single">
	<input type="reset" value="返回" onclick="javascript:history.go(-1);">
</div>

{include file='frontend/footer_nav.tpl'}

{include file='frontend/footer.tpl'}
