{include file='frontend/header.tpl'}

<body id="wbg_gg_list">

<div class="mod_top_search"><div class="sinner">
	<!--placeholder请酌情修改-->
	<input type="text" name="sotext" value="{$sotext}" placeholder="输入标题、时间或姓名" />
</div></div>
<script>
$onload(function() {
	var _tc = $one('.mod_top_search'),
		_ipt = $one('input', _tc);
	_ipt.addEventListener('keyup', function(e) {
		if (e.which === 13) { //回车
			window.location.href = '/notice/so?sotext=' + _ipt.value;
		};
	});
});
</script>

{if empty($list)}
<em class="mod_empty_notice"><span>没有您需要查看的公告列表</span></em>
{else}
<ul class="mod_common_list" id="notice_list">
	{include file='frontend/notice/list_li.tpl'}
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
		_more.init('show_more', 'notice_list', {'updated':'_notice_updated'});
	});
});
</script>
{/literal}

{include file='frontend/footer.tpl'}
