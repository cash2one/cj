{include file='frontend/header.tpl'}

<body id="wbg_xd_new">

<form name="productive_tn" id="productive_tn" method="post" action="{$form_action}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<h1 style="display:none;">我的位置</h1>
	<div class="geo" hidden>
		<div class="center">
			<span>定位失败请手动获取 <a href="如何手动发送位置.html">帮助</a></span>
			<!--<span>上海市普陀区宁夏路322号</span>-->
		</div>
	</div>
	
	<h1>门店名称</h1>
	{include file='frontend/mod_shop_select.tpl' spname='csp_id' jsonname='jsonstr' region2shop=$region2shop plugin_set=$productive_set}
	
	<div class="foot numbtns single">
		<input type="submit" value="开始评估" />
	</div>
	
	<p>该评估将保存至反馈计划</p>
</form>

<script>
{literal}
require(['dialog', 'members', 'business'], function() {
	//表单校验
	$one('form').addEventListener('submit', function(e) {
		var csp_id = $one('#csp_id').value;
		
		e.preventDefault();
		if (-1 == csp_id) {
			MDialog.notice('请选择门店!');
			return false;
		}
		
		aj_form_submit('productive_tn');
	});
});

function errorhandle_post(url, msg) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg) {
	MStorageForm.clear();
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}
{/literal}
</script>

{include file='frontend/footer.tpl'}
