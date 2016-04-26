{include file='mobile/header.tpl' navtitle="会议签到"}
<style>
.ui-tips i:before {
	background-image: url(/static/images/icon.png?_bid=306);
}
</style>
	<div id="oa-hy-signok">
		<div class="ui-tips ui-tips-success"> <i></i>&nbsp;&nbsp;&nbsp;
			已签到
		</div>
	</div>
	<div class="ui-form">
		<div class="ui-form-item ui-form-item-show ui-conten-more">
			<label>姓&nbsp;&nbsp;&nbsp;名</label>
			<p>{$user.m_username}</p>
		</div>
	
		<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
			<label>手机号</label>
			<p>{$user.m_mobilephone}</p>
		</div>
		<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
			<label>部&nbsp;&nbsp;&nbsp;门</label>
			<p>{$departments[$user.cd_id].cd_name}</p>
		</div>
		<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
			<label>职&nbsp;&nbsp;&nbsp;位</label>
			<p>{$jobs[$user.cj_id].cj_name}</p>
		</div>
	</div>
	<div class="ui-btn-wrap">
		<button id="return" class="ui-btn-lg ui-btn-primary">确定(<span id="time">4</span>)</button>
	</div>
    
{include file='mobile/footer.tpl'}
<script>
require(["zepto"], function($) {
	//返回详情页
	$('#return').click(function (){
		location.href = '/meeting/view/{$mt_id}';
	});
	setInterval(function (){
		var v = $('#time').text() * 1;
		if(v == 0) {
			return location.href = '/meeting/view/{$mt_id}';
		}else{
			$('#time').text((v - 1));
		}
	}, 1000);
});
</script>