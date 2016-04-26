{include file='admincp/header.tpl'}

<form id="form-servicetype" class="form-horizontal font12" role="form" method="post" action="{$application_update_submit_url}">
<input type="hidden" name="formhash" value="{$formhash}" />

<h4><strong>关闭 “<span class="text-primary">{$plugin['cp_name']|escape}</span>” 应用</strong></h4>
<div class="alert alert-warning" role="alert">
关闭该应用后将无法再发送消息，同时会在关注者的微信中消失。但应用的数据仍旧可以在本后台内浏览到，除非您手动删除处理。
<h5><strong class="font12">请登录微信企业平台（qy.weixin.qq.com）的“应用中心”，选择对应的应用“{$plugin['cp_name']|escape}”，“禁用”该应用，<a href="{$staticUrl}images/help/close_01.png" target="_blank" title="点击查看"><strong><i class="fa fa-external-link"></i> 如图所示 <i class="fa fa-image"></i></strong></a>。</strong></h5>
</div>

<div class="form-group">
	<div class="col-sm-offset-3 col-sm-8">
		<button type="submit" class="btn btn-warning">确定关闭</button>
		<span class="space"></span><span class="space"></span>
		<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
	</div>
</div>
</form>

<script type="text/javascript" src="{$staticUrl}js/ZeroClipboard/ZeroClipboard.js"></script>
<script type="text/javascript">
ZeroClipboard.config({
	"swfPath":"{$staticUrl}js/ZeroClipboard/ZeroClipboard.swf"
});

jQuery(function(){
	jQuery('._clip').css({
		"cursor":"pointer",
		"text-decoration":"none",
		"color":"#428bca"
	}).mouseover(function(){
		jQuery(this).css({
			"text-decoration":"underline",
			"color":"#2a6496"
		});
	}).mouseout(function(){
		jQuery(this).css({
			"text-decoration":"none",
			"color":"#428bca"
		});
	});
});

var client = new ZeroClipboard(jQuery('._clip'));
client.on('ready', function(event) {
	client.on('copy', function(event) {
		var id = (event.target.id).replace('-button', '');
		event.clipboardData.setData('text/plain', jQuery('#'+id).html());
	});
	client.on('aftercopy', function(event) {
		jQuery('#'+event.target.id).fadeOut(1000).text('已复制').fadeOut(500, function(){
			jQuery('#'+event.target.id).text('复　制').fadeIn('fast');
		});
	});
});
client.on('error', function(event) {
	alert('复制内容失败：'+event.message);
	ZeroClipboard.destroy();
});
</script>

{include file='admincp/footer.tpl'}