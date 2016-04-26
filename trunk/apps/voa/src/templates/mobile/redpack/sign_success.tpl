{include file='mobile/header.tpl' navtitle='签到' css_file='app_redpack.css'}

<section class="red-avatar">
	<div class="ui-avatar-one red-avatar-one">
		<span style="background-image:url({$cinstance->avatar($wbs_uid)})"></span>
	</div>
	<p>你好，{$wbs_username}</p>
	<h2>恭喜签到成功！</h2>
</section>

<div class="ui-btn-wrap red-btn-sign">
	<button id="getredpack" class="ui-btn-lg ui-btn-danger">点击领取红包</button>
</div>

<script type="text/javascript">
var redpack_id = {$redpack_id};
{literal}
require(["zepto", "underscore", "frozen"], function($, _, fz) {
	$("#getredpack").on('click', function(e) {
		var g_el = $.loading({
			content: '加载中...'
		});
		// 请求地址详情
		$.ajax({
			'type': 'POST',
			'url': '/api/redpack/post/presend/redpack_id/' + redpack_id,
			'success': function(data, status, xhr) {
				g_el.loading('hide');
				if (_.has(data, "errcode") && 0 < data["errcode"]) {
					var dia = $.dialog({
						title: '',
						content: _.isEmpty(data["errmsg"]) ? '红包领取错误, 请重新尝试' : data["errmsg"],
						button: ["确认"]
					});
				} else {
					location.href = '/frontend/redpack/signredpack';
				}
			}
		});
	});
});
{/literal}
</script>

{include file='mobile/footer.tpl'}