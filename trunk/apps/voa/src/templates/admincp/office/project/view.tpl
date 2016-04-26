{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title font12"><strong>{$project['p_subject']|escape}</strong></h3>
		</div>
		<div class="panel-body">
			<table class="table table-no-border font12">
				<colgroup>
					<col class="t-col-20" />
					<col />
				</colgroup>
				<tbody>
					<tr>
						<th class="text-right">发起人：</th>
						<td class="text-left">
							<strong class="label label-primary font12">{$project['m_username']|escape}</strong>
							<span class="space"></span>
							<abbr title="最后更新时间"><span class="badge">{$project['_updated']}</span></abbr>
						</td>
					</tr>
					<tr>
						<th class="text-right">状态：</th>
						<td class="text-left">{$project['_status']}</td>
					</tr>
					<tr>
						<th class="text-right">进度：</th>
						<td class="text-left">
							<div class="progress font10" title="已完成 {$project['p_progress']}%">
								<div class="progress-bar progress-bar-success t-col-{$project['p_progress']} progress_black" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
									<span>{$project['p_progress']}%</span>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<th class="text-right" style="vertical-align:top;">具体任务说明：</th>
						<td class="text-left">
							<blockquote class="m-0 font12">
								<h3 class="font12 text-bold"><strong>{$project['p_subject']|escape}</strong></h3>
								{$project['_message']}
							</blockquote>
							<div class="row">
{if $attach_list}
	{foreach $attach_list as $_at}
								<div class="col-xs-2">
									<a href="{$_at['url']}" target="_blank" class="thumbnail"><img src="{$_at['thumb']}" border="0" alt="" /></a>
								</div>
	{/foreach}
{/if}
							</div>
<!--
							<a href="{if $editUrl}{$editUrl}{else}javascript:;{/if}" class="btn btn-primary btn-sm{if !$editUrl} disabled{/if}" role="button"{if !$editUrl} disabled="disabled"{/if}><span class="fa fa-edit"></span> 编辑</a>
							<span class="space"></span>
							<a href="{if $advancedUrl}{$advancedUrl}{else}javascript:;{/if}" class="btn btn-warning btn-sm{if !$advancedUrl} disabled{/if}" role="button"{if !$advancedUrl} disabled="disabled"{/if}><span class="fa fa-bullhorn"></span> 推进</a>
-->
						</td>
					</tr>
					<tr>
						<th class="text-right">周期：</th>
						<td class="text-left">{$project['_begintime']} 至 {$project['_endtime']}</td>
					</tr>
					<tr>
						<th class="text-right">总时间：</th>
						<td class="text-left">{$project['_totaltime'][0]}天 {$project['_totaltime'][1]}小时 {$project['_totaltime'][2]}分</td>
					</tr>
					<tr>
						<th class="text-right">已用时：</th>
						<td class="text-left">{$project['_usetime'][0]}天 {$project['_usetime'][1]}小时 {$project['_usetime'][2]}分</td>
					</tr>
					<tr>
						<th class="text-right">剩余时间：</th>
						<td class="text-left">{if $project['_remaintime'][0] >= 0}{$project['_remaintime'][0]}天 {$project['_remaintime'][1]}小时 {$project['_remaintime'][2]}分{else}已过期{/if}</td>
					</tr>
{if $ccMemberList}
					<tr>
						<th class="text-right">抄送：</th>
						<td class="text-left">
	{foreach $ccMemberList as $_data}
							<span class="label label-info font12">{$_data['m_username']|escape}</span>
					
	{/foreach}
						</td>
					</tr>
{/if}
{if $memberList}
					<tr>
						<th class="text-right">参与者：</th>
						<td class="text-left">
	{foreach $memberList as $_data}
							<span class="label label-success font12">{$_data['m_username']|escape}</span>
	{/foreach}
						</td>
					</tr>
{/if}
				</tbody>
			</table>
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
	
			<div class="tab-content">
				<div class="tab-pane active" id="list_proc">
					<table class="table table-striped table-hover table-bordered font12 table-light">
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
					<table class="table table-striped table-hover table-bordered font12 table-light">
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
						<tbody>
{foreach $memberList as $_id => $_data}
							<tr>
								<td>{$_data['_updated']}</td>
								<td>{$_data['m_username']|escape}</td>
								<td>{$_data['_status']}</td>
								<td>
	{if $_data['pm_status'] != voa_d_oa_project_mem::STATUS_OUTOF}
									<div class="progress font10" title="已完成 {$_data['pm_progress']}%">
										<div class="progress-bar progress-bar-success t-col-{$_data['pm_progress']} progress_black" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
											<span>{$_data['pm_progress']}%</span>
										</div>
									</div>
	{/if}
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
			<a href="javascript:history.go(-1);" class="btn btn-default">返回</a>
			
		</div>
	</div>
</div>
{include file="$tpl_dir_base/footer.tpl"}