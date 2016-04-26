{include file='frontend/header.tpl'}

<body id="wbg_bx_select">

<div id="viewstack"><section>
	<form name="reimburse_post" id="reimburse_post" method="post" action="{$form_action}?handlekey=post">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<ul class="mod_common_list">
		<li>
			<label>主题：</label>
			<input placeholder="请输入主题" required name="subject" id="subject" type="text" value="{$reimburse['_subject']}" storage />
		</li>
	</ul>
	<h1>报销明细：</h1>
	{if empty($bills)}
	<em class="mod_empty_notice mod_common_list_style"><span>没有相关报销的单据列表</span></em>
	{else}
	<ul class="mod_common_list">
		{foreach $bills as $k => $v}
		<li>
			<input name="rbb_id[]" value="{$v['rbb_id']}" type="checkbox" />
			<div>{$types[$v['rbb_type']]} {$v['_time_md']} <span>￥<i>{$v['_expend']}</i></span></div>
			<div class="info" hidden>
				<a href="/reimburse/bill/edit/{$v['rbb_id']}" class="edit">修改</a>
				<a href="javascript:;" class="del rm_bill" ref="/reimburse/bill/del/{$v['rbb_id']}">删除</a>
				{if $v['_attachs']}
				{$attachs = $v['_attachs']}
				<div class="mod_photo_uploader readonly">
					{include file='frontend/mod_img_list.tpl'}
				</div>
				{$attachs = array()}
				{/if}
				<p>{$v['_reason']}</p>
			</div>
		</li>
		{/foreach}
	</ul>
	{/if}
	<div class="total">总计：<i>￥</i><span>0.00</span></div>
	<a href="/reimburse/bill/new" class="newOne">+ 添加一条明细</a>
	
	<h1>审批人：</h1>
	<fieldset>{include file='frontend/mod_approver_select.tpl' iptname='approveuid' iptvalue=$approveuid}</fieldset>
	
	<div class="foot numbtns double">
		<input  type="button" onclick="formReset()"  value="取消" /><input type="submit" value="保存" />
	</div>
	</form>
</section><menu class="mod_members_panel"></menu></div>

<script>
{if 'new' == $action}MStorageForm.init('reimburse_post');{/if}
{literal}
function formReset() {
	$one("#reimburse_post").reset();
	var $sp = $one('.total span');
	var t = 0;
	$sp.innerHTML = t.toFixed(2);
}
require(['dialog', 'members', 'business'], function() {
	function calcTotal(e) { //只在前台显示总额，具体数值后台再计算一次
		var $sp = $one('.total span'),
			t = 0;
		$each('input[type=checkbox]', function(chk) {
			if (chk.checked) {
				var n = parseFloat( $one('i', $next(chk)).innerHTML );				
				t += n;
			}
		});
		$sp.innerHTML = t.toFixed(2);
	}

	$onload(function() {
		$each('li>div:first-of-type', function(div) {
			div.onclick = function(e) {
				var $div = e.currentTarget,
					$info = $one('.info', $div.parentNode);
				if ($data($div, 'opened') === 'yes') {
					$hide($info);
					$data($div, 'opened', '');
				} else {
					$show($info);
					$data($div, 'opened', 'yes');
					var $img = $one('img', $info);
					if (!$img.hasAttribute('src')) {
						$img.setAttribute('src', $data($img, 'src'));
					}
				}
			};
		});
		
		$each('input[type=checkbox]', function(chk) {
			chk.onclick = calcTotal;
		});
		
		$one('.rm_bill').addEventListener('click', function(e) {
			_del_bill(e);
		});

		$one('form').addEventListener('submit', function(e) {
			var subIpt = $one('#subject');
			var memIpt = $one('#approveuid');
			var isselected = false;
			e.preventDefault();
			if (!subIpt.value || !$trim(subIpt.value).length) {
				MDialog.notice('请填写主题!');
				return false;
			}
			
			$each('input[type=checkbox]', function(chk) {
				if (true == chk.checked) {
					isselected = true;
				}
				
				return;
			});
			
			if (false == isselected) {
				MDialog.notice('请选择需要报销的单据!');
				return false;
			}

			if (!memIpt.value || !$trim(memIpt.value).length) {
				MDialog.notice('请选择审批人!');
				return false;
			}
			
			if (true == ajax_form_lock) {
				return false;
			}

			ajax_form_lock = true;
			MLoading.show('稍等片刻...');
			MAjaxForm.submit('reimburse_post', function(result) {
				MLoading.hide();
			});
		});
	});
});

/** 删除明细 */
function _del_bill(e) {
	var href = e.currentTarget;
	MDialog.confirm('取消', '您确定要删除该明细吗?', null, '取消', null, null, '确定', function(ebtn) {
		MLoading.show('稍等片刻...');
		MAjaxForm.analog(href.getAttribute("ref"), null, 'post', function (s) {
			window.location.href = window.location.href;
			MLoading.hide();
		});
	}, null, null, false);
}

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