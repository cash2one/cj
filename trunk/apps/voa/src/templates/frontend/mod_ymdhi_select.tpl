<!--提醒时间会放入该hidden的value中-->
<input type="hidden" id="{$iptname}" name="{$iptname}" value="{if $iptvalue}{$iptvalue}{/if}" />
<span class="fake ph" id="mod_datefaker_{$iptname}">请选择</span>
<script>
require(['business', 'timeslider'], function(){
	//时间设置
	var daysRange = (isLeapYear() ? 367: 366),
		timeConfig =
		{
			rangeDays: daysRange, //时间范围的业务逻辑为: 今天至一年后
			noticeMin: '请选择晚于当前的时间!',
			noticeMax: '请选择'+ daysRange +'天以内的时间!',
			startDay: {if $startts}new Date({((int)$startts - 15 * 86400) * 1000}){else}new Date{/if}
		};
	$onload(function(){
		parseIOS6styleTimeChooser(
			$one('#mod_datefaker_{$iptname}'),
			timeConfig
		);
	});
});
</script>
