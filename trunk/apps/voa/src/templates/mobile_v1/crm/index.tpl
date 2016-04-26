{include file='mobile_v1/header_fz.tpl'}

<div class="ui-list-goods">
	{foreach $indexs as $_index}
	{if 1 < count($_index)}
	<div class="ui-slider-goods">
	<div class="ui-slider ui-slider-indicators-no">
		<ul class="ui-slider-content" style="width: {ceil(count($_index) / 3) * 100}%">
			<li>
			{foreach $_index as $_ad name=li}
				{if 0 < $smarty.foreach.li.index && 0 == $smarty.foreach.li.index % 3}</li><li>{/if}
				<a href="{$_ad['href']}"><span style="background-image:url({$_ad['img']})"></span></a>
			{/foreach}
			</li>
		</ul>
		<div class="ui-slider-left"><i class="ui-icon ui-icon-slider-left"></i></div>
		<div class="ui-slider-right"><i class="ui-icon ui-icon-slider-right"></i></div>
	</div></div>
	{else}
	<ul class="ui-grid-trisect ui-goods-top">
		{foreach $_index as $_ad}
		<li>
			<a href="{$_ad['href']}"><div class="ui-grid-trisect-img"><img src="{$_ad['img']}" /></div></a>
		</li>
		{/foreach}
	</ul>
	{/if}
	{/foreach}
</div>
<div id="debug"></div>
{literal}
<script type="text/javascript">
require(["zepto", "underscore", "frozen"], function($, _, fz) {
	// 选择事件
	$("#classid").on("change", function(e) {
		$("#frmchgclassid").submit();
	});

	var page = 0;
	var slider = new window.fz.Scroll('.ui-slider', {
		role: 'slider',
		indicator: true,
		autoplay: true,
		interval: 3000
	});

	slider.on('scrollEnd', function(curpage) {
		page = curpage;
	});

	$(".ui-slider-left").on("click", function(e) {
		slider.currentPage = 1;
		slider.refresh();
	});

	$(".ui-slider-right").on("click", function(e) {
		slider.currentPage = 0;
		slider.refresh();
	});
});
</script>
{/literal}

{include file='mobile_v1/footer_fz.tpl'}