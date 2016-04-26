{include file='frontend/header.tpl'}

<body id="wbg_mp_new">

<form name="namecard_{$ac}" id="namecard_{$ac}" method="post" autocomplete="off" action="{$form_action}">
	<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
	<h1>个人信息：</h1>
	<ul class="mod_common_list part1">
		<li>
			<label>姓名：</label><input name="realname" id="realname" type="text" value="{$namecard['_realname']}" storage required autofocus />
		</li>
		<li>
			<label>微信：</label><input name="wxuser" id="wxuser" type="text" value="{$namecard['_wxuser']}" storage />
		</li>
		<li>
			<label>职位：</label><input name="job" id="job" type="text" value="{$job['_name']}" storage />
		</li>
		<li>
			<label>分组：</label>
			<select name="ncf_id" id="ncf_id">
				<option value="0">默认分组</option>
				{foreach $folders as $v}
				<option value="{$v['ncf_id']}"{if $v['ncf_id'] == $namecard['ncf_id']} selected{/if}>{$v['_name']}</option>
				{/foreach}
			</select>
		</li>
	</ul>
	
	<a href="javascript:;" class="newGroup" id="new_group" rel="/namecard/folder/new?handlekey=new_group">+ 新建分组</a>
	
	<h1>联系方式：</h1>
	<ul class="mod_common_list part3">
		<li>
			<label>手机：</label><input name="mobilephone" id="mobilephone" type="text" value="{$namecard['nc_mobilephone']}" storage required />
		</li>
		<li>
			<label>座机：</label><input name="telephone" id="telephone" type="text" value="{$namecard['nc_telephone']}" storage />
		</li>
		<li>
			<label>Email：</label><input name="email" id="email" type="text" value="{$namecard['nc_email']}" storage />
		</li>
	</ul>
	
	<h1>公司信息：</h1>
	<ul class="mod_common_list part3">
		<li>
			<label>公司：</label><input name="company" id="company" type="text" value="{$company['_name']}" storage />
		</li>
		<li>
			<label>地址：</label><input name="address" id="address" type="text" value="{$namecard['_address']}" storage />
		</li>
		<li>
			<label>邮编：</label><input name="postcode" id="postcode" type="text" value="{$namecard['nc_postcode']}" storage />
		</li>
	</ul>
	
	<h1>其他信息：</h1>
	<div class="mod_common_list_style remarks">
		<textarea name="remark" id="remark" storage>{$namecard['_remark_escape']}</textarea>
	</div>
	
	<footer>
		<input id="btn_go_back" type="reset" value="取消" /><input type="submit" name="sbtpost" value="保存" />
	</footer>
</form>

{include file='frontend/mod_textarea_dialog.tpl' scriptid='newGroupDialogTmpl' formid='new_group_post' textareaname='ncf_name'}

<script>
var _formname = 'namecard_{$ac}';
{if 'new' == $ac}MStorageForm.init(_formname);{/if}
{literal}
require(['dialog', 'members', 'business'], function() {
	$onload(function() {
		$one('form').onsubmit = function(e) {
			var rem = new RegExp("^0?1[3|5|8]\\d{9}$", 'ig'),
				ret = new RegExp("^0(([1-9]\\d)|([3-9]\\d{2}))?\\d{7,8}$"),
				ree = new RegExp("^\\w+@\\w+\\.\\w+$", 'ig'),
				req = new RegExp("^[1-9]\\d{4,9}$", 'ig'),
				ipt_realname = $one('#realname'),
				ipt_mobilephone = $one('#mobilephone');

			e.preventDefault();
			if (!ipt_realname.value || !$trim(ipt_realname.value).length) {
				MDialog.notice('请填写姓名!');
				return false;
			}

			if (ipt_mobilephone.value && !rem.test(ipt_mobilephone.value)) {
				MDialog.notice('请正确填写手机号码!');
				return false;
			}

			if (true == ajax_form_lock) {
				return false;
			}

			ajax_form_lock = true;
			MLoading.show('稍等片刻...');
			MAjaxForm.submit(_formname, function(result) {
				MLoading.hide();
			});

			return false;
		}
	});
	
	/** 创建新组 */
	$one('#new_group').addEventListener('click', function(e) {
		_m_ta_d_show_dialog(e, '请填写新群组的名称', '添加群组');
	});
});

/** 新增群组的回调 */
function errorhandle_new_group(url, msg, js) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_new_group(url, msg, js) {
	var re = new RegExp("ncf_name=(.*?)&ncf_id=(\\d+)", "i");
	var matches = url.match(re);
	$one('#ncf_id').options.add(new Option(matches[1], matches[2]));
	$one('#ncf_id').options[$one('#ncf_id').options.length - 1].selected = true;
}

/** 新增名片的回调 */
function errorhandle_post(url, msg, js) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg, js) {
	MStorageForm.clear();
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}
{/literal}
</script>


{include file='frontend/footer.tpl'}