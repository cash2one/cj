{include file='admincp/header.tpl'}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>申诉概要</strong></div>
	<div class="panel-body">
		<ul class="list-inline">
			<li>申诉人：</li>
			<li class="text-info"><strong>{$plead['m_username']|escape}</strong></li>
			<li>申诉月份：</li>
			<li class="text-info"><strong>{$plead['_date']}</strong></li>
			<li>申诉标题：</li>
			<li class="text-info"><strong>{$plead['_subject']}</strong></li>
		</ul>
		<ul class="list-inline">
			<li>申诉提交时间：</li>
			<li class="text-info"><strong>{$plead['_created']}</strong></li>
			<li>处理状态：</li>
			<li class="text-info"><strong>{$plead['_status']}</strong></li>
			<li>处理时间：</li>
			<li class="text-info"><strong>{$plead['_updated']}</strong></li>
		</ul>
	</div>
</div>
<form class="form-horizontal font12" role="form" method="post" action="{$formActionUrl}">
<input type="hidden" name="formhash" value="{$formhash}" />
<div class="panel panel-default font12">
	<div class="panel-heading"><strong>处理申诉</strong></div>
	<div class="panel-body">
		<table class="table table-striped table-hover font12">
			<colgroup>
				<col class="t-col-10" />
				<col class="t-col-35" />
				<col class="t-col-10" />
				<col class="t-col-10" />
				<col class="t-col-10" />
				<col class="t-col-25" />
			</colgroup>
			<thead>
				<tr>
					<th>日期</th>
					<th>申诉内容</th>
					<th>签到类型</th>
					<th>当前状态</th>
					<th>重置状态</th>
					<th>备忘说明</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="6" class="text-right">
						<button type="submit" class="btn btn-primary">提交处理申诉</button>
						<span class="space"></span>
						<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
					</td>
				</tr>
			</tfoot>
			<tbody>
{foreach $dayPleads as $_id => $_data}
	{foreach $signType AS $_type => $_typename}
				<tr>
					<td>
						{$_data['date']|escape}
						<br /><br />
						{$_data['week']}
					</td>
					<td><textarea id="_plead_msg_{$_id}" cols="9" rows="2" class="form-control font12" readonly="readonly">{$_data['message']|escape}</textarea></td>
					<td>{$_typename}</td>
					<td>
		{if isset($recordList[$_data['date']][$_type])}
						{$recordList[$_data['date']][$_type]['_status']}
		{else}
						{$signStatus[$signStatusSet['absent']]}
		{/if}
						<br /><br />
						{$_data['week']}
					</td>
					<td>
						<select name="sr_status[{$_data['date']}][{$_type}]" class="selectpicker bla bla bli bootstrap-select-small font12" data-width="auto">
							<option value="-1">不改变</option>
		{if isset($recordList[$_data['date']][$_type])}
			{foreach $signStatus AS $_statusValue => $_statusName}
							<option value="{$_statusValue}"{if $recordList[$_data['date']][$_type]['sr_status'] == $_statusValue} selected="selected"{/if}>{$_statusName}</option>
			{/foreach}
		{else}
							{$statusOptions}
		{/if}
						</select></td>
					<td><textarea id="_detail_msg_{$_id}" cols="9" rows="2" class="form-control font12" name="sd_message[{$_data['date']}][{$_type}]"></textarea></td>
				</tr>
	{/foreach}
{foreachelse}
				<tr>
					<td colspan="6" class="warning">暂无有效的申诉信息，请点击“提交处理”按钮进行操作。</td>
				</tr>
{/foreach}
			</tbody>
		</table>
	</div>
</div>
</form>
{include file='admincp/footer.tpl'}