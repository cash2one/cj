{include file='frontend/header.tpl'}

<body id="wbg_mp_detail">

<header>
	<h1>{$namecard['_realname']}&nbsp;</h1>
	<h2>{$job['_name']}&nbsp;</h2>
	<ul>
		<li>手机：{$namecard['nc_mobilephone']}&nbsp;</li>
		<li>座机：{$namecard['nc_telephone']}&nbsp;</li>
		<li>邮箱：{$namecard['nc_email']}&nbsp;</li>
	</ul>
	<h3>{$company['_name']}&nbsp;</h3>
</header>

<ul>
	<li><label>地址：</label>{$namecard['_address']}&nbsp;</li>
	<li><label>邮编：</label>{$namecard['nc_postcode']}&nbsp;</li>
	<li><label>其他信息：</label>{$namecard['_remark']}&nbsp;</li>
	{if $attach}
	<li><label>名片拍摄：</label><a class="thumb" href="javascript:void(0)"><img src="/attachment/read/{$attach['at_id']}/45?ts={$timestamp}&sig={voa_h_attach::attach_sig_create($attach['at_id'])}" data-big="http://{$domain}/attachment/read/{$attach['at_id']}/640" /></a></li>
	{/if}
</ul>

<div class="foot">
	<time>上传于：{$namecard['nc_created']}</time>
</div>
<div class="numbtns single">
	<input type="submit" value="编辑" onclick = "window.location.href = '/namecard/edit/{$namecard['nc_id']}' "/>
	<input type="submit" value="删除" class="rm_namecard" ref = "/namecard/delete/{$namecard['nc_id']}/?handlekey=del " />
  	<input type="reset" value="返回" onclick = "javascript:history.go(-1);"/>
</div>
<script>
var _nc_id = {$nc_id};
var _form_hash = '{$formhash}';
{literal}

require(['dialog', 'members', 'business'], function() {
	$onload(function() {
		if ($one('.thumb')) {
			$one('.thumb').onclick = function(e) {
				var m = MDialog.popupImage(
					$data($one('.thumb img'), 'big'),
					window.innerWidth - 18,
					false,
					true,
					function(img) { //微信中的webview永远是正常方向
						if (img.width > img.height) {
							var f1 = (window.innerHeight - 50) / img.width;
							var f2 = (window.innerWidth - 18) / img.height;
							var fm = Math.min(f1, f2);
							m.style[MOA.translate.vendor + 'Transform'] = 'rotate(90deg) scale('+fm+')';
						}
					},
					function() {}
				);
				$data(m, 'closeByModal', 1);
			};
		}
		
		$one('.rm_namecard').addEventListener('click', function(e) {
			_del_namecard(e);
		});
	});
});

/** 删除名片 */
function _del_namecard(e) {
	var href = e.currentTarget;
	MDialog.confirm('取消', '您确定要删除该名片吗?', null, '取消', null, null, '确定', function(ebtn) {
		MLoading.show('稍等片刻...');
		MAjaxForm.analog(href.getAttribute("ref"), null, 'post', function (s) {
			var dd = href.parentNode;
			dd.parentNode.removeChild(dd);
			ajax_form_lock = false;
			MLoading.hide();
		});
	}, null, null, false);
}

function errorhandle_del(url, msg) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_del(url, msg) {
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}
{/literal}
</script>



{include file='frontend/footer.tpl'}