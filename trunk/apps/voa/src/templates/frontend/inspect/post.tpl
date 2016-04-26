{include file='frontend/header.tpl'}

<body id="wbg_xd_send">

<div id="viewstack"><section>
	<form name="inspect_post" id="inspect_post" method="post" action="{$form_action}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<input type="hidden" name="insd_id" value="{$insd_id}" />
		<h1 style="display:none;">我的位置</h1>
		<div class="geo" hidden>
			<div class="center">
				<span>定位失败请手动获取 <a href="如何手动发送位置.html">帮助</a></span>
				<!--<span>上海市普陀区宁夏路322号</span>-->
			</div>
		</div>
		
		<h1>门店名称</h1>
		<ul class="mod_common_list">
			<li>{$shop['csp_name']}</li>
		</ul>
		
		<h1>门店评估</h1>
		<ul class="totalScore mod_common_list">
			<li>
				<a href="/frontend/inspect/editem/ins_id/{$inspect['ins_id']}" class="m_link">
					<label>{if 0 < $inspect_set['score_rule_diy']}合格率{else}各项总分{/if}</label>
					<em>{$total}{if 0 < $inspect_set['score_rule_diy']}%{else}分{/if}</em>
					<span>查看评估指标</span>
				</a>
			</li>
		</ul>
	
		<h1>接收人</h1>
		<fieldset>
			{include file='frontend/mod_cc_select.tpl' iptname='mem_uids' ccusers=$accepters}
		</fieldset>
		
		<h1>抄送人</h1>
		<fieldset>
			{include file='frontend/mod_cc_select.tpl' iptname='cc_uids' ccusers=$cculist}
		</fieldset>
		
		<div class="foot numbtns double">
			<input id="btn_go_back" type="reset" value="取消" /><input type="submit" value="提交" />
		</div>
	</form>
</section><menu class="mod_members_panel"></menu></div>

<script>

{literal}
require(['dialog', 'members', 'business'], function() {
	//表单校验
	$one('form').addEventListener('submit', function(e) {
		var mem_ipt = $one('#mem_uids');
		
		e.preventDefault();
		if (!mem_ipt.value || !$trim(mem_ipt.value).length) {
			MDialog.notice('接收人至少选择一个!');
			return false;
		}
		
		aj_form_submit('inspect_post');
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
