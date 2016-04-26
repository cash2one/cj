{include file='frontend/header.tpl'}

<style>
{literal}
div.no_after_a {margin-left:10px;}
div.no_after_a::after {background:none !important;content:"" !important;}
{/literal}
</style>
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>
<body id="wbg_xd_order">

<nav class="mod_common_list_style" style="margin-bottom:5px;">
	<div class="no_after_a">时间选择:</div>
	<div class="day day_s" data-range="30" data-init="{$ymd_s}"><select></select></div>
	<div class="day day_e" data-range="30" data-init="{$ymd_e}"><select></select></div>
</nav>

<nav class="mod_common_list_style">
	<div class="no_after_a">区域选择:</div>
	<div class="city" data-init="{$parent_shop['cr_name']}"><select>
		<option selected value="-1">全部{$inspect_set['title_city']}</option>
	</select></div>
	<div class="dist" data-init="{$cur_shop['cr_name']}"><select>
		<option value="-1">全部{$inspect_set['title_region']}</option>
	</select></div>
</nav>

{if empty($list)}
<em class="mod_empty_notice mod_common_list_style"><span>此范围内暂无数据</span></em>
{else}
<ul class="mod_common_list shoplist">
	{include file='frontend/inspect/rank_li.tpl'}
</ul>
{/if}

{if $perpage <= count($list)}
<a id="show_more" href="javascript:void(0)" class="mod_ajax_more">加载更多&gt;&gt;</a>
{/if}

<textarea name="ta_regions" id="ta_regions" style="display:none;">{$regions}</textarea>

<script>
//店名二级联动
var _shops_data = eval('(' + $one('#ta_regions').value + ')');
var _sel_dist = 0;
{literal}
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 加载更多 */
		var _more = new c_list_more();
		_more.init('show_more', 'inspect_list', {'page':'_page'});
	});

	function checkSubmit(e) {
		var $daySel_s = $one('.day_s select');
		var $daySel_e = $one('.day_e select');
		var $citySel = $one('.city select');
		var $distSel = $one('.dist select');
		
		if (_sel_dist != $distSel.value) {
			var url = '/frontend/inspect/rank/ymd_s/' + $daySel_s.value + '/ymd_e/' + $daySel_e.value + '/pid/' + $citySel.value + '/cid/' + $distSel.value;
			location.href = url;
		}
	}
	
	var cIdxCache = {};
	var dIdxCache = {};
	var nullOpt = '<option selected value="-1">请选择</option>';
	$each(_shops_data, function(city, cidx) {
		$append($one('.city select'), '<option value="' + city.id + '">' + city.title + '</option>');
		city._idx = cidx;
		cIdxCache[city.title] = city;
	});
	MOA.event.listenSelectChange($one('.city select'), onCityChg);
	function onCityChg(e) {
		var $distSel = $one('.dist select');
		$distSel.innerHTML = nullOpt;
		$distSel.value = -1;
		var cid = e.currentTarget.value;
		if (cid === -1) return;
		var cdata = null;
		for (var i = 0, lng = _shops_data.length; i < lng; i ++) {
			if (_shops_data[i].id == cid) {
				cdata = _shops_data[i];
				$data($distSel, 'cIdx', i);
				break;
			}
		}
		if (cdata === null) return;
		var dicts = cdata.districts;
		$each(dicts, function(d, didx) {
			$append($distSel, '<option value="'+d.id+'">'+d.title+'</option>');
			d._idx = didx;
			dIdxCache[d.title] = d;
		});
		MOA.event.listenSelectChange($distSel, checkSubmit);
	}
	var cityInit = $data($one('.city'), 'init');
	if (cityInit) {
		var city = cIdxCache[cityInit];
		$one('.city select').selectedIndex = city._idx + 1;
		onCityChg({currentTarget:{value:city.id}});
		
		var distInit = $data($one('.dist'), 'init');
		if (distInit) {
			var dist = dIdxCache[distInit];
			$one('.dist select').selectedIndex = dist._idx + 1;
		}
		
		_sel_dist = $one('.dist select').value;
	}
	
	//填充日期
	(function() {
		var sel_s = $one('.day_s select'),
			sel_e = $one('.day_e select'),
			range = parseInt($data(sel_s.parentNode, 'range')) + 1,
			day = new Date;
		day.setDate(day.getDate() + 1);
		var selected = null;
		while (range --) {
			day.setDate(day.getDate() - 1);
			var flag = fmtDate(day, '');
			selected_s = $data(sel_s.parentNode, 'init') == flag ? ' selected ' : '';
			selected_e = $data(sel_e.parentNode, 'init') == flag ? ' selected ' : '';
			$prepend(sel_s, '<option value="' + flag + '" ' + selected_s + '>' + fmtDate(day, '/') + '&nbsp;</option>');
			$prepend(sel_e, '<option value="' + flag + '" ' + selected_e + '>' + fmtDate(day, '/') + '&nbsp;</option>');
		}
		if (selected === null) {
			//sel_s.selectedIndex = sel_s.options.length - 1;
		}
		MOA.event.listenSelectChange(sel_s, checkSubmit);
		MOA.event.listenSelectChange(sel_e, checkSubmit);
	}());
	function fixNum(n) {
		if (n < 10) n = '0' + n.toString();
		return n;
	}
	function fmtDate(d, div) {
		var rtn = [d.getFullYear(), fixNum(d.getMonth()+1), fixNum(d.getDate())].join(div);
		return rtn;
	}
});
{/literal}
</script>


{include file='frontend/footer.tpl'}
