{include file="$tpl_dir_base/header.tpl"}

{if $first_start}
<!-- base::_before_action() -->
<div class="alert alert-warning" role="alert">
	<strong>这是您第一次使用本系统，在正式使用之前，您需要设置以下信息。</strong>
</div>
{/if}

<form id="step-form" method="post" action="{$modifyFormActionUrl}">
<input type="hidden" id="step-formhash" name="formhash" value="{$formhash}" />
<input type="hidden" id="step-corpid" name="corp_id" value="{$corp_id}" />
<input type="hidden" id="step-secret" name="corp_secret" value="{$corp_secret}" />
<input type="hidden" id="step-qrcode" name="qrcode" value="{$qrcode}" />
</form>

<div class="panel panel-default">
	<div id="step-header" class="panel-heading" id="panel-heading"><strong>欢迎使用畅移云工作平台！</strong><span class="font12 text-info">我是畅移小助手，会协助你开启畅移应用，请根据我的流程指引来操作，有问题可以咨询我们的<a href="http://wpa.b.qq.com/cgi/wpa.php?ln=1&key=XzkzODAyNTA0MV8yNTE0OTVfNDAwODYwNjk2MV8yXw" class="qq" target="_blank" title="在线客服 QQ"><span>在线客服QQ：4008606961</span></a></span></div>
	<div class="panel-body">
		<div id="step-nav" class="row">
			<div class="step-line">
				<div id="step-part-1" class="step-part">
					<strong>1</strong>
					<span>设置二维码地址</span>
				</div>
				
				<div id="step-part-2" class="step-part">
					<strong>2</strong>
					<span>填写开发者凭据</span>
				</div>
				
				<div id="step-part-3" class="step-part">
					<strong>3</strong>
					<span>设置通讯录权限</span>
				</div>
				
				<div id="step-part-4" class="step-part">
					<strong>4</strong>
					<span>同步员工信息</span>
				</div>
				
				<div id="step-part-5" class="step-part">
					<strong>5</strong>
					<span>开启应用</span>
				</div>
			</div>
		</div>
		<p>&nbsp;</p>
		<div id="step-show">
		</div>
	</div>
	<div id="step-footer" class="panel-footer">
		<div class="row">
			<div class="col-sm-2 text-left"><a href="javascript:;" role="button" class="btn btn-lg btn-default _step_prev">上一步</a></div>
			<div class="col-sm-8 text-center"><div id="tip-msg"></div></div>
			<div class="col-sm-2 text-right"><a href="javascript:;" role="button" class="btn btn-lg btn-primary _step_next">下一步</a></div>
		</div>
	</div>
</div>

<script type="text/template" id="step-1">
<p class="alert alert-info __top">设置二维码地址：请参考下图，复制二维码地址粘帖到下方输入框</p>
<div class="row">
	<div class="col-sm-11">
		<div class="form-horizontal">
			<label class="col-sm-3 control-label">{$lang_qrcode_url}</label>
			<div class="col-sm-9">
				<input type="text" class="form-control" id="qrcode" placeholder="填写微信企业号的二维码 URL 地址" value="{$qrcode}" />
			</div>
		</div>
	</div>
</div>
<p>&nbsp;</p>
<p class="well well-sm"><strong>1.1 </strong>登录微信企业号后台（http://qy.weixin.qq.com )，进入设置页面找到二维码图片，如下图所示：</p>
<p><img src="{$IMGDIR}help/corpid_01.png" alt="" /></p>

<p class="well well-sm"><strong>1.2 </strong>将鼠标移到图片位置，点击鼠标右键，选择“复制图片网址”（适用于chrome浏览器），如下图所示：  </p>
<p><img src="{$IMGDIR}help/corpid_02.png" alt="" /></p>

<p class="well well-sm"><strong>1.3 </strong>或点击“属性”复制图片地址（适用于其它浏览器），如下图所示：</p>
<p><img src="{$IMGDIR}help/corpid_03.png" alt="" /></p>
</script>

<script type="text/template" id="step-2">
<p class="alert alert-info __top">
	请参考下图的操作步骤，将开发者凭据的CorpID和Secret填入下方
</p>
<div class="row">
	<div class="col-sm-11">
		<div class="form-horizontal">
			<div class="form-group">
				<label class="col-sm-3 control-label">{$lang_corpid}</label>
				<div class="col-sm-9">
					<input type="text" class="form-control" id="corpid" placeholder="填写微信企业号的 {$lang_corpid}" value="{$corp_id}" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">{$lang_secret}</label>
				<div class="col-sm-9">
					<input type="text" class="form-control" id="secret" placeholder="填写微信企业号的 {$lang_secret}" value="{$corp_secret}" />
				</div>
			</div>
		</div>
	</div>
