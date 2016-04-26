<include file="Header" />

<script src="http://libs.baidu.com/jquery/1.9.1/jquery.min.js"></script>
<form action="#" method="post" id="login_form">
<input type="hidden" name="ep_id" value="{$ep_id}" />
<table>
  <tr>
    <th>用户名</th>
    <td><input type="text" name="username" /></td>
  </tr>
  <tr>
    <th>密码</th>
    <td><input type="password" name="passwd" /></td>
  </tr>
  <tr>
  	<td><input type="button" id="login_btn" value="登录" /></td>
  </tr>
</table>
<input type="hidden" name="_create" value="1" />
</form>

<script type="text/javascript">
var login_url = '{$login_url}';
var redirect_uri = '{$redirect_uri}';
$(document).ready(function() {
	!window.jQuery && document.write('<script src="/misc/script/jquery.js"><\/script>');
	
	$('#login_btn').click(function() {
		var _this = this;
		$(_this).attr('disabled', true);
		$(_this).val('登录中...');
		$.ajax({
			url: login_url,
			data: $('#login_form').serialize(),
			type: 'post',
			success: function(result) {
				if (!login_succeed(result)) {
					$(_this).attr('disabled', false);
					$(_this).val('登录');
					return false;
				}
				return false;
			},
			error: function() {
				$(_this).attr('disabled', false);
				$(_this).val('登录');
				alert('网络错误, 请重试');
			}
		});
		return false;
	});
});

// 结果处理
var login_succeed = function(result) {

	// 如果出错了
	var errcode = parseInt(result.errcode);
	if (isNaN(errcode) || 0 < errcode) {
		alert(result.errmsg);
		return false;
	}

	var code = result['result']['code'];
	redirect_uri += (-1 == redirect_uri.indexOf('?') ? '?' : '&') + 'code=' + code; 
	window.location.href = redirect_uri;
	return false;
};
</script>

<include file="Footer" />
