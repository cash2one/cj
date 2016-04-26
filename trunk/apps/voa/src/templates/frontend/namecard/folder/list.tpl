{include file='frontend/header.tpl'}

<body id="wbg_mp_editgroups">

<ul class="mod_common_list">
	{foreach $list as $f}
	<li id="{$f['ncf_id']}">
		<span>{$f['ncf_name']}</span>
		<a class="up edit_folder" href="javascript:void(0)" rel="/namecard/folder/edit/{$f['ncf_id']}?handlekey=post">编辑</a>
		<a class="rm rm_folder" href="javascript:void(0)" rel="/namecard/folder/delete/{$f['ncf_id']}?handlekey=del">删除</a>
	</li>
	{/foreach}
	<li>默认分组</li>
</ul>
<a class="new addgroup" href="javascript:void(0)" rel="/namecard/folder/new?handlekey=post">新建分组</a>

{include file='frontend/mod_textarea_dialog.tpl' scriptid='newGroupDialogTmpl' formid='frmpost' textareaname='ncf_name'}

<script>
{literal}
var _ajax_form_lock = false;
require(['dialog', 'members', 'business'], function() {
	$onload(function() {
		/** 增加 */
		$one('a.addgroup').addEventListener('click', function(e) {
			_m_ta_d_show_dialog(e, '请填写新分组的名称', '添加分组');
		});

		$each($all('a.edit_folder'), function(href) {
			href.addEventListener('click', function(e) {
				var lbl = $one('span', href.parentNode);
				_m_ta_d_show_dialog(e, '分组名称不能为空', '编辑分组', lbl.innerHTML);
			});
		});

		$each($all('a.rm_folder'), function(href) {
			href.addEventListener('click', function(e) {
				_del_group(e);
			});
		});
	});
});

/** 删除组 */
function _del_group(e) {
	var href = e.currentTarget;
	MDialog.confirm('删除分组', '您确定要删除该组吗?', null, '取消', null, null, '确定', function(ebtn) {
		MLoading.show('稍等片刻...');
		MAjaxForm.analog(href.rel, null, 'post', function (s) {
			var dd = href.parentNode;
			dd.parentNode.removeChild(dd);
			_ajax_form_lock = false;
			MLoading.hide();
		});
	}, null, null, false);
}

function errorhandle_del(url, msg) {
	MDialog.notice(msg);
}

function succeedhandle_del(url, msg) {
	MDialog.notice(msg);
}

function errorhandle_post(url, msg) {
	_ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg) {
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}

{/literal}
</script>

{include file='frontend/footer.tpl'}