</div>
<p>&nbsp;</p>
<p class="well well-sm"><strong>2.1 </strong>登录微信企业号后台(http://qy.weixin.qq.com)，进入设置页面“权限管理”一栏的“管理”按钮，如下图所示：</p>
<p><img src="{$IMGDIR}help/corpid_04.png" alt="" />

<p class="well well-sm"><strong>2.2 </strong>进入权限管理页面，新建管理组。</p>
<p><img src="{$IMGDIR}help/corpid_05.png" alt="" /></p>

<p class="well well-sm"><strong>2.3 </strong>进入刚刚建立好的管理组，找到开发者凭据，如下图所示：</p>
<p><img src="{$IMGDIR}help/corpid_06.png" alt="" /></p>
</script>

<script type="text/template" id="step-3">
<p class="alert alert-info __top">请按如下图示步骤开启通讯录权限，并点击页面右下角“<strong>确认提交</strong>”按钮进行提交：</p>
<p>&nbsp;</p>
<p class="well well-sm"><strong>3.1 </strong>在刚才建立的管理组中，点击“修改组织架构选择”，如下图所示：</p>
<p><img src="{$IMGDIR}help/corpid_07.png" alt="" /></p>

<p class="well well-sm"><strong>3.2 </strong>点击“修改组织架构选择”出现以下“选择组织架构”的弹窗，请选择顶级部门，不要选择顶级部门下的任何子部门</p>
<p><img src="{$IMGDIR}help/corpid_08.png" alt="" /></p>

<p class="well well-sm"><strong>3.3 </strong>选择好顶级部门后，勾选管理权限。并确认敏感接口权限已全部打开。</p>
<p><img src="{$IMGDIR}help/corpid_09.png" alt="" /></p>
</script>

<script type="text/javascript">
//步骤呈现区域 jQuery 对象
var jq_step_show = jQuery('#step-show');
// 上一步按钮 jquery 对象
var jq_step_prev = jQuery('._step_prev');
// 下一步按钮 jquery 对象
var jq_step_next = jQuery('._step_next');
// 提交表单 jquery 对象
var jq_step_form = jQuery('#step-form');
// 最大步骤号
var max_step = 3;

/**
 * 载入步骤单元
 * @param number id 步骤号
 * @param object data 步骤模板内的变量值
 */
function load_step(id, data) {
	
	var i = get_input();
	
	// 模板变量值定义
	if (typeof(data) === 'undefined') {
		data = {
			"step_prev":Number(id)-1,
			"step_next":Number(id)+1,
			"step":id,
			"corpid":i.corpid,
			"secret":i.secret,
			"qrcode":i.qrcode
		};
	}

	// 写入当前的操作步骤
	jQuery('body').data('cur_step', Number(id));

	// 载入模板内容
	jq_step_show.empty().append(txTpl('step-'+id+'', data));
	var scrollTo = jQuery('.__top');
	jq_step_show.scrollTop(
		scrollTo.offset().top - jq_step_show.offset().top + jq_step_show.scrollTop()
	);
	
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
		jq_step_next.addClass('btn-danger').text('确认提交');
	} else {
		// 不是最后一步
		jq_step_next.removeClass('btn-danger').text('下一步');
	}
	
	// 用户输入 corpid 输入框 jquery 对象
	jq_input_corpid = jQuery('#corpid');
	// 用户输入 secret 输入框 jquery 对象
	jq_input_secret = jQuery('#secret');
	// 用户输入 qrcode 输入框 jquery 对象
	jq_input_qrcode = jQuery('#qrcode');
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
 * 获取用户的输入
 */
function get_input() {
	
	// 用户输入 corpid 输入框 jquery 对象
	jq_input_corpid = jQuery('#corpid');
	// 用户输入 secret 输入框 jquery 对象
	jq_input_secret = jQuery('#secret');
	// 用户输入 qrcode 输入框 jquery 对象
	jq_input_qrcode = jQuery('#qrcode');

	var jq_corpid = jQuery('#step-corpid');
	var jq_secret = jQuery('#step-secret');
	var jq_qrcode = jQuery('#step-qrcode');
	
	var corpid = '';
	var secret = '';
	var qrcode = '';
	
	if (jq_input_corpid.length > 0) {
		corpid = jQuery.trim(jq_input_corpid.val());
		jq_corpid.val(corpid);
	} else {
		corpid = jq_corpid.val();
	}
	
	if (jq_input_secret.length > 0) {
		secret = jQuery.trim(jq_input_secret.val());
		jq_secret.val(secret);
	} else {
		secret = jq_secret.val();
	}
	
	if (jq_input_qrcode.length > 0) {
		qrcode = jQuery.trim(jq_input_qrcode.val());
		jq_qrcode.val(qrcode);
	} else {
		qrcode = jq_qrcode.val();
	}
	
	var data = {
		"corpid":corpid,
		"secret":secret,
		"qrcode":qrcode,
		"formhash":jQuery('#step-formhash').val(),
	};

	return data;
}

/**
 * 检查二维码图片地址
 */
function check_qrcode() {
	var i = get_input();
	if (!i.qrcode) {
		my_alert('对不起，请正确输入二维码图片地址');
	} else {
		// 进入下一步操作
		btn_next();
	}
}

/**
 * 检查corpid和secret
 */
function check_corpid_secret() {
	var i = get_input();
	if (!i.corpid) {
		my_alert('对不起，请正确填写 CorpID');
	} else if (!i.secret) {
		my_alert('对不起，请正确填写 Secret');
	} else {
		jQuery.post(jq_step_form.attr('action'), {
			"action":"check_corpid_secret",
			"formhash":i.formhash,
			"corp_id":i.corpid,
			"corp_secret":i.secret,
			"qrcode":i.qrcode
		}, function(data){
			if (typeof(data.errcode) == 'undefined') {
				my_alert('网络连接错误,请刷新后重试');
				return false;
			}
			if (data.errcode > 0) {
				my_alert(data.errmsg);
				return false;
			} else {
				// 进入下一步操作
				btn_next();
			}
		}, 'json');
	}
}

/**
 * 检查通讯录权限
 */
function check_addressbook_power() {
	// 进入下一步操作
	btn_next();
}

/**
 * 提交更新请求
 */
function submit_update() {
	var i = get_input();
	jQuery.post(jq_step_form.attr('action'), {
		"action":"submit-json",
		"formhash":i.formhash,
		"corp_id":i.corpid,
		"corp_secret":i.secret,
		"qrcode":i.qrcode
	}, function(data){
		if (typeof(data.errcode) == 'undefined') {
			my_alert('网络连接错误,请刷新后重试');
			return false;
		}
		if (data.errcode > 0) {
			my_alert(data.errmsg);
			return false;
		} else {
			my_alert(data.result.message);
			window.location.href=data.result.url;
		}
	}, 'json');
	return false;
}

function click_scroll() {
	return;
}

/**
 * 确定显示区的高度
 */
function fix_step_show_height() {
	var step_show_height = jQuery(window).height() - jQuery('#main-navbar').outerHeight();
	step_show_height = step_show_height - jQuery('#sub-navbar').outerHeight();
	step_show_height = step_show_height - jQuery('#step-header').outerHeight();
	step_show_height = step_show_height - jQuery('#step-footer').outerHeight();
	step_show_height = step_show_height - jQuery('#step-nav').outerHeight();
	step_show_height = step_show_height - 140;
	//alert(step_show_height);
	jQuery('#step-show').height(step_show_height).css({
		"border":"1px solid #ccc","overflow-y":"auto","background":"#fcfcfc","padding":"5px 0","margin":"0","overflow-x":"hidden"
	});
}

jQuery(function(){

	// 初始化当前操作步骤
	jQuery('body').data('cur_step', 1);
	
	// 初始化载入第一步
	load_step(1);
	
	// 固定内容显示区的高度
	fix_step_show_height();
	jQuery(window).resize(function(){
		fix_step_show_height();
	});
	
	// 步骤呈现区域 jQuery 对象
	jq_step_show = jQuery('#step-show');
	// 上一步按钮 jquery 对象
	jq_step_prev = jQuery('._step_prev');
	// 下一步按钮 jquery 对象
	jq_step_next = jQuery('._step_next');
	// 提交表单 jquery 对象
	jq_step_form = jQuery('#step-form');
	
	// 绑定触发上一步按钮
	jq_step_prev.click(function(){
		btn_prev();
	});
	
	// 绑定触发下一步按钮
	jq_step_next.click(function(){
		var cur_step = jQuery('body').data('cur_step');
		cur_step = Number(cur_step);
		if (cur_step == 1) {
			// 二维码图片地址
			check_qrcode();
		} else if (cur_step == 2) {
			// 检查通讯录权限
			check_corpid_secret();
		} else if (cur_step >= max_step) {
			// 提交更新
			submit_update();
		} else {
			// 直接进入下一步
			btn_next();
		}
	});
});
</script>

{include file="$tpl_dir_base/footer.tpl"}