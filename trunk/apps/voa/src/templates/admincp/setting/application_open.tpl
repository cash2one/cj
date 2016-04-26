{include file="$tpl_dir_base/header.tpl"}
<script type="text/javascript">
if (ie < 9) {
} else {
	document.write('<script src="{$JSDIR}ZeroClipboard/ZeroClipboard.js">'+"<"+"/script>");
	ZeroClipboard.config({
		"swfPath":"{$JSDIR}ZeroClipboard/ZeroClipboard.swf"
	});
}
</script>

<div class="panel panel-default">
	<div id="step-header" class="panel-heading">
		<strong>开启“{$plugin['cp_name']|escape}”应用：</strong>
		<span class="font12 text-info">我是畅移小助手，会协助你开启畅移应用，请根据我的流程指引来操作，有问题可以咨询我们的客服QQ：4008606961</span>
	</div>
	<div class="panel-body">
		<div id="step-nav" class="row">
			<div class="step-line">
{if $plugin['cp_identifier']=='sign'}
	{include file="$tpl_dir_base/setting/application_open_sign.tpl"}
{else}
	{include file="$tpl_dir_base/setting/application_open_default.tpl"}
{/if}			
			</div>
		</div>
		<p>&nbsp;</p>
		<form id="form-agent" method="post" action="{$application_update_submit_url}">
			<input type="hidden" id="form-formhash" value="{$formhash}" />
			<input type="hidden" id="form-agentid" value="{$agent_id}" />
		</form>
		<div id="step-show" class="step-content"></div>
	</div>
	<div id="step-footer" class="panel-footer">
		<div class="row">
			<div class="col-sm-2 text-left"><a href="javascript:;" role="button" class="btn btn-lg btn-default _step_prev">上一步</a></div>
			<div class="col-sm-8 text-center"><div id="tip-msg"></div></div>
			<div class="col-sm-2 text-right"><a href="javascript:;" role="button" class="btn btn-lg btn-primary _step_next">下一步</a></div>
		</div>
	</div>
</div>

<script type="text/javascript">
//步骤呈现区域 jQuery 对象
var jq_step_show = jQuery('#step-show');
// 上一步按钮 jquery 对象
var jq_step_prev = jQuery('._step_prev');
// 下一步按钮 jquery 对象
var jq_step_next = jQuery('._step_next');

function my_clip() {
	if (ie < 9) {
		jQuery('._clip').click(function(){
			alert('对不起，您的浏览器版本太低不支持复制\n请更换更高版本的 IE 或者试试\nChrome、Firefox等其他新代浏览器');
		});
		return;
	}

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
	// 始终滚动内容到顶部
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
	if (!agentid) {
		my_alert('请正确填写 应用ID');
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
		"border":"1px solid #ccc","overflow-y":"auto","background":"#fcfcfc","padding":"5px 0"
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

	// 绑定触发上一步按钮
	jq_step_prev.click(function(){
		btn_prev();
	});
	
	// 绑定触发下一步按钮
	jq_step_next.click(function(){
		var cur_step = jQuery('body').data('cur_step');
		cur_step = Number(cur_step);
		if (cur_step == step_appid) {
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

{include file="$tpl_dir_base/footer.tpl"}