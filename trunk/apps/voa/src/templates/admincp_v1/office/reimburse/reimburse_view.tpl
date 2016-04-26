{include file='admincp/header.tpl'}

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title font12"><strong>{$module_plugin['cp_name']|escape}：{$reimburse['rb_subject']|escape}</strong></h3>
	</div>
	<div class="panel-body">
		<dl class="dl-horizontal font12 vcy-dl-list">
			<dt>申请人：</dt>
			<dd>
				<strong class="label label-primary font12">{$reimburse['m_username']|escape}</strong>
				&nbsp;&nbsp;
				<abbr title="申请时间"><span class="badge">{$reimburse['_time']}</span></abbr>
			</dd>
			<dt>状态：</dt>
			<dd>{$reimburse['_status']}</dd>
		</dl>
		<br /><br />
		<ul class="nav nav-tabs font12">
			<li class="active">
				<a href="#list_bill" data-toggle="tab">
					<span class="badge pull-right"> {$bill_count} </span>
					账单详情&nbsp;
				</a>
			</li>
			<li>
				<a href="#list_proc" data-toggle="tab">
					<span class="badge pull-right"> {$proc_count} </span>
					审批进程&nbsp;
				</a>
			</li>
			<li>
				<a href="#list_comment" data-toggle="tab">
					<span class="badge pull-right"> {$post_count} </span>
					批复备注&nbsp;
				</a>
			</li>
		</ul>
		<br />
		<div class="tab-content">
			<div class="tab-pane active" id="list_bill">
				<table class="table table-striped table-hover font12">
					<colgroup>
						<col class="t-col-20" />
						<col class="t-col-20" />
						<col class="t-col-20" />
						<col />
					</colgroup>
					<thead>
						<tr>
							<th>账单类型</th>
							<th>支出时间</th>
							<th>金额</th>
							<th>事由</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="4"></td>
						</tr>
					</tfoot>
					<tbody>
{foreach $bill_list as $rbb}
						<tr>
							<td>{$rbb['_type']}</td>
							<td>{$rbb['_time']}</td>
							<td>{$rbb['_expend']}</td>
							<td>{$rbb['_reason']}</td>
						</tr>
{foreachelse}
						<tr>
							<td colspan="4">该申请无账单清单数据</td>
						</tr>
{/foreach}
					</tbody>
				</table>
			</div>
			<div class="tab-pane" id="list_proc">
				<table class="table table-striped table-hover font12">
					<colgroup>
						<col class="t-col-20" />
						<col class="t-col-20" />
						<col class="t-col-20" />
						<col />
					</colgroup>
					<thead>
						<tr>
							<th>姓名</th>
							<th>时间</th>
							<th>状态</th>
							<th>备注</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="4"></td>
						</tr>
					</tfoot>
					<tbody>
{foreach $proc_list as $proc}
						<tr>
							<td>{$proc['m_username']|escape}</td>
							<td>{$proc['_created']}</td>
							<td>{$proc['_status']}</td>
							<td>{$proc['_remark']}</td>
						</tr>
{foreachelse}
						<tr>
							<td colspan="4">暂无审批进度数据</td>
						</tr>
{/foreach}
					</tbody>
				</table>
			</div>
			<div class="tab-pane" id="list_comment">
				<table class="table table-striped table-hover font12">
					<colgroup>
						<col class="t-col-20" />
						<col class="t-col-20" />
						<col />
					</colgroup>
					<thead>
						<tr>
							<th>姓名</th>
							<th>时间</th>
							<th>批注</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="3"></td>
						</tr>
					</tfoot>
					<tbody>
{foreach $post_list as $_rbpt_id => $_rbpt}
						<tr>
							<td>{$_rbpt['m_username']|escape}</td>
							<td>{$_rbpt['_created']}</td>
							<td>{$_rbpt['_message']}</td>
						</tr>
{foreachelse}
						<tr class="warning">
							<td colspan="3">暂无批注信息</td>
						</tr>
{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

{include file='admincp/footer.tpl'}