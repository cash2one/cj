{include file='frontend/header.tpl'}
<style>
{literal}
.diy_li {margin-left:25px;}
a.no_after_a::after {background:none !important;}
{/literal}
</style>
<body id="wbg_xd_detail_lv2">

<header class="mod_profile_header type_A">
	<h1>{$shop['csp_name']}</h1>
	<h2>{$inspect_set['title_examinator']}：{$inspect['m_username']}</h2>
</header>

<div class="body mod_common_list_style">
	<div class="shoulder">
		<h1>{$items[$insi_id]['insi_name']}</h1>
		<h2>{$shop['csp_address']}</h2>
		<div class="score">
			<div>
				<em>{$item2score[$insi_id]}<i>{if 0 < $inspect_set['score_rule_diy']}%{else}分{/if}</i></em>
				<p>{if 0 < $inspect_set['score_rule_diy']}合格率{else}得分{/if}</p>
			</div>
		</div>
	</div>
</div>

{foreach $items['p2c'][$insi_id] as $_id}
<ul class="mod_common_list">
	<li>
		<a class="m_link{if empty($score_list[$_id]['isr_option']) && empty($score_list[$_id]['isr_message']) && empty($insi_id2at[$_id])} no_after_a{/if}" href="javascript:void(0)">
			<h2>{$items[$_id]['insi_describe']}</h2>
			{if 0 < $inspect_set['score_rule_diy']}
			<ul>
				<li class="diy_li"><span>{$inspect_set['score_rules'][$item2score[$_id]]}</span></li>
			</ul>
			{else}
			<ul class="mod_score_starsbar">
				<li><em data-num="{if $item2score[$_id] > $items[$_id]['insi_score']}5{else}{$item2score[$_id] * 5 / $items[$_id]['insi_score']}{/if}"><i></i></em><span><strong>{$item2score[$_id]}</strong>分</span></li>
			</ul>
			{/if}
		</a>
	</li>
	{if 0 == $inspect_set['score_rule_diy'] && !empty($items[$_id]['insi_hasselect']) && !empty($options['i2o'][$items[$_id]['insi_id']])}
	<li hidden>
		<h3>{if !empty($items[$_id]['insi_select_title'])}{$items[$_id]['insi_select_title']}{else}{$inspect_set['select_title']}{/if}</h3>
		<p>{$options[$score_list[$_id]['isr_option']]['inso_optvalue']}</p>
	</li>
	{/if}
	{if !empty($score_list[$_id]['isr_message'])}
	<li hidden>
		<h3>{if $items[$_id]['insi_feedback_title']}{$items[$_id]['insi_feedback_title']}{else}{$inspect_set['score_title_describe']}{/if}</h3>
		<p>{$score_list[$_id]['isr_message']}</p>
	</li>
	{/if}
	{if !empty($insi_id2at[$_id])}
	<li hidden>
		<h3>{if $items[$_id]['insi_att_title']}{$items[$_id]['insi_att_title']}{else}现场图片{/if}</h3>
		<div class="photos">
			{include file='frontend/mod_img_list.tpl' attachs=$insi_id2at[$_id]}
		</div>
	</li>
	{/if}
</ul>
{/foreach}

<script>
{literal}
$onload(function() {
	function _toggle($ul, on) {
		$each( $all(':first-child ~ li', $ul), function($li) {
			if (on) $show($li);
			else $hide($li);
		});
	}
	$each('body>ul', function($ul) {
		_toggle($ul, false);
		$rmCls($ul, 'opened');
		$one(':first-child', $ul).addEventListener('click', function(e) {
			if ($one('.no_after_a', $ul)) {
				return true;
			}
			
			if ($hasCls($ul, 'opened')) {
				$addCls($one('a:first-of-type', this), 'm_link');
				$rmCls($ul, 'opened');
				_toggle($ul, false);
			} else {
				$rmCls($one('a:first-of-type', this), 'm_link');
				$addCls($ul, 'opened');
				_toggle($ul, true);
			}
		});
	});
});
{/literal}
</script>

{include file='frontend/footer.tpl'}
