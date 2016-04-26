{include file='admincp/header.tpl'}

<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title font12"><strong>{$project['p_subject']|escape}</strong></h3>
		</div>
		<div class="panel-body">
			<dl class="dl-horizontal font12 vcy-dl-list">
				<dt>发起人：</dt>
				<dd>
					<strong class="label label-primary font12">{$project['m_username']|escape}</strong>
					&nbsp;&nbsp;
					<abbr title="最后更新时间"><span class="badge">{$project['_updated']}</span></abbr>
				</dd>
				<dt>状态：</dt>
				<dd>{$project['_status']}</dd>
				<dt>进度：</dt>
				<dd>
					<div class="progress font10" title="已完成 {$project['p_progress']}%">
						<div class="progress-bar progress-bar-success t-col-{$project['p_progress']} progress_black" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
							<span>{$project['p_progress']}%</span>
						</div>
					</div>
				</dd>
				<dt>具体任务说明：</dt>
				<dd>
					<blockquote class="m-0 font12">
						<h3 class="font12 text-bold"><strong>{$project['p_subject']|escape}</strong></h3>
						{$project['_message']}
					</blockquote>
					<a href="{if $editUrl}{$editUrl}{else}javascript:;{/if}" class="btn btn-primary btn-sm{if !$editUrl} disabled{/if}" role="button"{if !$editUrl} disabled="disabled"{/if}><span class="fa fa-edit"></span> 编辑</a>
					&nbsp;&nbsp;
					<a href="{if $advancedUrl}{$advancedUrl}{else}javascript:;{/if}" class="btn btn-warning btn-sm{if !$advancedUrl} disabled{/if}" role="button"{if !$advancedUrl} disabled="disabled"{/if}><span class="fa fa-bullhorn"></span> 推进</a>
				</dd>
				<dt>周期：</dt>
				<dd>{$project['_begintime']} 至 {$project['_endtime']}</dd>
				<dt>总时间：</dt>
				<dd>{$project['_totaltime'][0]}天 {$project['_totaltime'][1]}小时 {$project['_totaltime'][2]}分</dd>
				<dt>已用时：</dt>
				<dd>{$project['_usetime'][0]}天 {$project['_usetime'][1]}小时 {$project['_usetime'][2]}分</dd>
				<dt>剩余时间：</dt>
				<dd>{if $project['_remaintime'][0] > 0}{$project['_remaintime'][0]}天 {$project['_remaintime'][1]}小时 {$project['_remaintime'][2]}分{else}已过期{/if}</dd>
{if $ccMemberList}
				<dt>抄送：</dt>
				<dd>
	{foreach $ccMemberList as $_data}
					<span class="label label-info font12">{$_data['m_username']|escape}</span>
					
	{/foreach}
				</dd>
{/if}
{if $memberList}
				<dt>参与者：</dt>
				<dd>
	{foreach $memberList as $_data}
					<span class="label label-success font12">{$_data['m_username']|escape}</span>
	{/foreach}
				</dd>
{/if}

			</dl>
			<br /><br />
			<ul class="nav nav-tabs font12">
				<li class="active">
					<a href="#list_proc" data-toggle="tab">
						<span class="badge pull-right"> {$progressCount} </span>
						进程记录&nbsp;
					</a>
				</li>
				<li>
					<a href="#list_member" data-toggle="tab">
						<span class="badge pull-right"> {$memberCount} </span>
						参与者&nbsp;
					</a>
				</li>
			</ul>
			<br />
			<div class="tab-content">
				<div class="tab-pane active" id="list_proc">
					<table class="table table-striped table-hover font12">
						<colgroup>
							<col class="t-col-15" />
							<col class="t-col-15" />
							<col class="t-col-20" />
							<col />
						</colgroup>
						<thead>
							<tr>
								<th>时间</th>
								<th>参与人</th>
								<th>进度值</th>
								<th>备注</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="4"></td>
							</tr>
						</tfoot>
						<tbody>
{foreach $progressList as $_id => $_data}
							<tr>
								<td>{$_data['_updated']}</td>
								<td>{$_data['m_username']|escape}</td>
								<td>
									<div class="progress font10" title="已完成 {$_data['pp_progress']}%">
										<div class="progress-bar progress-bar-success t-col-{$_data['pp_progress']} progress_black" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
											<span>{$_data['pp_progress']}%</span>
										</div>
									</div>
								</td>
								<td>{$_data['_message']}</td>
							</tr>
{foreachelse}
							<tr class="warning">
								<td colspan="4">暂无进展记录</td>
							</tr>
{/foreach}
						</tbody>
					</table>
				</div>
				<div class="tab-pane" id="list_member">
					<table class="table table-striped table-hover font12">
						<colgroup>
							<col class="t-col-18" />
							<col class="t-col-18" />
							<col class="t-col-20" />
							<col />
						</colgroup>
						<thead>
							<tr>
								<th>最后更新时间</th>
								<th>参与人</th>
								<th>状态</th>
								<th>进度值</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="4"></td>
							</tr>
						</tfoot>
						<tbody>
{foreach $memberList as $_id => $_data}
							<tr>
								<td>{$_data['_updated']}</td>
								<td>{$_data['m_username']|escape}</td>
								<td>{$_data['_status']}</td>
								<td>
									<div class="progress font10" title="已完成 {$_data['pm_progress']}%">
										<div class="progress-bar progress-bar-success t-col-{$_data['pm_progress']} progress_black" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
											<span>{$_data['pm_progress']}%</span>
										</div>
									</div>
								</td>
							</tr>
{foreachelse}
							<tr class="warning">
								<td colspan="4">暂无参与者记录</td>
							</tr>
{/foreach}
						</tbody>
					</table>
			</div>
			<dl class="dl-horizontal font12 vcy-dl-list">
				<dt>&nbsp;</dt>
				<dd><a href="javascript:history.go(-1);" class="btn btn-default">返回</a></dd>
			</dl>
		</div>
	</div>
</div>
{include file='admincp/footer.tpl'}