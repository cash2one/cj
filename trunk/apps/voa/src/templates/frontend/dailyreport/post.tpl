{include file='frontend/header.tpl'}

<body id="wbg_rb_launch">
<script src="{$wbs_javascript_path}/MOA.hsliderchooser.js"></script>

<div id="viewstack">
	<section>
		<form name="dailyreport_post" id="dailyreport_post" method="post" action="/dailyreport/new?handlekey=post">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<input type="hidden" name="drd_id" value="{$drd_id}" />
		<fieldset class="time">
			<h1>日报日期：</h1>
			<div class="days">
				<span class="fake init">变更</span>
				<span class="fake init"></span>
				<select name="reporttime">
					{foreach $days as $k => $v}
					<option value="{$k}">{$v['m']}月{$v['d']}日 {$weeknames[$v['w']]}</option>
					{/foreach}
				</select>
			</div>
		</fieldset>
		
		<fieldset class="cont">
			<h1>日报内容:</h1>
			<textarea placeholder="在此填写内容" required id="message" name="message" storage>{$message}</textarea>
		</fieldset>
{if !empty($p_sets['upload_image'])}
		<h1>上传照片:</h1>
		<fieldset style="background:#FFF;border:1px solid rgba(169,169,169,0.6);border-width:1px 0 1px 0;">
			{include file='frontend/mod_upload.tpl' iptname='at_ids' iptvalue=$at_ids attach_total=$attach_total}
		</fieldset>
{/if}	
		<fieldset>
			<h1>接收人:</h1>
			{include file='frontend/mod_approver_select.tpl' iptname='approveuid' accepter=$accepter}
		</fieldset>
		
		<fieldset>
			<h1>抄送人:</h1>
			{include file='frontend/mod_cc_select.tpl' iptname='carboncopyuids' ccusers=$ccusers}
		</fieldset>
		<footer><a id="apost" class="mod_button1">发送</a></footer>
		</form>	
	</section>
	<menu class="mod_members_panel"></menu>
</div>

<script>
var default_index = {$default_index};
{if 'new' == $action}MStorageForm.init('dailyreport_post');{/if}
{literal}
require(['members', 'dialog', 'business'], function() {
	$onload(function() {
		/** 发布审批 */
		$one('#apost').addEventListener('click', function(e) {
			$one('form').onsubmit(e);
		});
	
		/** 表单校验 */
		$one('form').onsubmit = function(e) {
			var contentTa = $one('#message'),
			memIpt = $one('#approveuid');
			
			if (!contentTa.value || !$trim(contentTa.value).length){
				MDialog.notice('请填写内容!');
				e.preventDefault();
				return false;
			}
			
			if (!memIpt.value || !$trim(memIpt.value).length){
				MDialog.notice('请选择接收人!');
				e.preventDefault();
				return false;
			}
	
			if (true == ajax_form_lock) {
				e.preventDefault();
				return false;
			}
	
			ajax_form_lock = true;
			MLoading.show('稍等片刻...');
			MAjaxForm.submit('dailyreport_post', function(result) {
				MLoading.hide();
			});
	
			return false;
		};
	});
	
	parseHiddenSelect('.days select');
	setTimeout(function(){
		var $sel = $one('.days select'),
			$fake = $prev($sel);
		$sel.selectedIndex = default_index;
		$rmCls($fake, 'init');
		$fake.innerHTML = $sel.options[$sel.selectedIndex].innerHTML;
	}, 200);
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
