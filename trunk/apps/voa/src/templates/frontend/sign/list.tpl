{include file='frontend/header.tpl'}

<body id="wbg_msq_calendar">

<form name="frm" id="frm" method="post" action="/sign/plead">
	<input type="hidden" name="formhash" id="formhash" value="{$formhash}" />
	<header>
		<!--
			* 下方的日历表格格式会由这两个select的值自动计算填充,
				一次发生在页面onload, 另外就是在用户手动选择后
			* 每次填充日历表格之前, 会同步发起一次ajax,
				其中返回了考勤数据, 自动填充到考勤概览ul和表格中
			* 申诉的时间参数可以直接通过select的值由form提交
		-->
		<select name="year" id="year">
			{foreach $year_sel as $v}
			<option value="{$v}"{if $v == $year} selected{/if}>&nbsp;{$v}</option>
			{/foreach}
		</select>
		<select name="month" id="month">
			{foreach $month_sel as $v}
			<option value="{$v}"{if $v == $month - 1} selected{/if}>&nbsp;{$v + 1}月</option>
			{/foreach}
		</select>
		<h1>考勤统计</h1>
		<ul></ul>
	</header>
	<table class="center"><tbody></tbody></table>
	<footer>
		<a name="asubmit" id="asubmit" href="javascript:;" class="mod_button2">我要申诉</a>
	</footer>
</form>

<script>
var ajaxLock = false;
var _styles = {};
{foreach $styles as $k => $v}
_styles[{$k}] = '{$v}';
{/foreach}
var _sign_st = {};
{foreach $sign_st as $k => $v}
_sign_st[{$k}] = '{$v}';
{/foreach}

{literal}
/** 获取基于月的考勤数据并填充 */
function _updateCalendar(year, month) {
	if (ajaxLock) return;
	ajaxLock = true;
	MLoading.show('稍等片刻...');
	$ajax(
		'/sign/list?ac=refresh', 'POST', /** [ajax] url & method */
		{'year':year, 'month':month, /** [ajax] params */},
		function(ajaxResult) { /** [ajax] callback */
			var summUl = $one('header ul');
			var tbl = $one('table tbody');
			var k;

			summUl.innerHTML = '';
			for (k in ajaxResult.summary) {
				$append(summUl, '<li><em>' + ajaxResult.summary[k] + '</em>' + _sign_st[k] + '</li>');
			}

			var lis = $all('li', summUl),
				w1 = parseInt(summUl.clientWidth / lis.length);
			$each(lis, function(li) {
				li.style.width = w1 + 'px';
			});

			var tds = $all('td', tbl);
			for (k in ajaxResult.workdays) {
				var td = $one('#day-' + k);
				if (td) {
					$addCls(td, 'workday');
					var v = ajaxResult.workdays[k];
					if (v in _styles) {
						$append(td, '<i>' + _sign_st[v] + '</i>');
						$addCls(td, _styles[v]);
					}
				}
			}

			MLoading.hide();
			ajaxLock = false;
		},
		true /** [ajax] use json */
	);
}

function _renderMonth(e) {
	/** 只响应 change 事件 */
	if (e && 'change' != e.type) {
		return;
	}

	var year = parseInt($one('header select:first-of-type').value);
	var month = parseInt($one('header select:last-of-type').value);
	var matrix = MOA.utils.getDatesOfMonth(year, month);
	var tbl = $one('table tbody');
	tbl.innerHTML = '<tr><th>日</th><th>一</th><th>二</th><th>三</th><th>四</th><th>五</th><th>六</th></tr>';
	while (matrix.length) {
		var row = matrix.shift();
		var html = '<tr>';
		while(row.length) {
			var col = row.shift();
			if (col == null) {
				html += '<td></td>';
			}else{
				var d = MOA.utils.getFixedIOSDate(col);
				var ymd = [d.getFullYear(), d.getMonth() + 1, d.getDate()].join('-');
				html += '<td id="day-' + ymd + '">' + d.getDate() + '</td>';
			}
		}

		html += '</tr>';
		$append(tbl, html);
	}

	_updateCalendar(year, month);
}

require(['dialog'], function() {
	$onload(function() {
		_renderMonth();
		MOA.event.listenSelectChange($one('header select:first-of-type'), _renderMonth);
		MOA.event.listenSelectChange($one('header select:last-of-type'), _renderMonth);
		$one('#asubmit').addEventListener('click', function(e) {
			var year = $one('#year').value;
			var month = $one('#month').value;
			window.location.href = '/sign/plead?year=' + year + '&month=' + month;
		});
	});
});
{/literal}
</script>

{include file='frontend/footer.tpl'}