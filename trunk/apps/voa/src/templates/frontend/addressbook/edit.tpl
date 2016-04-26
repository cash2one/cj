{include file='frontend/header.tpl'}

<body id="wbg_grzx_profile">
<header>
	<div class="mod_member_item"><img src="{$cinstance->avatar($uid)}" />{$username}</div>
</header>

<ul class="mod_common_list part1">
	<li class="withicon department">
		<span class="m_icon"></span>
		<label>所属部门</label><i>{$department.cd_name}</i>
	</li>
	<li class="withicon job">
		<span class="m_icon"></span>
		<label>职位</label><i>{$job.cj_name}</i>
	</li>
</ul>

<ul class="mod_common_list part2">
	<li class="withicon mobile">
		<span class="m_icon"></span>
		<label>手机</label><a href="javascript:void(0)" class="update">√</a><input type="tel" name="mobilephone" value="{$address.cab_mobilephone}" readonly required pattern="^1[3|4|5|8][0-9]\d{ldelim}8{rdelim}$" />
	</li>
	<li class="withicon phone">
		<span class="m_icon"></span>
		<label>座机</label><a href="javascript:void(0)" class="update">√</a><input type="tel" name="telephone" value="{$address.cab_telephone}" readonly required pattern="^[\d\-\s]+$" />
	</li>
	<li class="withicon email">
		<span class="m_icon"></span>
		<label>邮箱</label><a href="javascript:void(0)" class="update">√</a><input type="email" name="email" value="{$address.cab_email}" readonly required pattern="^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{ldelim}2,3{rdelim}){ldelim}1,2{rdelim})$" />
	</li>
</ul>

<div class="foot">
	<a href="javascript:history.go(-1);" class="mod_button2">返回</a>
</div>

<script>
var _formhash = '{$formhash}';
{literal}
var _fields_cfg = {
	mobile: {
		requiredNotice: '请填写手机号',
		patternNotice: '请填写正确的手机号'
	},
	phone: {
		requiredNotice: '请填写座机号码',
		patternNotice: '请填写正确的座机号码'
	},
	email: {
		requiredNotice: '请填写邮箱地址',
		patternNotice: '请填写正确的邮箱地址'
	}
};
function errorhandle_edit(url, msg) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_edit(url, msg) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}
/**
 * 更新单项
 * @param type 类型 mobile|phone|email
 * @param value 新值
 */
function _doUpdateAjax(type, field, value) {
	if (ajax_form_lock) return;
	ajax_form_lock = true;

	var data = {'formhash':_formhash}; /** [ajax] params */
	data[field] = value;
	MLoading.show('正在更新...');
	MAjaxForm.analog(window.location.href + '?handlekey=edit', data, 'post', function (s) {
		MLoading.hide();
	});
}

$onload(function() {
	var onIptClick = function(e) {
		var ipt = e.currentTarget;
		if (!ipt.hasAttribute('readonly')) return;

		var ubtn = $prev(ipt);
		ipt.removeAttribute('readonly');
		$addCls(ubtn, 'show');
		ipt.focus();
		try {
			ipt.setSelectionRange(ipt.value.length, ipt.value.length);
		} catch(ex) {}

		$data(ipt, 'oldvalue', $trim(ipt.value));
	};
	var onBtnClick = function(e) {
		var ubtn = e.currentTarget,
			ipt = $next(ubtn);
		if (ipt.hasAttribute('readonly')) return;

		var type = ubtn.parentNode.className.replace('withicon ', ''),
			cfg = _fields_cfg[type],
			rqr = ipt.hasAttribute('required'),
			re = ipt.hasAttribute('pattern') ? new RegExp(ipt.pattern): null,
			v = $trim(ipt.value);
		if (rqr && v === '') {
			alert(cfg.requiredNotice);
			return;
		}

		if (!re.test(v)) {
			alert(cfg.patternNotice);
			return;
		}

		if (v !== $data(ipt, 'oldvalue')) {
			_doUpdateAjax(type, ipt.name, v);
		}

		ipt.setAttribute('readonly', true);
		$rmCls(ubtn, 'show');
	};
	$each('.part2 input', function(ipt) {
		ipt.addEventListener('click', onIptClick);
		$prev(ipt).addEventListener('click', onBtnClick);
	});
});
</script>
{/literal}

{include file='frontend/footer.tpl'}