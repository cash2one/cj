{include file='frontend/header.tpl'}

<body id="wbg_xd_detail">

<div id="viewstack"><section>

	<header class="mod_profile_header type_A">
		<h1>{$shop['csp_name']}</h1>
		<h2>{$inspect_set['title_examinator']}：{$inspect['m_username']}</h2>
	</header>

	<div class="body mod_common_list_style">
		<div class="shoulder">
			<h1>{$inspect['_created_ymd']} 巡店记录</h1>
			<h2>{$shop['csp_address']}</h2>
			<div class="score">
				<div>
					<em>{$total_score}<i>{if 0 < $inspect_set['score_rule_diy']}%{else}分{/if}</i></em>
					<p>{if 0 < $inspect_set['score_rule_diy']}合格率{else}总得分{/if}</p>
				</div>
			</div>
		</div>
		<ul class="mod_common_list">
			{foreach $items['p2c'][0] as $_id}
			<li>
				<a class="m_link" href="/frontend/inspect/viewscore/ins_id/{$inspect['ins_id']}/insi_id/{$_id}">
					<label>{$items[$_id]['insi_name']}</label>{$item2score[$_id]}{if 0 < $inspect_set['score_rule_diy']}%{else}分{/if}
				</a>
			</li>
			{/foreach}
		</ul>
	</div>

	<div class="foot numbtns double">
		<input id="btn_go_back" type="reset" value="返回" />
		<input id="transfor" type="submit" value="转发" />
	</div>

	<form name="inspect_post" id="inspect_post" method="post" action="/frontend/inspect/transmit/?handlekey=post" style="display:none;">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<input type="hidden" name="ins_id" value="{$inspect['ins_id']}" />
	<h1>接收人</h1>
	<fieldset>
		{include file='frontend/mod_cc_select.tpl' iptname='cc_uids' ccusers=$accepters}
	</fieldset>

	<div class="foot numbtns single">
		<input type="submit" value="提交" />
	</div>
	</form>
</section><menu class="mod_members_panel"></menu></div>

<script type="text/javascript">
{literal}
require(['dialog', 'members', 'business'], function() {
	//表单校验
	$one('form').addEventListener('submit', function(e) {
		var cc_ipt = $one('#cc_uids');
		
		e.preventDefault();
		if (!cc_ipt.value || !$trim(cc_ipt.value).length) {
			MDialog.notice('接收人至少选择一个!');
			return false;
		}
		
		aj_form_submit('inspect_post');
	});

	$one('#transfor').addEventListener('click', function(e) {
		if (1 == $data(this, 'show')) {
			$hide($one('#inspect_post'));
			$data(this, 'show', 0);
		} else {
			$show($one('#inspect_post'));
			$data(this, 'show', 1);
		}
	});
});

function errorhandle_post(url, msg) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg) {
	MStorageForm.clear();
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}
{/literal}
</script>

{include file='frontend/footer.tpl'}
