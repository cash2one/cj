{include file='frontend/header.tpl'}

<body id="wbg_spl_detail">

<header>
	<h1 style="table-layout:fixed;word-break:break-all;word-wrap:break-word;">{$askfor['af_subject']}</h1>
	<h2><b>申请人：</b>{$askfor['m_username']}</h2>
</header>


<div class="askfor_conten">		
	<p>申请部门：   采购部</P>
	{if $askfor['af_message']}<p style="table-layout:fixed;word-break:break-all;word-wrap:break-word;">审批内容：  {$askfor['af_message']}</p>{/if}
	{if $colsdata}
		{foreach $colsdata as $col}
		<p>{$col['name']}：   {$col['value']}</p>
		{/foreach}
	{/if}
	
	{if $attachs}
	<div style="background:#fff;padding:10px;">
		{if $attachs}
			{include file='frontend/mod_img_list.tpl'}
		{/if}
	</div>
	{/if}
	
	<p>{$askfor['created']}</p>
</div>

<ul class="box">
	<!--li元素的id对应于php结构中的uid-->
	{foreach $procs as $v}
	<li id="{$v['m_uid']}">
		
		<a href="/addressbook/show/{$v['m_uid']}"><img src="{$cinstance->avatar($v['m_uid'])}" width="40"/></a>{$v['m_username']}
		<span class="{$v['_status_class']}">{$v['_status_tip']}</span>
		<span class="{$v['_status_class']}">{$v['afp_note']}</span>
		<time>{$v['_created']}</time>
	</li>
	{/foreach}
</ul>
	
{if $askfor['m_uid'] == $wbs_user.m_uid}
<div class="foot">
	<a href="javascript:;" class="mod_button2" onclick="histroy.back()">返回</a>
	{if voa_d_oa_askfor::STATUS_NORMAL == $askfor['af_status']}
	<a href="javascript:;" class="mod_button1" id="af_cancel" rel="/askfor/cancel/{$af_id}?handlekey=post">撤销</a>
	{/if}
	{if voa_d_oa_askfor::STATUS_NORMAL == $askfor['af_status'] || voa_d_oa_askfor::STATUS_APPROVE_APPLY == $askfor['af_status'] ||voa_d_oa_askfor::STATUS_REMINDER == $askfor['af_status']}
	<a href="javascript:;" class="mod_button1"  id="af_reminder" rel="/askfor/reminder/{$af_id}?handlekey=post">催办</a>
	{/if}
</div>
<script>
{literal}
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 撤销 */
		$one('#af_cancel').addEventListener('click', function(e) {
			_show_form(e, '请输入撤销理由');
		});
		/** 催办 */
		$one('#af_reminder').addEventListener('click', function(e) {
			_show_form(e, '请输入催办理由');
		});	
	});	
});
{/literal}
</script>
{/if}

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
