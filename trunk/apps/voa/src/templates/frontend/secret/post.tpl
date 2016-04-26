{include file='frontend/header.tpl'}

<body id="wbg_spl_launch">
<script src="{$wbs_javascript_path}/MOA.memberselect.js"></script>

<div id="viewstack">
	<form name="secret_post" id="secret_post" method="post" action="/secret/new?handlekey=post">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<fieldset>
			<h1>主题:</h1>
			<textarea placeholder="输入主题" required name="subject" id="subject" storage></textarea>
		</fieldset>

		<fieldset>
			<h1>内容:</h1>
			<textarea ="输入秘密内容" required name="message" id="message" storage></textarea>
		</fieldset>

		<footer><a id="apost" class="mod_button1">秘密发布</a></footer>
	</form>
</div>

<script>
{if 'new' == $action}MStorageForm.init('secret_post');{/if}
{literal}
$onload(function() {
	/** 发布审批 */
	$one('#apost').addEventListener('click', function(e) {
		$one('form').onsubmit(e);
	});

	/** 表单校验 */
	$one('form').onsubmit = function(e) {
		var projectTa = $one('#subject'),
			contentTa = $one('#message');
		if (!projectTa.value || !$trim(projectTa.value).length) {
			MDialog.notice('请填写主题!');
			e.preventDefault();
			return false;
		}

		if (!contentTa.value || !$trim(contentTa.value).length) {
			MDialog.notice('请填写内容!');
			e.preventDefault();
			return false;
		}

		if (true == ajax_form_lock) {
			e.preventDefault();
			return false;
		}

		ajax_form_lock = true;
		MLoading.show('稍等片刻...');
		MAjaxForm.submit('secret_post', function(result) {
			MLoading.hide();
		});

		return false;
	};
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
