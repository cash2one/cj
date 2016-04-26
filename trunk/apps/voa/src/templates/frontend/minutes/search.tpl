{include file='frontend/header.tpl'}

<body id="wbg_hyt_search">
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>

<div class="mod_top_search"><div class="sinner">
	<!--placeholder请酌情修改-->
	<input type="text" value="{$sotext}" placeholder="搜索 输入日期或人名等" />
</div></div>
<script>
{literal}
$onload(function() {
	var _tc = $one('.mod_top_search'),
		_ipt = $one('input', _tc);
	_ipt.addEventListener('keyup', function(e) {
		if (e.which === 13) { //回车
			window.location.href = '/minutes/so?sotext=' + _ipt.value;
		}
	});
});
{/literal}
</script>

{if empty($list)}
<em class="mod_empty_notice"><span>没有您需要查看的会议记录列表</span></em>
{else}
<ul>{include file='frontend/minutes/search_li.tpl'}</ul>
{/if}

{if $perpage <= count($list)}
<a id="show_more" href="javascript:void(0)" class="mod_ajax_more">加载更多&gt;&gt;</a>
{/if}

{include file='frontend/footer_nav.tpl'}

<script>
{literal}
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 加载更多 */
		var _more = new c_list_more();
		_more.init('show_more', 'minutes_list', {'updated':'_minutes_updated'});
	});
});
{/literal}
</script>

{include file='frontend/footer.tpl'}
