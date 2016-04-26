{include file='frontend/header.tpl'}

<body id="wbg_qj_detail">

<header>
    <h1>{$askoff['m_username']} 申请{$types[$askoff['ao_type']]} {$askoff['_timespace']}</h1>
    <div class="center">
        <div class="t">
            <time><em>{$askoff['_begintime'][0]}</em><i>月</i><em>{$askoff['_begintime'][1]}</em><i>日</i><b>{$weeknames[$askoff['_begintime'][2]]}</b>
            </time>开始日期</div>
        <div class="t">
            <time><em>{$askoff['_endtime'][0]}</em><i>月</i><em>{$askoff['_endtime'][1]}</em><i>日</i><b>{$weeknames[$askoff['_endtime'][2]]}</b>
            </time>结束日期</div>
    </div>
</header>

<ul class="mod_common_list mem">
	<li>
		<label>开始：</label>  <span>{$askoff['_begintime_ymdhi']}</span>
	</li>
	<li>
		<label>结束：</label>  <span>{$askoff['_endtime_ymdhi']}</span>
	</li>
</ul>
{if $askoff['_message']}
<p>
	{$askoff['_message']}
</p>
{/if}
{if $attachs}
	{include file='frontend/mod_img_list.tpl'}
{/if}

<ul class="mod_common_list mem">
	{if $cc_users}
    <li class="cc">
        <label>抄送：</label>{foreach $cc_users as $uname}  <span>{$uname}</span>{/foreach}&nbsp;
    </li>
    {/if}
    <li class="to">
        <label>审批：</label>
        <div class="mod_members slider"><div class="sinner">
            <ul class="box">
                <!--li元素的id对应于php结构中的uid-->
                {foreach $procs as $v}
                <li id="{$v['m_uid']}">
                	<span class="{$v['_status_class']}">{$v['_status_tip']}</span>
                    <img src="{$cinstance->avatar($v['m_uid'])}" />{$v['m_username']}
                    <time>{$v['_updated_u']}</time>
                </li>
                {/foreach}
            </ul>
        </div></div>
    </li>
</ul>

<ul class="mod_comment_list simple">
	{foreach $posts as $v}
    <li>
        <h1>{$v['m_username']}:</h1>
        <p>{$v['_message']}</p>
        <time>{$v['_created_u']}</time>
    </li>
    {/foreach}
</ul>

{if $cur_proc['m_uid'] == $wbs_user.m_uid && voa_d_oa_askoff_proc::STATUS_NORMAL == $cur_proc['aopc_status']}
<div class="foot">
    <a href="javascript:;" id="ao_refuse" rel="/askoff/refuse/{$ao_id}?handlekey=post" class="mod_button2">不同意</a>
    <a href="/askoff/transmit/{$ao_id}" class="mod_button1">同意并转审批</a>
    <a href="javascript:;" id="ao_approve" rel="/askoff/approve/{$ao_id}?handlekey=post" class="mod_button1">同意</a>
</div>
<script>
{literal}
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 不同意 */
		$one('#ao_refuse').addEventListener('click', function(e) {
			_show_form(e, '请输入拒绝理由');
		});
		
		$one('#ao_approve').addEventListener('click', function(e) {
			_show_form(e, '请输入备注');
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
{/literal}
</script>

{include file='frontend/footer_nav.tpl'}

{include file='frontend/footer.tpl'}