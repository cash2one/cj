{include file='frontend/header.tpl'}

<body id="wbg_spl_launch">
<script src="{$wbs_javascript_path}/MOA.memberselect.js"></script>

<div id="viewstack"><section>
	<form name="askfor_post" id="askfor_post" method="post" action="/askfor/new/{$aft_id}?handlekey=post">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<fieldset>
			<h1>审批主题:</h1>
			<textarea placeholder="输入如出差审批" required name="subject" id="subject" storage></textarea>
		</fieldset>

		<fieldset>
			<h1>审批说明内容:</h1>
			<textarea placeholder="输入审批的具体事由和审批时间" required name="message" id="message" storage></textarea>
		</fieldset>
		
{if !empty($template.cols)}
		{foreach $template.cols as $v}
			{if $v.type == 2}
			<fieldset>
				<h1>{$v.name}:</h1>
				<textarea  {if $v.required == 1}required{/if} name="cols[{$v.afcc_id}]"  storage></textarea>
			</fieldset>
			{else}
			<fieldset>
				<h1>{$v.name}:</h1>
				<textarea  {if $v.required == 1}required{/if} name="cols[{$v.afcc_id}]"  storage></textarea>
			</fieldset>
			{/if}
		{/foreach}
{/if}
{if $template['upload_image'] == 1}
		<h1>上传图片:</h1>
		<fieldset style="background:#FFF;border:1px solid rgba(169,169,169,0.6);border-width:1px 0 1px 0;">
			{include file='frontend/mod_upload.tpl' iptname='at_ids' iptvalue=$at_ids attach_total=$attach_total}
		</fieldset>
{/if}		
		<div class="foot numbtns single">
				<input type="reset" value="返回" onclick="histroy.back()">
				<input id="apost" type="submit" value="发布审批">
				
			</div>
	</form>
</section><menu id="mod_members_panel" class="mod_members_panel"></menu></div>

<script>
{if 'new' == $action}MStorageForm.init('askfor_post');{/if}
{literal}
require(['members', 'dialog', 'business'], function() {
	$onload(function() {
		/** 发布审批 */
		$one('#apost').addEventListener('click', function(e) {
			$one('form').onsubmit(e);
		});
	
		/** 表单校验 */
		$one('form').onsubmit = function(e) {
			var projectTa = $one('#subject'),
				contentTa = $one('#message'),
				memIpt = $one('#approveuid');
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
			MAjaxForm.submit('askfor_post', function(result) {
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
