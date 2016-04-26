{include file='frontend/header.tpl'}

<body id="wbg_bbs_detail">

<h1>{$thread['t_subject']}</h1>
<article>
	<h3>内容：</h3>
	{$thread['tp_message']}
</article>

<ul class="btns">
	<li><a id="comment" href="javascript:void(0);" rel="/thread/comment/{$thread['t_id']}?handlekey=post">评论</a></li>
	<li><a href="/thread/edit/{$thread['t_id']}">编辑</a></li>
	<li><a id="share" href="/thread/share/{$thread['t_id']}">分享</a></li>
</ul>

<ul class="mod_comment_list">
	{foreach $posts as $pid => $p}
	<li>
		<div class="mod_member_item"><img src="{$cinstance->avatar($p['m_uid'])}" />{$p['m_username']}</div>
		<p>{$p['tp_message']}</p>
		<time>{$p['_created']}</time>
		<!--弹出对话框中form的action会被替换成comm元素的rel属性值-->
		<a href="javascript:void(0)" class="comm" rel="/thread/reply/{$thread['t_id']}/{$p['tp_id']}?handlekey=post">回复</a>
		{if $replies}
		<ul>
			{foreach $p2rs[$p['tp_id']] as $r}
			<li><i>{$r['m_username']}:</i><p>{$r['tpr_message']}</p><time>{$r['tpr_created']}</time></li>
			{/foreach}
		</ul>
		{/if}
	</li>
	{/foreach}
</ul>

<script type="text/moatmpl" id="dialogTmpl">
	<form name="frmpost" id="frmpost" method="post" action="">
		<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
		<textarea name="message" placeholder=""></textarea>
		<footer><input type="reset" value="取消" /><input type="submit" name="sbt" value="确定" /></footer>
	</form>
</script>

<script>
{if $wbs_uid == $thread['m_uid']}
var is_self = true;
{else}
var is_self = false;
{/if}
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
		MLoading.show('稍等片刻...');
		MAjaxForm.submit('frmpost', function(result) {
			MLoading.hide();
		});
		MDialog.close();
	};
}

$onload(function() {
	/** 发表评论 */
	$one('#comment').addEventListener('click', function(e) {
		_show_form(e, '请输入评论信息');
	});

	/** 回复留言 */
	$each('ul>li>.comm', function(comm) {
		comm.addEventListener('click', function(e) {
			_show_form(e, '请输入回复信息');
		});
	});
});

function errorhandle_post(url, msg) {
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg) {
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}
{literal}
</script>
</body>

{include file='frontend/footer.tpl'}