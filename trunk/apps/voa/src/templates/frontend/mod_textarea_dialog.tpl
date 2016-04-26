<script type="text/moatmpl" id="{$scriptid}">
	<form name="{$formid}" id="{$formid}" method="post" action=""> <!--li的id会自动附加到action最后-->
		<input type="hidden" name="formhash" value="{$formhash}" />
		<h1></h1>
		<textarea name="{$textareaname}" id="{$textareaname}"></textarea>
		<footer><input type="reset" value="取消" /><input type="submit" value="确定" /></footer>
	</form>
</script>

<script>
var _m_ta_d_scriptid = '{$scriptid}';
var _m_ta_d_formid = '{$formid}';
{literal}
/** 显示输入框 */
function _m_ta_d_show_dialog(e, tip, title, tavalue) {
	var html = $one('#' + _m_ta_d_scriptid).innerHTML;
	var dlg = MDialog.popupCustom(html, false, null, true);
	dlg.id = 'commentVerifyDlg2';
	dlg.style.left = .5 * (window.innerWidth - dlg.clientWidth) + 'px';
	$one('form', dlg).setAttribute('action', e.currentTarget.rel);
	$one('h1', dlg).innerHTML = title;
	$one('textarea', dlg).setAttribute('placeholder', tip);
	if ('undefined' != typeof(tavalue)) {
		$one('textarea', dlg).value = tavalue;
	}
	
	$one('input[type=reset]', dlg).addEventListener('click', function(e2) {
		MDialog.close();
	});
	$one('#' + _m_ta_d_formid).onsubmit = function(e) {
		e.preventDefault();
		if (true == ajax_form_lock) {
			return false;
		}

		ajax_form_lock = true;
		MLoading.show('稍等片刻...');
		MAjaxForm.submit(_m_ta_d_formid, function(result) {
			ajax_form_lock = false;
			MLoading.hide();
		});

		MDialog.close();
	};
}
{/literal}
</script>