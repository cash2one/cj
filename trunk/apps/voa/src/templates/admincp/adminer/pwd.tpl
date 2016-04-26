{$expand_js[]='expand_md5.js'}
{include file="$tpl_dir_base/adminer/header.tpl"}

<form id="pwdform" class="form-horizontal" role="form" method="POST" action="{$base->cpUrl('pwd')}" onsubmit="javascript:return false;">
<input type="hidden" id="formhash" name="formhash" value="{$formhash}" />
<div class="pwd-panel">
	<div class="panel panel-default">
		<div class="panel-heading"><h2 class="text-center">密码重置</h2></div>
		<div class="panel-body">
			<div class="form-group">
				<div class="text-danger text-center" id="send_tip">{$err_msg}</div>
			</div>
			<div class="form-group">
				<label for="id-seccode" class="col-sm-3 control-label">验证码</label>
				<div class="col-sm-6">
					<input type="tel" class="form-control" id="id-seccode" name="seccode" placeholder="输入验证码" value="" maxlength="4" />
				</div>
				<div class="col-sm-3">
						<img id="id-seccode-img" style="cursor:pointer;" src="/frontend/index/seccode?" />
				</div>
			</div>
			<div class="form-group">
				<label for="id-mobilephone" class="col-sm-3 control-label">手机号码</label>
				<div class="col-sm-6">
					<input type="tel" class="form-control" id="id-mobilephone" name="mobilephone" placeholder="输入手机号码" value="{$get_mobilephone}" maxlength="11" />
				</div>
				<div class="col-sm-3">

						<button type="button" style="margin-top: 5px;" id="send_button" class="btn btn-info font12">获取短信验证码</button>

				</div>
			</div>
			<div class="form-group">
				<label for="smscode" class="col-sm-3 control-label">短信验证码</label>
				<div class="col-sm-6">
					<input type="number" class="form-control" id="smscode" name="smscode" placeholder="请输入手机收到的短信验证码" value=""  max="999999" min="0" />
				</div>
			</div>
		<div id="input-pwd" class="pwd-init">
			<div class="form-group">
				<label for="newpassword" class="col-sm-3 control-label">设置新密码</label>
				<div class="col-sm-6">
					<input type="password" class="form-control" id="newpassword" name="password" placeholder="设置新的登录密码" />
				</div>
			</div>
			<div class="form-group">
				<label for="password2" class="col-sm-3 control-label">再次输入新密码</label>
				<div class="col-sm-6">
					<input type="password" class="form-control" id="password2" placeholder="再次确认输入新密码" value="" />
				</div>
			</div>
		</div>
			<div class="form-group">
				<div class="col-sm-9 col-sm-offset-3">
					<button id="resetpwd-submit" type="submit" class="btn btn-warning btn-lg disabled" disabled="disabled"><strong>提交重置密码</strong></button>
					&nbsp;&nbsp;
					<a href="/admincp/login/" role="button" class="btn btn-default btn-lg">返回登录</a>
				</div>
			</div>
		</div>
	</div>
</div>
</form>
{literal}
<script type="text/javascript">
/** 验证码发送间隔时间，单位：表 */
var wait = 60;
var _wait = wait;
/** 验证码发送loading */
function _send_loading() {
	jQuery('#send_button').append('<span id="_send_loading_icon"><img src="/admincp/static/images/loading.gif" alt="" /></span>').attr('disabled', true);
	jQuery('#send_tip').text('正在发送手机短信验证码，请稍候……');
}
/** 验证码发送完毕 */
function _send_complete() {
	jQuery('#_send_loading_icon').remove();
	jQuery('#send_tip').fadeOut(1000*5);
}
/** 发送按钮倒计时 */
get_code_time = function (o) {
	if (wait == 0) {
		o.text('再次获取验证码').removeAttr('disabled');
		wait = _wait;
	} else {
		o.text('' + wait + '秒后重新获取').attr('disabled', true);
		wait--;
		setTimeout(function() {
			get_code_time(o);
		}, 1000);
	}
}

