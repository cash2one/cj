{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索会议</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$formActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row m-b-20">
				<div class="form-group">
					<label class="vcy-label-none" for="id-mr_id">会议室：</label>
					<select id="id-mr_id" name="mr_id" class="form-control" data-width="auto">
						<option value="0">全部会议室</option>
{foreach $meetingRoomList as $_mr_id => $_mr}
						<option value="{$_mr_id}"{if $searchBy['mr_id'] == $_mr_id} selected="selected"{/if}>{$_mr['mr_name']|escape}</option>
{/foreach}
					</select>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_expire">是否已结束：</label>
					<select id="id_expire" name="expire" class="form-control" data-width="auto">
						<option value="-1">不限</option>
{foreach $expireStatus as $_k=>$_n}
						<option value="{$_k}"{if $searchBy['expire']==$_k} selected="selected"{/if}>{$_n}</option>
{/foreach}
					</select>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_mt_status">是否已取消：</label>
					<select id="id_mt_status" name="mt_status" class="form-control" data-width="auto">
						<option value="-1">不限</option>
{foreach $cancelStatus as $_k=>$_n}
						<option value="{$_k}"{if $searchBy['mt_status']==$_k} selected="selected"{/if}>{$_n}</option>
{/foreach}
					</select>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_m_username">发起人：</label>
					<input type="text" class="form-control form-small" id="id_m_username" name="m_username" placeholder="会议发起人" value="{$searchBy['m_username']|escape}" maxlength="54" />
					<span class="space"></span>
					<label class="vcy-label-none" for="id_mt_username">预定人：</label>
					<input type="text" class="form-control form-small" id="id_mt_username" name="mt_username" placeholder="会议预定人" value="{$searchBy['mt_username']|escape}" maxlength="54" />
					<span class="space"></span>
					<label class="vcy-label-none" for="id_mt_subject">主题：</label>
					<input type="text" class="form-control form-small" id="id_mt_subject" name="mt_subject" placeholder="会议主题" value="{$searchBy['mt_subject']|escape}" maxlength="255" />
					<span class="space"></span>
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			记录列表
		</div>
	</div>
	<form class="form-horizontal" role="form" method="post" action="{$formDeleteUrl}">

	<input type="hidden" name="formhash" value="{$formhash}" />
	<table class="table table-striped table-bordered table-hover font12">
		<colgroup>
			<col class="t-col-5" />
			<col class="t-col-9" />
			<col class="t-col-14" />
			<col class="t-col-12" />
			<col />
			<col class="t-col-9" />
			<col class="t-col-9" />
			<col class="t-col-18" />
			<col class="t-col-12" />
		</colgroup>
		<thead>
			<tr>
				<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');" /><span class="lbl">全选</span></label></th>
				<th>状态</th>
				<th>会议时间</th>
				<th>会议室名称</th>
				<th>地点</th>
				<th>发起人</th>
				<th>预定人</th>
				<th >会议主题</th>
				<th>操作</th>
			</tr>
		</thead>
	{if $total > 0}
		<tfoot>
			<tr>
				<td colspan="2"><button type="submit" class="btn btn-danger">批量删除</button></td>
				<td colspan="7" class="text-right vcy-page">{$multi}</td>
			</tr>
		</tfoot>
	{/if}
		<tbody>
	{foreach $meetingList as $_mt_id=>$_mt}
			<tr>
				<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete[{$_mt_id}]" value="{$_mt_id}" /><span class="lbl"> </span></label></td>
				<td>
			{if $_mt['mt_status'] == $cancelStatusValue}
				<span class="text-warning">{$cancelStatus[$cancelStatusValue]}</span>
			{else}
				{if $timestamp > $_mt['mt_endtime']}
					<span class="text-success">已结束</span>
				{elseif $timestamp < $_mt['mt_begintime']}
					<span class="text-primary">待开始</span>
				{else}
					<span class="text-info">进行中</span>
				{/if}
			{/if}
				</td>
				<td>
					<span class="text-danger">{$_mt['_begintime']}</span>
					<br />
					<span class="text-success">{$_mt['_endtime']}</span>
				</td>
				<td>
			{if $_mt['_meeting_room']}
					{$base->linkShow($_mt['_meeting_room_url'], '', $_mt['_meeting_room'], '', 'target="_blank"')}
			{else}
					<del>会议室不存在</del>
			{/if}
				</td>
				<td>
			{if $_mt['mt_address']}
				{$_mt['mt_address']|escape}
			{else}
				{if $_mt['_meeting_room']}
					{$_mt['_meeting_room']|escape}
				{else}
					<del>会议室不存在</del>
				{/if}
			{/if}
				</td>
				<td>
					{$_mt['m_username']|escape}
				</td>
				<td>
					{$_mt['mt_username']|escape}
				</td>
				<td class="text-left">
					{$_mt['mt_subject']|escape}
				</td>
				<td>
					{$base->linkShow($deleteUrlBase, $_mt_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
					{$base->linkShow($viewUrlBase, $_mt_id, '详情', 'fa-eye')}
				</td>
			</tr>
	{foreachelse}
			<tr>
				<td colspan="9" class="warning">{if $issearch}未搜索到指定条件的会议信息{else}暂无预定的会议信息{/if}</td>
			</tr>
	{/foreach}
		</tbody>
	</table>
	
	</form>
</div>

{include file="$tpl_dir_base/footer.tpl"}