{include file='mobile_v1/header_fz.tpl'}

<div class="myhome">
	<div class="header-top">
		<div class="ui-avatar">
			<span style="background-image:url({$cinstance->avatar($wbs_uid)})"></span>
		</div>
	</div>
	<div class="header-wrapper">
		<h5>{$wbs_username}</h5>
		<h4>{$wbs_user['m_mobilephone']}</h4>

		<div class="ui-tooltips">
			<div class="ui-tooltips-cnt-link">
				<a href="/frontend/travel/cpprofit"><span>收益</span>{if $to_total['profit']}{$to_total['profit']/100}{else}0.00{/if}<span>元</span></a>
			</div>
		</div>
	</div>
	<div class='body-wrapper'>
		<ul class="clearfix">
			<li><i class="ui-icon ui-icon-myhome"></i>我的订单</li>
			<li class="ui-side-right"><a href="/frontend/travel/cporderlist">今日订单<span>{$countorder}</span>笔</a></li>
		</ul>
		<ul class="clearfix">
			<li><i class="ui-icon ui-icon-myhome ui-icon-myhome01"></i>我的业绩</li>
			<li class="ui-side-right"><a href="/frontend/travel/cpturnover">今日业绩<span>{if $to_day['price']}{$to_day['price']/100}{else}0.00{/if}</span>元</a></li>
		</ul>
		 <ul class="clearfix ui-side-border">
			<li><a href="/frontend/travel/cpcustomer?__view=customers_list"><i class="ui-icon ui-icon-myhome ui-icon-myhome02"></i>我的客户</a></li>
			<li class="ui-side-right "><a href="/frontend/travel/cplist"><i class="ui-icon ui-icon-myhome ui-icon-myhome03"></i>我的买手店</a></li>
		</ul>
		 <ul class="clearfix ui-side-border">
			<li><a href="/frontend/travel/cpfodders"><i class="ui-icon ui-icon-myhome ui-icon-myhome04"></i>我的素材库</a></li>
			<li class="ui-side-right"><i class="ui-icon ui-icon-myhome ui-icon-myhome05"></i>我的数据</li>
		</ul>
	</div>
</div>

{include file='mobile_v1/footer_fz.tpl'}