{include file='frontend/header.tpl'}

<body id="wbg_xsgj_new">

<div id="viewstack"><section>
	<form name="footprint_post" id="footprint_post" method="post" action="{$form_action}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<h1 class="hd1" style="display:none;">我的足迹</h1>
	<div class="geo" hidden>
		<div class="center">
			<span>定位失败请手动获取 <a href="如何手动发送位置.html">帮助</a></span>
			<!--<span>上海市普陀区宁夏路322号</span>-->
		</div>
	</div>
	
	<h1 class="hd2">照片记录</h1>
	<fieldset class="mod_common_list_style">
	{include file='frontend/mod_upload.tpl' iptname='at_ids' iptvalue=$at_ids}
	</fieldset>
	
	<h1 class="hd3">客户信息</h1>
	<ul class="mod_common_list infos">
		<li>
			<label>客户名称：</label><input type="text" id="subject" name="subject" value="" required placeholder="请填写" />
		</li>
		<li class="time">
			<label>访问时间：</label>
			{include file='frontend/mod_ymdhi_select.tpl' iptname='time'}
		</li>
		<li class="tags">
			<label>本次事项：</label>
			<p>
				{foreach $types as $k => $v}
				<label><input name="type" type="radio" value="{$k}" /><span>{$v}</span></label>
				{/foreach}
				<a class="custom" href="javascript:void(0)">自定义</a>
			</p>
		</li>
	</ul>
	
	<h1>接收人：</h1>
	<fieldset class="mod_common_list_style">
	{include file='frontend/mod_cc_select.tpl' iptname='carboncopyuids' ccusers=$ccusers}
	</fieldset>
	
	<div class="foot numbtns double"><input type="submit" value="保存" /></div>
	</form>
</section><menu class="mod_members_panel"></menu></div>

<script type="text/moatmpl" id="dialogTmpl">
	<h1>自定义事项</h1>
	<form onsubmit="return false;">
		<fieldset>
			<label>事项: </label><input name="" type="text" placeholder="不超过10个字" maxlength="10" required />
		</fieldset>
		<footer>
			<input type="reset" value="取消" />
			<input type="submit" value="确定" />
		</footer>
	</form>
</script>

<script>
{literal}
require(['dialog', 'business', 'formvalidate', 'timeslider'], function() {
	//提交
	$onload(function() {
		//表单校验
		$one('form').addEventListener('submit', function(e) {
			var subject_ipt = $one('#subject'),
				time_ipt = $one('.time input');
			
			e.preventDefault();
			
			if (!subject_ipt.value || !$trim(subject_ipt.value).length) {
				MDialog.notice('请填写客户名字!');
				return false;
			}
			
			if (!time_ipt.value || !$trim(time_ipt.value).length) {
				MDialog.notice('请选择时间!');
				return false;
			}
			
			var is_select = false;
			$each($all('input', $one('form')), function(chk) {
				if ('type' == chk.name && chk.checked) {
					is_select = true;
					return true;
				}
			})
			
			if (false == is_select) {
				MDialog.notice('请选择类别!');
				return false;
			}
			
			aj_form_submit('footprint_post');
		});
		
		//自定义标签
		$one('.tags a.custom').addEventListener('click', function(e) {
			var lk = e.currentTarget;
			
			var html = $one('#dialogTmpl').innerHTML;
			var dlg = MDialog.popupCustom(html, false, null, true);
			dlg.id = 'cancelMeetingDlg';
			dlg.style.left = .5 * (window.innerWidth - dlg.clientWidth) + 'px';
			$one('input[type=reset]', dlg).addEventListener('click', function(e){
				MDialog.close();
			});
			$one('input[type=submit]', dlg).addEventListener('click', function(e){
				var ipt = $one('#cancelMeetingDlg input[type=text]');
				if (!MOA.form.FormValidate.requiredValid(ipt)){
					MDialog.close();
					return;	
				}
				$hide(lk);
				MDialog.close();
				
				var v = $trim(ipt.value);
				var nm = $one('.tags input[type=radio]:first-of-type').name;
				var tmpl = '<label id="customLabel"><input name="'+nm+'" type="radio" checked value="'+v+'" /><span>'+v+'</span><i class="rm">-</i></label>';
				$append($one('.tags p'), tmpl);
				var cl = $one('#customLabel'),
					cl_sp = $one('span', cl),
					cl_rd = $one('input[type=radio]', cl),
					cl_rm = $one('.rm', cl);
				cl_rd.style.width = cl.style.width = cl_sp.clientWidth + 'px';
				cl_rm.addEventListener('click', function(e2){
					e2.stopPropagation();
					cl.parentNode.removeChild(cl);
					$show(lk);
				});
			});
			
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

{include file='frontend/footer.tpl'}