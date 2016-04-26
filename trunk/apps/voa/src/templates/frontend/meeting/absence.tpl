{include file='frontend/header.tpl'}

<body>
<h1>您无法参加会议了吗?</h1>
<form method="post" action="/meeting/absence/{$id}" autocomplete="off">
	<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
	<input type="hidden" name="referer" value="{echo dreferer()}" />
	<fieldset>
		<label>原因: </label><input name="reason" id="reason" type="text" placeholder="必须填写" required />
	</fieldset>
	<input type="submit" name="absubmit" id="absubmit" value="确定" />
</form>


{include file='frontend/footer.tpl'}