// 刷新图片验证码
function fresh_seccode() {
	
	jQuery.ajax({
		'url': '/frontend/index/seccode',
		'type': 'POST',
		'dataType': 'json',
		'data': {'formhash': jQuery('#formhash').val()},
		'success': function(res) {
			if (typeof(res.errcode) === 'undefined') {
				alert('send return error');
				return false;
			}
			if (res.errcode > 0) {
				alert(res.errmsg);
				return false;
			}
			jQuery('#id-seccode-img').attr('src', '/frontend/index/seccode?width=100&height=45&code=' + res.result);
		}
	});
}

fresh_seccode();
// 刷新图片
jQuery('#id-seccode-img').click(function() {
	fresh_seccode();
});

/** 发送短信验证码 */
function _send_smscode() {
	var mobilephone = jQuery('#id-mobilephone').val();
	if (!mobilephone) {
		alert('请正确输入您的手机号码');
		jQuery('#id-mobilephone').focus();
		return false;
	}

	jQuery.ajax({
		"url":"/admincp/sms/",
		"type":"POST",
		"dataType":"json",
		"data":{
			"mobilephone": jQuery('#id-mobilephone').val(),
			'formhash': jQuery('#formhash').val(),
			'seccode': jQuery('#id-seccode').val()
		},
		"beforeSubmit":function(arr, form, options) {
			_send_loading();
		},
		"error":function(XmlHttpRequest, textStatus, errorThrown){
			alert('send error');
			return false;
		},
		"complete":function (XMLHttpRequest, textStatus) {
			_send_complete();
		},
		"success":function(r){
			if (typeof(r.errcode) === 'undefined') {
				alert('send return error');
				return false;
			}
			if (r.errcode > 0) {
				alert(r.errmsg);
				return false;
			}
			jQuery('#send_tip').text('手机短信验证码已发送，请注意接收。').show();
			get_code_time(jQuery('#send_button'));
			jQuery('#id-newpw-box').fadeIn('slow');
			jQuery('#input-pwd').show();
			jQuery('#resetpwd-submit').removeAttr('disabled').removeClass('disabled');
		}
	});
	return false;
}

jQuery(function(){
	jQuery('#send_button').on('click', function(){
		_send_smscode();
	});

	var jq_f = jQuery('#pwdform');
	jq_f.submit(function(){

		var mobilephone = jQuery.trim(jQuery('#id-mobilephone').val());
		var smscode = jQuery.trim(jQuery('#smscode').val());
		var password = jQuery.trim(jQuery('#newpassword').val());
		if (!mobilephone) {
			alert('请输入手机号码');
			return false;
		}
		if (smscode == '') {
			alert('请输入短信验证码');
			return false;
		}
		if (password == '') {
			alert('请输入新密码');
			return false;
		}
		if (password != jQuery('#password2').val()) {
			alert('两次密码输入不一致，请重新确认输入是否正确');
			return false;
		}

		jQuery.ajax({
			"url":jq_f.attr('action'),
			"type":"POST",
			"data":{
				"mobilephone":mobilephone,
				"smscode":smscode,
				"password":hex_md5(password),
				"formhash":jQuery('input[name=formhash]').val()
			},
			"dataType":"json",
			"beforeSubmit":function(arr, form, options) {
				//_submit_loading();
			},
			"error":function(XmlHttpRequest, textStatus, errorThrown){
				alert('submit error');
				return false;
			},
			"success":function(r){
				if (typeof(r.errcode) === 'undefined') {
					alert('submit return error');
					return false;
				}
				if (r.errcode > 0) {
					alert(r.errmsg);
					return false;
				} else {
					jQuery('#send_tip').html('<a style="color:blue" href="/admincp/login/">'+r.errmsg+'</a>').show();
					return false;
				}
				var data = r.result;
				return false;
			},
			"complete":function (XMLHttpRequest, textStatus) {
				//_submit_complete();
			}
		});

		return false;
	});
});
</script>
{/literal}

{include file="$tpl_dir_base/adminer/footer.tpl"}