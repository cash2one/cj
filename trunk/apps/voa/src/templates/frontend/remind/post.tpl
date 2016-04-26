{include file='frontend/header.tpl'}

<body>
<script src="{$wbs_javascript_path}/MOA.memberselect.js"></script>

<div id="viewstack"><section>
	<form name="remind_post" id="remind_post" method="post" action="/remind/new?handlekey=post">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<fieldset>
			<h1>主题:</h1>
			<textarea placeholder="输入主题" required name="subject" id="subject" storage></textarea>
		</fieldset>
		
		<fieldset>
			<h1>提醒时间:</h1>
			<textarea placeholder="输入提醒时间" required name="remindts" id="remindts" storage></textarea>
		</fieldset>

		<fieldset>
			<h1>内容:</h1>
			<textarea placeholder="输入内容" required name="message" id="message" storage></textarea>
		</fieldset>
		<footer><a id="apost" class="mod_button1">发布公告</a></footer>
	</form>
</section><menu id="mod_members_panel" class="mod_members_panel"></menu></div>

<script>
{if 'new' == $action}MStorageForm.init('remind_post');{/if}
{literal}
$onload(function() {
	/** 发布审批 */
	$one('#apost').addEventListener('click', function(e) {
		$one('form').onsubmit(e);
	});

	/** 表单校验 */
	$one('form').onsubmit = function(e) {
		var projectTa = $one('#subject'),
			contentTa = $one('#message'),
			remindtsTa = $one('#remindts');
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
		
		if (!remindtsTa.value || !$trim(remindtsTa.value).length) {
			MDialog.notice('请填写提醒时间');
			e.preventDefault();
			return false;
		}

		if (true == ajax_form_lock) {
			e.preventDefault();
			return false;
		}

		ajax_form_lock = true;
		MLoading.show('稍等片刻...');
		MAjaxForm.submit('remind_post', function(result) {
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
