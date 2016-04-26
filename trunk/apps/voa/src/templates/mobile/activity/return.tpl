{include file='mobile/header.tpl' css_file='app_activity.css'}

<div class="ui-form ui-border-t">
		<div id="view" class="ui-form-item ui-form-item-order ui-form-item-link">
			<label >活动主题</label>
			<p>{$data['title']}</p>
		</div>
		<div class="ui-form-item ui-border-t">
			<label>申请人</label>
			<p>{$data['name']}</p>
		</div>


		<div class="ui-form-item ui-border-t">
			<label>取消原因</label>
			<p>{$data['content']}</p>
		</div>
	</div>
<div class="ui-btn-group-tiled ui-btn-wrap">
	<button class="ui-btn-lg" id="cancel">驳回</button>
	<button class="ui-btn-lg ui-btn-primary" id="agree">同意</button>
</div>
<!--弹出框-->
<div class="ui-dialog">
	<div class="ui-dialog-cnt">
		 <form name="frmpost" id="frmpost" method="post" action="">
			<div class="ui-dialog-bd">
					<input type="hidden" name="m_uid" value="{$data['m_uid']}" />
					<input type="hidden" id="acid" name="acid" value="{$data['acid']}"/>
					<input type="hidden" id="apid" name="apid" value="{$data['apid']}"/>
					<input type="hidden" id="ac" name="ac" value=""/>
					<textarea name="message" placeholder="填写理由" id="message"></textarea>
			</div>
			<div class="ui-dialog-ft ui-btn-group">
				<button type="button"   class="select" id="message_cancel">取消</button> 
				<button type="submit"  class="select" id="message_sure">提交</button>
			</div>
		</form>
	</div>
</div>
<input type="hidden" id="url" value="{$url}" />
{literal}
<script type="text/javascript">
require(["zepto", "underscore", "submit", "frozen"], function($, _,submit) {

	var sbt = new submit();
	sbt.init({"form": $("#frmpost")});

	$('#cancel').on('click', function() {
		$(".ui-dialog").dialog("show");
	});
	$('#message_cancel').bind('click',function(){
		$(".ui-dialog").dialog("hide");
	});
	$('#message_sure').bind('click',function (e) {
		$('#ac').attr('value','reject');
		$('#frmpost').attr('action', '/api/activity/post/sign');
	});
	$('#agree').bind('click',function (e) {
		$('#ac').attr('value','agree');
		$('#frmpost').attr('action', '/api/activity/post/sign');
		$('#frmpost').submit();
	});
	$('#view').bind('click',function (e) {
		var url = $('#url').val();
		window.location.href=url;
	});
});
</script>
{/literal}
{include file='mobile/footer.tpl'}