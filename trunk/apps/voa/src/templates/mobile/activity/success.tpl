{include file='mobile/header.tpl' css_file='app_activity.css'}


<div class="ui-tips ui-tips-success"> <i></i>&nbsp;&nbsp;
	报名成功
</div>
<div class="ui-form">
	<div class="ui-form-item ui-border-t" style="height:300px;text-align:center">
		<img style="margin:10px auto" src="/frontend/activity/outqcode?ac=takecode&acid={$getqcode['acid']}&outname={$getqcode['outname']}&outphone={$getqcode['outphone']}" width="230px;" height="230px;">
        <span style="margin:-20px auto;display:block;">此二维码为签到凭证，请保存</span>

    </div>
	<div class="ui-form-item ui-form-item-show ui-conten-more ui-border-t">
		<label>姓名</label>
		<p>{$data['outname']}</p>
	</div>
	<div class="ui-form-item ui-form-item-show ui-conten-more ui-border-t">
		<label>手机号</label>
		<p>{$data['outphone']}</p>
	</div>
	<div class="ui-form-item ui-form-item-show ui-conten-more ui-border-t">
		<label>备注</label>
		<p>{$data['remark']}</p>
	</div>
	{if $other_feild != ''}
	{foreach $other_feild as $k => $v}
		<div class="ui-form-item ui-form-item-show ui-conten-more ui-border-t">
			<label>{$k}</label>
			<p>{$v}</p>
		</div>
	{/foreach}
	{/if}

</div>
<div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
	<button class="ui-btn-lg ui-btn-primary" id="go_back">确定</button>
</div>
<br/>
<br/>
<br/>
<script type="text/javascript">
	require(["zepto"], function ($) {
		$('#go_back').on('click', function() {
			window.location.href = '{$viewurl}';
		});
	});
</script>


{include file='mobile/footer.tpl'}