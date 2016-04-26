{include file='frontend/header.tpl'}

<body id="wbg_wpx_result">

<header>
	<h1>{$vote['v_subject']}</h1>
	<h2>发起：{$vote['m_username']}</h2>
	{if $is_active}
	<div class="center" id="time_left">
		<em>0</em><i>天</i><em>0</em><i>小时</i><em>0</em><i>分</i>
		<p>距离投票结束时间</p>
	</div>
	{else}<h3>活动已结束</h3>{/if}
</header>

{if !$is_voted || !$is_active}
<form name="frmpost" id="frmpost" method="post" action="/vote/choice/{$v_id}?handlekey=post">
	<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
	<ol class="mod_common_list">
		{foreach $options as $k => $v}
		<li>
			<abbr>{$v['_order_num']}</abbr>
			<p>{$v['vo_option']}</p>
			{if $is_multi}
			<input type="checkbox" class="chk_option" name="options[]" value="{$v['vo_id']}" />
			{else}
			<input type="radio" class="chk_option" name="options" value="{$v['vo_id']}" />
			{/if}
		</li>
		{/foreach}
	</ol>
</form>
<a id="sbta" href="javascript:;" class="mod_button1">投票</a><br /><br />
{else}
<h2>目前共{$vote['v_voters']}人参与投票：</h2>
<ol class="mod_common_list result">
	{foreach $options as $v}
	<li>
		<abbr>{$v['_order_num']}</abbr>
		<p>{$v['vo_option']}</p>
		<span data-base-vote-num="{$v['vo_votes']}"><em>{$v['vo_votes']}</em>票<b></b></span>
	</li>
	{/foreach}
</ol>
{/if}

<a href="javascript:void(0)" class="mod_button1" id="sharebtn">分享微评选</a>

<script>
var _share_notice = '点击右上角这个神奇的...按钮<br/>可以分享给朋友或微信群<br/>让大家参与投票！';
var _vote_begintime = {$vote['v_begintime']};
var _vote_endtime = {$vote['v_endtime']};
var _vote_min = {$vote['v_minchoices']};
var _vote_max = {$vote['v_maxchoices']};

{literal}
var _lockPage = function(isLock) {
	if (typeof isLock == 'undefined') isLock = true;
	$one('body').style.overflow = isLock ? 'hidden' : 'visible';
};
var _makeModal = function() {
	var tmpl = '<div class="mModal"><a href="javascript:void(0)"><span>'+ _share_notice +'</span></a></div>';
	$one('body').insertAdjacentHTML('beforeEnd', tmpl);
	tmpl = null;
	var m = $one('.mModal:last-of-type');
	if ($all('.mModal').length > 1) m.style.opacity = .01;
	$one('a',m).style.height = window.innerHeight + 'px';
	m.style.zIndex = 999;
	return m;
};

/** 获取天/时/分 */
function _get_dhi(ts) {
	if (0 >= ts) {
		return '<em>0</em><i>天</i><em>0</em><i>小时</i><em>0</em><i>分</i>';
	}

	var d = Math.floor(ts / 86400);
	var h = Math.floor((ts % 86400) / 3600);
	var i = Math.floor((ts % 3600) / 60);
	return '<em>' + d + '</em><i>天</i><em>' + h + '</em><i>小时</i><em>' + i + '</em><i>分</i>';
}

/** 显示投票结果 */
function _show_result() {
	var rst = $one('ol.result');
	if (!rst) {
		return;
	}

	$each($all('li>span', rst), function(sp) {
		var base = parseInt($data(sp, 'baseVoteNum')),
			v = parseInt($one('em', sp).innerHTML),
			pct = v/base,
			w = parseInt(45 * pct),
			bar = $one('b', sp);
		bar.style.width = w + 'px';
		bar.style.right = -(5 + w) + 'px';
	});
}

/** 添加显示分享方法的按钮时间 */
function _init_share() {
	var share_btn = $one('#sharebtn');
	if (!share_btn) {
		return;
	}

	share_btn.addEventListener('click', function(e) {
		_lockPage();
		var m = _makeModal();
		m.style.backgroundColor = 'rgba(0,0,0,.8)';
		$one('a', m).addEventListener('click', function(e2) {
			e2.currentTarget.removeEventListener('click', arguments.callee);
			m.parentNode.removeChild(m);
			_lockPage(false);
		});
	});
}

/** 验证提交 */
var _submit_chk = function() {
	var sel_num = 0;
	$each($all('input.chk_option'), function(chk) {
		if (chk.checked) {
			sel_num ++;
		}
	});

	if (0 == sel_num) {
		MDialog.notice('请选择投票选项!');
		return false;
	}

	if (_vote_min > sel_num || _vote_max < sel_num) {
		MDialog.notice('只能选择 ' + (_vote_min == _vote_max ? _vote_min : _vote_min + '-' + _vote_max) + ' 个选项!');
		return false;
	}

	if (true == ajax_form_lock) {
		return false;
	}

	ajax_form_lock = true;
	MLoading.show('稍等片刻...');
	MAjaxForm.submit('frmpost', function(result) {
		MLoading.hide();
	});

	return true;
};

function _init_time_left() {
	var tl = $one('#time_left');
	if (!tl) {
		return;
	}

	var _d = new Date();
	var _remain_ts = _vote_endtime - (_d.getTime() / 1000);
	tl.innerHTML = _get_dhi(_remain_ts) + '<p>距离投票结束时间</p>';
	if (0 < _remain_ts) {
		var _interval = setInterval(function() {
			_remain_ts -= 60;
			if (0 >= _remain_ts) {
				window.location.reload();
			}

			tl.innerHTML = _get_dhi(_remain_ts) + '<p>距离投票结束时间</p>';
		}, 60000);
	}
}

function _init_submit() {
	var abt = $one('#sbta');
	var frm = $one('form');
	if (!abt || !frm) {
		return;
	}

	abt.addEventListener('click', function(e) {
		_submit_chk();
	});

	frm.onsubmit = function(e) {
		e.preventDefault();
		_submit_chk();
		return false;
	};
}

require(['dialog', 'business'], function() {
	$onload(function() {
		/** 显示投票结果 */
		_show_result();

		/** 分享 */
		_init_share();

		/** 初始化 form 提交相关 */
		_init_submit();

		/** 倒计时 */
		_init_time_left();
	});
});

function errorhandle_post(url, msg) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg) {
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}
{/literal}
</script>


{include file='frontend/footer.tpl'}