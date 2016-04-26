<!--
	@param rangeStart 开始时间可选择的起点
	@param rangeLength 开始时间最多可选择到多少天之后
	@param rangeStep 结束时间默认比开始时间晚几天
	@param startSelected 可选，用于更新等处的已选索引数
	@param endSelected 可选，用于更新等处的已选索引数
-->
<ul class="mod_common_list mod_startend_time time" 
	data-range-start="{$range_start}" 
	data-range-length="60" 
	data-range-step="0"
	data-range-start-tailstr="00:00"
	data-range-end-tailstr="24:00"
	data-start-selected="{$start_selected}"
	data-end-selected="{$end_selected}">
	<li class="begin">
		<label>开始时间：</label><span class="fake init">请选择</span>
		<select name="begintime"></select>
	</li>
	<li class="end">
		<label>结束时间：</label><span class="fake init">请选择</span>
		<select name="endtime"></select>
	</li>
</ul>

<script>
{literal}
require(['dialog', 'business'], function(){
	$onload(function(){
		parseStartEndDateSelects('ul.time');
	});
});
{/literal}
</script>