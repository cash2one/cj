{include file='admincp/header.tpl'}

<div class="panel panel-default">
	<div class="panel-body">
		<dl class="dl-horizontal font12 vcy-dl-list">
			<dt>签到人：</dt>
				<dd><strong class="text-info">{$view_username|escape}</strong></dd>
			<dt>日期：</dt>
				<dd><strong class="text-default">{$today_date} {$today_week}</strong></dd>
				<dd>
					<form method="get" action="{$view_detail_url_base}">
					<input type="hidden" name="m_uid" value="{$view_uid}" />
					<input type="date" name="date" value="{$today_ymd}" placeholder="YYYY-mm-dd" class="form-control form-small" style="width:120px;display:inline-block" />
					<button type="submit" class="btn btn-default btn-xs font12">查看指定日期的签到详情</button>
					</form>
				</dd>
			<dt>上班：</dt>
				<dd>
{if !empty($work_on)}
					<strong class="text-info"><i class="fa fa-sign-in"></i> {$work_on['_status']}</strong>
					<span class="space"></span>
					<span class="text-info"><i class="fa fa-clock-o"></i> {$work_on['_signtime']}</span>
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
				</dd>

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

{if $sign_message}
			<dt>备注：</dt>
			<dd>
	{foreach $sign_message as $sm}
		{if $sm['_reason']}
				<strong class="text-info">{$sm['_updated']}: </strong><span>{$sm['_reason']}</span><br />
		{/if}
	{/foreach}
			</dd>
{/if}
		</dl>
		<span class="space"></span>
		<ul class="nav nav-tabs font12">
			<li class="active">
				<a href="javascript:;" data-toggle="tab"><i class="fa fa-map-marker"></i> 地理位置上报</a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="list_detail">
				<table class="table table-striped table-hover font12">
					<colgroup>
						<col class="t-col-20" />
						<col class="t-col-20" />
						<col />
					</colgroup>
					<thead>
						<tr>
							<th>时间</th>
							<th>IP 地址</th>
							<th>地址</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="3" class="text-right">{$location_multi}</td>
						</tr>
					</tfoot>
					<tbody>
{foreach $location_list as $_id => $_data}
						<tr>
							<td>{$_data['_updated']}</td>
							<td>{$_data['sl_ip']}</td>
							<td>{$_data['sl_address']|escape}</td>
						</tr>
{foreachelse}
						<tr class="warning">
							<td colspan="3">暂无上报地理位置数据</td>
						</tr>
{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

{include file='admincp/footer.tpl'}
