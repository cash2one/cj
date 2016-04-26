{include file='frontend/header.tpl'}

<body>

<header>
	<h1>{$secret['st_subject']}</h1>
	<div class="info">
		<h3>秘密：</h3>
		<p>{$secret['st_message']}</p>
	</div>
</header>

<!--弹出对话框中 form 的 action 会被替换成 replyBtn 元素的 rel 属性值-->
<a href="javascript:void(0)" class="replyBtn" rel="/secret/reply/{$secret['st_id']}?handlekey=post"><span>我要留言</span></a>
<ul class="mod_reply_list">
	{foreach $posts as $v}
	<li>
		<p>{$v['afc_message']}</p>
		<time>{$v['_created']}</time>
	</li>
	{/foreach}
</ul>

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

$onload(function() {
	$one('.replyBtn').addEventListener('click', function(e) { /** 我要留言 */
		_show_form(e, '请填写回复内容');
	});
});
{/literal}
</script>


{include file='frontend/footer.tpl'}