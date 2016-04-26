{include file='frontend/header.tpl'}

<body id="wbg_bwl_new">

<div id="viewstack">
	<section>
		<form name="vnote_post" id="vnote_post" method="post" action="{$form_action}">
			<input type="hidden" name="formhash" value="{$formhash}" />
			<input type="hidden" name="vnd_id" value="{$vnd_id}" />
			<fieldset>
				<textarea id="message" placeholder="输入备忘内容：" name="message" required storage>{$vnote['_message']}</textarea>
				{*{include file='frontend/h5mod/record.tpl'}*}
			</fieldset>

			<h2>分享人:</h2>
			<fieldset class="share">
				{include file='frontend/mod_cc_select.tpl' iptname='carboncopyuids' ccusers=$ccusers}
			</fieldset>

			<div class="foot numbtns double">
				<input id="sbt" type="submit" value="保存" />
			</div>
		</form>
	</section>
	<menu class="mod_members_panel"></menu>
</div>

<script>
{if 'new' == $action}MStorageForm.init('vnote_post');{/if}
{literal}
require(['members', 'dialog', 'business'], function() {
	$onload(function() {
		/** 发布审批 */
		$one('#sbt').addEventListener('click', function(e) {
			$one('form').onsubmit(e);
		});

		/** 表单校验 */
		$one('form').onsubmit = function(e) {
			var contentTa = $one('#message');

			e.preventDefault();
			if (!contentTa.value || !$trim(contentTa.value).length) {
				MDialog.notice('请填写内容!');
				return false;
			}

			aj_form_submit('vnote_post');
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