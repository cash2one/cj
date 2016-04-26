{include file='frontend/header.tpl'}

<body id="wbg_bx_detail">

<form action="">
<header>
	<h1>{$reimburse['_subject']}</h1>
	<div class="mod_steps_slider"><div class="sinner"><ul class="box">
		{foreach $procs as $v name=proc}
		<li><span class="{$v['_status_class']}"></span><p>{$v['m_username']}</p></li>
		{/foreach}
	</ul></div></div>
</header>

<div class="total mod_common_list_style">报销总计：<span>{$reimburse['_expend']}</span><i>￥</i></div>

<h1>报销明细：</h1>
<ul class="mod_common_list">
	{foreach $bills as $k => $v}
	<li>
		<div>{$types[$v['rbb_type']]} {$v['_time_md']} <span>￥<i>{$v['_expend']}</i></span></div>
		<div class="info" hidden>
			<p>{$v['_reason']}</p>
			{if $v['_attachs']}
				{$attachs = $v['_attachs']}
				<div class="mod_photo_uploader readonly">
					{include file='frontend/mod_img_list.tpl'}
				</div>
				{$attachs = array()}
			{/if}
		</div>
	</li>
	{/foreach}
</ul>
</form>

{if $cur_proc['m_uid'] == $wbs_uid && voa_d_oa_reimburse_proc::STATUS_NORMAL == $cur_proc['rbpc_status']}
<script type="text/moatmpl" id="dialogTmpl">
	<form name="frmpost" id="frmpost" method="post" action="">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<textarea name="message" placeholder=""></textarea>
		<footer><input type="reset" value="取消" /><input type="submit" name="sbtpost" value="确定" /></footer>
	</form>
</script>

<div class="foot numbtns triple">
	<a href="javascript:;" id="rb_refuse" rel="/reimburse/refuse/{$rb_id}?handlekey=post" class="mod_button2">不同意</a>
	<a href="/reimburse/transmit/{$rb_id}" class="mod_button1">同意并转审批</a>
	<a href="javascript:;" id="rb_approve" rel="/reimburse/approve/{$rb_id}?handlekey=post" class="mod_button1">同意</a>
</div>
<script>
{literal}
require(['dialog', 'members', 'business'], function() {
	$onload(function() {
		/** 不同意 */
		$one('#rb_refuse').addEventListener('click', function(e) {
			_show_form(e, '请输入拒绝理由', '拒绝');
		});
		
		$one('#rb_approve').addEventListener('click', function(e) {
			_show_form(e, '请输入备注', '同意');
		});
	});

	/** 显示输入框 */
	function _show_form(e, tip, def) {
		var html = $one('#dialogTmpl').innerHTML;
		var dlg = MDialog.popupCustom(html, false, null, true);
		dlg.id = 'commentVerifyDlg';
		dlg.style.left = .5 * (window.innerWidth - dlg.clientWidth) + 'px';
		$one('form', dlg).setAttribute('action', e.currentTarget.rel);
		$one('textarea', dlg).setAttribute('placeholder', tip);
		$one('textarea', dlg).value = def;
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
{/if}

<script>
{literal}
require(['dialog', 'members', 'business'], function() {
	$onload(function() {
		$each('li>div:first-of-type', function(div) {
			div.onclick = function(e) {
				var $div = e.currentTarget,
					$info = $one('.info', $div.parentNode);
				if ($data($div, 'opened') === 'yes') {
					$hide($info);
					$data($div, 'opened', '');
					$rmCls($div, 'opened');
				} else {
					$show($info);
					$data($div, 'opened', 'yes');
					$addCls($div, 'opened');
					var $img = $one('img', $info);
					if ($img && !$img.hasAttribute('src')) {
						$img.setAttribute('src', $data($img, 'src'));
					}
				}
			};
		});
	});
});
{/literal}
</script>

{include file='frontend/footer.tpl'}