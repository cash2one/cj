{include file='mobile/header.tpl' navtitle="新建报告"}

<div class="ui-top-border"></div>
<form name="dailyreport_post" id="dailyreport_post" method="post" action="/dailyreport/new?handlekey=post">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<input type="hidden" name="drd_id" value="{$drd_id}" />
	<div class="frozen-module-dom">
		<div class="ui-form ui-border-b">
			<div class="ui-form-item ui-form-item-order ui-form-item-link ui-border-b">
				<label>报告类型</label>
				<p id="type_p" style="padding-left:40%">日报</p>
				<select id="daily_type" name="daily_type" onchange="change_type()">
{foreach $dailyType as $k => $v}
	{if $v[1] == 1}
					<option value="{$k}">{$v[0]}</option>
	{/if}
{/foreach}
				</select>
			</div>
			<div class="ui-form-item ui-form-item-order ui-form-item-link">
				<label>报告时间</label>
				<p id="time_p" style="padding-left:40%"></p>
				<select id="reporttime1" name="reporttime1" class="time_type" data-type="1" onchange="javascript:change_time(this);">
{foreach $days as $k => $v}
					<option value="{$k}">{$v['Y']}年{$v['m']}月{$v['d']}日</option>
{/foreach}
				</select>
				<select id="reporttime2" name="reporttime2" class="time_type" data-type="2" hidden="true" onchange="javascript:change_time(this);">
{foreach $weeks as $k => $v}
					<option value="{$k}">{$v}</option>
{/foreach}
				</select>
				<select id="reporttime3" name="reporttime3" class="time_type" data-type="3" hidden="true" onchange="javascript:change_time(this);">
{foreach $months as $k => $v}
					<option value="{$k}">{$v}</option>
{/foreach}
				</select>
				<select id="reporttime4" name="reporttime4" class="time_type" data-type="4" hidden="true" onchange="javascript:change_time(this);">
{foreach $seasons as $k => $v}
					<option value="{$k}">{$v}</option>
{/foreach}
				</select>
				<select id="reporttime5" name="reporttime5" class="time_type" data-type="5" hidden="true" onchange="javascript:change_time(this);">
{foreach $years as $k => $v}
					<option value="{$k}">{$v}</option>
{/foreach}
				</select>
			</div>
			<!--报告内容 -->
			{cyoa_textarea
				title='报告内容'
				attr_placeholder='在此填写内容'
				attr_id='message'
				attr_name='message'
				attr_value=$message
				attr_style="height:61px"
				div_style="height:76px"
			}
{if $p_sets['upload_image']}
			<!-- 允许上传图片 -->
			{cyoa_upload_image
				max=5
				allow_upload=1
				name=at_ids
			}
{/if}
		</div>
	</div>
	<div class="ui-form">
		{cyoa_user_selector
			title='接收人'
			user_input=approveuid
			users=$accepter
		}
	</div>
	<div class="ui-btn-wrap">
		<button id="apost" class="ui-btn-lg ui-btn-primary">提交</button>
	</div>
</form>
<script>
{literal}
/**
 * 报告类型，变更时间
 */
function change_type() {
 	var type = $('#daily_type').val(); 
	$(".time_type").each(function(index, item) {
		if (type == $(item).data("type")) {
			$(item).removeAttr('hidden');
			var opts = $(item).find("option");
			$('#time_p').text($(opts[0]).text()); 
		} else {
			$(item).attr('hidden', 'true');
		}
	});
	$('#daily_type option').each(function() {
		if ($(this).prop("selected")) {
 			$('#type_p').text($(this).text()); 
		}
	});
}

/**
 * 变更报告时间
 */
function change_time(obj){
	$(obj).find('option').each(function () {
		if ($(this).prop('selected')) {
			$('#time_p').text($(this).text()); 
		}
	});
}

/**
 * 弹出对话框
 */
function show_dialog(content){
	var dia=$.dialog({
		title:'温馨提示',
		content:content,
		button:["确认","取消"]
	});
	dia.on("dialog:action",function(e){
		console.log(e.index)
	});
	dia.on("dialog:hide",function(e){
		console.log("dialog hide")
	});
}

require(["zepto", "underscore", "submit", "frozen"], function($, _, submit, fz) {
	var sbt = new submit();//报告提交
	sbt.init({"form": $("#dailyreport_post")});
});

require(["zepto", "underscore", "frozen"], function($, _, fz) {
	$(document).ready(function() {
		//默认日报时间
		$('#reporttime option').each(function(index, opt) {
 			if (index == 0) {
	 			$('#time_p').text($(opt).text()); 
			} 
		});
		change_type();
		//发布报告
		$('#apost').on('click', function(e) {
			var contentTa =$.trim($('#message').val());
			if(contentTa.length == 0){
				show_dialog('请填写报告内容！');
				return false;
			}
			var uids = $.trim($('#approveuid').val());
			if(uids.length == 0){
				show_dialog('请选择接收人！');
				return false;
			}
			
		});
	});
});
{/literal}
</script>

{include file='mobile/footer.tpl'}