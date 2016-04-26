{include file='frontend/header.tpl'}
<body id="wbg_db_list">

	<div class="mod_common_list_style launch">
		<form id="form_post" action="{$form_action}" method="post" hidden>
			<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
			<input type="text" name="subject" />
			<input type="submit" value=""/>
		</form>
		<a href="javascript:void(0)">新建待办事项</a>
	</div>

	{include file='frontend/todo/list_li.tpl'}

<script>
require(['dialog'], function(){

	var ajaxLock = false;

	$onload(function(){
		//即时新建
		$one('.launch>a').addEventListener('click', function(e){
			var lk = e.currentTarget;
			var fm = $prev(lk);
			var ipt = $one('input[type=text]', fm);
			var btn = $one('input[type=submit]', fm);
			$hide(lk);
			$show(fm);
			ipt.focus();
			fm.onsubmit = function(e){
				e.preventDefault();

				if ( !$trim(ipt.value) ){
					ipt.blur();
					$hide(fm);
					$show(lk);
					return false;
				}

				if (true == ajax_form_lock) {
					return false;
				}

				ajax_form_lock = true;
				MLoading.show('稍等片刻...');

				MAjaxForm.submit('form_post', function(result) {
					MLoading.hide();
				});

				return true;
			};

			return true;
		});
	});
});
function errorhandle_post(url, msg, js) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg, js) {
	MStorageForm.clear();
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}
</script>
{include file='frontend/footer.tpl'}
