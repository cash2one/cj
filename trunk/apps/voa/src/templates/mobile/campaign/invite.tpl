{include file='mobile/header.tpl' navtitle="活动详情"}

<link rel="stylesheet" type="text/css" href="/misc/styles/oa_ght.css">

	<div class="ui-tips ui-tips-success ui-top-ght"> <i></i>
		&nbsp;&nbsp;报名成功!
	</div>
	<div class="ui-list ui-conten-ght">
		<h2>邀请函</h2>
		<p class="head">尊敬的客户，您好！</p>
		<p>
			诚邀您参加本次活动！邀请函附带二维码，建议您截图保存邀请函，届时直接出示二维码即可在现场签到入场！
			<br>如有疑问，可以联系邀请人获得帮助！
		</p>
		<div class="ui-barcode">
			<img id="qrcode" src="">
			<p class="ui-barcode-text">(请长按保存图片或截图)</p>
		</div>
		
		<li class="ui-info">
			<div class="ui-avatar-s">
				<img id="avatar" src=""/>
			</div>
			<div class="ui-list-info">
				<p>邀请人：<span id="salename"></span></p>
				<p id="salemobile">13823098765</p>
			</div>
		</li>
	</div>
			
<script type="text/javascript">
var regid = "{$regid}";
{literal}
require(["zepto"], function($) {
	//活动详情
	$.getJSON('/api/campaign/get/invite?regid='+regid, function (json){
		if(json.errcode == 0) {
			$('#qrcode').attr('src', json.result.qrcode);
			$('#avatar').attr('src', json.result.avatar);
			$('#salename').text(json.result.salename);
			$('#salemobile').text(json.result.salemobile);
		}else{
			$.tips({content: '很抱歉,返回邀请码信息错误', stayTime: 10000});
		}
	});
});
{/literal}
</script>
{include file='mobile/footer.tpl'}