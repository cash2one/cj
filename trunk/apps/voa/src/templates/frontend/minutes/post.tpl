{include file='frontend/header.tpl'}

<body id="wbg_hyt_record_new">
<div id="viewstack"><section>
	<form name="minutes_post" id="minutes_post" method="post" action="/minutes/new?handlekey=post">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<input type="hidden" name="mid_id" value="{$mid_id}" />
		<fieldset class="project">
			<label>主题:</label><input placeholder="输入主题" type="text" name="subject" id="subject" storage required />
		</fieldset>

		<fieldset class="cont">
			<h1>会议内容纪录</h1>
			<textarea placeholder="输入内容" required name="message" id="message" storage required>{$message}</textarea>
		</fieldset>
{if !empty($p_sets['upload_image'])}
		<h1>上传照片:</h1>
		<fieldset style="background:#FFF;border:1px solid rgba(169,169,169,0.6);border-width:1px 0 1px 0;">
			{include file='frontend/mod_upload.tpl' iptname='at_ids' iptvalue=$at_ids attach_total=$attach_total}
		</fieldset>
{/if}
		<fieldset>
			<h1>参会人:</h1>
			{include file='frontend/mod_cc_select.tpl' iptname='recvuids' ccusers=$accepters}
		</fieldset>

		<fieldset>
			<h1>抄送人:</h1>
			{include file='frontend/mod_cc_select.tpl' iptname='carboncopyuids' ccusers=$cculist}
		</fieldset>

		<footer><a id="apost" class="mod_button1">发布会议纪要</a></footer>
	</form>
</section><menu id="mod_members_panel" class="mod_members_panel"></menu></div>

<script>
{if 'new' == $action}MStorageForm.init('minutes_post');{/if}
{literal}
require(['dialog', 'members', 'business'], function(){
	$onload(function() {
		/** 发布审批 */
		$one('#apost').addEventListener('click', function(e) {
			$one('form').onsubmit(e);
		});

		/** 表单校验 */
		$one('form').onsubmit = function(e) {
			var projectTa = $one('#subject'),
				contentTa = $one('#message'),
				memIpt = $one('#recvuids');
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

			if (!memIpt.value || !$trim(memIpt.value).length) {
				MDialog.notice('请选择参会人!');
				e.preventDefault();
				return false;
			}

			if (true == ajax_form_lock) {
				e.preventDefault();
				return false;
			}

			ajax_form_lock = true;
			MLoading.show('稍等片刻...');
			MAjaxForm.submit('minutes_post', function(result) {
				MLoading.hide();
			});

			return false;
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
