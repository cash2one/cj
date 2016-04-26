<div class="panel panel-default">
	<div class="panel-heading">
		<strong>开启“{$plugin['cp_name']|escape}”应用：</strong>
		<span class="font12 text-info">我是畅移小助手，会协助你开启畅移应用，请根据我的流程指引来操作，有问题可以咨询我们的客服QQ：4008606961</span>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="step-line">
				<div id="step-part-1" class="step-part step-part-small">
					<strong>1</strong>
					<span>添加应用</span>
				</div>
				
				<div id="step-part-2" class="step-part step-part-small">
					<strong>2</strong>
					<span>设置可信域名</span>
				</div>
				
				<div id="step-part-3" class="step-part step-part-small">
					<strong>3</strong>
					<span>开启回调模式</span>
				</div>
				
				<div id="step-part-4" class="step-part">
					<strong>4</strong>
					<span>开启自定义菜单</span>
				</div>
				
				<div id="step-part-5" class="step-part">
					<strong>5</strong>
					<span>开启上报地理位置</span>
				</div>
						

				<div id="step-part-6" class="step-part step-part-small">
					<strong>6</strong>
					<span>填写应用 ID</span>
				</div>
				
				<div id="step-part-7" class="step-part">
					<strong>7</strong>
					<span>设置应用权限</span>
				</div>
			</div>
		</div>
		<p>&nbsp;</p>
		<form id="form-agent" method="post" action="{$application_update_submit_url}">
			<input type="hidden" id="form-formhash" value="{$formhash}" />
			<input type="hidden" id="form-agentid" value="{$agent_id}" />
		</form>
		<div id="step-show" class="step-content"></div>
	</div>
	<div class="panel-footer">
		<div class="row">
			<div class="col-sm-2 text-left"><a href="javascript:;" role="button" class="btn btn-lg btn-default _step_prev">上一步</a></div>
			<div class="col-sm-8 text-center"><div id="tip-msg"></div></div>
			<div class="col-sm-2 text-right"><a href="javascript:;" role="button" class="btn btn-lg btn-primary _step_next">下一步</a></div>
		</div>
	</div>
</div>

<script type="text/javascript" src="{$staticUrl}js/ZeroClipboard/ZeroClipboard.js"></script>
<script type="text/javascript">
ZeroClipboard.config({
	"swfPath":"{$staticUrl}js/ZeroClipboard/ZeroClipboard.swf"
});
</script>

<script type="text/javascript">
//步骤呈现区域 jQuery 对象
var jq_step_show = jQuery('#step-show');
// 上一步按钮 jquery 对象
var jq_step_prev = jQuery('._step_prev');
// 下一步按钮 jquery 对象
var jq_step_next = jQuery('._step_next');
// 最大步骤号
var max_step = 7;

function my_clip() {
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
}

/**
 * 载入步骤单元
 * @param number id 步骤号
 * @param object data 步骤模板内的变量值
 */
function load_step(id, data) {

	// 模板变量值定义
	if (typeof(data) === 'undefined') {
		data = {};
	}

	// 写入当前的操作步骤
	jQuery('body').data('cur_step', Number(id));

	// 载入模板内容
	jq_step_show.empty().append(txTpl('step-'+id+'', data));
	
	// 渲染进度图标文字颜色
	jQuery('.step-part').removeClass('step-part-current');
	jQuery('#step-part-'+id).addClass('step-part-current');

	if (id <= 1) {
		// 如果是第一步，则禁用“上一步”按钮
		jq_step_prev.addClass('disabled').prop('disabled', true);
	} else {
		// 否则，则取消禁用
		jq_step_prev.removeClass('disabled').prop('disabled', false);
	}
	
	if (id >= max_step) {
		// 达到最后一步，则“下一步”变为“提交保存”
		jq_step_next.addClass('btn-danger').text('确认开启');
	} else {
		// 不是最后一步
		jq_step_next.removeClass('btn-danger').text('下一步');
	}

	my_clip();
	click_scroll();
}

/**
 * 点击下一步按钮
 */
function btn_next() {
	var cur_step = jQuery('body').data('cur_step');
	if (cur_step >= max_step) {
		return false;
	}
	load_step(cur_step + 1);
}

/**
 * 点击上一步按钮
 */
function btn_prev() {
	var cur_step = jQuery('body').data('cur_step');
	if (cur_step <= 1) {
		return false;
	}
	load_step(cur_step - 1);
}

/**
 * 提示窗
 */
function my_alert(msg) {
	alert(msg);
}

