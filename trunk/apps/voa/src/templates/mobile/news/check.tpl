{include file='mobile/header.tpl' css_file='app_news.css' navtitle='公告预览'}
<div class="ui-top-border"></div>
<div class="ui-form">
	<div class="ui-form-item ui-border-t ui-form-item-link">
		<label for="#">公告标题</label>
		<p><a href="{$viewurl}">{$result.title}</a></p>
	</div>
	<div class="ui-form-item ui-border-t">
		<label for="#">发起人</label>
		<p>{$result.username}</p>
	</div>
	<div class="ui-form-item ui-border-t ui-conten-more">
		<label for="#">预览说明</label>
		<p>{$result.check_summary}</p>
	</div>
</div>
<div class="ui-btn-group-tiled ui-btn-wrap">
	<button class="ui-btn-lg ui-btn-primary" id="contra">回复</button>
	<!-- <button class="ui-btn-lg ui-btn-primary" id="agree">同意</button> -->
</div>

<div class="ui-dialog">
	<div class="ui-dialog-cnt">
		<div class="ui-dialog-bd">
			<div>
				<div>
					<textarea  id="check_note" placeholder="请输入回复说明，120字以内！"></textarea>
				</div>
			</div>
		</div>
		<div class="ui-dialog-ft ui-btn-group">
			<button type="button" data-role="button"  id="note_sure">确认</button>
			<button type="button" data-role="button"  id="cancel">取消</button>
		</div>
	</div>
</div>

<script type="text/javascript">
	var ne_id = '{$ne_id}';
</script>
{literal}

</script>
<script type="text/javascript">
require(["zepto", "underscore", "frozen"], function($, _, fz) {
	$("#agree").tap(function(){
		var dia=$.dialog({
			title:'操作提示',
			content:'此审批已经通过！',
			button:["确定"]
		});
		dia.on("dialog:action",function(e){
			if(e.index == 0) {
				var data = { ne_id: ne_id, content:'' };
				$.post('/api/news/post/check', data, function (json){
					if(json.errcode == 0 && json.result.update) {
						window.location.href = '/frontend/news/view?ne_id=' + json.result.ne_id;
					}else{
						$.tips({content: '审核失败:' + json.errmsg});
					}
				}, 'json');
			}
		});
	});
	$("#contra").tap(function(){
		var dia = $(".ui-dialog").dialog("show");
		dia.on('dialog:action', function (e) {
			if (e.index == 0) {
				var content = $('#check_note').val();
				var data = { ne_id: ne_id, content: content };
				$.post('/api/news/post/check', data, function (json){
					if(json.errcode == 0 && json.result.update) {
						window.location.href = '/frontend/news/view?ne_id=' + json.result.ne_id;
					}else{
						$.tips({content: '回复失败:' + json.errmsg});
					}
				}, 'json');
			}
		});
	});
})
</script>
{/literal}
{include file='mobile/footer.tpl'}