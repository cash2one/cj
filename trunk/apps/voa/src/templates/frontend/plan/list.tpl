{include file='frontend/header.tpl'}
<body id="wbg_rc_calendar">

<header>
	<h1><span class="fake">{$year}<i>年</i>{$month}<i>月</i></span>
		<select name=""
				data-range-start="{$start_year}-{$start_month}"
				data-range-end="{$end_year}-{$end_month}"
				data-selected="{$year}-{$month}"
		></select>
	</h1>
	<a class="toggle" href="/plan/new"><!--<span>全部日程</span>--></a>
</header>

<table class="center">
	<tbody>
	</tbody>
</table>

<ul class="mod_common_list" hidden>
</ul>

{literal}
<script type="text/moatmpl" id="ajaxTmpl">
	{##items#,#
	<li class="withicon">
		<a href="_field.href_">
			<img src="_field.img_" />
			<time>_field.time.from_<em>_field.time.to_</em></time>
			<div><h1>_field.title_</h1></div>
			<div data-hidden-when-lost="_field.desc_">_field.desc_</div>
		</a>
	</li>
	##}
</script>
{/literal}

{include file='frontend/footer_nav.tpl'}

<script>
{literal}
require(['dialog', 'business', 'template'], function () {

var plan = (function() {
	var _fix_num = function(n, pre) {
		if ('undefined' == typeof(pre)) {
			pre = true;
		}

		if (false != pre && n < 10) n = '0' + n.toString();
		return n;
	};
	var _fmt_date = function(d, div) {
		var rtn = [d.getFullYear(), _fix_num(d.getMonth() + 1, false), _fix_num(d.getDate(), false)].join(div||'/');
		return rtn;
	};
	var _fmt_hi = function(d, div) {
		var rtn = [_fix_num(d.getHours()), _fix_num(d.getMinutes())].join(div||'/');
		return rtn;
	};
	var _sort = function(res, plan_data) {
		var ts_b = plan_data.pl_begin_at * 1000;
		var ts_e = plan_data.pl_finish_at * 1000;
		var day = new Date;
		var ymd;
		/** 如果时间错误 */
		if (ts_b >= ts_e) {
			return res;
		}

		/** 日程数据 */
		day.setTime(ts_b);
		var hi_f = _fmt_hi(day, ':');
		day.setTime(ts_e);
		var hi_t = _fmt_hi(day, ':');
		var plan = {
			"href": "/plan/view/" + plan_data.pl_id,
			"img": "/misc/images/rc_type"+ (parseInt(plan_data.pl_type) + 1) +".png",
			"title": plan_data.pl_subject,
			"desc": "123123",
			"time": {
				"from": hi_f,
				"to": hi_t
			}
		};
		/** 遍历时间范围 */
		for (var ts = ts_b; ts < ts_e; ts += 86400000) {
			/** 取年/月/日 */
			day.setTime(ts);
			ymd = _fmt_date(day, '-');
			/** 键值不存在, 则 */
			if (!res.hasOwnProperty(ymd)) {
				res[ymd] = [];
			}

			res[ymd].push(plan);
		}

		return res;
	};
	var _arrange = function(data) {
		var result = {};
		for (var todo in data.tododays) {
			result = _sort(result, data.tododays[todo]);
		}

		return result;
	};

	return {'arrange':_arrange};
}());

	var ajaxLock = false;
	var cache = {};
	var today = new Date;
	today = [today.getFullYear(), today.getMonth() + 1, today.getDate()].join('-');

	//获取某日数据
	var t = new MOA.mvc.Template;

	function getOneDayCalendar(e) {
		/*
		if (ajaxLock) return;
		ajaxLock = true;
		*/
		$hide($one('header select'));

		var $td = e.currentTarget;
		var $ul = $one('ul');
		var day = $td.id.replace(/^day\-/, ''); //2014-5-7

		$each($all('td', $td.parentNode.parentNode), function (other) {
			$rmCls(other, 'curr');
		});
		$addCls($td, 'curr');

		/*
		MLoading.show('正在读取...');
		$ajax(
			'sampleGetOneDayCalendarAjax.php', 'POST', //[ajax] url & method
			{
				'day': day //[ajax] params
			},
			function(ajaxResult){ //[ajax] callback

				$show($ul);
				$ul.innerHTML = '';
				var html = t.parse( $one('#ajaxTmpl').innerHTML, ajaxResult );
				$append( $ul, html );

				$show($one('header select'));
				MLoading.hide();
				ajaxLock = false;
			},
			true //[ajax] use json
		);
		*/

		$show($ul);
		$ul.innerHTML = '';

		// if (!cache[day]) return;
		var html = t.parse($one('#ajaxTmpl')
			.innerHTML, {
				items: cache[day]
			});
		$append($ul, html);

		$show($one('header select'));
	}

	//获取基于月的数据
	function updateCalendar(year, month) {
		if (ajaxLock) return;
		ajaxLock = true;
		MLoading.show('loading..');
		$hide($one('header select'));
		$hide($one('ul'));
		$ajax(
			'/plan?ac=ajaxMonth', 'GET',
			{
				'date': year + '-' + (month + 1)
			},
			function (ajaxResult) {
				var tbl = $one('table tbody');
				var tds = $all('td', tbl);
				var td;
				var k;

				cache = {};

				ajaxResult = plan.arrange(ajaxResult);

				for (k in ajaxResult) {
					td = $one('#day-' + k);
					if (td) {
						$addCls(td, 'todo');
						td.addEventListener('click', getOneDayCalendar);

						if (ajaxResult[k] instanceof Array) {
							cache[k] = ajaxResult[k];
						}
					}
				}

				$show($one('header select'));
				MLoading.hide();
				ajaxLock = false;

				td = $one('#day-' + today);
				if (td) {
					getOneDayCalendar({ currentTarget: td });
				}
			},
			true
		);
	}

	//更新表格
	function renderMonth() {
		var $sel = $one('header select');
		var ym = $sel.options[$sel.selectedIndex].value.split('-');
		var year = parseInt(ym[0]);
		var month = parseInt(ym[1]) - 1;
		var tbl = $one('table tbody');

		fillCalendarTable(tbl, year, month);
		updateCalendar(year, month);
	}

	//月份选择
	$onload(function () {
		var sel = $one('header select'),
			start = $data(sel, 'rangeStart'),
			end = $data(sel, 'rangeEnd'),
			sidx = $data(sel, 'selected'),
			fmtMonth = function (d, readmode) {
				if (readmode) {
					return d.getFullYear() + '年' + (d.getMonth() + 1) + '月';
				}
				var month=d.getMonth()+1;
 					month =(month<10 ? "0"+month:month); 
				return d.getFullYear() + '-' + month;
			},
			day = new Date(start),
			f = null,
			cache = {
				length: 1
			};
		f = fmtMonth(day);
		cache[f] = 0;
		while (f != end) {
			$append(sel, '<option value="' + f + '">' + fmtMonth(day, true) + '</option>');
			day.setMonth(day.getMonth() + 1);
			f = fmtMonth(day);
			cache[f] = (cache.length += 1) - 1;
		}
		$append(sel, '<option value="' + end + '">' + fmtMonth(new Date(end), true) + '</option>');
		cache[end] = (cache.length += 1) - 1;

		sel.selectedIndex = cache[sidx];
		cache = null;

		parseHiddenSelect('header select', function ($sel, $fake, value) {
			$fake.innerHTML = value.replace(/^(\d+)([^\d])(\d*)(.*$)/, "$1<i>$2</i>$3<i>$4</i>");
			renderMonth();
		});

		renderMonth();
	});
});
{/literal}
</script>

{include file='frontend/footer.tpl'}
