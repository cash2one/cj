{include file='admincp/header.tpl'}

<div class="panel panel-default">
	<div class="panel-body">
		<dl class="dl-horizontal font12 vcy-dl-list">
			<dt>投票主题：</dt>
			<dd><strong>{$vote['v_subject']|escape}</strong></dd>
			<dt>投票发起人：</dt>
			<dd>
				<strong class="label label-primary font12">{$vote['m_username']|escape}</strong>
				&nbsp;&nbsp;
				<abbr title="最后更新时间"><span class="badge">{$vote['_updated']}</span></abbr>
			</dd>
			<dt>投票状态：</dt>
			<dd><strong class="text-info">{$vote['_status']}</strong>{if $vote['_set_status_urls']}  <i class="fa fa-angle-double-right"></i> 重设为：{$vote['_set_status_urls']}{/if}</dd>
			<dt>项目进度：</dt>
			<dd>
				
			</dd>
			<dt>投票周期：</dt>
			<dd>{$vote['_begintime']} 至 {$vote['_endtime']}</dd>
			<dt>投票详情：</dt>
			<dd>
				<blockquote class="m-0 font12">
					<h3 class="font12 text-bold"><strong>{$vote['v_subject']|escape}</strong></h3>
					{$vote['_message']}
				</blockquote>
				<a href="{if $editUrl}{$editUrl}{else}javascript:;{/if}" class="btn btn-primary btn-sm{if !$editUrl} disabled{/if}" role="button"{if !$editUrl} disabled="disabled"{/if}><span class="fa fa-edit"></span> 编辑投票</a>
			</dd>
			<dt>投票人次：</dt>
			<dd>{$vote['v_voters']}</dd>
{if in_array('v_ismulti', $voteFunctions)}
			<dt>是否多选：</dt>
			<dd>{$vote['_ismulti']}</dd>
{/if}
{if in_array('v_ismulti', $voteFunctions)}
			<dt>是否开放：</dt>
			<dd>{$vote['_isopen']}</dd>
{/if}
{if in_array('v_ismulti', $voteFunctions)}
			<dt>是否对外开放：</dt>
			<dd>{$vote['_inout']}</dd>
{/if}
			<dt>允许参与投票的人员：</dt>
			<dd style="line-height:150%;">
{if $vote['v_friend']}
	{foreach $permitUsers AS $_id => $_data}
				<span class="label label-info font12">{$_data['m_username']|escape}</span>
	{foreachelse}
				<strong>所有人</strong>
	{/foreach}
{else}
				<strong>所有人</strong>
{/if}
			</dd>
		</dl>
		<div class="panel panel-default font12">
			<div class="panel-heading"><strong>票选详情</strong></div>
			<div class="panel-body">
				<div class="row">
					<div class="col-sm-5 text-right text-success"><strong>选项</strong></div>
					<div class="col-sm-5"><strong>得票率</strong></div>
					<div class="col-sm-2 text-left text-danger"><strong>票数/总票数</strong></div>
				</div>
{foreach $options AS $_id => $_data}
				<div class="row">
					<div class="col-sm-5 text-right text-success"><strong>{$_data['vo_option']|escape}</strong></div>
					<div class="col-sm-5">
						<div class="progress font10" title="得票率{$_data['_vote_rate']}%">
							<div class="progress-bar progress-bar-success t-col-{$_data['_vote_rate']} progress_black" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
								<span>{$_data['_vote_rate']}%</span>
							</div>
						</div>
					</div>
					<div class="col-sm-2 text-left text-danger">{$_data['vo_votes']}/{$vote['v_voters']}</div>
				</div>
{/foreach}
    		</div>
		</div>

		<ul class="nav nav-tabs font12">
			<li class="active">
				<a href="#list_member" data-toggle="tab">
					<span class="badge pull-right"> {$total} </span>
					投票日志&nbsp;
				</a>
			</li>
		</ul>
		<br />
		<div class="tab-content">
			<div class="tab-pane active" id="list_proc">
				<table class="table table-striped table-hover font12">
					<colgroup>
						<col class="t-col-15" />
						<col />
						<col class="t-col-20" />
						<col class="t-col-15" />
					</colgroup>
					<thead>
						<tr>
							<th>投票人</th>
							<th>投出选项</th>
							<th>时间</th>
							<th>IP</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="4"></td>
						</tr>
					</tfoot>
					<tbody>
{foreach $voteList as $_id => $_data}
						<tr>
							<td>{$_data['m_username']|escape}</td>
							<td>{$_data['_option']|escape}</td>
							<td>{$_data['_updated']}</td>
							<td>{$_data['vm_ip']}</td>
						</tr>
{foreachelse}
						<tr class="warning">
							<td colspan="4">暂无任何投票记录</td>
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