{include file='admincp/header.tpl'}

<form id="form-servicetype" class="form-horizontal font12" role="form" method="post" action="{$application_update_submit_url}">
<input type="hidden" name="formhash" value="{$formhash}" />

<h4><strong>重新开启 “<span class="text-primary">{$plugin['cp_name']|escape}</span>” 应用</strong></h4>

<h5><strong class="font12">1. 请先在微信企业号后台登录（qy.weixin.qq.com），进入“应用中心”，在页面的“已禁用”列表内找到“{$plugin['cp_name']|escape}”进入，点击“启用”，操作位置 <a href="{$staticUrl}images/help/reopen_01.png" target="_blank" title="点击查看"><strong><i class="fa fa-external-link"></i> 如图所示 <i class="fa fa-image"></i></strong></a></strong></h5>

<h5><strong class="font12">2. 查看该应用的“应用ID”并复制，是否与下面记录一致。如不一致，请复制微信企业平台的“应用ID”填写在下面 Agent ID 输入框内。操作位置 <a href="{$staticUrl}images/help/reopen_02.png" target="_blank" title="点击查看"><strong><i class="fa fa-external-link"></i> 如图所示 <i class="fa fa-image"></i></strong></a></strong></h5>
<table class="table table-bordered font12">
	<colgroup>
		<col class="t-col-22" />
		<col class="t-col-81" />
	</colgroup>
	<tbody>
		<tr>
			<th class="active text-right">
				<label for="agentid" class="control-label">Agent ID</label>
			</th>
			<td>
				<div class="col-sm-6">
					<input type="text" class="form-control col-sm-6" id="agentid" name="agent_id" placeholder="填写应用 Agent ID" value="{$agent_id}" />
				</div>
				<div class="col-sm-3"><label class="control-label text-danger">必须填写</label></div>
			</td>
		</tr>
	</tbody>
</table>

<h5><strong class="font12">4. 检查可信域名、回调URL、密钥Token以及EncodingAESKey是否与下面记录相同，如不相同，请复制下面对应内容填写到企业微信平台内。</strong></h5>
<table class="table table-bordered font12">
	<colgroup>
		<col class="t-col-10" />
		<col class="t-col-36" />
		<col class="t-col-9" />
		<col class="t-col-45" />
	</colgroup>
	<tbody>
		<tr>
			<th class="active text-center">可信域名</th>
			<td class="text-center" id="txt-domain">{$setting['domain']|escape}</td>
			<td class="text-center"><span id="txt-domain-button" class="_clip">复　制</span></td>
			<td class="info">
				在应用中查看“可信域名”是否与左侧记录相同，如不相同，将左侧域名复制粘贴到微信企业平台内，操作位置 <a href="{$staticUrl}images/help/reopen_03.png" target="_blank" title="点击查看"><strong><i class="fa fa-external-link"></i> 如图所示 <i class="fa fa-image"></i></strong></a>
			</td>
		</tr>
		<tr>
			<th class="active text-center">Url</th>
			<td class="text-center" id="txt-url">{$plugin_url}</td>
			<td class="text-center"><span id="txt-url-button" class="_clip">复　制</span></td>
			<td rowspan="3" class="info">
				<h5><strong class="font12">检查Url、Token、AE Key</strong></h5>
				<ol>
					<li>在应用中，进入“回调模式”，操作位置：<a href="{$staticUrl}images/help/reopen_04.png" target="_blank" title="点击查看"><strong><i class="fa fa-external-link"></i> 如图所示 <i class="fa fa-image"></i></strong></a></li>
					<li>进入应用回调模式，点击“修改配置”，检查各项配置是否与左侧相同，如不相同请复制并粘贴到微信企业平台对应输入框内，操作位置：<a href="{$staticUrl}images/help/reopen_05.png" target="_blank" title="点击查看"><strong><i class="fa fa-external-link"></i> 如图所示 <i class="fa fa-image"></i></strong></a></li>
				</ol>
			</td>
		</tr>
		<tr>
			<th class="active text-center">Token</th>
			<td class="text-center" id="txt-token">{$setting['token']}</td>
			<td class="text-center"><span id="txt-token-button" class="_clip">复　制</span></td>
		</tr>
		<tr>
			<th class="active text-center">AESkey</th>
			<td class="text-center" id="txt-aeskey">{$setting['aes_key']}</td>
			<td class="text-center"><span id="txt-aeskey-button" class="_clip">复　制</span></td>
		</tr>
	</tbody>
</table>

<div class="form-group">
	<div class="col-sm-offset-3 col-sm-8">
		<button type="submit" class="btn btn-primary">完<span class="space"></span>成</button>
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