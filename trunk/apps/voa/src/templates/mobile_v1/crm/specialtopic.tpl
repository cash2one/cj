{include file='mobile_v1/header_fz.tpl'}

<div class="ui-goods-select">
	<div class="ui-selector ui-selector-line">
		<div class="ui-selector-content">
			<div class="ui-selector-item ui-border-b">
				<a href="javascript:history.go(-1);" class="ui-back-a"> <i class="ui-back"></i></a>
				<h3 class="clearfix" id="subject"></h3>
			</div>
		</div>
	</div>
	
	<div id="message" class="ui-list ui-main-content"></div>
</div>

<script type="text/javascript">
var mtid = '{$mtid}';
{literal}
require(["zepto", "underscore", "frozen"], function($, _, fz) {

	var el = $.loading({
		content: '加载中...'
	});
	// 请求详情
	$.ajax({
		'type': 'GET',
		'url': '/api/travel/get/specialtopic/?mtid=' + mtid,
		'success': function(data, status, xhr) {
			$("#message").html(data["result"]["message"]);
			$("#subject").html(data["result"]["subject"]);
			el.loading('hide');
		}
	});
});
{/literal}
</script>

{include file='mobile_v1/footer_fz.tpl'}
