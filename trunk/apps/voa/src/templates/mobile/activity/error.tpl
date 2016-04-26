{include file='mobile/header.tpl' css_file='app_activity.css'}


	<section class="ui-notice ui-notice-fail"> <i></i>
		<h2>对不起</h2>
		<p>{$data['message']}</p>
		<div class="ui-btn-wrap">
			<button class="ui-btn-lg" onclick="javascript:history.go(-1);">返回</button>
		</div>
	</section>


{include file='mobile/footer.tpl'}