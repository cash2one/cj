{include file='frontend/header.tpl'}

<body id="wbg_mp_sortbya2z">

{include file='frontend/mod_top_search.tpl' iptvalue=$sotext placeholder='搜索 输入人名'}

{if empty($list)}
<em class="mod_empty_notice mod_common_list_style"><span>您还没有名片夹信息</span></em>
{else}
{foreach $list as $k => $lis}
<abbr>{$k}</abbr>
<ul class="mod_common_list">
	{foreach $lis as $v}
	{include file='frontend/namecard/list_li.tpl'}
	{/foreach}
</ul>
{/foreach}
{/if}
<ol class="footFix mod_a2z_search"></ol>

<script>
var _ascii_range = '{$ascii_range}';
{literal}
require(['a2z'], function(){
	var lettersRange = _ascii_range.split(','); //如果不指定字母范围(显示所有), 则应将此值置为null

	$onload(function(){ 
		parseA2ZSearch({lettersRange: lettersRange});
	});
});
{/literal}
</script>

{include file='frontend/footer.tpl'}