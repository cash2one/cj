{include file='frontend/header.tpl'}
<style>
{literal}
.diy_editable input[type=radio] {float:right; margin-right:20px;}
.diy_editable label {width:auto !important;}
.diy_editable li {height: 26.5px; margin-bottom: 10px; line-height: 26.5px;}
.productive_rules h2 {font-size:14px; line-height:17px;padding-left: 15px;padding-top: 12px;margin-left: 10px;width: 226px;margin-bottom:10px;}
ul.desc {margin-bottom:0px !important;}
{/literal}
</style>
<body id="wbg_xd_indicator_detail">

<form name="productive_post" id="productive_post" method="post" action="{$form_action}">
<input type="hidden" name="formhash" value="{$formhash}" />
<input type="hidden" id="ac" name="ac" value="" />
	
	<h1>{$shop['csp_name']}</h1>
	<ul class="desc mod_common_list">
		<li>
			<div class="clearfix"><h1>{$p_item['pti_name']}</h1></div>
		</li>
		<li>
			<h2>{$c_item['pti_describe']}</h2>
		</li>
	</ul>
	{if !empty($c_item['pti_rules'])}
	<h1>{$productive_set['score_title_standard']}</h1>
	<ul class="mod_common_list productive_rules">
		<li>
			<h2>{$c_item['pti_rules']}</h2>
		</li>
	</ul>
	{/if}
	{if empty($c_item['pti_fix_score'])}
	<h1>{$productive_set['score_title_mark']}</h1>
	<div class="stars mod_common_list_style">
		{if !empty($productive_set['score_rules']) && 0 < $productive_set['score_rule_diy']}
		<ul class="diy_editable">
			{foreach $productive_set['score_rules'] as $_k => $_v}
			<li><input class="diy_score_radio" name="score" type="radio" value="{$_k}"{if $item_score['ptsr_score'] == $_k} checked{/if} /><label>{$_v}</label></li>
			{/foreach}
		{else}
		<ul class="mod_score_starsbar editable" data-callback="onStarsUpdate">
			<li>
				<input name="score" id="score" type="hidden" value="{$item_score['ptsr_score']}" />
				<em data-num="{$item_score['ptsr_score']}"><i></i></em><span><strong>{$item_score['ptsr_score']}</strong>分</span>
				<script>
				var onStarsUpdate = function($li, num) {
					$one('span',$li).innerHTML = '<strong>'+num+'</strong>分';
					$one('.stars input[type=hidden]').value = num;
				}
				</script>
			</li>
		{/if}
		</ul>
	</div>
	{/if}
	<h1>{$productive_set['score_title_describe']}</h1>
	<textarea id="message" name="message" placeholder="在此填写内容">{$item_score['ptsr_message']}</textarea>
	
	<h1>照片</h1>
	<fieldset>
		{include file='frontend/mod_upload.tpl' iptname='at_ids' iptvalue=$at_ids attach_total=$attach_total}
	</fieldset>
	
	<div class="foot numbtns single">
		<input id="next" type="submit" value="保存并评估下一项" />
		{if $is_last}<input id="send" type="submit" value="保存并提交" />{/if}
		<input id="toitem" type="reset" value="返回评估指标" />
	</div>
</form>

<script>
var _pt_id = {$productive['pt_id']};
var _message_title = '{$productive_set['score_title_describe']}';
{literal}
require(['dialog', 'business'], function() {
	//表单校验
	$one('form').addEventListener('submit', function(e) {
		e.preventDefault();
		{/literal}
		{if empty($c_item['pti_fix_score'])}
		{literal}
		var _all_radio = $all('.diy_score_radio');
		var _is_select = false;
		if (_all_radio && 0 < _all_radio.length) {
			$each(_all_radio, function(s_r) {
				if (s_r.checked) {
					_is_select = true;
				}
			});
			
			if (false == _is_select) {
				MDialog.notice('尚未打分!');
				return false;
			}
		} else {
			if ($one('#score').value == 0) {
				MDialog.notice('尚未打分!');
				return false;
			};
		}
		{/literal}
		{else}
		{literal}
		if ('' == $one('#message').value) {
			MDialog.notice('请填写' + _message_title);
			return false;
		}
		{/literal}
		{/if}
		{literal}
		
		aj_form_submit('productive_post');
	});
});

/** 返回打分项列表 */
$one('#toitem').addEventListener('click', function(e) {
	window.location.href = '/frontend/productive/editem/pt_id/' + _pt_id;
});
{/literal}
{if $is_last}
{literal}
$one('#send').addEventListener('click', function(e) {
	$one('#ac').value = e.currentTarget.id;
});
{/literal}
{/if}
{literal}
$one('#next').addEventListener('click', function(e) {
	$one('#ac').value = e.currentTarget.id;
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
