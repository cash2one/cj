{include file='admincp/header.tpl'}

	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title font12"><strong>{$askfor['af_subject']|escape}</strong></h3>
		</div>
		<div class="panel-body">
			<dl class="dl-horizontal font12 vcy-dl-list">
				<dt>申请人：</dt>
				<dd>
					<strong class="label label-primary font12">{$askfor['m_username']|escape}</strong>
					&nbsp;&nbsp;
					<abbr title="申请时间"><span class="badge">{$askfor['_created']}</span></abbr>
				</dd>
{if $ccMemberList}
				<dt>抄送人：</dt>
				<dd>
	{foreach $ccMemberList as $_uid => $_username}
					<span class="label label-info font12">{$_username}</span>
					
	{/foreach}
				</dd>
{/if}
				<dt>申请说明：</dt>
				<dd>
					<blockquote class="m-0 font12">
						<h3 class="font12 text-bold"><strong>{$askfor['af_subject']|escape}</strong></h3>
						{$askfor['af_message']|escape}
					</blockquote>
				</dd>
{if $procMemberList}
				<dt>审批人：</dt>
				<dd>
	{foreach $procMemberList as $_uid => $_username}
					<span class="label label-success font12">{$_username|escape}</span>
	{/foreach}
				</dd>
{/if}
				<dt>审批状态：</dt>
				<dd class="text-{$askfor['_status_class_tag']}">{$askfor['_status']}</dd>
			</dl>
			<br /><br />
			<ul class="nav nav-tabs font12">
				<li class="active">
					<a href="#list_proc" data-toggle="tab">
						<span class="badge pull-right"> {$countProc} </span>
						审核进程&nbsp;
					</a>
				</li>
				<li>
					<a href="#list_comment" data-toggle="tab">
						<span class="badge pull-right"> {$countComment} / {$countReply} </span>
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
{foreach $procList as $_afp_id => $_afp}
							<tr>
								<td>{$_afp['_username']}</td>
								<td class="text-{$_afp['_status_class_tag']}">{$_afp['_status']}</td>
								<td>{$_afp['_created']}</td>
								<td>{$_afp['afp_note']}</td>
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
{foreach $commentList as $_afc_id => $_afc}
					<div class="well">
						<div class="row">
							<div class="col-sm-12 text-primary">
								<h4 class="font12">
									<strong class="label label-{if $askfor['m_uid'] == $_afc['_thread']['m_uid']}primary{elseif isset($proc_users[$_afc['_thread']['m_uid']])}success{else}info{/if} font12">{$_afc['_thread']['_username']}</strong>
									<span class="badge">{$_afc['_thread']['_created']}</span>
								</h4>
								{$_afc['_thread']['_message']}
							</div>
						</div>
	{foreach $_afc['_reply'] as $_afr_id => $_afr}
						<div class="row">
							<div class="col-md-11 col-md-offset-1 text-right text-info">
								<h4 class="font12">
									<strong class="label label-{if $askfor['m_uid'] == $_afr['m_uid']}primary{elseif isset($proc_users[$_afr['m_uid']])}success{else}info{/if} font12">{$_afr['_username']}</strong>
									<span class="badge">{$_afr['_created']}</span>
								</h4>
								{$_afr['_message']}
							</div>
						</div>
	{/foreach}
					</div>
{foreachelse}
					<dl class="font12 vcy-dl-list">
						<dt>&nbsp;</dt>
						<dd><p class="well text-warning well-lg">暂无评论信息</p></dd>
					</dl>
{/foreach}
				</div>
			</div>
		</div>
	</div>

{include file='admincp/footer.tpl'}