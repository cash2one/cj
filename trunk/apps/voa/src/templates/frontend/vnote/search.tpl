{include file='frontend/header.tpl'}

<body id="wbg_bwl_search">
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>

<div class="mod_top_search"><div class="sinner">
	<!--placeholder请酌情修改-->
	<input type="text" name="sotext" value="{$sotext}" placeholder="搜索 请输入姓名" />
</div></div>
<script>
$onload(function() {
	var _tc = $one('.mod_top_search'),
		_ipt = $one('input', _tc);
	_ipt.addEventListener('keyup', function(e) {
		if (e.which === 13) { //回车
			window.location.href = '/vnote/so?sotext=' + _ipt.value;
		};
	});
});
</script>

{if empty($list)}
	<em class="mod_empty_notice"><span>没有您需要查看的备忘信息</span></em>
{else}
	<ul class="mod_common_list" id="vnote_list">
		{include file='frontend/vnote/search_li.tpl'}
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
		_more.init('show_more', 'vnote_list', {'updated':'_vnote_updated'});
	});
});
</script>
{/literal}

{include file='frontend/footer.tpl'}
