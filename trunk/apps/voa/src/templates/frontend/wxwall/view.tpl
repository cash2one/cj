{include file='frontend/header.tpl'}

<body id="wbg_wxq_detail">
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>

<header>
	<h1>{$wall['ww_subject']}{if !$wall_status} - <font color="red">{$st_tip}</font>{/if}</h1>
	<h2>发起：{$wall['m_username']}</h2>
	<div class="center">
		<div class="t"><time><em>{$b_m}</em><i>月</i><em>{$b_d}</em><i>日</i><b>{$b_h}:{$b_i}</b></time>开始时间</div>
		<div class="t"><time><em>{$e_m}</em><i>月</i><em>{$e_d}</em><i>日</i><b>{$e_h}:{$e_i}</b></time>结束时间</div>
	</div>
</header>
<ul class="mod_common_list">
	<li>
		<label>管理地址：</label><i>{$wall_url}</i>
	</li>
</ul>
<ul class="mod_common_list">
	<li>
		<label>管理员：</label><i>{$wall['ww_admin']}</i>
	</li>
	<li>
		<label>密码：</label><i>******</i>
	</li>
</ul>

<ul id="wxwall_post">
{include file='frontend/wxwall/view_li.tpl'}
</ul>

<a href="#" class="mod_button1">关闭微信墙</a>

{if $wall_status}
	<script>
	{literal}
	var _get_wxwall_post = function() {
		if (true == ajax_form_lock) {
			return false;
		}

		ajax_form_lock = true;
		MAjaxForm.analog(window.location.href, {'updated':_wxwall_updated}, 'post', function (s) {
			ajax_form_lock = false;
			$prepend($one('#wxwall_post'), s);
		});
	};
	{/literal}
	</script>
	{if $ts > $wall['ww_endtime']}
	<a id="show_more" href="javascript:void(0)" class="mod_ajax_more">加载更多&gt;&gt;</a>
	<script>
	{literal}
	$onload(function() {
		/** 加载更多 */
		var _more = new c_list_more();
		_more.init('show_more', 'wxwall_post', '_wxwall_updated');
	});
	{/literal}
	</script>
	{elseif $ts > $wall['ww_begintime']}
	<script type="text/moatmpl" id="dialogTmpl">
	<form name="frmpost" id="frmpost" method="post" action="wxwall.php?mod=reply&ww_id={$wall['ww_id']}">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<textarea name="message" placeholder=""></textarea>
		<footer><input type="reset" value="取消" /><input type="submit" name="sbtpost" value="确定" /></footer>
	</form>
	</script>
	<a href="javascript:;" id="wall_reply" hidden>post</a>
	<script>
	{literal}
	/** 显示输入框 */
	function _show_form(e, tip) {
		var html = $one('#dialogTmpl').innerHTML;
		var dlg = MDialog.popupCustom(html, false, null, true);
		dlg.id = 'commentVerifyDlg';
		dlg.style.left = .5 * (window.innerWidth - dlg.clientWidth) + 'px';
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
				ajax_form_lock = false;
				MLoading.hide();
			});

			MDialog.close();
		};
	}
	$onload(function() {
		/**$one('#wall_reply').addEventListener('click', function(e) {
			_show_form(e, '请输入信息内容');
		});*/

		/** 获取回复 */
		//setInterval(_get_wxwall_post, 5000);
	});
	{/literal}
	</script>
	{/if}
{/if}


{include file='frontend/footer.tpl'}
