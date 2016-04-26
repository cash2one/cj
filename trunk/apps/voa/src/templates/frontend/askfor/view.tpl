{include file='frontend/header.tpl'}

<body id="wbg_spl_detail">

<header>
	<h1 style="table-layout:fixed;word-break:break-all;word-wrap:break-word;">{$askfor['af_subject']}</h1>
	<h2><b>申请人：</b>{$askfor['m_username']}</h2>
</header>

{if $askfor['af_message'] || $attachs}
<div style="background:#fff;padding:10px;">
	{if $askfor['af_message']}<p style="table-layout:fixed;word-break:break-all;word-wrap:break-word;">{$askfor['af_message']}</p>{/if}
	{if $attachs}
		{include file='frontend/mod_img_list.tpl'}
	{/if}
</div>
{/if}


<article>
	<h3>审批人：</h3>
	<div class="mod_members slider"><div class="sinner">
		<ul class="box">
			<!--li元素的id对应于php结构中的uid-->
			{foreach $procs as $v}
			<li id="{$v['m_uid']}">
				<span class="{$v['_status_class']}">{$v['_status_tip']}</span>
				<a href="/addressbook/show/{$v['m_uid']}"><img src="{$cinstance->avatar($v['m_uid'])}" /></a>{$v['m_username']}
				<time>{$v['_created']}</time>
			</li>
			{/foreach}
		</ul>
	</div></div>
</article>

<!--弹出对话框中form的action会被替换成commentBtn元素的rel属性值-->
<a href="javascript:void(0)" class="commentBtn" rel="/askfor/comment/{$askfor['af_id']}?handlekey=post"><span>我要留言</span></a>
<ul class="mod_comment_list">
	{foreach $comments as $v}
	<li>
		<div class="mod_member_item">
			<a href="/addressbook/show/{$v['m_uid']}"><img src="{$cinstance->avatar($v['m_uid'])}" /></a>{$v['m_username']}
		</div>
		<p>{$v['afc_message']}</p>
		<time>{$v['_created']}</time>
		<a href="javascript:void(0)" class="comm" rel="/askfor/reply/{$v.afc_id}?handlekey=post">回复</a>
		{if $cmt2reply[$v['afc_id']]}
		<ul>
			{foreach $cmt2reply[$v['afc_id']] as $r}
			<li><i>{$r['m_username']}:</i><p>{$r['afr_message']}</p><time>{$r['_created']}</time></li>
			{/foreach}
		</ul>
		{/if}
	</li>
	{/foreach}
</ul>

{if $cur_proc['m_uid'] == $wbs_user.m_uid && voa_d_oa_askfor_proc::STATUS_NORMAL == $cur_proc['afp_status']}
<div class="foot">
	<a href="javascript:;" id="af_refuse" class="mod_button2" rel="/askfor/refuse/{$af_id}?handlekey=post">不同意</a>
	<a href="/askfor/transmit/{$af_id}" class="mod_button1">同意并转审批</a>
	<a href="javascript:;" id="af_approve" class="mod_button1" rel="/askfor/approve/{$af_id}?handlekey=post">同意</a>
</div>
<script>
{literal}
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 不同意 */
		$one('#af_refuse').addEventListener('click', function(e) {
			_show_form(e, '请输入拒绝理由');
		});
		
		$one('#af_approve').addEventListener('click', function(e) {
			var url = e.currentTarget.getAttribute('rel');
			MDialog.confirm('同意', '您确定要同意该审批申请吗?', null, '取消', function(ebtn) {
				/***/
			}, null, '确定', function(ebtn) {
				ajax_form_lock = true;
				MLoading.show('稍等片刻...');
				MAjaxForm.analog(url, {}, 'post', function (s) {
					ajax_form_lock = false;
					MLoading.hide();
				});
			}, null, null, false);
		});
	});
});
{/literal}
</script>
{/if}

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
		$one('.commentBtn').addEventListener('click', function(e) { /** 我要留言 */
			_show_form(e, '请填写评论内容');
		});
	
		$each('ul>li>.comm', function(comm) { /** 回复留言 */
			comm.addEventListener('click', function(e) {
				_show_form(e, '请填写回复内容');
			});
		});
	});
});
{/literal}
</script>


{include file='frontend/footer.tpl'}
