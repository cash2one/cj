{include file='mobile/header.tpl' }

<section class="ui-notice ui-notice-fail kdzs-ui-margin-bottom"> <i></i>
    <h2>设置代人失败！</h2>
    <p>您的快递已领取！</p>
</section>
<div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
    <button id="cancle" class="ui-btn-lg ui-btn-primary">确定</button>
</div>

<script>

require(["zepto", "underscore", "frozen"], function($, _, fz) {
	$(document).ready(function() {

		//取消
		$('#cancle').on('click', function(e) {
			 var url = "/frontend/express/list";
	 	     window.location.href=url;  
		});
		
	});
});

</script>

{include file='mobile/footer.tpl'}