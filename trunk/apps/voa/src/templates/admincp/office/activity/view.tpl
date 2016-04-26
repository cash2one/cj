{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title font12"><strong>{$module_plugin['cp_name']|escape}：{$activity['title']|escape}</strong></h3>
	</div>
	<div class="panel-body">
		<dl class="dl-horizontal font12 vcy-dl-list" style="margin-bottom:0">
			<dt>活动标题：</dt>
			<dd>
				<strong class="label label-primary font12">{$activity['title']|escape}</strong>
			</dd>
			<dt>活动内容：</dt>
			<dd>{$activity['content']}</dd>
			<dt></dt><dd>
			{foreach $image as $_id => $_data}
				{$_data}
			{/foreach}
			</dd>
			<dt>活动开始时间：</dt>
			<dd>{$activity['start_time']|escape}</dd>
			<dt>活动结束时间：</dt>
			<dd>{$activity['end_time']|escape}</dd>
			<dt>报名截止日期：</dt>
			<dd>{$activity['cut_off_time']|escape}</dd>
			<dt>活动地点：</dt>
			<dd>{$activity['address']|escape}</dd>
			<dt>限制人数：</dt>
			<dd>{$activity['np']|escape}</dd>
			<dt>邀请人员：</dt>
			<dd>{$activity['usernames']}</dd>
			<dt>发起人：</dt>
			<dd><strong class="label label-primary font12">{$activity['uname']|escape}</strong></dd>
		</dl>
	</div>
</div>
<ul class="nav nav-tabs font12">
	<li class="active"><a href="#inner" data-toggle="tab">
			<span class="badge pull-right">{$postsCount}</span> 内部人员&nbsp;
		</a></li>
	<li><a href="#outer" data-toggle="tab">
			<span class="badge pull-right">{$likesCount}</span> 外部人员&nbsp;
		</a></li>
	<li class="pull-right">
		<button type="button" id="btn_export" class="btn btn-primary">导出</button>
	</li>
</ul>

<div class="tab-content">
	<div class="tab-pane active" id="inner">
		<table
				class="table table-striped table-hover table-bordered font12 table-light">
			<colgroup>
				<col class="t-col-25" />
				<col class="t-col-30" />
				<col class="t-col-30" />
				<col class="t-col-15" />
				<col />
			</colgroup>
			<thead>
			<tr>
				<th>报名人</th>
				<th>报名时间</th>
				<th>备注</th>
				<th>状态</th>
			</tr>
			</thead>
			{if $total > 0}
				<tfoot>
				<tr>
					<td colspan="4" class="text-right vcy-page">{$multi}</td>
				</tr>
				</tfoot>
			{/if}
			<tbody>
			{foreach $list as $_id => $_data}
				<tr>
					<td>{$_data['name']|escape}</td>
					<td>{$_data['created']|escape}</td>
					<td>{$_data['remark']|escape}</td>
					<td>{if $_data['check'] == 1}已签到
						{elseif $_data['type'] == 1}已报名
						{elseif $_data['type'] == 2}申请取消中
						{elseif $_data['type'] == 3}已取消{/if}</td>
				</tr>
			{foreachelse}
				<tr class="warning">
					<td colspan="4">暂无报名人员</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<div class="tab-pane" id="outer">
		<table
				class="table table-striped table-hover table-bordered font12 table-light">
			<colgroup>
				<col class="t-col-10" />
				<col class="t-col-15" />
				<col />
			</colgroup>
			<thead>
			<tr>
				<th>姓名</th>
				<th>手机号</th>
				<th>备注</th>
				<th>报名时间</th>
				<th>自定义</th>
				<th>状态</th>
			</tr>
			</thead>
			{if $ex_total > 0}
				<tfoot>
				<tr>
					<td colspan="6" class="text-right vcy-page">{$ex_multi}</td>
				</tr>
				</tfoot>
			{/if}
			<tbody>
			{foreach $ex_list as $_ex_id => $_ex_data}
				<tr>
					<td>{$_ex_data['outname']|escape}</td>
					<td>{$_ex_data['outphone']}</td>
					<td>{$_ex_data['remark']}</td>
					<td>{$_ex_data['created']}</td>
					<td>
					{foreach $_ex_data['other'] as $k => $v}
						{$k}-->{$v}<br>
					{/foreach}
					</td>
					<td>{if $_ex_data['check'] == 1}已签到{else}已报名{/if}</td>
				</tr>
				{foreachelse}
				<tr class="warning">
					<td colspan="6">暂无报名人</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<a href="javascript:history.go(-1);" class="btn btn-default">返回</a>
</div>

<script type="text/javascript">
	$(function(){
		$('#btn_export').on('click', function(){
			var href = $('.nav-tabs li.active a').attr('href').replace('#', '');
			window.location.href = window.location.href + '&export=' + href;

			return false;
		});
	});
</script>
{*<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}">
<input type="hidden" name="formhash" value="{$formhash}" />
<div class="table-light">
	<div class="table-header">
		<strong class="label label-info font12">报名人</strong>
	</div>
	<table class="table table-bordered table-hover font12">
		<colgroup>
			<col class="t-col-50" />
			<col class="t-col-50" />
		</colgroup>
		<thead>
			<tr>
				<th>报名人</th>
				<th>报名时间</th>
			</tr>
		</thead>
	{if $total > 0}
		<tfoot>
			<tr>
				<td colspan="2" class="text-right vcy-page">{$multi}</td>
			</tr>
		</tfoot>
	{/if}
		<tbody>
	{foreach $list as $_id => $_data}
			<tr>
				<td>{$_data['name']|escape}</td>
				<td>{$_data['created']|escape}</td>
			</tr>
	{foreachelse}
			<tr>
				<td colspan="2" class="warning">{if $issearch}未搜索到指定条件的{$module_plugin['cp_name']|escape}数据{else}暂无任何{$module_plugin['cp_name']|escape}数据{/if}</td>
			</tr>
	{/foreach}
		</tbody>
	</table>
	</form>
</div>*}


{include file="$tpl_dir_base/footer.tpl"}