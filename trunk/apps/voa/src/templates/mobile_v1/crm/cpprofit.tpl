{include file='mobile_v1/header_fz.tpl'}

<section class="ui-selector ui-selector-line">
	<div class="ui-selector-content">
		<ul>
			<li class="ui-selector-item ui-border-b">
				<a href="/frontend/travel/cpindex" class="ui-back-a"> <i class="ui-back"></i></a>
				<h3 class="clearfix">我的提成</h3>
			</li>
		</ul>
	</div>
</section>
<ul class="ui-list ui-border-no">
	<li>
		<div class="ui-avatar-s">
			<span style="background-image:url({$cinstance->avatar($wbs_uid)})"></span>
		</div>
		<div class="ui-list-info ui-text-center">
			<h4>手机：{$wbs_user['m_mobilephone']}</h4>
		</div>
	</li>
</ul>
<div class="frozen-module-dom">
	<div class="ui-form ">
		<div class="ui-form-item ui-border-b">
			<label for="#">昨日提成</label>
			<p class="ui-text-right">{if $to_yesterday['profit']}{$to_yesterday['profit']/100}{else}0.00{/if}元</p>
		</div>
		<div class="ui-form-item ui-border-b">
			<label for="#">本月提成</label>
			<p class="ui-text-right">{if $to_month['profit']}{$to_month['profit']/100}{else}0.00{/if}元</p>
		</div>
		<div class="ui-form-item ui-border-b">
			<label for="#">上月提成</label>
			<p class="ui-text-right">{if $to_lastmonth['profit']}{$to_lastmonth['profit']/100}{else}0.00{/if}元</p>
		</div>
		<div class="ui-form-item">
			<label for="#">总提成</label>
			<p class="ui-text-right">{if $to_total['profit']}{$to_total['profit']/100}{else}0.00{/if}元</p>
		</div>
	</div>
</div>
	
{include file='mobile_v1/footer_fz.tpl'}