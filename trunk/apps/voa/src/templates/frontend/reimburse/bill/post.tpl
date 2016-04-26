{include file='frontend/header.tpl'}

<body id="wbg_bx_launch">

<form name="bill_post" id="bill_post" method="post" action="{$form_action}?handlekey=post">
<input type="hidden" name="formhash" value="{$formhash}" />
<h1>个人信息：</h1>
<ul class="mod_common_list">
	<li class="types">
		<label>类别：</label>
		<div class="opts">
			<span class="fake init">选择</span>
			<span class="fake init"></span>
			<select name="type">
				<option value="0">请选择</option>
				{foreach $types as $k => $v}
				<option value="{$k}"{if $k == $bill['rbb_type']} selected{/if}>{$v}</option>
				{/foreach}
			</select>
		</div>
	</li>
	<li class="payment">
		<label>金额：</label><input name="expend" id="expend" type="text" required pattern="{literal}^(?=.*\d)\d*(?:\.\d*)?${/literal}" value="{$bill['_expend']}" storage />
	</li>
</ul>

<ul class="mod_common_list">
	<li class="time">
		<label>时间:</label>
		{include file='frontend/mod_ymdhi_select.tpl' iptname="time" iptvalue=$bill['_time'] startts=$bill['rbb_time']}
	</li>
	<li class="cont">
		<label>事由:</label><textarea required name="reason" id="reason" placeholder="请填写内容" storage>{$bill['_reason']}</textarea>
	</li>
</ul>

{if !empty($p_sets['upload_image'])}
<h1>照片:</h1>
<ul class="mod_common_list">
	<li style="margin-left:0px;">
		{include file='frontend/mod_upload.tpl' iptname='at_ids' iptvalue=$at_ids attach_total=$attach_total}
	</li>
</ul>
{/if}

<div class="foot numbtns double">
	<input id="btn_go_back" type="reset" value="取消" /><input type="submit" value="保存" />
</div>
</form>

<script>
var _type_index = {$type_index};
var _time_start = {$current_ts} - 15 * 86400;
{if 'bill_new' == $action}MStorageForm.init('bill_post');{/if}
{literal}
require(['dialog', 'timeslider', 'business', 'formvalidate'], function() {	
	//提交
	$onload(function() {
		$one('form').addEventListener('submit', function(e) {
			e.preventDefault();
			var tool = MOA.form.FormValidate;
			if (!tool.selectedWithFake($one('.opts select'), $one('.opts .fake:nth-of-type(2)'))) {
				MDialog.notice('请选则类别');
				return false;
			}

			if (!tool.patternValid($one('.payment input'))) {
				MDialog.notice('请填写金额');
				return false;
			}

			if (!tool.requiredValid($one('.cont textarea'))) {
				MDialog.notice('请填写事由');
				return false;
			}

			if (!tool.requiredValid($one('.time input'))) {
				MDialog.notice('请选择时间');
				return false;
			}

			if (true == ajax_form_lock) {
				return false;
			}

			ajax_form_lock = true;
			MLoading.show('稍等片刻...');
			MAjaxForm.submit('bill_post', function(result) {
				MLoading.hide();
			});
		});
	});

	$onload(function() {
		//报销类别
		parseHiddenSelect('.opts select');
		setTimeout(function() {
			var $sel = $one('.opts select'), $fake = $prev($sel);
			$sel.selectedIndex = _type_index;
			$rmCls($fake, 'init');
			$fake.innerHTML = $sel.options[$sel.selectedIndex].innerHTML;
		}, 200);
	});
});

function errorhandle_post(url, msg) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg) {
	MStorageForm.clear();
	MDialog.notice(msg);

	var resume = [
		'报销',
		$one('.time .fake').innerHTML,
		$one('.cont textarea').value,
		'的',
		$one('.opts .fake:nth-of-type(2)').innerHTML,
		'￥', $one('.payment input').value,,
		' 将保存在您的报销单据中。'
	].join(' ');

	MDialog.alert(
		'保存成功',
		resume,
		null,
		'知道了',
		function() {
			window.location.href = url;
		}
	);
}
{/literal}
</script>

{include file='frontend/footer.tpl'}