{include file='frontend/header.tpl'}
<style>
{literal}
.diy_li {margin-left:25px;}
{/literal}
</style>
<body id="wbg_xd_detail_lv2">

<header class="mod_profile_header type_A">
	<h1>{$shop['csp_name']}</h1>
	<h2>{$productive_set['title_examinator']}：{$productive['m_username']}</h2>
</header>

<div class="body mod_common_list_style">
	<div class="shoulder">
		<h1>{$items[$pti_id]['pti_name']}</h1>
		<h2>{$shop['csp_address']}</h2>
		<div class="score">
			<div>
				<em>{$item2score[$pti_id]}<i>{if 0 < $productive_set['score_rule_diy']}%{else}分{/if}</i></em>
				<p>{if 0 < $productive_set['score_rule_diy']}合格率{else}得分{/if}</p>
			</div>
		</div>
	</div>
</div>

{foreach $items['p2c'][$pti_id] as $_id}
<ul class="mod_common_list">
	<li>
		<a class="m_link" href="javascript:void(0)">
			<h2>{$items[$_id]['pti_describe']}</h2>
			{if 0 < $productive_set['score_rule_diy']}
			{if 0 == $items[$_id]['pti_fix_score']}
			<ul>
				<li class="diy_li"><span>{$productive_set['score_rules'][$item2score[$_id]]}</span></li>
			</ul>
			{/if}
			{else}
			<ul class="mod_score_starsbar">
				<li><em data-num="{$item2score[$_id]}"><i></i></em><span><strong>{$item2score[$_id]}</strong>分</span></li>
			</ul>
			{/if}
		</a>
	</li>
	{if !empty($score_list[$_id]['ptsr_message'])}
	<li hidden>
		<h3>{$productive_set['score_title_describe']}</h3>
		<p>{$score_list[$_id]['ptsr_message']}</p>
	</li>
	{/if}
	{if !empty($pti_id2at[$_id])}
	<li hidden>
		<h3>现场图片</h3>
		<div class="photos">
			{include file='frontend/mod_img_list.tpl' attachs=$pti_id2at[$_id]}
		</div>
	</li>
	{/if}
</ul>
{/foreach}

<script>
{literal}
$onload(function(){
	function _toggle($ul, on){
		$each( $all(':first-child ~ li', $ul), function($li){
			if (on) $show($li);
			else $hide($li);
		});
	}
	$each('body>ul', function($ul){
		_toggle($ul, false);
		$rmCls($ul, 'opened');
		$one(':first-child',$ul).addEventListener('click', function(e){
			if ( $hasCls($ul, 'opened') ){
				$rmCls($ul, 'opened');
				_toggle($ul, false);
			}else{
				$addCls($ul, 'opened');
				_toggle($ul, true);
			}
		});
	});
});
{/literal}
</script>

{include file='frontend/footer.tpl'}