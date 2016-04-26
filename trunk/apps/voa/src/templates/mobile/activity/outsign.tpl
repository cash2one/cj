{include file='mobile/header.tpl' css_file='app_activity.css'}

<form method="post" action="/frontend/activity/outsign">

	<input type="hidden" name="formhash" value="{$formhash}" />
	<input type="hidden" name="acid" value="{$acid}" />

	<div class="ui-top-box hd-ui-top-box">
		<p style="width:250px; margin:0 auto; font-size:18px;">活动：{$data['title']}</p>
		<p style="margin-top:-20px;">姓名和手机号可用于重新获取二维码</p>
	</div>

	<div class="ui-form">
		<div class="ui-form-item ui-border-t">
			<label for="outname">名字</label>
			<input name="outname" id="outname" value="" type="text" placeholder="请输入（此处为必填）" required="1">
		</div>
		<div class="ui-form-item ui-border-t">
			<label for="outphone">手机</label>
			<input name="outphone" id="outphone" value="" type="phone" maxlength="11" placeholder="请输入（此处为必填）" required="1">
		</div>
		<div class="ui-form-item ui-border-t">
			<label for="remark">备注</label>
			<input name="remark" id="remark" value="" type="text" placeholder="请输入">
		</div>
	{foreach $outfield as $k => $v}
		{if $v['require'] == '0'}
			{$_required = null}
		{else}
			{$_required = 1}
		{/if}
		{if $v['require'] == '1'}
		{cyoa_input_text
		attr_type=$v['type']
		attr_required=$_required
		attr_placeholder='请输入（此处为必填）'
		title=$v['name']
		attr_id=$k
		attr_name=$v['name']
		}
		{else}
		{cyoa_input_text
		attr_type=$v['type']
		attr_placeholder='请输入'
		title=$v['name']
		attr_id=$k
		attr_name=$v['name']
		}
		{/if}
	{/foreach}
	</div>

	<div class="ui-btn-group-tiled ui-btn-wrap">
		<input id="submit" type="submit" class="ui-btn-lg ui-btn-primary" value="报名参加" />
	</div>

</form>
{literal}
<script type="text/javascript">
require(["zepto", "underscore", "submit", "frozen"], function($, _,submit) {
	$(function () {
		var outname = $('#outname');
		var outphone = $('#outphone');
		$('#submit').on('click', function () {
			if (outname.val() == '') {
				$.tips({content:'姓名不能为空'});
				outname.focus();
				return false;
			}
			if (outphone.val() == '' || outphone.val().length != '11') {
				$.tips({content:'手机格式不对，或不能为空'});
				outphone.focus();
				return false;
			}
		});
	});
});
</script>
{/literal}
{include file='mobile/footer.tpl'}