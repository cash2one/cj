{include file='frontend/header.tpl'}

<body id="wbg_qj_launch">

<div id="viewstack">
	<section>
		<form name="reimburse_post" id="reimburse_post" method="post" action="/reimburse/transmit/{$rb_id}?handlekey=post">
			<input type="hidden" name="formhash" value="{$formhash}" />
			<fieldset>
				<h1>批注(可不填, 默认同意):</h1>
				<textarea placeholder="请填写批注内容" required name="message">同意</textarea>
			</fieldset>
			
			<fieldset>
				<h1>审批人:</h1>
				{include file='frontend/mod_approver_select.tpl' iptname='approveuid' iptvalue=$approveuid}
			</fieldset>
			
			<footer><a class="mod_button1">转审批</a></footer>
		</form>	
	</section>
	<menu class="mod_members_panel"></menu>
</div>
	
<script>
{literal}
require(['members', 'dialog', 'business'], function() {
	$onload(function() {
		//发布审批
		$one('form footer a:nth-of-type(1)').addEventListener('click', function(e) {
			$one('form').onsubmit(e);
		});
		
		//表单校验
		$one('form').onsubmit = function(e) {
			var contentTa = $one('fieldset:nth-of-type(1) textarea'),
				memIpt = $one('fieldset:nth-of-type(2) input[type=hidden]');

			e.preventDefault();
			if (!contentTa.value || !$trim(contentTa.value).length) {
				MDialog.notice('请填写内容!');
				return false;
			}
			
			if (!memIpt.value || !$trim(memIpt.value).length) {
				MDialog.notice('请选择审批人!');
				return false;
			}

			/** 提交 */
			aj_form_submit('reimburse_post');
			return true;
		}; 
	});
});

function errorhandle_post(url, msg) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg) {
	MStorageForm.clear();
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}
{/literal}
</script>

{include file='frontend/footer.tpl'}