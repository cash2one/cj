{include file='frontend/header.tpl'}

<body id="wbg_spl_launch">

<div class="center"><div id="viewstack"><section>
	<form name="frmpost" id="frmpost" method="post" action="/thread/share/{$t_id}?handlekey=post">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<input type="hidden" name="referer" value="{$refer}" />
		<fieldset>
			<h1>分享:</h1>
			<input name="shareuids" id="shareuids" type="hidden" value="{$uidstr}" />
			<div class="mod_members to">
				<ul class="box" id="ul_list">
					{foreach $tpus as $v}
					<li id="{$v['m_uid']}"><a class="rm" href="javascript:void(0)">取消参会</a><img src="{$cinstance->avatar($v['m_uid'])}" />{$v['m_username']}</li>
					{/foreach}
					<li><a id="mem_add" class="add" href="javascript:void(0)">添加</a></li>
				</ul>
			</div>
		</fieldset>
		<footer><a id="sbta" class="mod_button1">分享</a></footer>
	</form>
</section><menu class="mod_members_panel" id="mod_members_panel"></menu></div></div>

<script>
{literal}
$onload(function() {
	/** 发布转审批 */
	$one('#sbta').addEventListener('click', function(e) {
		$one('form').onsubmit(e);
	});

	/** 表单校验 */
	$one('form').onsubmit = function(e) {
		MLoading.show('稍等片刻...');
		MAjaxForm.submit('frmpost', function(result) {
			MLoading.hide();
		});

		return true;
	};
});

function errorhandle_post(url, msg) {
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg) {
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}

var ajaxLock = false;

$onload(function() {
	/** 增加分享人 */
	var _mem_share = new MemberSelect();
	_mem_share.init('mem_add', {
		id_panel:'mod_members_panel',
		id_view_stack:'viewstack',
		id_uids:'shareuids',
		id_ul_container:'ul_list',
		multi:true
	});
	_mem_share.update_uids();
});
{literal}
</script>
</body>

{include file='frontend/footer.tpl'}