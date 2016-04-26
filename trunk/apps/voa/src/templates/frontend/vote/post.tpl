{include file='frontend/header.tpl'}

<body id="wbg_wpx_launch">
<script src="{$wbs_javascript_path}/MOA.memberselect.js"></script>
<script src="{$wbs_javascript_path}/MOA.dateselect.js"></script>

<div id="viewstack"><section>
	<form name="vote_{$ac}" id="vote_{$ac}" method="post" autocomplete="off" action="{$form_action}">
		<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
		<h2>基本信息</h2>
		<ul class="mod_common_list basic">
			<li class="sponsor">
				<label>发起人：</label><input type="text" readonly value="{$wbs_username}" />
			</li>
			<li class="project">
				<label>主题：</label><input type="text" name="subject" id="subject" value="{$vote['v_subject']}" placeholder="请填写主题" required storage />
			</li>
		</ul>
		{include file='frontend/mod_date_range.tpl' range_start=$range_start start_selected=$start_selected end_selected=$end_selected}
		<h2>参与人</h2>
		<div class="members mod_common_list_style">
			<p><label>限受邀者投票</label><input name="friend" type="checkbox" value="1"{if $users} checked{/if} /></p>
			{include file='frontend/mod_cc_select.tpl' iptname='uids' display_none=true}
		</div>
		<h2>评选内容</h2>
		<ul id="ul_options" class="mod_common_list options">
			<li style="display:none;">
				<label>选项：</label><input type="text" name="options[]" />
			</li>
			{if !empty($options)}
			{foreach $options as $v}
			<li><label>选项:</label><input type="text" class="vote_option" name="oldoptions[{$v['vo_id']}]" value="{$v['vo_option']}" /></li>
			{/foreach}
			{else}
			<li><label>选项:</label><input type="text" class="vote_option" name="options[]" /></li>
			<li><label>选项:</label><input type="text" class="vote_option" name="options[]" /></li>
			{/if}
		</ul>
		<a href="javascript:void(0)" id="moreoptions" class="moreoptions" data-toggle-text="-&nbsp;较少选项">+&nbsp;更多选项</a>

		<input type="submit" name="sbtpost" value="发起评选" />
	</form>
</section><menu id="mod_members_panel" class="mod_members_panel"></menu></div>

<script>
var _frm_name = 'vote_{$ac}';
{if 'new' == $ac}MStorageForm.init(_frm_name);{/if}
{literal}
require(['members', 'dialog', 'business'], function() {
	$onload(function() {
		/** onsubmit 事件 */
		$one('form').onsubmit = function(e) {
			e.preventDefault();
			check_submit();
			return false;
		};

		/** 新增投票选项操作 */
		$one('#moreoptions').addEventListener('click', function(e) {
			var ul = $one('#ul_options');
			var new_li = $one('li:first-of-type', ul).cloneNode(true);
			new_li.style.display = '';
			ul.appendChild(new_li);
		});

		/** 增减受邀投票成员 */
		init_vote_mem();
	});
});

/** 初始化投票成员显示 */
function init_vote_mem() {
	var cb = $one('.members input[type=checkbox]'),
		hidd = $one('.members input[type=hidden]'),
		m = $one('.members .mod_members');

	cb.addEventListener('click', function(e) {
		if (cb.checked) {
			$show(m);
		} else {
			$hide(m);
			hidd.value = '';
			$each($all('li.newjoin', m), function(li) {
				li.parentNode.removeChild(li);
			});
		}
	});
}

/** 检查 form */
function check_submit() {
	var ipt_subject = $one('#subject'),
		bFake = $one('.time .begin .fake'),
		eFake = $one('.time .end .fake'),
		ipt_begintime = $one('#begintime'),
		ipt_endtime = $one('#endtime');

	if (!ipt_subject.value || !$trim(ipt_subject.value).length) {
		MDialog.notice('请填写主题!');
		return false;
	}

	if ($hasCls(bFake, 'init') || $hasCls(eFake, 'init')) {
		MDialog.notice('请选择日期!');
		return false;
	}

	/** 判断是否有选择 */
	var ct_option = 0;
	$each($all('input.vote_option'), function(op) {
		if (0 < $trim(op.value).length) {
			ct_option ++;
		}
	});
	if (2 > ct_option) {
		MDialog.notice('请至少填写 2 个投票选项!');
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
}

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
