{include file='frontend/header.tpl'}

<body id="wbg_crm_profile">

<header class="mod_profile_header type_A">
	<div class="center">
		<figure><img src="{$cinstance->avatar($uid)}" /></figure>
		<h1>{$wbs_username}<span>{$jobs[$wbs.cj_id].cj_name}</span></h1>
		<h2>畅移云工作－CRM</h2>
	</div>
	<ul>
		<li><a href="/datum"><b>{$ct_datum}</b>名片夹</a></li>
		<li><a href="/datum/folder"><b>{$ct_folder}</b>文件夹管理</a></li>
		<li><a href="/datum/new"><i class="icon build"></i>新的名片</a></li>
	</ul>
</header>

<div style="position: relative;">
	<dl>
		{foreach $list as $f}
		<dd>
			<div class="state_r">
				<label>{$f['dtf_name']}</label><span>({$f['dtf_num']})</span>
				<a href="javascript:;" class="rm rm_folder" rel="/datum/folder/delete/{$f['dtf_id']}?handlekey=post">删除</a>
				<a href="javascript:;" class="edit edit_folder" rel="/datum/folder/edit/{$f['dtf_id']}?handlekey=post" class="edit">编辑</a>
			</div>
			<div class="state_w" id="" hidden>
				<input type="text" value="{$f['dtf_name']}" autocomplete="off" maxlength="12" required /><a class="update" href="javascript:void(0)">√</a>
			</div>
		</dd>
		{/foreach}
	</dl>
	<a href="javascript:void(0)" id="new_folder" rel="/datum/folder/new?handlekey=post" class="addgroup">增加新文件夹</a>
</div>

<script type="text/moatmpl" id="newGroupDialogTmpl">
	<form name="frmpost" id="frmpost" method="post" action="">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<textarea name="dtf_name" class="dtf_name" placeholder="请填写文件夹的名称"></textarea>
		<footer><input type="reset" value="取消" /><input type="submit" value="确定" /></footer>
	</form>
</script>

<script>
{literal}
var _valid_group_notice = '请填写文件夹名';
$onload(function() {
	/** 增加 */
	$one('a.addgroup').addEventListener('click', function(e) {
		_show_form(e, '请输入文件夹名称');
	});

	$each($all('a.edit_folder'), function(href) {
		href.addEventListener('click', function(e) {
			var lbl = $one('label', href.parentNode);
			_show_form(e, '文件夹名称不能为空', lbl.innerHTML);
		});
	});

	$each($all('a.rm_folder'), function(href) {
		href.addEventListener('click', function(e) {
			_del_group(e);
		});
	});
});

/** 删除组 */
function _del_group(e) {
	var href = e.currentTarget;
	MDialog.confirm('取消', '您确定要删除该文件夹吗?', null, '取消', null, null, '确定', function(ebtn) {
		MLoading.show('稍等片刻...');
		MAjaxForm.analog(href.rel, null, 'post', function (s) {
			var dd = href.parentNode.parentNode;
			dd.parentNode.removeChild(dd);
			ajax_form_lock = false;
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

/** 显示输入框 */
function _show_form(e, tip, dtf_name) {
	var html = $one('#newGroupDialogTmpl').innerHTML;
	var dlg = MDialog.popupCustom(html, false, null, true);
	dlg.id = 'commentVerifyDlg';
	dlg.style.left = .5 * (window.innerWidth - dlg.clientWidth) + 'px';
	$one('form', dlg).setAttribute('action', e.currentTarget.rel);
	$one('textarea.dtf_name', dlg).setAttribute('placeholder', tip);
	if ('undefined' != typeof(dtf_name)) {
		$one('textarea.dtf_name', dlg).value = dtf_name;
	}
	$one('input[type=reset]', dlg).addEventListener('click', function(e2) {
		MDialog.close();
	});
	$one('#frmpost').onsubmit = function(e) {
		e.preventDefault();
		if (true == ajax_form_lock) {
			return false;
		}

		ajax_form_lock = true;
		MLoading.show('稍等片刻...');
		MAjaxForm.submit('frmpost', function(result) {
			ajax_form_lock = false;
			MLoading.hide();
		});

		MDialog.close();
	};
}

function errorhandle_post(url, msg) {
	ajax_form_lock = false;
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