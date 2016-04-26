{include file='frontend/header.tpl'}

<body id="wbg_msq_checkin">
{literal}
<style type="text/css">
.mod_button1{
	margin:10px auto 0;width:300px;display:block;
}
.llist{margin-top:25px;font-size:15px;border-bottom:2px solid rgba(169, 169, 169, 0.5)}
	.llist thead th{width:40px;padding-left:20px;white-space:nowrap;padding-right:20px}
	.llist tr{border-bottom:1px solid rgba(220,227,230,0.6);cursor:pointer;}
	.llist th{width:40px;padding-left:20px;white-space:nowrap;padding-right:20px}
	.llist th
		,.llist td{height:40px;vertical-align:middle;font-weight:normal}
	
	.llist tbody td
		,.llist th.handle{text-align:left;padding-left:0}
	.llist .toggle{width:40px;padding-left:0;}

	.llist em{display:block;width:25px;height:40px;margin:0;padding:0;}
		.llist em.close{background:url(/misc/images/arrow_right.png) no-repeat 0 10px;}
		.llist em.open{background:url(/misc/images/arrow_down.png) no-repeat 0 10px}
.new_head{height:26px !important;background-position:0 -202px !important;}
</style>
{/literal}
<header class="mod_profile_header type_A new_head">
	<div>
		<time>{$cur_m}<i>月</i>{$cur_d}<i>日</i> {$weeknames[$cur_w]}</time>
	</div>
</header>

<ul class="mod_common_list" >
	<li>
		<div>上班<time>{$sign_set['work_begin_hi']}</time></div>
		<time>{$on_signtime_hi}&nbsp;</time>
		{if $allow_sign}
			{if empty($work_on)}
		<a href="javascript:;" id="a_sign" name="a_sign" data-st="on" style="background: #009900;color: #fff;">签到</a>
			{else}
		<span style="font-size:14px">{$work_on['sr_address']}</span>
			{/if}
		{else}
		<span style="font-size:14px">非工作日不需要签到</span>
		{/if}
	</li>
	<li>
		<div>下班<time>{$sign_set['work_end_hi']}</time></div>
		<time class="off">{$off_signtime_hi}&nbsp;</time>
		{if $allow_sign}
			{if empty($work_off) && !empty($work_on)}
		<a href="javascript:;" id="a_sign" name="a_sign" data-st="off" style="background: #009900;color: #fff;">签退</a>
			{else}
		<span style="font-size:14px">{$work_off['sr_address']}</span>
			{/if}
		{else}
		<span style="font-size:14px">非工作日不需要签到</span>
		{/if}
	</li>
</ul>

{if $allow_sign}
<ul class="mod_common_list" style="margin-top: 15px;height:{$signdetail_box_height}px">
	<li class="cont">
		<label>备注:</label><textarea required name="reason" disabled id="reason"  storage style="color:#333;text-align:left;height:{$signdetail_height}px">{$sign_detail['sd_reason']}</textarea>
	</li>
</ul>

<div class="foot">
	{if $sign_detail['sd_reason']}
		<a  class="mod_button1" id="reply" href="javascript:void(0)" rel="/frontend/sign/reply/sr_id/{$sr_id}?handlekey=post" >修改备注</a>	
	{else}
		<a  class="mod_button1" id="reply" href="javascript:void(0)" rel="/frontend/sign/reply/sr_id/{$sr_id}?handlekey=post" >添加备注</a>
	{/if}
</div>
{/if}

{if $location}
<table class="mod_common_list llist">
	{foreach $location as $r}
		{if $r@index == 0}
	<thead>
		<tr onclick="javascript:location_toggle('open');">
			<th>{$r['_signtime_hi']}</th>
			<td class="handle">{$r['sl_address']}</td>
			<td class="toggle"><em id="toggle-icon" class="close">&nbsp;</em></td>
		</tr>
	</thead>
	{if count($location) > 1 || count($location) == 1}<tbody id="tbody" style="display:none">{/if}
		{else}
		<tr>
			<th>{$r['_signtime_hi']}</th>
			<td colspan="2">{$r['sl_address']}</td>
		</tr>
		{/if}
	{/foreach}
	{if count($location) > 1 || count($location) == 1}</tbody>{/if}
</table>
{/if}

<div class="foot">
	<a  class="mod_button1" href="javascript:;" id="a_up" name="a_up" data-st="up" >上报地理位置</a>
</div>

<script type="text/moatmpl" id="dialogTmpl">
{if $allow_sign}
	<h1>备注</h1>
	<form name="frmpost" id="frmpost" method="post" action="">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<textarea name="message" type="text" required>{$sign_detail['sd_reason']}</textarea>
		<footer>
			<input type="reset" value="取消" />
			<input type="submit" value="确定" />
		</footer>
	</form>
{/if}
</script>

{include file='frontend/footer_nav.tpl'}

<script>
var _off_hi = '{$sign_set['work_end_hi']}';
var toggle = 'open';
var js_location = {};

window.onload=function(){
	wx.ready(function () {
		_get_location();
	});
};

