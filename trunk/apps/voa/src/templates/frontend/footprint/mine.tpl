{include file='frontend/header.tpl'}

<body id="wbg_xsgj_profile">
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>

<header class="single">
	<img src="{$cinstance->avatar($wbs_uid)}" />
	<h1>{$wbs_username}</h1>
	<h2>{if !empty($jobs[$wbs_user.cj_id])}{$jobs[$wbs_user.cj_id]['cj_name']}{/if}</h2>
	<div>
		<time>1<i>月</i>1<i>日</i></time>
		<select name="" data-from="{$date_from}" data-to="{$date_to}" data-init="{$date_current}"></select>
		<h3>销售轨迹</h3>
		<a class="table" href="/footprint/report/{$wbs_uid}">报表</a>
	</div>
</header>

<script>
{literal}
require(['dialog', 'members', 'business'], function(){
	$onload(function(){
		var sel = $one('header.single select'),
			fake = $prev(sel),
			from = ( $data(sel, 'from') ),
			to = ( $data(sel, 'to') ),
			init = ( $data(sel, 'init') ),
			day = new Date(from),
			day_end = new Date(to),
			cache = {length: 0},
			fixNum = function(n){
				if (n<10) n='0'+n.toString();
				return n;
			},
			fmtDate = function(d, div, needYear){
				var tmp = [fixNum(d.getMonth()+1), fixNum(d.getDate())];
				if (needYear){
					tmp.unshift(d.getFullYear());
				}
				return tmp.join(div||'/');
			},
			updateFake = function(value){
				var str = value.replace(/^\d{4}\-/, '');
				str = str.replace(/^(\d{2})\-(\d{2})/, "$1<i>月</i>$2<i>日</i>");
				fake.innerHTML = str;
			};
		day.setDate( day.getDate()-1 );
		while(!(
				day.getMonth() == day_end.getMonth() 
				&& day.getYear() == day_end.getYear() 
				&& day.getDate()==day_end.getDate()
			)){
			
			day.setDate( day.getDate()+1 );
			
			var dvlu =  fmtDate(day, '-', true);
			$append(sel, '<option value="'+ dvlu +'">'+ dvlu +'&nbsp;</option>');
			
			cache[dvlu] = cache.length++;
		}
		
		sel.selectedIndex = cache[init];
		updateFake(init);
		
		parseHiddenSelect(sel, function($sel, $fake, value){
			updateFake(value);
			window.location.href = '/footprint/mine?btime=' + value;
		});
	});
});
{/literal}
</script>

{include file='frontend/footprint/footprint.tpl'}

{include file='frontend/footer.tpl'}