/**
 * 滚动到指定位置
 */
function click_scroll() {
	return;
}

/**
 * 应用ID检查
 */
function check_agentid() {
	var agentid = jQuery.trim(jQuery('#id_agent_id').val());
	if (!agentid || agentid == '0') {
		my_alert('请正确填写 应用ID');
		jQuery('#id_agent_id').focus();
	} else {
		jQuery('#form-agentid').val(agentid);
		btn_next();
	}
}

/**
 * 提交应用ID
 */
function submit_agentid() {
	jQuery.post(jQuery('#form-agent').attr('action'), {
		"formhash":jQuery('#form-formhash').val(),
		"agent_id":jQuery.trim(jQuery('#form-agentid').val()),
		"ajax":1
	}, function(data){
		if (typeof(data.errcode) == 'undefined') {
			my_alert('网络连接错误,请刷新后重试');
			return false;
		}
		if (data.errcode != 0) {
			my_alert(data.errmsg);
			return false;
		} else {
			// 进入下一步操作
			my_alert(data.errmsg);
			window.location.href=data.result.url;
		}
	}, 'json');
	return false;
}

jQuery(function(){

	// 初始化当前操作步骤
	jQuery('body').data('cur_step', 1);
	
	// 初始化载入第一步
	load_step(1);

	// 步骤呈现区域 jQuery 对象
	jq_step_show = jQuery('#step-show');
	// 上一步按钮 jquery 对象
	jq_step_prev = jQuery('._step_prev');
	// 下一步按钮 jquery 对象
	jq_step_next = jQuery('._step_next');

	// 绑定触发上一步按钮
	jq_step_prev.click(function(){
		btn_prev();
	});
	
	// 绑定触发下一步按钮
	jq_step_next.click(function(){
		var cur_step = jQuery('body').data('cur_step');
		cur_step = Number(cur_step);
		if (cur_step == 6) {
			// 检查应用ID
			check_agentid();
		} else if (cur_step >= max_step) {
			// 最后一步确认提交
			submit_agentid();
		} else {
			// 否则，下一步
			btn_next();
		}
	});
});
</script>

<script type="text/template" id="step-1">
	<h5 class="alert alert-warning"><strong>进入微信企业号（qy.weixin.qq.com）应用中心，进入 “{$plugin['cp_name']|escape}”应用</strong></h5>
	<p class="thumbnail"><img src="{$staticUrl}images/help/app_001.png" alt="" /></p>
	<h5 class="alert alert-warning"><strong>根据下面内容填写应用基本信息</strong></h5>
	<table class="table table-bordered">
		<colgroup>
			<col class="t-col-15" />
			<col />
			<col class="t-col-20" />
		</colgroup>
		<tbody>
			<tr class="text-center">
				<th class="active text-center">{$lang_app_name}</th>
				<td id="txt-appname">{$plugin['cp_name']|escape}</td>
				<td id="txt-appname-button" class="_clip">复　制</td>
			</tr>
			<tr class="text-center">
				<th class="active text-center">{$lang_app_description}</th>
				<td id="txt-appdescription">{$plugin['cp_description']}</td>
				<td id="txt-appdescription-button" class="_clip">复　制</td>
			</tr>
			<tr class="text-center">
				<th class="active text-center">{$lang_app_logo}</th>
				<td><img src="{$plugin['_icon_url']}" alt="" style="width:48px;height:48px;" /></td>
				<td><a href="{$plugin['_icon_url']}">右键点击<br />另存下载</a></td>
			</tr>
		</tbody>
	</table>
	<p class="thumbnail"><img src="{$staticUrl}images/help/app_003.png" alt="" /></p>
	<h5 class="alert alert-warning"><strong>选择应用可见范围</strong></h5>
	<p class="thumbnail"><img src="{$staticUrl}images/help/app_004.png" alt="" /></p>
</script>

<script type="text/template" id="step-2">
	<h5 class="alert alert-warning"><strong>进入可信域名设置</strong></h5>
	<p class="thumbnail"><img src="{$staticUrl}images/help/app_005.png" alt="" /></p>
	<h5 class="alert alert-warning"><strong>复制下面内容，填写可信域名</strong></h5>
	<table class="table table-bordered">
		<colgroup>
			<col class="t-col-15" />
			<col />
			<col class="t-col-20" />
		</colgroup>
		<tbody>
			<tr class="text-center">
				<th class="active text-center">{$lang_app_domain}</th>
				<td id="txt-domain">{$setting['domain']|escape}</td>
				<td id="txt-domain-button" class="_clip">复　制</td>
			</tr>
		</tbody>
	</table>
	<p class="thumbnail"><img src="{$staticUrl}images/help/app_007.png" alt="" /></p>
