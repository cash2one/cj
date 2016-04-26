{include file='frontend/header.tpl'}

<body id="wbg_msq_complaint">

<form name="sign_{$ac}" id="sign_{$ac}" method="post" action="/sign/plead?handlekey=post">
	<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
	<input type="hidden" name="year" id="year" value="{$year}" />
	<input type="hidden" name="month" id="month" value="{$month - 1}" />
	<label>申诉主题:</label><input name="subject" id="subject" type="text" value="{$username}在{$year}年{$month}月的考勤申诉" required storage />
	<label>申诉内容:</label><textarea name="message" id="message" required storage>{$def_msg}</textarea>
	<input type="submit" name="sbtpost" id="sbtpost" value="我要申诉" />
</form>

<hr />
<table>
	<thead>
		<tr><th>日期</th><th>签到</th><th>签退</th><th>状态</th></tr>
	</thead>
	<tbody>
		{foreach $sign_day as $k => $v}
		<tr>
			<td>{$k}</td>
			<td>{if empty($signs_on[$k])}无{else}{$signs_on[$k]['_signtime']}{/if}</td>
			<td>{if empty($signs_off[$k])}无{else}{$signs_off[$k]['_signtime']}{/if}</td>
			<td>{$sign_st[$v]}</td>
		</tr>
		{/foreach}
	</tbody>
</table>

<script>
var _frm_name = 'sign_{$ac}';
{if 'plead' == $ac}MStorageForm.init(_frm_name);{/if}
{literal}
$onload(function() {
	$one('form').onsubmit = function(e) {
		var projectIpt = $one('#subject'),
			contentTa = $one('#message');

		if (!projectIpt.value || !$trim(projectIpt.value).length) {
			MDialog.notice('请填写主题!');
			e.preventDefault();
			return false;
		}

		if (!contentTa.value || !$trim(contentTa.value).length) {
			MDialog.notice('请填写内容!');
			e.preventDefault();
			return false;
		}

		if (true == ajax_form_lock) {
			e.preventDefault();
			return false;
		}

		ajax_form_lock = true;
		MLoading.show('稍等片刻...');
		MAjaxForm.submit(_frm_name, function(result) {
			MLoading.hide();
		});

		return false;
	}
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