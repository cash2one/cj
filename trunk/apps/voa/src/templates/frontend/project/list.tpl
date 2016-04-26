{include file='frontend/header.tpl'}

<body id="wbg_gzt_list">
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>

<h1>任务列表</h1>
<ul class="mod_common_list" id="project_list" style="margin-top:0px;">
	{if empty($list)}
		<li><em class="mod_empty_notice"><span>没有您需要查看的任务列表</span></em></li>
	{else}
		{include file='frontend/project/list_li.tpl'}
	{/if}
</ul>

{include file='frontend/footer_nav.tpl'}
{if $perpage <= count($list)}
<a id="show_more" href="javascript:void(0)" class="mod_ajax_more">加载更多...</a>
{/if}

<script>
{literal}
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 加载更多 */
		var _more = new c_list_more();
		_more.init('show_more', 'project_list', {'page':_page+1}, {'callback' :function(s){
			if(_count < 10){
				$('#show_more').hide();
			}
			$append($one('#project_list'), s);
		}});
	});
});
{/literal}
</script>


{include file='frontend/footer.tpl'}
