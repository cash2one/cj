{include file='frontend/header.tpl'}

<body id="wbg_rb_search">
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>

<div class="mod_top_search"><div class="sinner">
	<!--placeholder请酌情修改-->
	<input type="text" name="sotext" value="{$sotext}" placeholder="搜索 输入日期或人名等" />
</div></div>
{literal}
<script>
$onload(function() {
	var _tc = $one('.mod_top_search'),
		_ipt = $one('input', _tc);
	_ipt.addEventListener('keyup', function(e) {
		if (e.which === 13) { //回车
			window.location.href = '/dailyreport/so?sotext=' + _ipt.value;
		};
	});
});
</script>
{/literal}

<h1>{if 'mine' == $ac}我发出的{elseif 'recv' == $ac}我收到的{/if}日报</h1>
{if empty($list)}
<em class="mod_empty_notice"><span>没有您需要查看的报告列表</span></em>
{else}
<ul class="mod_common_list" id="dailyreport_list">
	{include file='frontend/dailyreport/search_li.tpl'}
</ul>
{/if}

{if $perpage <= count($list)}
<a id="show_more" href="javascript:void(0)" class="mod_ajax_more">加载更多&gt;&gt;</a>
{/if}

{include file='frontend/footer_nav.tpl'}

{literal}
<script>
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 加载更多 */
		var _more = new c_list_more();
		_more.init('show_more', 'dailyreport_list', {'updated':'_dailyreport_updated'});
	});
});
</script>
{/literal}

{include file='frontend/footer.tpl'}
