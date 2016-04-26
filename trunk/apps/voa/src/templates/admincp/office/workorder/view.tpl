{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title font12"><strong>工单编号: {$workorder['woid']}</strong></h3>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-2 text-right">工单编号:</div>
			<div class="col-sm-4 text-danger">{$workorder['woid']} ({$workorder['wostate_name']})</div>
			<div class="col-sm-2 text-right">联系人:</div>
			<div class="col-sm-4"><i class="fa fa-user"></i> {$workorder['contacter']|escape}</div>
		</div>
		<div class="row">
			<div class="col-sm-2 text-right">派单人:</div>
			<div class="col-sm-4">
				<span class="text-info"><i class="fa fa-phone"></i> {$workorder['sender_info']['mobilephone']}</span>
				<span class="space"></span>
				<span class="text-default">{$workorder['sender_info']['realname']}</span>
			</div>
			<div class="col-sm-2 text-right">联系电话:</div>
			<div class="col-sm-4"><i class="fa fa-phone"></i> {$workorder['phone']|escape}</div>
		</div>
		<div class="row">
			<div class="col-sm-2 text-right">派单时间:</div>
			<div class="col-sm-4">
				<span class="text-default"><i class="fa fa-clock-o"></i> {$workorder['ordertime']}</span>
			</div>
			<div class="col-sm-2 text-right">联系地址:</div>
			<div class="col-sm-4"><i class="fa fa-building-o"></i> {$workorder['address']|escape}</div>
		</div>
		<div class="row">
			<div class="col-sm-2 text-right">接收人:</div>
			<div class="col-sm-9">
{foreach $receiver_list as $var_op}
				<span class="text-info"><i class="fa fa-phone"></i> {$var_op['user_info']['mobilephone']}</span>
				<span class="space"></span>
				<span class="text-default">{$var_op['user_info']['realname']}</span>
				<span class="space"></span>
				<span class="space"></span>
{/foreach}
				
			</div>
		</div>
		<div class="row">
			<div class="col-sm-2 text-right">工单备注:</div>
			<div class="col-sm-10">
			{$workorder['remark']|escape}
			</div>
		</div>
	</div>
</div>

<ul class="nav nav-tabs font12">
	<li class="active"><a href="#operator-log" data-toggle="tab"><i class="fa fa-flag"></i> 工单状态</a></li>
	<li><a href="#result" data-toggle="tab"><i class="fa fa-file-o"></i> 工单结果</a></li>
{if $receiver_count > 1}	<li><a href="#operator-list" data-toggle="tab"><i class="fa fa-group"></i> 接收人</a></li>{/if}
</ul>
		
<div class="tab-content">
	<div class="tab-pane active" id="operator-log">
		<table class="table table-striped table-hover table-bordered font12 table-light">
			<colgroup>
				<col class="t-col-13" />
				<col class="t-col-18" />
				<col class="t-col-30" />
				<col />
			</colgroup>
			<thead>
				<tr>
					<th>操作人</th>
					<th>时间/状态</th>
					<th>地址</th>
					<th>备注</th>
				</tr>
			</thead>
			<tbody>
{foreach $log_list as $var_log}
				<tr>
					<td>{$var_log['user_info']['realname']|escape}</td>
					<td>{$var_log['time']}<br />{$var_log['action_name']}</td>
					<td>{$var_log['ip']}<br />{$var_log['location']|escape}</td>
					<td>{$var_log['reason']}</td>
				</tr>
{foreachelse}
				<tr class="warning">
					<td colspan="4">暂无任何操作记录</td>
				</tr>
{/foreach}
			</tbody>
		</table>
	</div>
	
	<div class="tab-pane" id="result">
		<div class="panel panel-default">
			<div class="panel-body">
{if !$workorder['completetime']}
				<div class="text-warning text-center">本工单尚未完成</div>
{else}
				<div class="row">
	{foreach $operation_result['attachment_list'] as $var_at}
					<div class="col-sm-3">
						<a href="{$var_at['url']}" target="_blank" class="thumbnail"><img data-src="{$var_at['url']}" src="{$var_at['url']}" alt="..." style="max-height:120px;" /></a>
					</div>
	{/foreach}
				</div>
				<div class="row">
					<div class="col-sm-2 text-right"><strong>操作人: </strong></div>
					<div class="col-sm-4">{$workorder['operator_info']['realname']}</div>
					<div class="col-sm-2 text-right"><strong>操作时间: </strong></div>
					<div class="col-sm-4">{$workorder['completetime']}</div>
				</div>
				<div class="row">
					<div class="col-sm-2 text-right"><strong>完成说明: </strong></div>
					<div class="col-sm-9">
					{$operation_result['caption']|escape}
					</div>
				</div>
{/if}
			</div>
		</div>
	</div>
{if $receiver_count > 1}
	<div class="tab-pane" id="operator-list">
		<table class="table table-striped table-hover table-bordered font12 table-light">
			<colgroup>
				<col class="t-col-30" />
				<col class="t-col-30" />
				<col />
			</colgroup>
			<thead>
				<tr>
					<th>姓名</th>
					<th>手机号码</th>
					<th>状态</th>
				</tr>
			</thead>
			<tbody>
	{foreach $receiver_list as $var_user}
				<tr>
					<td>{$var_user['user_info']['realname']}</td>
					<td>{$var_user['user_info']['mobilephone']}</td>
					<td>{$var_user['worstate_name']|escape}<br />{$var_user['actiontime']}</td>
				</tr>
	{foreachelse}
				<tr class="warning">
					<td colspan="3">暂无接收人信息</td>
				</tr>
	{/foreach}
			</tbody>
		</table>
	</div>
{/if}
</div>

{include file="$tpl_dir_base/footer.tpl"}