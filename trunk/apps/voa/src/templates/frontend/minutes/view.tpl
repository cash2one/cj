{include file='frontend/header.tpl'}

<body id="wbg_hyt_record_view">

<header>
	<h1>{$minutes['_subject']}&nbsp;</h1>
	<h2>参会人&nbsp;&nbsp;&nbsp;</h2>
	<div class="infobox members"><div class="mod_members slider"><div class="sinner">
		<ul class="box">
			{foreach $tousers as $v}
			<li id="{$v['m_uid']}"><img src="{$cinstance->avatar($v['m_uid'])}" />{$v['m_username']}</li>
			{/foreach}
		</ul>
	</div></div></div>
</header>

<div class="mod_common_list_style cont">
	<h2>会议记录</h2>
	<p>{$minutes['_message']}</p>
{if !empty($attachs)}
	<div class="photos">
		{include file='frontend/mod_img_list.tpl'}
	</div>
{/if}
</div>

<ul class="mod_common_list members">
	<li>
		<label>记录人: </label>{$minutes['m_username']}&nbsp;
	</li>
	<li>
		<label>抄送人：</label>{foreach $ccusers as $v} <span>{$v['m_username']}</span>{/foreach}&nbsp;
	</li>
</ul>

<ul class="mod_comment_list">
	{foreach $posts as $v}
	<li>
		<div class="mod_member_item"><img src="{$cinstance->avatar($v['m_uid'])}" />{$v['m_username']}</div>
		<p>{$v['_message']}</p>
		<time>{$v['_created_u']}</time>
	</li>
	{/foreach}
</ul>

<div class="foot">
	<a href="/minutes/so" class="mod_button2">其他会议记录</a><a href="javascript:void(0)" class="mod_button1 replyBtn" rel="/minutes/reply/{$minutes['mi_id']}?handlekey=post">添加评论</a>
</div>

<script type="text/moatmpl" id="dialogTmpl">
	<form name="frmpost" id="frmpost" method="post" action="">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<textarea name="message" placeholder=""></textarea>
		<footer><input type="reset" value="取消" /><input type="submit" name="sbtpost" value="确定" /></footer>
	</form>
</script>

<script>
{literal}
/** 显示输入框 */
function _show_form(e, tip) {
	var html = $one('#dialogTmpl').innerHTML;
	var dlg = MDialog.popupCustom(html, false, null, true);
	dlg.id = 'commentVerifyDlg';
	dlg.style.left = .5 * (window.innerWidth - dlg.clientWidth) + 'px';
	$one('form', dlg).setAttribute('action', e.currentTarget.rel);
	$one('textarea', dlg).setAttribute('placeholder', tip);
	$one('input[type=reset]', dlg).addEventListener('click', function(e2) {
		MDialog.close();
	});
	$one('#frmpost').onsubmit = function(e) {
		if (true == ajax_form_lock) {
			e.preventDefault();
			return false;
		}

		ajax_form_lock = true;
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

require(['dialog', 'business'], function() {
	$onload(function() {
		$each('.replyBtn', function(btn) {
			btn.addEventListener('click', function(e) { /** 我要留言 */
				_show_form(e, '请填写回复内容');
			});
		});
	});
});
{/literal}
</script>

{include file='frontend/footer_nav.tpl'}


{include file='frontend/footer.tpl'}
