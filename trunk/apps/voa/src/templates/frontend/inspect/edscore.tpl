{include file='frontend/header.tpl'}

<style>
{literal}
.diy_editable input[type=radio] {float:right; margin-right:20px;}
.diy_editable label {width:auto !important;}
.diy_editable li {height: 26.5px; margin-bottom: 10px; line-height: 26.5px;}
.inspect_rules h2 {font-size:14px; line-height:17px;padding-left: 15px;padding-top: 12px;margin-left: 10px;width: 226px;margin-bottom:10px;}
ul.desc {margin-bottom:0px !important;}
{/literal}
</style>
<body id="wbg_xd_indicator_detail">

<form name="inspect_post" id="inspect_post" method="post" action="{$form_action}">
<input type="hidden" name="formhash" value="{$formhash}" />
<input type="hidden" id="ac" name="ac" value="" />
	
	<h1>{$shop['csp_name']}</h1>
	<ul class="desc mod_common_list">
		<li>
			<div class="clearfix"><h1>{$p_item['insi_name']}</h1></div>
		</li>
		<li>
			<h2>{$c_item['insi_describe']}</h2>
		</li>
	</ul>
	
	{if !empty($c_item['insi_rules'])}
	<h1>{if $c_item['insi_rules_title']}{$c_item['insi_rules_title']}{else}{$inspect_set['score_title_standard']}{/if}</h1>
	<ul class="mod_common_list inspect_rules">
		<li>
			<h2>{$c_item['insi_rules']}</h2>
		</li>
	</ul>
	{/if}
	
	{if !empty($inspect_set['score_rules']) && 0 < $inspect_set['score_rule_diy']}
	<h1>{$inspect_set['score_title_mark']}</h1>
	<div class="stars mod_common_list_style">
		<ul class="diy_editable">
			{foreach $inspect_set['score_rules'] as $_k => $_v}
			<li><input class="diy_score_radio" name="score" type="radio" value="{$_k}"{if $item_score['isr_score'] == $_k} checked{/if} /><label>{$_v}</label></li>
			{/foreach}
		</ul>
	</div>
	{else}
	<h1>{if !empty($c_item['insi_score_title'])}{$c_item['insi_score_title']}{else}{$inspect_set['score_title_mark']}{/if}</h1>
	<div class="stars mod_common_list_style">
		<ul class="mod_score_starsbar editable" data-callback="onStarsUpdate">
			<li>
				<input name="score" id="score" type="hidden" value="{$item_score['isr_score']}" />
				<em data-num="{$item_score['isr_score'] * 5 / $c_item['insi_score']}"><i></i></em><span><strong>{$item_score['isr_score']}</strong>分</span>
				<script>
				var onStarsUpdate = function(li, num) {
					num = num * {$c_item['insi_score']} / 5;
					$one('span', li).innerHTML = '<strong>' + num + '</strong>分';
					$one('.stars input[type=hidden]').value = num;
				}
				</script>
			</li>
		</ul>
	</div>
	{if !empty($c_item['insi_hasselect']) && !empty($options['i2o'][$c_item['insi_id']])}
	<h1>{if !empty($c_item['insi_select_title'])}{$c_item['insi_select_title']}{else}{$inspect_set['select_title']}{/if}</h1>
	<div class="stars mod_common_list_style">
		<ul class="diy_editable">
			{foreach $options['i2o'][$c_item['insi_id']] as $_k => $_v}
			<li><input class="diy_score_radio" name="isr_option" type="radio" value="{$_v}"{if $item_score['isr_option'] == $_v} checked{/if} /><label>{$options[$_v]['inso_optvalue']}</label></li>
			{/foreach}
		</ul>
	</div>
	{/if}
	{/if}

	{if !empty($c_item['insi_hasfeedback'])}
	<h1>{if $c_item['insi_feedback_title']}{$c_item['insi_feedback_title']}{else}{$inspect_set['score_title_describe']}{/if}</h1>
	<textarea name="message" placeholder="在此填写内容">{$item_score['isr_message']}</textarea>
	{/if}
	
	{if !empty($c_item['insi_hasatt'])}
	<h1>{if $c_item['insi_att_title']}{$c_item['insi_att_title']}{else}照片{/if}</h1>
	<fieldset>
		{include file='frontend/mod_upload.tpl' iptname='at_ids' iptvalue=$at_ids attach_total=$attach_total}
	</fieldset>
	{/if}
	
	<div class="foot numbtns single">
		<input id="next" type="submit" value="保存并评估下一项" />
		{if $is_last}<input id="send" type="submit" value="保存并提交" />{/if}
		<input id="toitem" type="reset" value="返回评估指标" />
	</div>
</form>

<script>
var _ins_id = {$inspect['ins_id']};
{literal}
require(['dialog', 'business'], function() {
	//表单校验
	$one('form').addEventListener('submit', function(e) {
		e.preventDefault();
{/literal}
		{if !empty($inspect_set['score_rules']) && 0 < $inspect_set['score_rule_diy']}
		var _all_radio = $all('.diy_score_radio');
		var _is_select = false;
		$each(_all_radio, function(s_r) {
			if (s_r.checked) {
				_is_select = true;
			}
		});
		
		if (false == _is_select) {
			MDialog.notice('尚未评估!');
			return false;
		}
		{else}
		if ($one('#score').value == 0) {
			MDialog.notice('尚未评估!');
			return false;
		};
		{/if}
{literal}
		aj_form_submit('inspect_post');
	});
});

/** 返回打分项列表 */
$one('#toitem').addEventListener('click', function(e) {
	window.location.href = '/frontend/inspect/editem/ins_id/' + _ins_id;
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
