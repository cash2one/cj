{include file='admincp/header.tpl'}

	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title font12"><strong>{$askoff['ao_subject']|escape}</strong></h3>
		</div>
		<div class="panel-body">
			<dl class="dl-horizontal font12 vcy-dl-list">
				<dt>申请人：</dt>
				<dd>
					<strong class="label label-primary font12">{$askoff['m_username']|escape}</strong>
					&nbsp;&nbsp;
					<abbr title="申请时间"><span class="badge">{$askoff['_created']}</span></abbr>
				</dd>
{if $cc_users}
				<dt>抄送人：</dt>
				<dd>
	{foreach $cc_users as $_uid => $_username}
					<span class="label label-info font12">{$_username}</span>
					
	{/foreach}
				</dd>
{/if}
				<dt>申请说明：</dt>
				<dd>
					<blockquote class="m-0 font12">
						<h3 class="font12 text-bold"><strong>{$askoff['ao_subject']|escape}</strong></h3>
						{$askoff['_message']}
					</blockquote>
				</dd>
				<dt>审批状态：</dt>
				<dd class="text-{$askoff['_status_class_tag']}">
					<strong>{$current_proc['_status']}</strong>
					{if $current_proc['_remark']}<span class="help-block">{$current_proc['_remark']}</span>{/if}
				</dd>
			</dl>
			<br /><br />
			<ul class="nav nav-tabs font12">
				<li class="active">
					<a href="#list_proc" data-toggle="tab">
						<span class="badge pull-right"> {$proc_count} </span>
						审核进程&nbsp;
					</a>
				</li>
				<li>
					<a href="#list_comment" data-toggle="tab">
						<span class="badge pull-right"> {$post_count} </span>
						评论/回复&nbsp;
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
								<th>审批人</th>
								<th>审批状态</th>
								<th>审批时间</th>
								<th>备注</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="4"></td>
							</tr>
						</tfoot>
						<tbody>
{foreach $proc_list as $_aopc_id => $_aopc}
							<tr>
								<td>{$_aopc['m_username']|escape}</td>
								<td class="text-{$_aopc['_status_class_tag']}">{$_aopc['_status']}</td>
								<td>{$_aopc['_created']}</td>
								<td>{$_aopc['_remark']}</td>
							</tr>
{foreachelse}
							<tr class="warning">
								<td colspan="4">暂无审批记录</td>
							</tr>
{/foreach}
						</tbody>
					</table>
				</div>
				<div class="tab-pane" id="list_comment">
					<table class="table table-striped table-hover font12">
						<colgroup>
							<col class="t-col-15" />
							<col class="t-col-15" />
							<col />
						</colgroup>
						<thead>
							<tr>
								<th>回复人</th>
								<th>回复时间</th>
								<th>内容</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="3"></td>
							</tr>
						</tfoot>
						<tbody>
{foreach $post_list as $_aopt_id => $_aopt}
							<tr>
								<td>{$_aopt['m_username']|escape}</td>
								<td>{$_aopt['_created']}</td>
								<td>{$_aopt['_message']}</td>
							</tr>
{foreachelse}
							<tr class="warning">
								<td colspan="3">暂无评论信息</td>
							</tr>
{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

{include file='admincp/footer.tpl'}