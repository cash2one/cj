{if empty($list)}
<div class="mod_empty_notice"><span>没有最新轨迹</span></div>
{else}
<ol class="mod_common_list mod_circlestyle_list">
	{include file='frontend/footprint/footprint_li.tpl'}
</ol>
{/if}

{if $perpage <= count($list)}
<a id="show_more" href="javascript:void(0)" class="mod_ajax_more">加载更多&gt;&gt;</a>
{/if}

<script type="text/moatmpl" id="dialogTmpl">
	<form name="frmpost" id="frmpost" method="post" action="">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<textarea name="message" placeholder="回复内容"></textarea>
		<footer><input type="reset" value="取消" /><input type="submit" value="确定" /></footer>
	</form>
</script>

<script>
{literal}
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 加载更多 */
		var _more = new c_list_more();
		_more.init('show_more', 'askfor_list', {'updated':'_askfor_updated'});
	});
});

require(['dialog'], function() {
	$onload(function() {
		$each($all('.commentBtn'), function(btn) {
			btn.addEventListener('click', function(e) { //回复
				var html = $one('#dialogTmpl').innerHTML;
				var dlg = MDialog.popupCustom(html, false, null, true);
				dlg.id = 'commentVerifyDlg';
				dlg.style.left = .5 * (window.innerWidth - dlg.clientWidth) + 'px';
				$one('form', dlg).setAttribute('action', e.currentTarget.rel);
				$one('input[type=reset]', dlg).addEventListener('click', function(e2) {
					MDialog.close();
				});
				
				$one('#frmpost').onsubmit = function(e) {
					aj_form_submit('frmpost');
					MDialog.close();
				};
			});
		});
	});
});

function errorhandle_post(url, msg) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg) {
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = window.location.href;
	}, 500);
}
{/literal}
</script>