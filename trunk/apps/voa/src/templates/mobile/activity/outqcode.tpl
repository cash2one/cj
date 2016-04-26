{include file='mobile/header.tpl' css_file='app_activity.css'}

{if $allow == 1}
	<div class="ui-top-box hd-ui-top-box">
		<h2>请在活动签到时出示</h2>
	</div>

	<div class="ui-form-item ui-border-t" style="text-align:center; margin-top: 50px;">
		<img style="margin:0 auto" src="/frontend/activity/outqcode?ac=takecode&acid={$data['acid']}&outname={$data['outname']}&outphone={$data['outphone']}" width="230px;" height="230px;">
	</div>
	<div class="ui-btn-wrap ui-padding-bottom-0" style="margin-top: 200px;">
		<button class="ui-btn-lg ui-btn-primary" onclick="javascript:history.go(-2);">返回</button>
	</div>
{else}
<form method="post" action="/frontend/activity/outqcode">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<input type="hidden" name="acid" value="{$acid}" />

	<div class="ui-top-box hd-ui-top-box">
		<h2>请输入报名时的姓名和手机号</h2>
	</div>

	<div class="ui-form">
		<div class="ui-form-item ui-border-t">
			{cyoa_input_text
			attr_type='text'
			attr_required=1
			attr_placeholder='姓名'
			title='姓名'
			attr_id='outname'
			attr_name='outname'
			}
		</div>
		<div class="ui-form-item ui-border-t">
			{cyoa_input_text
			attr_type='number'
			attr_required=1
			attr_placeholder='手机号'
			title='手机号'
			attr_id='outphone'
			attr_name='outphone'
				attr_maxlength='11'
			}
		</div>
	</div>

	<div class="ui-btn-group-tiled ui-btn-wrap">
		<input type="submit" class="ui-btn-lg ui-btn-primary" value="获取二维码" />
	</div>
</form>
{/if}
{include file='mobile/footer.tpl'}