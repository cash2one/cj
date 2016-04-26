{include file='mobile/header.tpl' navtitle="活动详情"}
<style>
	#content img {
		width: 100%;
		max-width: 900px;
	}
</style>
<link rel="stylesheet" type="text/css" href="/misc/styles/oa_ght.css">
<div class="ui-list ui-img-box" id="content">

</div>
<form action="javascript:;" id="signfrm" style="display: none;">
	<input name="id" type="hidden" value="{$smarty.get.id}">
	<input name="saleid" type="hidden" value="{$smarty.get.saleid}">
	<div id="custom" class="ui-form">
		<div class="ui-form-item ui-list-title">报名表</div>
		<div class="ui-form-item ui-border-b">
			<label>姓名</label>
			<input name="name" id="name" type="text" placeholder="请输入姓名">
		</div>
		<div class="ui-form-item ui-border-b">
			<label>手机号</label>
			<input name="mobile" id="mobile" type="tel" placeholder="请输入手机号">
		</div>
	</div>
	<div class="ui-btn-wrap">
		<button id="submit" class="ui-btn-lg ui-btn-primary"{if $is_sale} disabled{/if}>提交</button>
	</div>
</form>

<script type="text/javascript">
	var id = "{$smarty.get.id}";
	var saleid = "{$smarty.get.saleid}";
	var sharetime = "{$smarty.get.sharetime}";
	{literal}
	require(["zepto"], function($) {
		//活动详情
		$.getJSON('/api/campaign/get/view?id='+id+'&saleid='+saleid+'&sharetime='+sharetime, function(json) {
			if(json.errcode > 0) {
				return $.tips({content: json.errmsg});
			}

			$('title').text(json.result.subject);
			$('#content').html(json.result.content);

			// 如果需要报名
			if (0 < json.result.needsign) {
				$("#signfrm").show();
			}

			if(json.result.is_custom == 0) return;
			var custom = json.result.custom;
			//生成表单
			for(k in custom) {
				$('#custom').append('<div class="ui-form-item ui-border-b">\
				<label>'+custom[k]+'</label>\
				<input name="custom[]" type="text" placeholder="请输入'+custom[k]+'">\
			</div>');
			}
		});
		//报名
		$('#submit').click(function() {
			var n = $('#name');
			var m = $('#mobile');
			if(!n.val()) {
				n.focus();
				return $.tips({content: '请输入姓名'});
			}

			if(n.val().length > 32) {
				n.focus();
				return $.tips({content: '姓名长度过长'});
			}

			if(!m.val()) {
				m.focus();
				return $.tips({content: '请输入手机号'});
			}

			var patrn=/^1[0-9]{10}$/;
			if(!patrn.exec(m.val())) {
				m.focus();
				return $.tips({content: '手机号不正确'});
			}

			var data = $('form').serializeArray();
			$.post('/api/campaign/post/reg', data, function(json) {
				if(json.errcode == 0) {
					location.href = '/frontend/campaign/invite?regid=' + json.result;
				}else{
					$.tips({content: '报名失败:' + json.errmsg});
				}
			}, 'json');
		});
	});
	{/literal}
</script>
{include file='mobile/footer.tpl'}
