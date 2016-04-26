{include file='frontend/header.tpl'}

<body>

<form name="meeting_{$ac}" id="meeting_{$ac}" method="post" action="{$form_action}" autocomplete="off">
	<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
	<input type="hidden" name="referer" value="{$refer}" />
	<h1>会议基本信息</h1>
	<fieldset class="project">
		{foreach $tablecol as $_tc}
			{if 'radio' == $_tc['ct_type']}
			radio
			{else}
				{include file=$_tc.tpladd ipttitle=$_tc.fieldname iptname=$_tc.field iptvalue=$_tc.initval placeholder=$_tc.placeholder}
			{/if}
		{/foreach}
	</fieldset>
	
	<div class="foot">
		<input id="btn_go_back" type="reset" value="取消" /><input type="submit" value="新产品" />
	</div>
</form>

{include file='frontend/footer.tpl'}