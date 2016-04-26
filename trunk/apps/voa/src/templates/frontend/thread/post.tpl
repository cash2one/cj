{include file='frontend/header.tpl'}

<link rel="stylesheet" href="/misc/styles/MOA.timeslider.css" />
<body id="wbg_bbs_launch">
<script src="{$wbs_javascript_path}/MOA.memberselect.js"></script>

<div id="viewstack"><section>
	<form name="thread_{$ac}" id="thread_{$ac}" method="post" autocomplete="off" action="{$form_action}">
		<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
		<fieldset class="basic">
			<div>
				<label>主题:</label><input name="subject" id="subject" type="text" value="{$thread['t_subject']}" placeholder="请填写" required storage />
			</div>
			<div>
				<label>内容:</label><textarea name="message" id="message" placeholder="请填写" required storage>{$thread['tp_message']}</textarea>
			</div>
		</fieldset>
		<fieldset class="time">
			<!--提醒时间会放入该hidden的value中-->
			<label>提醒时间:</label><input name="remindtime" id="remintime" value="{$thread['_rtime_hide']}" type="hidden" />
			<span class="fake ph">{if $thread['_rtime_show']}{$thread['_rtime_show']}{else}请选择/不选无提醒{/if}</span>
		</fieldset>
		<fieldset class="share">
			<!--uid会逗号分割依次放入该hidden的value中-->
			<label>分享:</label><input name="uids" id="uids" type="hidden" value="{$uids_str}" />
			<div class="mod_members">
				<ul class="box" id="ul_user_list">
					{foreach $permit_list as $v}
					<li id="{$v['m_uid']}"><a class="rm" href="javascript:void(0)">取消参会</a><img src="{$cinstance->avatar($v['m_uid'])}" />{$v['m_username']}</li>
					{/foreach}
					<li><a id="mem_add" class="add" href="javascript:void(0)">添加</a></li>
				</ul>
			</div>
		</fieldset>
		<input type="submit" name="sbtsend" id="sbtsend" value="发布新工作" />
	</form>
</section><menu id="mod_members_panel" class="mod_members_panel"></menu></div>

<script>
var _from_name = 'thread_{$ac}';
{if 'newthread' == $ac}MStorageForm.init('thread_{$ac}');{/if}
{literal}
/** 表单校验 */
$onload(function() {
	$one('form').onsubmit = function(e) {
		var projectIpt = $one('#subject'),
			contentTa = $one('#message'),
			timeIpt = $one('#remintime');

		if (!projectIpt.value || !$trim(projectIpt.value).length) {
			MDialog.notice('请填写主题!');
			e.preventDefault();
			return false;
		}

		if (!contentTa.value || !$trim(contentTa.value).length) {
			MDialog.notice('请填写内容!');
			e.preventDefault();
			return false;
		}

		if (true == ajax_form_lock) {
			e.preventDefault();
			return false;
		}

		ajax_form_lock = true;
		MLoading.show('稍等片刻...');
		MAjaxForm.submit(_form_name, function(result) {
			MLoading.hide();
		});

		return false;
	};
});

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
	/** 增加分享人 */
	var _mem_s = new MemberSelect();
	_mem_s.init('mem_add', {
		id_panel:'mod_members_panel',
		id_view_stack:'viewstack',
		id_uids:'uids',
		id_ul_container:'ul_user_list',
		multi:true
	});
	_mem_s.update_uids();

	/** 时间选择 */
	(function() {
		var timeHidd = $one('fieldset.time input[type=hidden]'),
			fake = $one('fieldset.time .fake'),
			d_today = new Date,
			d_min = d_today,
			d_max = (function() {
				var t = new Date;
				t.setHours(0);	t.setMinutes(0);	t.setSeconds(0);
				t.setTime(t.getTime() + timeConfig['rangeDays']* 24 * 60 * 60 * 1000);
				return t;
			}()),
			ensureNum = MOA.utils.ensureNumberStringLength,
			updateTime = function(e) {
				var d1 = null;
				if (!timeHidd.value.length) {
					d1 = d_today;
				} else {
					d1 = MOA.utils.getFixedIOSDate(timeHidd.value);
					if (d1.getTime() + 1 * 60 * 1000 < d_today.getTime()) {
						d1 = d_today;
						timeHidd.value = d1.toISOString();
						timeHidd.blur();
						if ('_hideDTPanel' in window) _hideDTPanel();
						alert(timeConfig['noticeMin']);
					} else if (d1.getTime() > d_max.getTime()) {
						d1 = d_max;
						timeHidd.value = d1.toISOString();
						timeHidd.blur();
						if ('_hideDTPanel' in window) _hideDTPanel();
						alert(timeConfig['noticeMax']);
					}
				}
				$data(timeHidd, 'selected', 1);
				$rmCls(fake, 'ph');
				fake.innerHTML = [
					d1.getMonth() + 1, '月',
					d1.getDate(), '日',
					' ',
					ensureNum(d1.getHours()), ':', ensureNum(d1.getMinutes())
				].join('');
			};

		fake.addEventListener('click', function(e) {
			_showDTPanel( d_today, timeConfig['rangeDays'], function callback(value){
				timeHidd.value = value.toISOString();
				updateTime();
			});
		});
	}());
});
{/literal}
</script>
</body>

{include file='frontend/footer.tpl'}
