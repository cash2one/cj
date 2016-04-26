{include file='frontend/header.tpl'}
<style>
.mod_top_search {
	margin-right:0px !important; margin-bottom:0px !important;
}
</style>

<body id="wbg_mp_sortbygroup">

{include file='frontend/mod_top_search.tpl' iptvalue=$sotext placeholder='搜索 输入人名'}

{if empty($ncfs)}
<em class="mod_empty_notice"><span>您还没有名片夹信息</span></em>
{else}
{foreach $ncfs as $f}
<h1{if $f@first} class="opened"{/if}>{$f['_name']}</h1>
<ul class="mod_common_list"{if $f@first}{else} hidden{/if}>
	{foreach $list[$f['ncf_id']] as $v}
	{include file='frontend/namecard/list_li.tpl'}
	{/foreach}
</ul>
{/foreach}
{/if}


<script>
{literal}
$onload(function() { 
	$each('body>h1', function(h1) {
		h1.addEventListener('touchend', function(e2) {
			var $ul = $next(h1);
			
			if ($hasCls(h1, 'opened')) {
				$rmCls(h1, 'opened');
				$hide($ul);
				return;
			}
			
			$each('body>h1', function(h1b) {
				$rmCls(h1b, 'opened');
				$hide($next(h1b));
			});
			
			$addCls(h1, 'opened');
			$show($ul);
		});
	});
});
{/literal}
</script>

{include file='frontend/footer.tpl'}