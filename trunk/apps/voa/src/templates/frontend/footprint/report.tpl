{include file='frontend/header.tpl'}

<body id="wbg_xsgj_table">

<header>
	<img src="{$cinstance->avatar($wbs_uid)}" /><h1>{$wbs_username}</h1><h2>{$jobs[$wbs_user['cj_id']]['cj_name']}</h2>
</header>

<table>
	<thead><tr>
		<th>
			<span class="fake init">{$cur_year_month}</span>
			<select>
				{foreach $year_months as $k => $v}
				<option value="{$k}"{if $selected_year_month == $k} selected{/if}>{$v}</option>
				{/foreach}
			</select>
		</th>
		<th><em>{$month_sign}</em>签约</th>
		<th><em>{$month_total}</em>触达</th>
	</tr></thead>
	<tbody>
		{foreach $weeks as $k => $v name=winfo}
		<tr>
			<td><a class="week" href="javascript:void(0)">{$v[0]}~{if empty($v[1])}{$v[0]}{else}{$v[1]}{/if}<i>第 {$smarty.foreach.winfo.iteration} 周</i></a></td>
			<td>{$list_week_sign[$k]['count']}</td>
			<td>{$list_week_total[$k]['count']}</td>
		</tr>
		<tr hidden><td colspan="3">
			<table>
				{foreach $week_to_days[$k] as $dk => $dv}
				<tr>
					<td><a class="date" href="/footprint/list/{$wbs_uid}?btime={$dk}">{$dv}</a></td>
					<td>{$list_day_sign[$dk]['count']}</td>
					<td>{$list_day_total[$dk]['count']}</td>
				</tr>
				{/foreach}
			</table>
		</td></tr>
		{/foreach}
	</tbody>
</table>

<script>
var __uid = '{$cur_uid}';
{literal}
require(['dialog', 'business'], function(){
	$onload(function(){ 
		parseHiddenSelect('th select', function($sel, $fake, value){
			$fake.innerHTML = value.replace(/^(\d+)(.*)$/, "<i>$1</i>$2");
			window.location.href = '/footprint/report/' + __uid + '?month=' + $sel.value;
		});
		
		$each('a.week', function(btn){
			btn.addEventListener('click', function(e){
				var $wtr = e.currentTarget.parentNode.parentNode;
				var $dtr = $next($wtr);
				if (!$dtr) return;
				if ( $hasCls($wtr, 'dl-open') ){
					$hide($dtr);
					$rmCls($wtr, 'dl-open');
				}else{
					$show($dtr);
					$addCls($wtr, 'dl-open');
				}
			});
		});
	});
});
{/literal}
</script>

{include file='frontend/footer.tpl'}