{include file='frontend/header.tpl'}

<body id="moa_verify">

<div class="center">
	<form name="frmpost" id="frmpost" method="post" action="/register" autocomplete="off">
		<input type="hidden" name="handlekey" value="reg" />
		<input type="hidden" name="referer" value="{$refer}" />
		<input type="hidden" name="formhash" value="{$formhash}" />
		<h1>畅移云工作</h1>
		<fieldset>
			<p><label>姓名:</label><input name="username" id="username" type="text" placeholder="" /></p>
			<p><label>工号:</label><input name="number" id="number" type="number" placeholder="" /></p>
			<p><label>身份证号:</label><input name="cardnum" id="cardnum" type="text" placeholder="输入身份证后六位" /></p>
			<p><label>手机号码:</label><input name="mobilephone" id="mobilephone" type="number" /></p>
		</fieldset>
		<footer>
			<input type="submit" name="sbtpost" value="提交" />
		</footer>
	</form>

	<h2>注意事项：</h2>
	<ul>
		<li><p>只针对正式开放</p></li>
		<li><p>只针对正式员工 实习生暂不开放</p></li>
	</ul>
</div>

{literal}
<script>
$one('form').addEventListener('submit', function(e) {
	var re = new RegExp("^0?1[3|5|8]\\d{9}", 'ig');
	var _uname = $one('#username'),
		_num = $one('#number'),
		_cardnum = $one('#cardnum'),
		_mphone = $one('#mobilephone');
	if (!_uname.value || !$trim(_uname.value).length) {
		MDialog.notice('请输入姓名!');
		e.preventDefault();
		return false;
	}

	if (!_num.value || !$trim(_num.value).length) {
		MDialog.notice('请输入工号!');
		e.preventDefault();
		return false;
	}

	if (!_cardnum.value || !$trim(_cardnum.value).length) {
		MDialog.notice('请输入身份证号!');
		e.preventDefault();
		return false;
	}

	if (!_mphone.value || !re.test(_mphone.value)) {
		MDialog.notice('请输入手机号!');
		e.preventDefault();
		return false;
	}

	MLoading.show('稍等片刻...');
	MAjaxForm.submit('frmpost', function(result) {
		MLoading.hide();
	});

	return false;
});

function errorhandle_reg(url, msg) {
	MDialog.notice(msg);
}

function succeedhandle_reg(url, msg) {
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}
</script>
{/literal}


{include file='frontend/footer.tpl'}