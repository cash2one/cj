{include file='frontend/header.tpl'}

<body id="wbg_rb_detail">

<header>
	<div class="center">
		<div class="t"><time>
			<em>{$dailyreport['_reporttime_fmt']['m']}</em><i>月</i>
			<em>{$dailyreport['_reporttime_fmt']['d']}</em><i>日</i>
			<b>{$weeknames[$dailyreport['_reporttime_fmt']['w']]}</b>
		</time></div>
		<h1>{if $dailyreport['_subject']}{$dailyreport['_subject']}{else}{$dailyreport['m_username']}的日报{/if}</h1>
		<h2>{$dailyreport['_created_u']} 发出</h2>
	</div>
</header>

<ol class="mod_common_list steps">
	{foreach $dailyreport['_message_li'] as $msg}
	<li>{$msg}</li>
	{/foreach}
	{if !empty($attachs)}
	<li>
		<div class="photos">
		{include file='frontend/mod_img_list.tpl'}
		</div>
	</li>
	{/if}
</ol>

<ul class="mod_common_list mem">
	<li>
		<label>抄送：</label>{foreach $ccusers as $u} <span>{$u['m_username']}</span>{/foreach}&nbsp;
	</li>
	<li class="to">
		<label>接收人：</label>
		<div class="mod_members slider"><div class="sinner">
			<ul class="box">
				<!--li元素的id对应于php结构中的uid-->
				{foreach $tousers as $u}
				<li id="{$u['m_uid']}"><img src="{$cinstance->avatar($u['m_uid'])}" />{$u['m_username']}<time></time></li>
				{/foreach}
			</ul>
		</div></div>
	</li>
</ul>

<ul class="mod_comment_list simple">
	{foreach $posts as $p}
	<li>
		<h1>{$p['m_username']}:</h1>
		<p>{$p['_message']}</p>
		<time>{$p['_created_u']}</time>
	</li>
	{/foreach}
</ul>

<div class="foot">
	<a {if $is_recv} href="/dailyreport/so/recv/?sotext={$dailyreport['m_username']}" {else} href="/dailyreport/so/?sotext={$dailyreport['m_username']}" {/if} class="mod_button2">查看往期</a>
	<a id="reply" href="javascript:void(0)" rel="/dailyreport/reply/{$dr_id}?handlekey=post" class="mod_button2">添加评论</a>
</div>

<script type="text/moatmpl" id="dialogTmpl">
	<h1>评论</h1>
	<form name="frmpost" id="frmpost" method="post" action="">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<textarea name="message" type="text" required></textarea>
		<footer>
			<input type="reset" value="取消" />
			<input type="submit" value="确定" />
		</footer>
	</form>
</script>

<script>
{literal}
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

require(['members', 'dialog', 'business'], function() {
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
	
	$onload(function() {
		$one('#reply').addEventListener('click', function(e) { /** 我要留言 */
			_show_form(e, '请填写内容');
		});
	});
});
{/literal}
</script>

{include file='frontend/footer_nav.tpl'}


{include file='frontend/footer.tpl'}