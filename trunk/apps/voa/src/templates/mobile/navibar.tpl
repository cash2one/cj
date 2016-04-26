<div class="ui-tab-nav ui-tab-nav-footer ui-border-t">
	<div class="ui-selector ui-selector-line">
		<div class="ui-selector-item">
			<a href="javascript:history.go(-1);" class="ui-back-a sale-ui-back">
				<i class="ui-back sale-back-top"></i>
			</a>
		</div>
	</div>
	<div id="main-home" class="sale-back-main">
		<button type="button" >销售管理</button>
	</div>
</div>
<script type="text/javascript">
require(["zepto", "underscore", "frozen"], function($, _) {
	$("#main-home").on("click", function(e) {
		window.location.href = "/frontend/sale/main";
	});
});
</script>