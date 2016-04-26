{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title font12"><strong>{$module_plugin['cp_name']|escape}：{$reimburse['rb_subject']|escape}</strong></h3>
	</div>
	<div class="panel-body">
		<dl class="dl-horizontal font12 vcy-dl-list" style="margin-bottom:0">
			<dt>申请人：</dt>
			<dd>
				<strong class="label label-primary font12">{$reimburse['m_username']|escape}</strong>
				&nbsp;&nbsp;
				<abbr title="申请时间"><span class="badge">{$reimburse['_time']}</span></abbr>
			</dd>
			<dt>状态：</dt>
			<dd>{$reimburse['_status']}</dd>
			<dt>报销总计：</dt>
			<dd>￥{$money_total}</dd>
{if !empty($proc_user_list)}
			<dt>审批人：</dt>
			<dd>
	{foreach $proc_user_list as $_proc}
				<strong class="label label-info font12">{$_proc['m_username']|escape}</strong>
				<span class="space"></span>
	{/foreach}
			</dd>
{/if}
			<dt>事由：</dt>
			<dd>{$reimburse['rb_subject']|escape}</dd>
		</dl>
	</div>
</div>
		<ul class="nav nav-tabs font12">
			<li class="active">
				<a href="#list_bill" data-toggle="tab">
					<span class="badge pull-right"> {$bill_count} </span>
					清单明细&nbsp;
				</a>
			</li>
			<li>
				<a href="#list_proc" data-toggle="tab">
					<span class="badge pull-right"> {$proc_count} </span>
					审批进程&nbsp;
				</a>
			</li>
<!--
			<li>
				<a href="#list_comment" data-toggle="tab">
					<span class="badge pull-right"> {$post_count} </span>
					批复备注&nbsp;
				</a>
			</li>
-->
		</ul>
		
		<div class="tab-content">
			<div class="tab-pane active" id="list_bill">
				<table class="table table-striped table-hover table-bordered font12 table-light">
					<colgroup>
						<col class="t-col-15" />
						<col class="t-col-20" />
						<col class="t-col-10" />
						<col />
					</colgroup>
					<thead>
						<tr>
							<th>账单类型<span class="space"></span><a href="{$plugin_setting_url}" target="_blank" style="font-weight:normal;">新增类型？</a></th>
							<th>支出时间</th>
							<th>金额</th>
							<th>事由</th>
						</tr>
					</thead>
					
					<tbody>
{foreach $bill_list as $rbb}
						<tr>
							<td>{$rbb['_type']}</td>
							<td>{$rbb['_time']}</td>
							<td>{$rbb['_expend']}</td>
							<td class="text-left">
								{$rbb['_reason']}
	{if !empty($rbb['_attachs'])}
								<div class="row" style="margin:0;padding:0">
		{foreach $rbb['_attachs'] as $_at}
									<div class="col-xs-2">
										<a href="{$_at['url']}" target="_blank" class="thumbnail"><img src="{$_at['thumb']}" border="0" alt="" /></a>
									</div>
		{/foreach}
								</div>
	{/if}
							</td>
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
				<table class="table table-striped table-hover table-bordered font12 table-light">
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
							<td>状态</td>
							<th>备注</th>
						</tr>
					</thead>
					
					<tbody>
{foreach $proc_list as $proc}
	{if $proc['m_uid'] != $reimburse['m_uid']}
						<tr>
							<td>{$proc['m_username']|escape}</td>
							<td>{$proc['_created']}</td>
							<td>{$proc['_status_tip']}</td>
							<td>{$proc['_remark']}</td>
						</tr>
	{/if}
{foreachelse}
						<tr>
							<td colspan="4">暂无审批进度数据</td>
						</tr>
{/foreach}
					</tbody>
				</table>
			</div>
<!--
			<div class="tab-pane" id="list_comment">
				<table class="table table-striped table-hover table-bordered font12 table-light">
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
					
					<tbody>
{foreach $post_list as $_rbpt_id => $_rbpt}
						<tr>
							<td>{$_rbpt['m_username']|escape}<span class="help-block">{$_rbpt['_created']}</span></td>
							<td>{$_rbpt['_updated']}</td>
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
-->
		</div>



{include file="$tpl_dir_base/footer.tpl"}