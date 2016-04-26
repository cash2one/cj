{include file='frontend/header.tpl'}

<body id="wbg_hyt_detail">

<header>
	<h1>{$meeting['mt_subject']}</h1>
	<h2>发起: {$meeting['m_username']} 确定参会: {$meeting['mt_agreenum']}人</h2>

	<script type="text/moatmpl" id="dialogTmpl">
	<h1></h1>
	<form name="frmpost" id="frmpost" method="post" action="" autocomplete="off">
		<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
		<input type="hidden" name="referer" value="{$refer}" />
		<fieldset>
			<label>原因: </label><input name="message" id="message" type="text" placeholder="必须填写" required />
		</fieldset>
		<footer>
			<input type="reset" name="abreset" id="abreset" value="在想想" />
			<input type="submit" name="absubmit" id="absubmit" value="确定" />
		</footer>
	</form>
	</script>
	<script>
	{literal}
	/** 显示输入框 */
	function _show_form(e, title, tip, rt, sbt) {
		var html = $one('#dialogTmpl').innerHTML;
		var dlg = MDialog.popupCustom(html, false, null, true);
		var btnR = $one('input[type=reset]', dlg);
		var btnSbt = $one('input[type=submit]', dlg);
		var msg = $one('#message', dlg);
		var h1t = $one('h1', dlg);
		h1t.innerHTML = title;
		btnR.value = rt;
		btnSbt.value = sbt;
		msg.setAttribute('placeholder', tip);
		dlg.id = 'cancelMeetingDlg';
		dlg.style.left = .5 * (window.innerWidth - dlg.clientWidth) + 'px';
		$one('form', dlg).setAttribute('action', e.currentTarget.rel);
		btnR.addEventListener('click', function(e2) {
			MDialog.close();
		});
		$one('#frmpost').onsubmit = function(e) {
			if (!msg.value || !$trim(msg.value).length) {
				MDialog.notice('原因必须填写');
				e.preventDefault();
				return false;
			}

			MLoading.show('稍等片刻...');
			MAjaxForm.submit('frmpost', function(result) {
				MLoading.hide();
			});
			MDialog.close();
		};
	}

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

	{if $mms[$wbs_uid]['mm_status'] == voa_d_oa_meeting_mem::STATUS_NORMAL && !$meeting_closed}
	<div class="infobox actions">
		<a href="javascript:void(0)" rel="/meeting/absence/{$mt_id}?handlekey=post">不参加</a><a href="/meeting/confirm/{$mt_id}">参加</a>
	</div>
	<script>
	{literal}
	require(['dialog', 'members', 'business'], function() {
		$onload(function() {
			var box = $one('#wbg_hyt_detail .infobox');
			/** 缺席/取消 */
			$one('a:first-of-type', box).addEventListener('click', function(e) {
				_show_form(e, '您无法参加会议了吗?', '必须填写', '再想想', '不参加');
			});
		});
	});
	{/literal}
	</script>
	{else}
	<div class="infobox members"><div class="mod_members slider"><div class="sinner">
		<ul class="box" id="mlist">
			<!--li元素的id对应于php结构中的uid-->
			{foreach $mmlist as $v}
			<li id="{$v['m_uid']}">
				{if !$meeting_closed && !empty($unconfirm_users[$v['mm_id']])}<span class="wait">待</span>{/if}
				{if !$meeting_closed && !empty($absence_list[$v['mm_id']])}<span class="absent">否</span>{/if}
				<a href="/addressbook/show/{$v.m_uid}"><img src="{$cinstance->avatar($v['m_uid'])}" /></a>{$v['m_username']}
			</li>
			{/foreach}
		</ul>
	</div></div></div>
	{/if}
</header>

<div class="timebox">
	<time>{$meeting['_ymd']}</time><time>{$meeting['_begin_hm']}-{$meeting['_end_hm']}</time><time>{$weeknames[$meeting['_w']]}</time>
</div>

<h3>
{if $meeting['mt_endtime'] < $timestamp}
会议已结束
{elseif voa_d_oa_meeting::STATUS_CANCEL == $meeting['mt_status']}
会议已取消
{elseif voa_d_oa_meeting_mem::STATUS_ABSENCE == $my_mm['mm_status']}
已选择不参加
{else}
{$room['mr_name']}
{/if}
</h3>

{if !empty($meeting['mt_message'])}
<article>
	<h4>议题</h4>
	<p>{$meeting['mt_message']}</p>
</article>
{/if}

<div class="foot">
	<h4>共邀请{$meeting['mt_invitenum']}人</h4>
	{if $confirm_users}
	<div><label>确定{$ct_confirm_users}人</label>{$str_confirm_users}</div>
	{/if}
	{if $unconfirm_users}
	<div><label>待定{$ct_unconfirm_users}人</label>{$str_unconfirm_users}</div>
	{/if}
	{if $absence_list}
	<div>
		<label>取消{$ct_absence_list}人</label>
		{foreach $absence_list as $v}
		<p><b>{$v['m_username']}</b><span>{$v['mm_reason']}</span></p>
		{/foreach}
	</div>
	{/if}
</div>

{if $wbs_uid == $meeting['m_uid'] && voa_d_oa_meeting::STATUS_CANCEL != $meeting['mt_status'] && $meeting['mt_begintime'] > $timestamp}
<div class="center"><a id="mtcancel" href="javascript:;" rel="/meeting/cancel/{$mt_id}?handlekey=post" class="mod_button2 cancelBtn">取消会议</a></div>
<script>
{literal}
require(['dialog', 'business'], function() {
	$onload(function() {
		$one('#mtcancel').addEventListener('click', function(e) {
			_show_form(e, '您要取消这次会议吗?', '必须填写', '再想想', '确定');
		});
	});
});
{/literal}
</script>
{/if}


{include file='frontend/footer.tpl'}