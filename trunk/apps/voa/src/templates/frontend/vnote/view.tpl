{include file='frontend/header.tpl'}

<body id="wbg_bwl_new">

<form>
	<fieldset>
		<textarea readonly required name="">{$vnote['_message']}</textarea>
	</fieldset>

	<h1 style="float:left;">分享给:{foreach $ccusers as $u} &nbsp;{$u['m_username']}{/foreach}</h1>
	
	{if $vnote['m_uid'] == $wbs_uid}
	<div class="foot numbtns single">
		<input type="button" value="删除" id = "vn_delete" rel = "/vnote/delete/{$vnote['vn_id']}?handlekey=post" />
		<input type="button" value="编辑" onclick = "window.location.href = '/vnote/edit/{$vnote['vn_id']}' "/>
		<input type="reset" value="返回" onclick = "javascript:history.go(-1);"/>
	</div>
	<script>
	{literal}
	require(['dialog', 'members', 'business'], function() {
		$onload(function() {
			/** 不同意 */
			$one('#vn_delete').addEventListener('click', function(e) {
				MDialog.confirm('提示', '您确定要删除当前备忘内容吗?', null, '取消', null, null, '确定', function(e) {
					aj_form_analog($one('#vn_delete').getAttribute("rel"));
				}, null);
			});
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
	{/if}
</form>

{include file='frontend/footer.tpl'}