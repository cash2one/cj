{include file='admincp/header.tpl'}

	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title font12"><strong>{$meeting['mt_subject']|escape}</strong></h3>
		</div>
		<div class="panel-body">
			<dl class="dl-horizontal font12 vcy-dl-list">
				<dt>发起人：</dt>
				<dd>{$meeting['m_username']|escape}&nbsp;&nbsp;<abbr title="发起时间">{$meeting['_created']}</abbr></dd>
				<dt>预定人：</dt>
				<dd>{if $meeting['mt_username']}{$meeting['mt_username']|escape}{else}{$meeting['m_username']|escape}{/if}</dd>
				<dt>会议状态：</dt>
				<dd><strong class="label label-primary font12">{$meeting['_status']}</strong></dd>
{if $meetingRoom}
				<dt>会议室信息：</dt>
				<dd>
	{foreach $meetingRoomFields as $_k=>$_n}
		{if !empty($meetingRoom[$_k])}
					<span class="label label-info font12">{if $_n}{$_n}{/if}{$meetingRoom[$_k]|escape}</span>
		{/if}
	{/foreach}
				</dd>
{/if}
				<dt>会议标题：</dt>
				<dd>{$meeting['mt_subject']|escape}</dd>
				<dt>会议内容：</dt>
				<dd><blockquote class="m-0">{$meeting['mt_message']|escape}</blockquote></dd>
			</dl>
		</div>
	</div>

	<table class="table table-hover table-striped font12">
		<colgroup>
			<col class="t-col-6" />
			<col class="t-col-10" />
			<col class="t-col-12" />
			<col class="t-col-18" />
			<col />
		</colgroup>
		<thead>
			<tr>
				<th>序号</th>
				<th>参会人</th>
				<th>状态</th>
				<th>操作时间</th>
				<th>原因</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5"></td>
			</tr>
		</tfoot>
		<tbody>
{foreach $memList as $_mm_id=>$_mm}
			<tr>
				<td>{$_mm['_order_num']}</td>
				<td>{$_mm['m_username']|escape}</td>
				<td class="text-{$_mm['_status_tag']}">{$_mm['_status']}</td>
				<td>{$_mm['_time']}</td>
				<td>{$_mm['mm_reason']|escape}</td>
			</tr>
{foreachelse}
			<tr class="warning">
				<td colspan="5">该会议暂无参会人员</td>
			</tr>
{/foreach}
		</tbody>
	</table>


{include file='admincp/footer.tpl'}