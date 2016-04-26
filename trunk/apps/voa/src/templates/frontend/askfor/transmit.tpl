{include file='frontend/header.tpl'}

<body id="wbg_spl_launch">
<script src="{$wbs_javascript_path}/MOA.memberselect.js"></script>

<div id="viewstack"><section>
	<form name="frmpost" id="frmpost" method="post" action="/askfor/transmit/{$af_id}?handlekey=post">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<input type="hidden" name="referer" value="{$refer}" />
		<fieldset>
			<h1>转审批人:</h1>
			{include file='frontend/mod_approver_select.tpl' iptname='approveuid' iptvalue=$approveuid}
		</fieldset>

		<fieldset>
			<h1>抄送人:</h1>
            {include file='frontend/mod_cc_select.tpl' iptname='carboncopyuids' ccusers=$ccusers}
		</fieldset>

		<footer><a id="sbta" class="mod_button1">发布转审批</a></footer>
	</form>
</section><menu id="mod_members_panel" class="mod_members_panel"></menu></div>

{literal}
<script>
$onload(function() {
	/** 发布转审批 */
	$one('#sbta').addEventListener('click', function(e) {
		$one('form').onsubmit(e);
	});

	/** 表单校验 */
	$one('form').onsubmit = function(e) {
		var memIpt = $one('#approveuid');
		if (!memIpt.value || !$trim(memIpt.value).length) {
			MDialog.notice('请选择审批人!');
			e.preventDefault();
			return false;
		}

		MLoading.show('稍等片刻...');
		MAjaxForm.submit('frmpost', function(result) {
			MLoading.hide();
		});

		return true;
	};
});

function errorhandle_post(url, msg) {
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg) {
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}

</script>
{/literal}


{include file='frontend/footer.tpl'}
