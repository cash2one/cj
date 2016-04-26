{include file='frontend/header.tpl'}

<body>

<header>
	<h1>{$remind['rm_subject']}</h1>
	<div class="info">
		<h3>详情：</h3>
		<p>{$remind['rm_message']}</p>
	</div>
</header>

{include file='frontend/footer_nav.tpl'}


{include file='frontend/footer.tpl'}