</script>

<script type="text/template" id="step-3">
	<h5 class="alert alert-warning"><strong>进入回调模式</strong></h5>
	<p class="thumbnail"><img src="{$staticUrl}images/help/app_008.png" alt="" /></p>
	<h5 class="alert alert-warning"><strong>右上角开启</strong></h5>
	<p class="thumbnail"><img src="{$staticUrl}images/help/app_009.png" alt="" /></p>
	<h5 class="alert alert-warning"><strong>根据复制下面内容填写接口配置信息</strong></h5>
	<table class="table table-bordered">
		<colgroup>
			<col class="t-col-15" />
			<col />
			<col class="t-col-20" />
		</colgroup>
		<tbody>
			<tr class="text-center">
				<th class="active text-center">{$lang_app_url}</th>
				<td id="txt-url">{$plugin_url}</td>
				<td id="txt-url-button" class="_clip">复　制</td>
			</tr>
			<tr class="text-center">
				<th class="active text-center">{$lang_token}</th>
				<td id="txt-token">{$setting['token']}</td>
				<td id="txt-token-button" class="_clip">复　制</td>
			</tr>
			<tr class="text-center">
				<th class="active text-center">{$lang_aeskey}</th>
				<td id="txt-aeskey">{$setting['aes_key']}</td>
				<td id="txt-aeskey-button" class="_clip">复　制</td>
			</tr>
		</tbody>
	</table>
	<p class="thumbnail"><img src="{$staticUrl}images/help/app_011.png" alt="" /></p>
</script>

<script type="text/template" id="step-4">
	<h5 class="alert alert-warning"><strong>开启自定义菜单</strong></h5>
	<p class="thumbnail"><img src="{$staticUrl}images/help/app_012.png" alt="" /></p>
	<p class="thumbnail"><img src="{$staticUrl}images/help/app_013.png" alt="" /></p>
	<p class="thumbnail"><img src="{$staticUrl}images/help/app_014.png" alt="" /></p>
</script>


<script type="text/template" id="step-5">
	<p class="alert alert-warning">开启地理位置上报</p>
	<p><img src="{$staticUrl}images/help/app_sign_1.png" alt="" /></p>
	<p class="alert alert-warning">修改上报用户地理位置</p>
	<p><img src="{$staticUrl}images/help/app_sign_2.png" alt="" /></p>
	<p class="alert alert-warning">在用户处于应用中时，每5秒上报一次</p>
	<p><img src="{$staticUrl}images/help/app_sign_3.png" alt="" /></p>
</script>


<script type="text/template" id="step-6">
	<div class="form-horizontal">
	<table class="table table-bordered font12">
		<colgroup>
			<col class="t-col-15" />
			<col />
			<col class="t-col-20" />
		</colgroup>
		<tbody>
			<tr>
				<td colspan="3" class="info">在微信企业号后台“应用中心”打开“<strong>{$plugin['cp_name']|escape}</strong>”应用，复制“{$lang_agentid}”到下方</td>
			</tr>
			<tr>
				<th class="active text-right">
					<label for="id_agent_id" class="control-label">{$lang_agentid}</label>
				</th>
				<td>
					<div class="col-sm-6">
						<input type="text" class="form-control col-sm-6" id="id_agent_id" placeholder="仅需填写数字ID" value="{$agent_id}" />
					</div>
					<div class="col-sm-3"><label class="control-label text-danger">必须填写</label></div>
				</td>
				<td class="text-right"><button type="button" class="btn btn-primary btn-lg" onclick="javascript:check_agentid();">下一步</button></td>
			</tr>
		</tbody>
	</table>
	</div>
	<p class="thumbnail"><img src="{$staticUrl}images/help/app_020.png" alt="" /></p>
</script>

<script type="text/template" id="step-7">
	<h5 class="alert alert-warning"><strong>进入权限管理</strong></h5>
	<p class="thumbnail"><img src="{$staticUrl}images/help/app_021.png" alt="" /></p>
	<h5 class="alert alert-warning"><strong>修改应用权限，点击“修改应用选择”，勾选“通讯录”应用，给对应的应用点选“配置应用”权限</strong></h5>
	<p class="thumbnail"><img src="{$staticUrl}images/help/app_022.png" alt="" /></p>
</script>