function location_toggle() {
	document.getElementById('toggle-icon').className = toggle;
	if (toggle == 'open') {
		document.getElementById('tbody').style.display = '';
		
		toggle = 'close';
	} else {
		document.getElementById('tbody').style.display = 'none';
		toggle = 'open';
	}
}
{literal}
require(['dialog'], function() {
	$onload(function() { //问候语
		var hello = {
			'5:00-10:00': '早上好!',
			'10:00-11:30': '上午好!',
			'11:30-13:30': '中午好!',
			'13:30-18:00': '下午好!',
			'18:00-5:00': '晚上好!'
		};
		var inRange = function(range) {
			var now = (new Date).getTime();
			var r = range.split('-');
			r = r.map(function(ele, idx, arr) {
				var t = ele.split(":");
				return {h: parseInt(t[0]), m: parseInt(t[1])};
			});
			var robj = {begin: r[0], end: r[1]};
			var begin = new Date;
			var end = new Date;
			begin.setHours(robj.begin.h);
			begin.setMinutes(robj.begin.m);
			end.setHours(robj.end.h);
			end.setMinutes(robj.end.m);
			if (now >= begin.getTime() && now <=end.getTime()) {
				return true;
			}
			return false;
		};

		$one('#a_sign') && $one('#a_sign').addEventListener('click', _sign_location);
		$one('#a_up') && $one('#a_up').addEventListener('click', _sign_location);
	});
});

/** 签到操作 */
function _sign_location(e) {
	var ha = e.currentTarget;
	e.preventDefault();
	if (ajax_form_lock) return false;
	
	/** 计算下班时间 */
	var d = new Date();
	var cur_ts = d.getTime();
	var hi = _off_hi.split(':');
	var y = d.getFullYear();
	var m = d.getMonth();
	var d = d.getDate();
	d = new Date(y, m, d, hi[0], hi[1], 0);
	
	if ('off' == $data(ha, 'st') && cur_ts < d.getTime()) {
		MDialog.confirm('签退', '还未到下班时间, 确定需要签退?', null, '取消', function(ebtn) {
			/***/
		}, null, '确定', function(ebtn) {
			_sign_ajax();
		}, null, null, false);
		return false;
	}
	
	if ('off' == $data(ha, 'st') || 'on' == $data(ha, 'st')) {
		_sign_ajax();
	} else {
		_uplocation_ajax();
	}
	
	return false;
}

function _sign_ajax() {
	ajax_form_lock = true;
	MLoading.show('稍等片刻...');
	if (typeof(js_location.latitude) != 'undefined') {
		MAjaxForm.analog('/frontend/sign/location?handlekey=post', js_location, 'post', function (s) {
			ajax_form_lock = false;
			MLoading.hide();
		});
	} else {
		_get_location(function(){
			MAjaxForm.analog('/frontend/sign/location?handlekey=post', js_location, 'post', function (s) {
				ajax_form_lock = false;
				MLoading.hide();
			});
		});
	}
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

require([ 'dialog', 'business'], function() {
	/** 显示输入框 */
	function _show_form(e, tip) {
		var html = $one('#dialogTmpl').innerHTML;
		var dlg = MDialog.popupCustom(html, false, null, true);
		dlg.id = 'commentVerifyDlg';
		dlg.style.left = .5 * (window.innerWidth - dlg.clientWidth) + 'px';
		$one('form', dlg).setAttribute('action', e.currentTarget.rel);
		$one('textarea', dlg).setAttribute('placeholder', tip);
		$one('input[type=reset]', dlg).addEventListener('click', function(e2) {
			MDialog.close();
		});
		$one('#frmpost').onsubmit = function(e) {
			if (true == ajax_form_lock) {
				e.preventDefault();
				return false;
			}

			ajax_form_lock = true;
			MLoading.show('稍等片刻...');
			MAjaxForm.submit('frmpost', function(result) {
				MLoading.hide();
			});
			MDialog.close();
		};
	}
	
	$onload(function() {
		$one('#reply') && $one('#reply').addEventListener('click', function(e) { /** 我要留言 */
			_show_form(e, '请填写内容');
		});
	});
});

function _get_location(callback) {
	wx.getLocation({
		"success": function (res) {
			js_location = {
				"latitude": res.latitude,
				"longitude": res.longitude
			};
		},
		"complete": function(res) {
			if (typeof(callback) != 'undefined') {
				callback();
			}
		}
	});
}

function _uplocation_ajax() {
	ajax_form_lock = true;
	MLoading.show('稍等片刻...');
	
	if (typeof(js_location.latitude) != 'undefined') {
		MAjaxForm.analog('/frontend/sign/uplocation?handlekey=post', js_location, 'post', function (s) {
			ajax_form_lock = false;
			MLoading.hide();
		});
	} else {
		_get_location(function(){
			MAjaxForm.analog('/frontend/sign/uplocation?handlekey=post', js_location, 'post', function (s) {
				ajax_form_lock = false;
				MLoading.hide();
			});
		});
	}
}

{/literal}
</script>

{include file='frontend/footer.tpl'}