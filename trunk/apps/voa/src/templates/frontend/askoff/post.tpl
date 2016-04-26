{include file='frontend/header.tpl'}

<body id="wbg_qj_launch">

<div id="viewstack">
	<section>
		<form name="askoff_post" id="askoff_post" method="post" action="/askoff/new?handlekey=post">
			<input type="hidden" name="formhash" value="{$formhash}" />
			<input type="hidden" name="aod_id" value="{$aod_id}" />
			<fieldset>
				<h1>请假内容:</h1>
				<textarea placeholder="在此说明请假内容" required id="message" name="message" storage>{$askoff['_message']}</textarea>
			</fieldset>
			
			<fieldset>
				<div class="types">
					<label>请假类别：</label><span class="fake init">选择</span>
					<select name="type">
						<option value="0">请选择</option>
						{foreach $types as $k => $v}
						<option value="{$k}">{$v}</option>
						{/foreach}
					</select>
				</div>
			</fieldset>
{if !empty($p_sets['upload_image'])}
			<h1>上传照片:</h1>
			<fieldset style="background:#FFF;border:1px solid rgba(169,169,169,0.6);border-width:1px 0 1px 0;">
				{include file='frontend/mod_upload.tpl' iptname='at_ids' iptvalue=$at_ids attach_total=$attach_total}
			</fieldset>
{/if}
			<fieldset>
				<h1>请假时间:</h1>
				<ul class="mod_common_list mod_startend_time time" >
					<li class="time">
						<label>开始时间:</label>
						{include file='frontend/mod_ymdhi_select.tpl' iptname='begintime' }
					</li>
					<li class="time">
						<label>结束时间:</label>
						{include file='frontend/mod_ymdhi_select.tpl' iptname='endtime' }
					</li>
				</ul>
			</fieldset>
			
			<fieldset>
				<h1>审批人:</h1>
				{include file='frontend/mod_approver_select.tpl' iptname='approveuid' accepter=$accepter}
			</fieldset>
			
			<fieldset>
				<h1>抄送人:</h1>
				{include file='frontend/mod_cc_select.tpl' iptname='carboncopyuids' ccusers=$ccusers}
			</fieldset>
			
			<footer><a id="apost" class="mod_button1">申请</a></footer>
		</form>	
	</section>
	<menu id="mod_members_panel" class="mod_members_panel"></menu>
</div>

<script>
{if 'new' == $action}MStorageForm.init('askoff_post');{/if}
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
				tFake = $one('.types .fake'),
				bFake = $one('#begintime'),
				eFake = $one('#endtime'),
				memIpt = $one('#approveuid');

			e.preventDefault();
			if (!contentTa.value || !$trim(contentTa.value).length) {
				MDialog.notice('请填写内容!');
				return false;
			}
			
			if ($hasCls(tFake, 'init')) {
				MDialog.notice('请选择类别!');
				return false;
			}
			
			if (!bFake.value || !eFake.value) {
				MDialog.notice('请选择日期!');
				return false;
			}
			
			if (!memIpt.value || !$trim(memIpt.value).length) {
				MDialog.notice('请选择审批人!');
				return false;
			}

			if (true == ajax_form_lock) {
				return false;
			}

			ajax_form_lock = true;
			MLoading.show('稍等片刻...');
			MAjaxForm.submit('askoff_post', function(result) {
				MLoading.hide();
			});
			
			return true;
		}; 

		//时间设置
		var daysRange = (isLeapYear() ? 367: 366),
			timeConfig = {
				rangeDays: daysRange, //时间范围的业务逻辑为: 今天至一年后
				noticeMin: '请选择晚于当前的时间!',
				noticeMax: '请选择'+ daysRange +'天以内的时间!'
			};

		parseHiddenSelect('.types select');
		//parseStartEndDateSelects('ul.time');
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

{include file='frontend/footer_nav.tpl'}

{include file='frontend/footer.tpl'}