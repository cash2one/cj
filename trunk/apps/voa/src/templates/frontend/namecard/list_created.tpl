{include file='frontend/header.tpl'}
<style>
.mod_top_search {
	margin-right:0px !important; margin-bottom:0px !important;
}
</style>

<body id="wbg_mp_sortbytime">

{include file='frontend/mod_top_search.tpl' iptvalue=$sotext placeholder='搜索 输入人名'}

{if empty($list)}
<em class="mod_empty_notice mod_common_list_style"><span>您还没有名片夹信息</span></em>
{else}
<ul class="mod_common_list">
	{foreach $list as $v}
	{include file='frontend/namecard/list_li.tpl'}
	{/foreach}
</ul>
{/if}

{include file='frontend/footer.tpl'}