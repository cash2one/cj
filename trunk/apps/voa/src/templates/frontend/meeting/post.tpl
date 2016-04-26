{include file='frontend/header.tpl'}

<style type="text/css">
{literal}
#roomsDlg:after {height:0px;}
{/literal}
</style>
<body id="wbg_hyt_launch">
<script src="{$wbs_javascript_path}/MOA.memberselect.js"></script>

<div id="viewstack"><section>
	<form name="meeting_{$ac}" id="meeting_{$ac}" method="post" action="{$form_action}" autocomplete="off">
		<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
		<input type="hidden" name="referer" value="{$refer}" />
		<input type="hidden" name="mr_id" id="mr_id" value="{$meeting['mr_id']}" />
		<input type="hidden" name="mtd_id" value="{$mtd_id}" />
		<h1>会议基本信息</h1>
		<fieldset class="basic">
			<div class="sponsor">
				<input type="hidden" name="sponsor" id="sponsor" value="{$wbs_uid}" />
				<label>发起:</label><span>{$wbs_username}</span><a id="chg_sponsor" href="javascript:void(0)" style="display:none;">更换</a>
			</div>
			<div class="dt" data-range="30"><!--range表示可以选择多少天之内的-->
				<label>日期:</label><select name="date" id="date"></select>
			</div>
			<div class="hr" data-default-from="0:00" data-default-end="23:59" data-default-delay="30"><!--默认起始时间 以及 持续时间(分钟)-->
				<label>时间:</label><select name="begin_hm" id="begin_hm"></select><span>到</span><select name="end_hm" id="end_hm"></select>
			</div>
			<div class="rooms">
				<label>会议室:</label><input name="roomname" id="roomname" type="text" value="{$room['mr_name']}" placeholder="请选择" required readonly />
				<span class="init">选择</span>
				<a class="trigger" href="javascript:void(0)"></a>
				<div hidden>
					<article style="height:180px;">
						<ul><!--b元素中的会议室名次会直接被放入input的value-->
							{foreach $rooms as $v}
							<li><i>{$volumes[$v['mr_volume']]}</i><id hidden>{$v['mr_id']}</id><b>{$v['mr_name']}</b><span>{$v['mr_galleryful']}人{if $v['mr_device']}|{$v['mr_device']}{/if}</span></li>
							{/foreach}
						</ul>
					</article>
				</div>
			</div>
		</fieldset>

		<h1>会议说明</h1>
		<fieldset class="info">
			<div class="project">
				<label>主题:</label><input name="subject" id="subject" type="text" value="{$meeting['mt_subject']}" placeholder="请填写主题" required storage />
			</div>
			<div class="content">
				<label>议题:</label><textarea name="message" id="message" placeholder="例如会议主要讨论内容" storage>{$meeting['mt_message']}</textarea>
			</div>
		</fieldset>

		<h1>参与人</h1>
		<fieldset>
			{include file='frontend/mod_cc_select.tpl' iptname='join_uids' ccusers=$ccusers}
		</fieldset>
		
		<div class="foot">
			<input id="btn_go_back" type="reset" value="取消" /><input type="submit" value="发起会议" />
		</div>
	</form>
</section>
<menu id="mod_members_panel" class="mod_members_panel"></menu></div>

<script>
var _bhi = '{$meeting['_bhi']}';
var _ehi = '{$meeting['_ehi']}';
var _ymd = '{$meeting['_ymd']}';
var _form_name = 'meeting_{$ac}';
var _current_uid = '{$wbs_uid}';
{if 'new' == $ac}MStorageForm.init('meeting_{$ac}');{/if}
{literal}
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

