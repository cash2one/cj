{include file='mobile/header.tpl'}


	<div class="btn-group">
		<div class="ui-btn-wrap btn-two">
			<button id="out" class="ui-btn-lg ui-btn-primary ui-icon">提前退场</button>
		</div>
	</div>
	
{include file='mobile/footer.tpl'}
<script>
require(["zepto"], function($) {
	//退场
	$('#out').click(function (){
		location.href = '/frontend/meeting/scan?act=out&mt_id={$mt_id}';
	});
});
</script>