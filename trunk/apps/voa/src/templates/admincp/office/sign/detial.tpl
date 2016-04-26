{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title font12"><strong>考勤详情</strong></h3>
	</div>
	<div class="panel-body">
		<dl class="dl-horizontal font12 vcy-dl-list" style="margin-bottom:0">
			<dt>签到人：</dt>
			<dd><strong class="text-info">{$m_username}</strong></dd>
			<dt>日期：</dt>
			<dd><strong class="text-default">{$date} {$week_val}</strong></dd>
			<dd>
				<form method="get" id="timepicker_form" action="{$view_detail_url_base}">
					<input type="hidden" name="m_uid" value="{$m_uid}" />
					<div class="row">
						<div class="col-sm-3">
							<div class="input-group date">
								<input type="text" readonly class="form-control" id="bs-timepicker-component"  name="date" value="{$date}"><span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
								<script>
									init.push(function () {
										var options2 = {
											todayBtn: "linked",
											orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
										}
										$('#bs-timepicker-component').datepicker(options2);
										$('#bs-timepicker-component').datepicker().on('changeDate', function() {
											$('#timepicker_form').submit();
										});
									});
								</script>
							</div>

						</div>
						{*<button type="submit" class="btn">查看指定日期的签到详情</button>*}
				</form>
			</dd>
			{foreach $data as $val}
				{if $val['sr_type'] == 1}
					<dt>上班：</dt>
					<dd>
						<strong class="text-info"><i class="fa fa-sign-in"></i> {$val['_sr_status']}</strong>
						<span class="space"></span>
						<span class="text-info"><i class="fa fa-clock-o"></i> {$val['_sr_signtime']}</span>
						<span class="space"></span>
						<span class="text-info"><i class="fa fa-map-marker"></i> {if $val['sr_address']}{$val['sr_address']}{else}无位置信息{/if}（{$val['sr_ip']}）</span>
					</dd>
				{elseif $val['sr_type'] == 2}
					<dt>下班：</dt>
					<dd>
						<strong class="text-info"><i class="fa fa-sign-in"></i> {$val['_sr_status']}</strong>
						<span class="space"></span>
						<span class="text-info"><i class="fa fa-clock-o"></i> {$val['_sr_signtime']}</span>
						<span class="space"></span>
						<span class="text-info"><i class="fa fa-map-marker"></i> {if $val['sr_address']}{$val['sr_address']}{else}无位置信息{/if}（{$val['sr_ip']}）</span>
					</dd>
				{/if}
			{/foreach}
			{if $data['sr_type'] == 1}
			<dt>上班：</dt>
			<dd>
				{if !empty($work_on)}
					<strong class="text-info"><i class="fa fa-sign-in"></i> {$work_on['_status']}</strong>
					<span class="space"></span>
					<span class="text-info"><i class="fa fa-clock-o"></i> {$data['_sr_signtime']}</span>
					<span class="space"></span>
					<span class="text-info"><i class="fa fa-map-marker"></i> {if $work_on['sr_address']}{$work_on['sr_address']}{else}无位置信息{/if}（{$work_on['sr_ip']}）</span>
				{else}
					<span>
	{if $is_work_day}
		<strong class="text-danger">未签到</strong>
	{else}
					<span class="text-warning">非工作日无须签到</span>
	{/if}
					</span>
				{/if}
				{/if}
			</dd>

			{if $data['sr_type'] == 2}
				<dt>下班：</dt>
				<dd>
					{if !empty($work_off)}
						<strong class="text-info"><i class="fa fa-sign-out"></i> {$work_off['_status']}</strong>
						<span class="space"></span>
						<span class="text-info"><i class="fa fa-clock-o"></i> {$work_off['_signtime']}</span>
						<span class="space"></span>
						<span class="text-info"><i class="fa fa-map-marker"></i> {if $work_off['sr_address']}{$work_off['sr_address']}{else}无位置信息{/if}（{$work_off['sr_ip']}）</span>
					{else}
						{if $is_work_day}
							<strong class="text-danger">未签退</strong>
						{else}
							<span class="text-warning">非工作日无须签退</span>
						{/if}
					{/if}
				</dd>
			{/if}
			{if $detail_list}
				<dt>备注：</dt>
				<dd>
					{foreach $detail_list as $reason}
						{if $reason['sd_reason']}
							<strong class="text-info">{$reason['_sd_updated']}: </strong><span>{$reason['sd_reason']}</span><br />
						{/if}
					{/foreach}
				</dd>
			{/if}
		</dl>
	</div>
</div>

</div>

{include file="$tpl_dir_base/footer.tpl"}