{include file='admincp/header.tpl'}

<div class="panel panel-default">
	<div class="panel-body">
		<dl class="dl-horizontal font12 vcy-dl-list">
			<dt>{$module_plugin['cp_name']|escape}标题：</dt>
			<dd><strong>{$dailyreport['_reporttime_fmt']['m']}-{$dailyreport['_reporttime_fmt']['d']} {$weeknames[$dailyreport['_reporttime_fmt']['w']]}日报</strong></dd>
			<dt>发起人：</dt>
			<dd>
				<strong class="label label-primary font12">{$dailyreport['m_username']|escape}</strong>
				&nbsp;&nbsp;
				<abbr title="发起时间"><span class="badge">{$dailyreport['_created']}</span></abbr>
			</dd>
			<dt>报告日期：</dt>
			<dd>{$dailyreport['_reporttime_fmt']['Y']}-{$dailyreport['_reporttime_fmt']['m']}-{$dailyreport['_reporttime_fmt']['d']}</dd>
			<dt>发送给：</dt>
			<dd style="line-height:150%;">
{foreach $to_users AS $_id => $_data}
				<span class="label label-info font12">{$_data['m_username']|escape}</span>
{foreachelse}
				<strong>无</strong>
{/foreach}
			</dd>
			<dt>转发给：</dt>
			<dd style="line-height:150%;">
{foreach $cc_users AS $_id => $_data}
				<span class="label label-info font12">{$_data['m_username']|escape}</span>
{foreachelse}
				<strong>无</strong>
{/foreach}
			</dd>
			<dt>详情：</dt>
			<dd>
				<blockquote class="m-0 font12">
					<h3 class="font12 text-bold"><strong>{$dailyreport['dr_subject']|escape}</strong></h3>
					{if isset($dailyreport['_message'])}{$dailyreport['_message']}{/if}
				</blockquote>
			</dd>
		</dl>

		<ul class="nav nav-tabs font12">
			<li class="active">
				<a href="#list_member" data-toggle="tab">
					<span class="badge pull-right"> {$posts_total} </span>
					{$module_plugin['cp_name']|escape}批注&nbsp;
				</a>
			</li>
		</ul>
		<br />
		<div class="tab-content">
			<div class="tab-pane active" id="list_proc">
				<table class="table table-striped table-hover font12">
					<colgroup>
						<col class="t-col-15" />
						<col class="t-col-20" />
						<col />
					</colgroup>
					<thead>
						<tr>
							<th>用户</th>
							<th>时间</th>
							<th>内容</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="3"></td>
						</tr>
					</tfoot>
					<tbody>
{foreach $posts as $_id => $_data}
						<tr>
							<td>{$_data['m_username']|escape}</td>
							<td>{$_data['_created']|escape}</td>
							<td>
								{if $_data['drp_subject']}<h4>{$_data['drp_subject']}</h4>{/if}
								<p>{$_data['_message']}</p>
							</td>
						</tr>
{foreachelse}
						<tr class="warning">
							<td colspan="3">暂无任何批注记录</td>
						</tr>
{/foreach}
					</tbody>
				</table>
			</div>
			<div class="text-right"><a href="javascript:history.go(-1);" class="btn btn-default">返回</a></div>
		</div>
	</div>
</div>

{include file='admincp/footer.tpl'}