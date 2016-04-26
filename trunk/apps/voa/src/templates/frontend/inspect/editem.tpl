{include file='frontend/header.tpl'}

<body id="wbg_xd_indicators">

<h1>{$shop['csp_name']}</h1>

{foreach $items['p2c'][0] as $pid}
<ul class="mod_common_list">
	<li>
		<a class="m_link" href="javascript:void(0)">
			<div class="clearfix">
				<h1>{$items[$pid]['insi_name']}</h1>
				{if $list[$pid]['_is_over']}<em>{$item2score[$pid]}{if 0 < $inspect_set['score_rule_diy']}%{else}分{/if}</em>{else}<span class="unevaluated">待评估</span>{/if}
			</div>
			<div><p>{$items[$pid]['insi_describe']}</p></div>
		</a>
	</li>
	{foreach $items['p2c'][$pid] as $cid}
	<li hidden>
		<a class="m_link" href="/frontend/inspect/edscore/ins_id/{$inspect['ins_id']}/insi_id/{$cid}/">
			<h2>{$items[$cid]['insi_describe']}</h2>
			{if 0 < $item2score[$cid]}<em>{if 0 < $inspect_set['score_rule_diy']}{$inspect_set['score_rules'][$item2score[$cid]]}{else}{$item2score[$cid]}分{/if}</em>{else}<span class="unevaluated">待评估</span>{/if}
		</a>
	</li>
	{/foreach}
</ul>
{/foreach}

<div class="foot numbtns double">
	<input id="btn_go_back" type="reset" value="返回" /><input id="submit" type="submit" value="提交报告" />
</div>


<script>
var _ins_id = {$inspect['ins_id']};
{literal}
$onload(function() {
	require(['dialog', 'business'], function() {
		$one('#submit').addEventListener('click', function(e) {
			aj_form_analog('/frontend/inspect/chksend/ins_id/' + _ins_id + '/?handlekey=post', {});
		});
	});
	function _toggle($ul, on) {
		$each( $all('li:nth-of-type(1) ~ li', $ul), function($li) {
			if (on) $show($li);
			else $hide($li);
		});
	}
	$each('ul', function($ul) {
		_toggle($ul, false);
		$rmCls($ul, 'opened');
		$one('li:first-of-type', $ul).addEventListener('click', function(e) {
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

function errorhandle_post(url, msg) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg) {
	window.location.href = url;
}
{/literal}
</script>

{include file='frontend/footer.tpl'}
