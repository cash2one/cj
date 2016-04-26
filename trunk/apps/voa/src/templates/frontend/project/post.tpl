{include file='frontend/header.tpl'}

<body id="wbg_gzt_command">
<script src="{$wbs_javascript_path}/MOA.memberselect.js"></script>

<div id="viewstack"><section>
	<form name="proj_{$ac}" id="proj_{$ac}" method="post" action="{$form_action}">
		<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
		<input type="hidden" name="referer" value="{$refer}" />
		<input type="hidden" name="p_id" id="p_id" value="{$project['p_id']}" />
		<input type="hidden" name="pd_id" value="{$pd_id}" />
		<input type="hidden" name="sbtpost" id="sbtpost" value="true" />
		<fieldset>
			<div class="p_name">
				<label>任务名称:</label><input name="subject" id="subject" value="{$project['p_subject']}" type="text" required placeholder="请填写" storage />
			</div>
		</fieldset>

		<fieldset>
			<div class="p_describe">
				<label>任务说明:</label><textarea name="message" id="message" value="" placeholder="可选填内容" storage >{$project['p_message']}</textarea>
			</div>
		</fieldset>

		<ul class="mod_common_list mod_startend_time time" >
			<li class="time">
				<label>开始时间:</label>
				{include file='frontend/mod_ymdhi_select.tpl' iptvalue="{$start_selected}" iptname='begintime' }
			</li>
			<li class="time">
				<label>结束时间:</label>
				{include file='frontend/mod_ymdhi_select.tpl' iptvalue="{$end_selected}" iptname='endtime' }
			</li>
		</ul>

		{*{include file='frontend/mod_date_range.tpl' range_start=$range_start start_selected=$start_selected end_selected=$end_selected}*}
{if !empty($p_sets['upload_image'])}
		<h2>上传照片:</h2>
		<fieldset style="background:#FFF;border:1px solid rgba(169,169,169,0.6);border-width:1px 0 1px 0;">
			{include file='frontend/mod_upload.tpl' iptname='at_ids' iptvalue=$at_ids attach_total=$attach_total}
		</fieldset>
{/if}
		<h2>任务成员：</h2>
		<div class="members mod_common_list_style">
			<p><label>发起者参与任务</label><input type="checkbox" name="join" id="join" /></p>
			{include file='frontend/mod_cc_select.tpl' iptname='project_uids' ccusers=$accepters}
		</div>

		<h2>抄送人：</h2>
		<fieldset class="cc">
			{include file='frontend/mod_cc_select.tpl' iptname='cc_uids' ccusers=$cculist}
		</fieldset>

		<footer><a id="asbt" class="mod_button1">发布任务</a></footer>
	</form>
</section><menu id="mod_members_panel" class="mod_members_panel"></menu></div>

<script>
var _frm_name = 'proj_{$ac}';
{if 'new' == $ac}MStorageForm.init(_frm_name);{/if}
{literal}
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

/** 表单提交前端校验 */
function _onFormSubmit(e) {
	var sipt = $one('#subject'),
		uidipt = $one('#project_uids'),
		bFake = $one('#mod_datefaker_begintime'),
		eFake = $one('#mod_datefaker_endtime');
	if (!sipt.value || !$trim(sipt.value).length) {
		MDialog.notice('请填写任务主题!');
		e.preventDefault();
		return false;
	}

	if ($hasCls(bFake, 'init') || $hasCls(eFake, 'init')){
		MDialog.notice('请选择日期!');
		e.preventDefault();
		return false;
	}

	if (!uidipt.value || !$trim(uidipt.value).length) {
		MDialog.notice('请选择任务人员!');
		e.preventDefault();
		return false;
	}

	if (true == ajax_form_lock) {
		e.preventDefault();
		return false;
	}

	ajax_form_lock = true;
	MLoading.show('稍等片刻...');
	MAjaxForm.submit(_frm_name, function(result) {
		MLoading.hide();
	});

	return false;
}

require(['dialog', 'members', 'business'], function() {
	/** 时间设置 */
	var isLeapYear = function() { /** 是否闰年 */
			var year = (new Date).getFullYear();
		    return (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0)) ? 1 : 0;
		},
		daysRange = (isLeapYear() ? 367: 366),
		timeConfig = {
			rangeDays: daysRange, /** 时间范围的业务逻辑为: 今天至一年后 */
			noticeMin: '请选择晚于当前的时间!',
			noticeMax: '请选择'+ daysRange +'天以内的时间!'
		};

	$onload(function() {

		/** 表单校验 */
		$one('#asbt').addEventListener('click', function(e) {
			_onFormSubmit(e)
		});

		/** 时间选择 */
//		(function() {
//			var tul = $one('ul.time'),
//				bSel = $one('.begin select', tul),
//				eSel = $one('.end select', tul),
//				bFake = $prev(bSel),
//				eFake = $prev(eSel),
//				rt1 = $data(tul, 'rangeStartTailstr'),
//				rt2 = $data(tul, 'rangeEndTailstr'),
//				rstart = $data(tul, 'rangeStart'),
//				rleng = parseInt($data(tul, 'rangeLength')),
//				rstep = parseInt($data(tul, 'rangeStep'));
//
//			function fixNum(n) {
//				if (n<10) n='0'+n.toString();
//				return n;
//			}
//
//			function fmtDate(d, div) {
//				return [d.getFullYear(), fixNum(d.getMonth()+1), fixNum(d.getDate())].join(div||'/');
//			}
//
//			function fillSel(sel, day) {
//				var dstr = fmtDate(day);
//				$append(sel, '<option value="'+ dstr +'">'+ dstr +'&nbsp;</option>');
//			}
//
//			var arr = [], flag;
//			var day = MOA.utils.string2Date(rstart);
//			day.setDate(day.getDate() - 1);
//			flag = rleng + rstep;
//			while (flag--) {
//				day.setDate(day.getDate() + 1);
//				arr.push(day.toISOString());
//			}
//
//			var bArr = arr.slice(0, rleng),
//				eArr = arr.slice(rstep);
//
//			for (var i=0, lng = rleng; i < lng; i++) {
//				fillSel(bSel, MOA.utils.getFixedIOSDate(bArr[i]));
//				fillSel(eSel, MOA.utils.getFixedIOSDate(eArr[i]));
//			}
//
//			var checkAndRender = function(e) {
//				if ('_chkSelsTo' in window) clearInterval(window._chkSelsTo);
//				window._chkSelsTo = setTimeout(function() {
//					if (bSel.selectedIndex > eSel.selectedIndex) {
//						eSel.selectedIndex = bSel.selectedIndex;
//					}
//
//					$rmCls(bFake, 'init');
//					$rmCls(eFake, 'init');
//
//					bFake.innerHTML = bSel.options[bSel.selectedIndex].innerHTML + ' ' + rt1;
//					eFake.innerHTML = eSel.options[eSel.selectedIndex].innerHTML + ' ' + rt2;
//
//					clearInterval(window._chkSelsTo);
//					delete window._chkSelsTo;
//				}, 500);
//			}
//			MOA.event.listenSelectChange(bSel, checkAndRender);
//			MOA.event.listenSelectChange(eSel, checkAndRender);
//		}());
	});
});
{/literal}
</script>

{include file='frontend/footer_nav.tpl'}


{include file='frontend/footer.tpl'}