/** 表单提交前端校验 */
function _on_form_submit(e) {
	var ript = $one('#roomname'),
		pipt = $one('#subject'),
		ta = $one('#message'),
		uids = $one('#join_uids');
	e.preventDefault();
	if (!ript.value || !$trim(ript.value).length) {
		MDialog.notice('请选择会议室!');
		return false;
	}

	if (!pipt.value || !$trim(pipt.value).length) {
		MDialog.notice('请填写会议主题!');
		return false;
	}

	if (!uids.value || uids.value == _current_uid || !$trim(uids.value).length) {
		MDialog.notice('请选择参会人员!');
		return false;
	}

	if (true == ajax_form_lock) {
		return false;
	}

	ajax_form_lock = true;
	MLoading.show('稍等片刻...');
	MAjaxForm.submit(_form_name, function(result) {
		MLoading.hide();
	});

	return false;
}
require(['dialog', 'members', 'business'], function(){
	/** 加载时执行函数 */
	$onload(function() {
		function fixNum(n) {
			if (n < 10) n = '0' + n.toString();
			return n;
		}

		function fmtDate(d, div, needWeekday){
			var rtn = [d.getFullYear(), fixNum(d.getMonth()+1), fixNum(d.getDate())].join(div||'/');
			if (needWeekday) rtn += ' 星期'+['日','一','二','三','四','五','六'][d.getDay()];
			return rtn;
		}

		function fmtHour(d){
			return fixNum(d.getHours()) + ':' + fixNum(d.getMinutes());
		}

		function updateHours(e, bhi, ehi) { /** 小时选择 */
			var p = $one('.hr'),
				hourSel1 = $one('select:first-of-type', p),
				hourSel2 = $one('select:last-of-type', p),
				defaultFrom = $data(p, 'defaultFrom'),
				defaultEnd = $data(p, 'defaultEnd'),
				defaultDelay = parseInt($data(p, 'defaultDelay')),
				d = new Date(parseInt($one('.dt select').value)),
				now = new Date,
				isToday = (now.getFullYear() == d.getFullYear()) && (now.getDate() == d.getDate()) && (now.getMonth() == d.getMonth()),
				fill = function(sel, arr, hi) {
					sel.innerHTML = '';
					var issel = false,
						selIndex = 0;
					$each(arr, function(time) {
						if (!issel && hi != fmtHour(new Date(time))) {
							selIndex ++;
						} else {
							issel = true;
						}
						$append(sel, '<option value="'+time+'">&nbsp;&nbsp;'+ fmtHour(new Date(time)) +'</option>');
					});
					return true == issel ? selIndex : 0;
				},
				chk = function() {
					if (hourSel1.value >= hourSel2.value) {
						hourSel2.value = parseInt(hourSel1.value) + defaultDelay * 60 * 1000;
					}
				},
				fromH, endH;
			$one('section h1:first-of-type').title = now.toLocaleString();
			endH = new Date(fmtDate(d, '/') + ' ' + defaultEnd); /** 如果是用 - 连接则IOS不支持 */
			if (now.getTime() > endH.getTime()) {
				d.setDate(d.getDate() + 1);
				$one('.dt select').selectedIndex += 1;
				isToday = false;
			}

			if (isToday) { /** 当天 取最近的半小时整数时间开始 */
				fromH = new Date(fmtDate(d, '/') + ' ' + defaultFrom);
				if (now.getTime() < fromH.getTime()) {
					$one('.dt select').selectedIndex = 0;
				} else {
					var d1 = new Date;
					if (d1.getMinutes() > 30) {
						d1.setHours(d1.getHours() + 1);
						d1.setMinutes(0);
					} else {
						d1.setMinutes(30);
					}

					d1.setSeconds(0);
					fromH = d1;
				}
				endH = new Date(fmtDate(now, '/') + ' ' + defaultEnd);
			} else { /** 非当天 默认时间 */
				fromH = new Date(fmtDate(d, '/') + ' ' + defaultFrom);
				endH = new Date(fmtDate(d, '/') + ' ' + defaultEnd);
			}

			fromH = fromH.getTime();
			endH = endH.getTime();
			var arr = [];
			if (fromH >= endH) {
				d.setDate(d.getDate() + 1);
				$one('.dt select').selectedIndex += 1;
				isToday = false;
				fromH = new Date(fmtDate(d, '/') + ' ' + defaultFrom);
				endH = new Date(fmtDate(d, '/') + ' ' + defaultEnd);
				fromH = fromH.getTime();
				endH = endH.getTime();
			}

			while(fromH < endH) {
				arr.push(fromH);
				fromH += defaultDelay * 60 * 1000;
			}

			arr.push(fromH); /** endH会在chk时匹配不上 */
			var arr1 = arr.slice();
			arr1.pop();
			var arr2 = arr.slice();
			arr2.shift();
			var bIndex = fill(hourSel1, arr1, bhi);
			var eIndex = fill(hourSel2, arr2, ehi);
			MOA.event.listenSelectChange(hourSel1, chk);
			MOA.event.listenSelectChange(hourSel2, chk);
			hourSel1.selectedIndex = bIndex;
			hourSel2.selectedIndex = eIndex;
		}

		/** 日期选择 */
		(function(){
			var sel = $one('.dt select'),
				range = parseInt($data(sel.parentNode, 'range')) + 1,
				day = new Date,
				selIndex = 0,
				issel = false;
			day.setDate(day.getDate() - 1);
			while(range --) {
				day.setDate(day.getDate() + 1);
				if (_ymd != fmtDate(day) && !issel) {
					selIndex ++;
				} else {
					issel = true;
				}
				$append(sel, '<option value="' + day.getTime() + '">' + fmtDate(day, '/', true) + '&nbsp;</option>');
			}
			sel.selectedIndex = true == issel ? selIndex : 0;
			MOA.event.listenSelectChange(sel, updateHours);
			updateHours(null, _bhi, _ehi);
		}());

		/** 会议室 */
		(function() {
			var r = $one('.rooms'),
				ipt = $one('input', r),
				trigger = $one('a.trigger', r),
				provider = $one('div', r),
				onRoomsItemClk = function(e3) {
					e3.preventDefault();
					e3.stopPropagation();
					$each('#roomsDlg li', function(li2) {
						$rmCls(li2, 'selected');
					});
					var li = e3.currentTarget.parentNode;
					var idx = parseInt(li.rel);
					$addCls(li, 'selected');
					ipt.value = $one('b', li).innerHTML;
					$data(ipt, 'selectedIdx', idx);
					$one('#mr_id').value = $one('id', li).innerHTML;
					setTimeout(function() {
						MDialog.close();
					},500);
				};
			trigger.addEventListener('click', function(e) {
				setTimeout(function() {
					var m = MDialog.popupCustom(provider.innerHTML, false, function(e2) {}, true);
					m.id = 'roomsDlg';
					m.style.left = .5 * (window.innerWidth - m.clientWidth) + 'px';
					$data(m, 'closeByModal', 1);
					if (typeof $data(ipt, 'selectedIdx') !== 'undefined') {
						try {
							var liIdx = parseInt($data(ipt, 'selectedIdx')) + 1;
							$addCls( $one('li:nth-of-type('+liIdx+')', m), 'selected');
						} catch(ex) {}
					}

					$each($all('li', m), function(li, idx) {
						li.rel = idx;
						$append(li, '<a href="javascript:void(0)" class="tri"></a>');
						$one('.tri', li).addEventListener('touchend', onRoomsItemClk);
					});
				}, 500);
			});
		}());
		
		/** 表单校验 */
		$one('#' + _form_name).addEventListener('submit', _on_form_submit);
	});
});
{/literal}
</script>


{include file='frontend/footer.tpl'}
