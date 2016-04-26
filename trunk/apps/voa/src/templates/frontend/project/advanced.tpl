{include file='frontend/header.tpl'}

<body id="wbg_gzt_promote">
<script src="{$wbs_javascript_path}/MOA.memberselect.js"></script>

<div id="viewstack"><section>
	<form name="proj_{$ac}" id="proj_{$ac}" method="post" action="/project/advanced/{$p_id}?handlekey=post">
		<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
		<input type="hidden" name="referer" value="{$refer}" />
		<h2>推进消息：</h2>
		<div class="mod_common_list_style">
			<textarea name="message" id="message" placeholder="请填写推进消息" value="" required></textarea>
		</div>
		<h2>任务成员：</h2>
		<fieldset class="to">
			{include file='frontend/mod_cc_select.tpl' iptname='project_uids' ccusers=$users}
		</fieldset>
		<input type="submit" name="sbtpost" id="sbtpost" value="立刻推进" />
	</form>
</section><menu id="mod_members_panel" class="mod_members_panel"></menu></div>

{include file='frontend/footer_nav.tpl'}

<script>
var _frm_name = 'proj_{$ac}';
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

require(['dialog', 'members', 'business'], function() {
	$onload(function() { /** 表单提交 */
		$one('form').onsubmit = function(e){
			var ta = $one('textarea');
			e.preventDefault();
			if (!ta.value || !$trim(ta.value).length){
				MDialog.notice('请填写推进消息!');
				return false;
			}

			var memIpt = $one('#project_uids');
			if (!memIpt.value || !$trim(memIpt.value).length){
				MDialog.notice('请选择任务成员!');
				return false;
			}

			if (true == ajax_form_lock) {
				e.preventDefault();
				return false;
			}

			ajax_form_lock = true;
			MLoading.show('稍等片刻...');
			MAjaxForm.submit(_frm_name, function(result) {
				MLoading.hide();
			});

			return false;
		};
	});
});
{/literal}
</script>


{include file='frontend/footer.tpl'}
