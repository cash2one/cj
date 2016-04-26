{include file='frontend/header.tpl'}

<body id="wbg_gzt_detail2" class="user-select">

<header>
	<h1>{$project['p_subject']}</h1>
	<h2>任务成员：{$ct_project_mems}人</h2>
	<div class="mod_members slider"><div class="sinner">
		<ul class="box">
			{foreach $project_mems as $m}
			{if voa_d_oa_project_mem::STATUS_QUIT != $m['pm_status']}
			<li id="{$m['m_uid']}">
				<a href="/addressbook/show/{$m['m_uid']}"><img src="{$cinstance->avatar($m['m_uid'])}" /></a>{$m['m_username']}
			</li>
			{/if}
			{/foreach}
		</ul>
	</div></div>
	<time id="time_left">
		<b>剩余时间：</b>0<i>天</i>0<i>小时</i>0<i>分</i>
	</time>
	<time id="time_total">
		<b>任务规定时限：</b>0<i>天</i>0<i>小时</i>0<i>分</i>
	</time>
	<p class="progress" data-max="100" data-value="{$project['p_progress']}">
		<span></span><i>总进度</i>
	</p>
</header>

<div class="mod_common_list_style desc">
	<h1>具体任务：</h1>
	<p>{$project['p_message']}</p>
{if $attachs}
	{include file='frontend/mod_img_list.tpl'}
{/if}
</div>

{if $puids[$wbs_uid] && 100 > $progress}
<div class="mod_common_list_style now">
	<b>{$wbs_username}:<a class="update"{if $project['p_status']<3} href="/project/progress/{$p_id}"{else}href="javascript:;"{/if}>+添加我的进度</a></b>
</div>
{/if}

<ol class="mod_common_list">
	{foreach $project_mems as $m}
	<li>
		<a class="m_link item" href="javascript:void(0)">
			<time>{$m['_updated']}</time>
			<b>{$m['m_username']}</b>
			<p class="progress" data-max="100" data-value="{$m['pm_progress']}" data-unit="%"><span></span></p>
		</a>
		{if $uid2proc[$m['m_uid']] || voa_d_oa_project_mem::STATUS_QUIT == $m['pm_status'] || !empty($m['_jointime'])}
		<dl style="display:none;">
			{foreach $uid2proc[$m['m_uid']] as $p}
			<dt>{$p['_created']}</dt><dd>{$p['pp_progress']}% {$p['pp_message']}</dd>
			{/foreach}
		</dl>
		{/if}
	</li>
	{/foreach}
</ol>

{if 100 > $progress && $project['m_uid'] == $wbs_uid && $is_running}
<a href="/project/advanced/{$p_id}" class="mod_button1">任务推进</a>
<a href="/project/close/{$p_id}" id="close_proj" class="mod_button1">关闭任务</a>
{/if}
<a href="/project/list" class="mod_button2">返回</a>
<style>
	.user-select{
		-webkit-user-select:auto;
	}
</style>
<script>
var _ajax_lock = false;
var _project_stime = '{$project['p_begintime']}';
var _project_etime = '{$project['p_endtime']}';
{literal}
function formatDetail(data) {
	if (!data) return '';
	var rst = '<dl style="display:none;">';
	$each(data, function(d) {
		rst += '<dt>' + d.percent + '%</dt><dd>' + d.describe + '</dd>';
	});
	rst += '</dl>';
	return rst;
}

function updateProgress() {
	$each('ol .progress', function(prog) {
		var bar = $one('span', prog),
			w = parseInt(parseFloat(prog.getAttribute('data-value')) / parseFloat(prog.getAttribute('data-max')) * 51);
		bar.style.width = w + 'px';
		bar.style.right = -w + 'px';
	});
}

function toggleItemDetail() {
	$each('ol a.item', function(btn) {
		if ($hasCls(btn, 'parsed')) return;
		$addCls(btn, 'parsed');
		btn.addEventListener('click', function(e) {
			var li = e.currentTarget.parentNode;
			var dl = $next(e.currentTarget);
			if (!dl) return;
			if ($hasCls(li, 'dl-open')) {
				$hide(dl);
				$rmCls(li, 'dl-open');
			} else {
				$show(dl);
				$addCls(li, 'dl-open');
			}
		});
	});
}

function _get_dhi(ts) {
	if (0 >= ts) {
		return '0<i>天</i>0<i>小时</i>0<i>分</i>';
	}

	var d = Math.floor(ts / 86400);
	var h = Math.floor((ts % 86400) / 3600);
	var i = Math.floor((ts % 3600) / 60);
	return '' + d + '<i>天</i>' + h + '<i>小时</i>' + i + '<i>分</i>';
}

function _show_time_left() {
	/** init */
	$one('#time_total').innerHTML = '<b>任务规定时限：</b>' + _get_dhi(_project_etime - _project_stime);
	/** 倒计时 */
	var _d = new Date();
	var _remain_ts = _project_etime - (_d.getTime() / 1000);
	$one('#time_left').innerHTML = '<b>剩余时间：</b>' + _get_dhi(_remain_ts);
	if (0 < _remain_ts) {
		var _interval = setInterval(function() {
			_remain_ts -= 60;
			if (0 >= _remain_ts) {
				window.location.reload();
			}

			$one('#time_left').innerHTML = _get_dhi(_remain_ts);
		}, 60000);
	}
}

$onload(function() {
	updateProgress();
	toggleItemDetail();

	var hp = $one('header .progress');
	var p = $one('span', hp);
	var pct = parseInt(hp.getAttribute('data-value'));
	if (pct < 100) {
		p.innerHTML = pct + '<b>%</b>';
	} else {
		p.innerHTML = '完成';
		$addCls(p, 'complete');
	}
{/literal}

{if $is_running}
	_show_time_left();
{/if}

{if 100 > $progress && $project['m_uid'] == $wbs_uid && $is_running}
{literal}
require(['dialog', 'business'], function() {
	/** 关闭任务 */
	$one('#close_proj').addEventListener('click', function(e) {
		var ha = e.currentTarget;
		e.preventDefault();
		MDialog.confirm('关闭任务', '确定将当前任务关闭? 一旦关闭将无法恢复', null, '取消', function(ebtn) {
			/***/
		}, null, '确定', function(ebtn) {
			MLoading.show('稍等片刻...');
			MAjaxForm.analog(ha.getAttribute("href"), null, 'post', function (s) {
				window.location.href = window.location.href;
				MLoading.hide();
			});
		}, null, null, false);
		return false;
	});
});
{/literal}
{/if}

{literal}
});
{/literal}
</script>

{include file='frontend/footer_nav.tpl'}


{include file='frontend/footer.tpl'}