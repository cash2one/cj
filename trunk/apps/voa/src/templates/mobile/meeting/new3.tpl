{include file='mobile/header.tpl' navtitle="发起会议3/3"}

<div class="ui-top-box">
    <h2>正在发起会议</h2>
    <p>{$room.mr_name}</p>
</div>
<div class="ui-form">
	<div class="ui-form-item ui-form-item-show ui-conten-more">
		<label class="ui-icon add">地点</label>
		<p>{$room['mr_address']|escape}</p>
	</div>
    <div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
        <label class="ui-icon date">日期</label>
        <p>{$smarty.get.date} (星期{$week})</p>
    </div>
    <div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
        <label class="ui-icon duration">时长</label>
        <p>{$smarty.get.length}小时</p>
    </div>
    <div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
        <label class="ui-icon time">时间</label>
        <p>{$smarty.get.time}</p>
    </div>
</div>

<form action="javascript:;" method="POST">
	<input type="hidden" name="date" value="{$smarty.get.date}"/>
	<input type="hidden" name="length" value="{$smarty.get.length}"/>
	<input type="hidden" name="time" value="{$smarty.get.time}"/>
	<input type="hidden" name="id" value="{$smarty.get.id}"/>
	<div class="ui-form">
		<div class="ui-form-item">
			<label for="#">会议主题</label>
			<input type="text" name="subject" value="{$user}发起的会议">
		</div>
			{cyoa_user_selector title='参会人员' users=$cc_users user_input='join_uids' user_max=-1}
	
	</div>
	<div class="ui-checkbox-box">
		<label class="ui-checkbox ui-checkbox-s">
		<input type="checkbox" name="send" checked>微信企业号通知参会人员</label>
	</div>
	<div class="ui-btn-wrap">
		<button id="submit" type="button" class="ui-btn-lg ui-btn-primary">确定预定</button>
	</div>
</form>
<script>
require(["zepto"], function($) {
	$('#submit').click(function(){
		var post = $('form').serialize();
		$.post('?step=submit', post, function (json){
			if(json.state) {
				$.tips({
			        content:'提交成功'
			    });
			    location.href = "/meeting/view/"+json.info;
			}else{
				$.tips({
			        content:'提交失败 : ' + json.info,
			        type: 'warn',
			        stayTime: 5000
			    });
			}
		}, 'json');
	});
});
</script>
{include file='mobile/footer.tpl'}
