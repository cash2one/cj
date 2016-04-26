{include file='frontend/header.tpl'}

<body id="wbg_wxq_launch">
<script src="{$wbs_javascript_path}/MOA.dateselect.js"></script>

<form name="wxwall_{$ac}" id="wxwall_{$ac}" method="post" autocomplete="off" action="{$form_action}">
	<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
	<h2>基本信息</h2>
	<ul class="mod_common_list basic">
		<li class="sponsor">
			<label>发起人：</label><input type="text" readonly value="{$wbs_username}" />
		</li>
		<li class="project">
			<label>主题：</label><input type="text" name="subject" id="subject" ids value="{$wall['ww_subject']}" required placeholder="请填写主题" storage />
		</li>
		{if $wall}
		<li><label>重置密码:</label><input type="checkbox" name="resetp" value="true" /></li>
		{/if}
	</ul>
	{include file='frontend/mod_date_range.tpl'}
	
	{if $vy_users && $verify}
	<h2>审核人</h2>
	<input name="vy_u" value="{$uid_str}" type="hidden" />
	<div class="mod_members mod_common_list_style">
		<div class="sinner">
			<ul class="box">
				{foreach $vy_users as $u}
				<li data-id="{$u['m_uid']}"><img src="{$cinstance->avatar($u['m_uid'])}" />{$u['m_username']}</li>
				{/foreach}
			</ul>
		</div>
	</div>
	{/if}
	<h2>备注</h2>
	<div class="remarks mod_common_list_style">
		<textarea name="message" id="message" storage>{$wall['ww_message']}</textarea>
	</div>
	<input type="submit" name="sbtpost" value="申请微信墙" />
</form>

<script>
var _frm_name = 'wxwall_{$ac}';
{if 'new' == $ac}MStorageForm.init(_frm_name);{/if}
{literal}
$onload(function(){
	/** 初始化时间选择 */
	init_date_select('$b_ymd', '{$e_ymd}');
	/** 初始化 */
	$one('form').onsubmit = function(e) {
		var ipt_subject = $one('#subject'),
			ipt_begintime = $one('#begintime'),
			ipt_endtime = $one('#endtime'),
			bFake = $one('.time .begin .fake'),
			eFake = $one('.time .end .fake');

		e.preventDefault();
		if ($hasCls(bFake, 'init') || $hasCls(eFake, 'init')){
			MDialog.notice('请选择日期!');
			return false;
		}

		if (!ipt_subject.value || !$trim(ipt_subject.value).length) {
			MDialog.notice('请填写主题!');
			return false;
		}

		if (true == ajax_form_lock) {
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
