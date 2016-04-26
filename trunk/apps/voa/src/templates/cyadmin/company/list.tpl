{include file='cyadmin/header.tpl'}
<script>
	$(function () {
		$('.fa-lock').parents('a').click(function () {
			if (confirm('确定操作?')) {
				return true;
			} else {
				return false;
			}
		});
		$('#sandbox-container .input-daterange').datepicker({
			todayHighlight: true
		});
	});
</script>

{if $job == 1}
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" {if !$cust}class="active"{/if}><a role="tab" data-toggle="tab" id="all_customer">全部客户</a></li>
		<li role="presentation" {if $cust == 'mine'}class="active"{/if}><a role="tab" data-toggle="tab" id="my_customer">我的客户</a></li>
		<li role="presentation" {if $cust == 'under'}class="active"{/if}><a role="tab" data-toggle="tab" id="my_un_customer">我下属的客户</a></li>
	</ul>
{/if}
<script>
	$(function () {
		// URL路径
		var domain_url = '{$url}';
		var basic_param = '/enterprise/company';

		$('#all_customer').on('click', function () {
			console.log(1);
			location.href = domain_url + basic_param;
		});
		$('#my_customer').on('click', function () {
			console.log(2);
			location.href = domain_url + basic_param + '?cust=mine';
		});
		$('#my_un_customer').on('click', function () {
			console.log(3);
			location.href = domain_url + basic_param + '?cust=under';
		});
	})
</script>

<div class="panel panel-default">
	<div class="panel-heading"><b>搜索</b>

		<div class="panel-body">
			<form class="form-horizontal" action="{$form_url}" method="post">
				<input type="hidden" name="issearch" value="1"/>
				<input type="hidden" name="cust" value="{$cust}"/>
				<div class="form-group ">
					<label class="control-label col-sm-1">企业名称</label>

					<div class="col-sm-2">
						<input type="text"
						       name="ep_name"
						       value="{$searchBy['ep_name']}"
						       class="input-sm form-control"/>
					</div>

					<label class="control-label col-sm-1">所在行业</label>

					<div class="col-sm-2">
						<select name="ep_industry" class="form-control form-small"
						        data-width="auto" style="height: 34px; border-radius: 4px;">
							<option value="">请选择</option>
							{foreach $industry as $k => $v}
								{if $searchBy['ep_industry'] == $v}
									<option value="{$v}" selected>{$v}</option>
								{else}
									<option value="{$v}">{$v}</option>
								{/if}
							{/foreach}
						</select>
					</div>

					<label class="control-label col-sm-1">客户状态</label>

					<div class="col-sm-2">
						<select id="customer_status" name="customer_status" class="form-control form-small" data-width="auto"
						        style="height: 30px; border-radius: 4px;">
							<option value=""{if $searchBy['customer_status'] == ''} selected="selected"{/if}>请选择</option>
							{foreach $customer_status as $k => $v}
								<option value="{$k}"{if $searchBy['customer_status'] == $k} selected="selected"{/if}>{$v}</option>
								{foreachelse}
								<option value="">无</option>
							{/foreach}
						</select>
					</div>

					<label class="control-label col-sm-1">客户等级</label>

					<div class="col-sm-2">
						<select id="ep_customer_level" name="ep_customer_level" class="form-control form-small" data-width="auto"
						        style="height: 30px; border-radius: 4px;">
							<option value=""{if $searchBy['ep_customer_level'] == ''} selected="selected"{/if}>请选择</option>
							{foreach $customer_level as $k => $v}
								<option value="{$k}"{if $searchBy['ep_customer_level'] == $k} selected="selected"{/if}>{$v}</option>
								{foreachelse}
								<option value="">无</option>
							{/foreach}
						</select>
					</div>
				</div>

				<div class="form-group ">
					<label class="control-label col-sm-1">客户来源</label>

					<div class="col-sm-2">
						<input type="text"
						       name="ep_ref"
						       value="{$searchBy['ep_ref']}"
						       class="input-sm form-control"/>
					</div>

					<label class="control-label col-sm-1">手机号</label>

					<div class="col-sm-2">
						<input type="text"
						       name="ep_mobilephone"
						       value="{$searchBy['ep_mobilephone']}"
						       class="input-sm form-control"/>
					</div>

					<label class="control-label col-sm-1">代理商</label>

					<div class="col-sm-2">
						<select id="id_number" name="id_number" class="form-control form-small" data-width="auto"
						        style="height: 30px; border-radius: 4px;">
							<option value=""{if $searchBy['id_number'] == ''} selected="selected"{/if}>请选择</option>
							{foreach $all_agent as $k => $v}
								<option value="{$v['id_number']}"{if $searchBy['id_number'] == $v['id_number']} selected="selected"{/if}>{$v['id_number']}</option>
								{foreachelse}
								<option value="">无</option>
							{/foreach}
						</select>
					</div>

					<label class="control-label col-sm-1">是否绑定</label>

					<div class="col-sm-2">
						<select id="ep_wxcorpid" name="ep_wxcorpid" class="form-control form-small" data-width="auto"
						        style="height: 30px; border-radius: 4px;">
							<option value=""{if $searchBy['ep_wxcorpid'] == ''} selected="selected"{/if}>请选择</option>
							<option value="1"{if $searchBy['ep_wxcorpid'] == 1} selected="selected"{/if}>已绑定</option>
							<option value="2"{if $searchBy['ep_wxcorpid'] == 2} selected="selected"{/if}>未绑定</option>
						</select>
					</div>

					{*<label class="control-label col-sm-1">付费类型</label>*}

					{*<div class="col-sm-2">*}
					{*<select id="pay_type" name="pay_type" class="form-control form-small" data-width="auto"*}
					{*style="height: 30px; border-radius: 4px;">*}
					{*<option value=""{if $searchBy['pay_type'] == ''} selected="selected"{/if}>请选择</option>*}
					{*<option value="1"{if $searchBy['pay_type'] == 1} selected="selected"{/if}>标准产品</option>*}
					{*<option value="3"{if $searchBy['pay_type'] == 3} selected="selected"{/if}>私有部署</option>*}
					{*<option value="2"{if $searchBy['pay_type'] == 2} selected="selected"{/if}>定制服务</option>*}
					{*</select>*}
					{*</div>*}

				</div>

				<div class="form-group ">

					<label class="control-label col-sm-1">付费状态</label>

					<div class="col-sm-2">
						<select id="pay_status" name="pay_status" class="form-control form-small" data-width="auto"
						        style="height: 30px; border-radius: 4px;">
							<option value=""{if $searchBy['pay_status'] == ''} selected="selected"{/if}>请选择</option>
							{foreach $pay_status as $k => $v}
								<option value="{$k}"{if $searchBy['pay_status'] == $k} selected="selected"{/if}>{$v}</option>
								{foreachelse}
								<option value="">无</option>
							{/foreach}
						</select>
					</div>

					<label class="control-label col-sm-1">购买套件</label>

					<div class="col-sm-2">
						<select id="cpg_id" name="cpg_id" class="form-control form-small" data-width="auto"
						        style="height: 30px; border-radius: 4px;">
							<option value=""{if $searchBy['cpg_id'] == ''} selected="selected"{/if}>请选择</option>
							{foreach $taojian as $k => $v}
								<option value="{$v['cpg_id']}"{if $searchBy['cpg_id'] == $k} selected="selected"{/if}>{$v['cpg_name']}</option>
								{foreachelse}
								<option value="">无</option>
							{/foreach}
						</select>
					</div>

					{if $job == 1}
						<label class="control-label col-sm-1">负责人</label>
						<div class="col-sm-2">
							<select id="ca_id" name="ca_id" class="form-control form-small" data-width="auto" style="height: 30px; border-radius: 4px;">
								<option value=""{if $searchBy['ca_id'] == ''} selected="selected"{/if}>请选择</option>
								<option value="0" {if $searchBy['ca_id'] == '0'} selected="selected" {/if}>无负责人</option>
								{foreach $leader as $k => $v}
									<option value="{$v['ca_id']}" {if $searchBy['ca_id'] == $v['ca_id']} selected="selected" {/if}>{$v['ca_realname']}</option>
								{/foreach}
							</select>
						</div>
					{/if}

					{*<label class="control-label col-sm-1">销售人员(*)</label>*}

					{*<div class="col-sm-2">*}
					{*<select id="ca_id" name="ca_id" class="form-control form-small"*}
					{*data-width="auto" style="height: 30px; border-radius: 4px;">*}
					{*<option value=""{if $searchBy['ca_id'] == ''} selected="selected"{/if}>请选择</option>*}
					{*{foreach $users as $k => $v}*}
					{*<option value="{$v['m_uid']}" {if $searchBy['ca_id'] == $v['m_uid']} selected="selected" {/if}>{$v['m_username']}</option>*}
					{*{foreachelse}*}
					{*<option value="">无</option>*}
					{*{/foreach}*}
					{*</select>*}
					{*</div>*}

				</div>

				<script>
					$(function () {
						$('#sandbox-container .input-daterange').datepicker({
							todayHighlight: true
						});
					});
				</script>
				<div class="form-group ">
					<label class="control-label col-sm-1">注册时间</label>

					<div class="col-md-4" id="sandbox-container">
						<div class="input-daterange input-group" id="datepicker">
							<input type="text"
							       class="input-sm form-control"
							       value="{$searchBy['date_start']}"
							       name="date_start">

							<span class="input-group-addon">to</span>

							<input type="text"
							       class="input-sm form-control"
							       value="{$searchBy['date_end']}"
							       name="date_end">
						</div>
					</div>

					<label class="control-label col-sm-1">最后一次操作时间</label>

					<div class="col-md-4" id="sandbox-container">
						<div class="input-daterange input-group" id="datepicker">
							<input type="text"
							       class="input-sm form-control"
							       value="{$searchBy['operation_date_start']}"
							       name="operation_date_start">

							<span class="input-group-addon">to</span>

							<input type="text"
							       class="input-sm form-control"
							       value="{$searchBy['operation_date_end']}"
							       name="operation_date_end">
						</div>
					</div>

				</div>

				<div class="form-group ">
					<label class="control-label col-sm-1">每页数量</label>

					<div class="col-md-4">
						<div class="input-daterange input-group" id="limit">
							<input type="number"
							       class="input-sm form-control"
							       value="{$limit}"
							       name="limit">
						</div>
					</div>
				</div>

				<div class="form-group">

					<label class="control-label col-sm-1"></label>

					<div class="col-sm-3">
						<button name="search" value="search" type="submit" class="btn btn-primary">搜 索</button>
						<a class="btn btn-default" href="">全部记录</a>
						<button name="export" value="export" type="submit" class="btn btn-warning">导 出
						</button>
					</div>

				</div>

			</form>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-1">
		<div type="button" class="btn btn-default input-sm" data-toggle="modal" data-target="#adduser_modal">新增客户</div>
	</div>
	{if $list != null}
		<div class="col-md-1">
			<div id="batch" class="btn btn-default input-sm" data-toggle="modal" data-target="#message_modal">通知</div>
		</div>
		{if $job != 2}
			<div class="col-md-1">
				<div id="leader" class="btn btn-default input-sm" data-toggle="modal" data-target="#leader_modal">添加负责人</div>
			</div>
			<div class="col-md-1">
				<div id="edit_leader" class="btn btn-default input-sm" data-toggle="modal" data-target="#edit_leader_modal">修改负责人</div>
			</div>
			<div class="col-md-1">
				<div id="transfer_leader" class="btn btn-default input-sm" data-toggle="modal" data-target="#transfer_leader_modal">迁移负责人</div>
			</div>
		{/if}
	{/if}
</div>

<div style="width: 100%; overflow: auto;">
	<div style="  width: 200%;">
		<table class="table table-striped table-hover font12">
			<colgroup>
				<col class="t-col-1"/>
				<col class="t-col-6"/>
				<col class="t-col-2"/>
				<col class="t-col-5"/>
				<col class="t-col-1"/>
				<col class="t-col-1"/>
				<col class="t-col-3"/>
				<col class="t-col-3"/>
				<col class="t-col-3"/>
				<col class="t-col-3"/>
				<col class="t-col-4"/>
				<col class="t-col-3"/>
				<col class="t-col-3"/>
				<col class="t-col-4"/>
			</colgroup>
			<thead>
			<tr>
				<th class="text-center">
					<input type="checkbox" class="px btn-lg" id="select_all"
					       onchange="javascript:checkAll(this,'select');"{if !$total} disabled="disabled"{/if} />
					<span class="lbl">全选</span>
				</th>
				<th>公司名称</th>
				<th>手机号</th>
				<th>所在行业</th>
				<th>客户状态</th>
				<th>客户等级</th>
				<th>企业规模</th>
				<th>客户来源</th>
				<th>是否绑定</th>
				<th>负责人</th>
				<th>付费状态</th>
				<th>注册及创建时间</th>
				<th>最后更新时间</th>
				<th>操作</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				{if $list != null}
					<td colspan="15"  class="text-right">{$multi}</td>
				{/if}
			</tr>
			</tfoot>
			<tbody>
			<tr>
				{foreach $list as $_ca_id=>$_ca}
				<td class="px text-center"><input type="checkbox" class="px btn-lg" name="select[{$_ca['ep_id']}]"
				                                  value="{$_ca['ep_id']}"/></td>

				<td>{if !empty($_ca['ep_agent'])}<i style="color:red" class="glyphicon glyphicon-star"></i>{/if}{$_ca['ep_name']|escape}</td>
				{*手机号*}
				<td>{$_ca['ep_mobilephone']|escape}</td>
				<td>{$_ca['ep_industry']|escape}</td>
				{*客户状态*}
				<td>
					{if $_ca['customer_status'] == 1}
						新增客户
					{elseif $_ca['customer_status'] == 2}
						初步沟通
					{elseif $_ca['customer_status'] == 3}
						见面拜访
					{elseif $_ca['customer_status'] == 4}
						确定意向
					{elseif $_ca['customer_status'] == 5}
						正式报价
					{elseif $_ca['customer_status'] == 6}
						商务谈判
					{elseif $_ca['customer_status'] == 7}
						签约成交
					{elseif $_ca['customer_status'] == 8}
						售后服务
					{elseif $_ca['customer_status'] == 9}
						停滞
					{elseif $_ca['customer_status'] == 10}
						流失
					{/if}
				</td>
				<td>
					{if $_ca['ep_customer_level'] == 1}
						小客户
					{elseif  $_ca['ep_customer_level'] == 2}
						中型客户
					{elseif  $_ca['ep_customer_level'] == 3}
						大型客户
					{elseif  $_ca['ep_customer_level'] == 4}
						VIP客户
					{/if}
				</td>
				<td>{$_ca['ep_companysize']|escape}</td>
				<td>{$_ca['ep_ref']|escape}</td>
				<td>{if $_ca['ep_wxcorpid']}已绑定{else}未绑定{/if}</td>
				{*跟进销售*}
				<td>{$_ca['ca_realname']}</td>
				{*付费状态*}
				<td>
					{foreach $_ca['pay_list'] as $key => $val}
						<b style="  color: #ED2C2C; font-size: 15px;">
							{$val['cpg_name']}
						</b>
						{if $val['pay_type'] == 1}
							{if $val['pay_status'] == 1}
								已付费
							{elseif $val['pay_status'] == 2}
								已付费-即将到期
							{elseif $val['pay_status'] == 3}
								已付费-已到期
							{elseif $val['pay_status'] == 5}
								试用期-即将到期
							{elseif $val['pay_status'] == 6}
								试用期-已到期
							{elseif $val['pay_status'] == 7}
								试用期
							{/if}
						{elseif $val['pay_type'] == 2}
							定制服务
						{elseif $val['pay_type'] == 3}
							私有部署
						{/if}
						<br>
					{/foreach}
				</td>
				{*创建时间*}
				<td>{$_ca['ep_created']}</td>
				{*更新时间*}
				<td>{$_ca['ep_updated']}</td>
				{*操作*}
				<td>
					{$base->show_link($view_url_base, $_ca['ep_id'], '详情', 'fa-eye')} |
					{if !$_ca['ep_locked']}
						{*锁定操作*}
						{$base->show_link($lock_url_base, $_ca['ep_id'], '已开启', 'fa-unlock')}
					{else}
						{*解锁操作*}
						{$base->show_link($unlock_url_base, $_ca['ep_id'], '已锁定', 'fa-lock')}
					{/if}
				</td>
			</tr>
			{foreachelse}
			<tr>
				<td colspan="17" class="warning">
					暂无数据
				</td>
			</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
</div>

<!-- Modal -->
{*批量消息模态框*}
<div class="modal fade" id="message_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">选择消息模板</h4>
			</div>
			<div class="modal-body">
				<div class="btn-group" id="batch_type" data-toggle="buttons" style="margin: 0 0 18px 40px;">
					{if $job != 2}
						<label class="btn btn-default">
							<input type="radio" name="all_batch" value="-1" autocomplete="off"> 通知全部
						</label>
					{/if}
					<label class="btn btn-default active">
						<input type="radio" name="batch" value="1" autocomplete="off" checked> 批量通知
					</label>
				</div>

				<div class="mb">

					<form class="mb-search">
						<div class="input-group" style="width:80%;margin-left:40px;">
							<input class="form-control" type="text" name="search" />
							<span class="input-group-addon" id="basic-addon2">搜一搜</span>
						</div>
					</form>

					<ul class="nav" style="margin-left:40px;margin-top:15px;" sign="">
						{foreach $data1 as $k=>$val}
							<li><input type="radio" name="meiid[]" value="{$val['meid']}" /><span style="margin-left:12px;">{$val['title']}</span></li>
						{/foreach}
					</ul>
					<div class="text-center fy">{$multi1}</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="sure">确定</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
			</div>
		</div>
	</div>
</div>
{*新增客户模态框*}
<div class="modal fade" id="adduser_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" style="border-bottom: 0; text-align: center;">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h3 class="modal-title" id="myModalLabel">新 增 客 户</h3>
			</div>
			<div class="modal-body">
				<form class="form-horizontal">
					<div class="form-group">
						<label for="add_ep_name" class="col-sm-2 control-label">企业名称</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="add_ep_name" placeholder="请输入工商登记全称">
						</div>
					</div>
					<div class="form-group">
						<label for="add_ep_domain" class="col-sm-2 control-label">企业账号</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="add_ep_domain" placeholder="请输入企业注册账号">
						</div>
					</div>
					<div class="form-group">
						<label for="add_ep_password" class="col-sm-2 control-label">企业密码</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="add_ep_password" placeholder="请输入企业密码">
						</div>
					</div>
					<div class="form-group">
						<label for="add_ep_city" class="col-sm-2 control-label">企业地址</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="add_ep_city" placeholder="请输入地址">
						</div>
					</div>
					<div class="form-group">
						<label for="add_ep_companysize" class="col-sm-2 control-label">企业规模</label>
						<div class="col-sm-10">
							<select id="add_ep_companysize" name="add_ep_companysize" class="form-control form-small"
							        data-width="auto" style="height: 34px; border-radius: 4px;">
								{foreach $scale as $k => $v}
									<option value="{$v}">{$v}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="add_ep_industry" class="col-sm-2 control-label">所在行业</label>
						<div class="col-sm-10">
							<select id="add_ep_industry" name="add_ep_industry" class="form-control form-small"
							        data-width="auto" style="height: 34px; border-radius: 4px;">
								{foreach $industry as $k => $v}
									<option value="{$v}">{$v}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="add_ep_ref" class="col-sm-2 control-label">客户来源</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="add_ep_ref" placeholder="请输入来源">
						</div>
					</div>
					<div class="form-group">
						<label for="add_customer_status" class="col-sm-2 control-label">客户状态</label>
						<div class="col-sm-10">
							<select id="add_customer_status" name="add_customer_status" class="form-control form-small"
							        data-width="auto" style="height: 34px; border-radius: 4px;">
								{foreach $customer_status as $k => $v}
									<option value="{$k}">{$v}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="add_ep_customer_level" class="col-sm-2 control-label">客户等级</label>
						<div class="col-sm-10">
							<select id="add_ep_customer_level" name="add_ep_customer_level" class="form-control form-small"
							        data-width="auto" style="height: 34px; border-radius: 4px;">
								{foreach $customer_level as $k => $v}
									<option value="{$k}">{$v}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="add_ep_contact" class="col-sm-2 control-label">联系人</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" maxlength="12" id="add_ep_contact" placeholder="必填">
						</div>
					</div>
					<div class="form-group">
						<label for="add_ep_contactposition" class="col-sm-2 control-label">客户职位</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="add_ep_contactposition">
						</div>
					</div>
					<div class="form-group">
						<label for="add_ep_mobilephone" class="col-sm-2 control-label">手机号</label>
						<div class="col-sm-10">
							<input type="number" maxlength="11" class="form-control" id="add_ep_mobilephone" placeholder="必填">
						</div>
					</div>
					<div class="form-group">
						<label for="add_ep_email" class="col-sm-2 control-label">电子邮箱</label>
						<div class="col-sm-10">
							<input type="email" class="form-control" id="add_ep_email">
						</div>
					</div>
					<div class="form-group">
						<label for="add_bank_account" class="col-sm-2 control-label">银行账户</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="add_bank_account">
						</div>
					</div>
					<div class="form-group">
						<label for="add_ep_url" class="col-sm-2 control-label">公司域名</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="add_ep_url">
						</div>
					</div>
					<div class="form-group">
						<label for="add_ep_wxcorpid" class="col-sm-2 control-label">CorpID</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="add_ep_wxcorpid">
						</div>
					</div>
					<div class="form-group">
						<label for="add_id_number" class="col-sm-2 control-label">代理商</label>
						<div class="col-sm-10">
							<select id="add_id_number" name="add_id_number" class="form-control form-small" data-width="auto"
							        style="height: 30px; border-radius: 4px;">
								<option value="">请选择</option>
								{foreach $all_agent as $k => $v}
									<option value="{$v['acid']}">{$v['id_number']}</option>
									{foreachelse}
									<option value="">无</option>
								{/foreach}
							</select>
						</div>
					</div>

				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
				<button type="button" id="add_user_submit" class="btn btn-primary">保存</button>
			</div>
		</div>
	</div>
</div>
{*添加负责人*}
<div class="modal fade" id="leader_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" style="border-bottom: 0;">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">选择负责人</h4>
			</div>
			<div class="modal-body">
				<select id="leading" name="leading" class="form-control form-small" data-width="auto"
				        style="height: 40px;border-radius: 4px;font-size: 15px;">
					<option value="">请选择</option>
					{foreach $leader as $k => $v}
						<option value="{$v['ca_id']}">{$v['ca_realname']}</option>
						{foreachelse}
						<option value="">无</option>
					{/foreach}
				</select>
			</div>
			<div class="modal-footer">
				<button type="button" id="sure_leader" class="btn btn-primary">确 定</button>
			</div>
		</div>
	</div>
</div>
{*修改负责人*}
<div class="modal fade" id="edit_leader_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" style="border-bottom: 0;">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">选择负责人</h4>
			</div>
			<div class="modal-body">
				<select id="edit_lead_id" name="edit_lead_id" class="form-control form-small" data-width="auto"
				        style="height: 40px;border-radius: 4px;font-size: 15px;">
					<option value="">请选择</option>
					{foreach $leader as $k => $v}
						<option value="{$v['ca_id']}">{$v['ca_realname']}</option>
						{foreachelse}
						<option value="">无</option>
					{/foreach}
				</select>
			</div>
			<div class="modal-footer">
				<button type="button" id="sure_edit_leader" class="btn btn-primary">确 定</button>
			</div>
		</div>
	</div>
</div>
{*迁移负责人*}
<div class="modal fade" id="transfer_leader_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" style="border-bottom: 0;">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">迁移负责人</h4>
			</div>
			<div class="modal-body">
				<select id="transfer_form" class="form-control form-small" data-width="auto"
				        style="height: 40px;border-radius: 4px;font-size: 15px;">
					<option value="">请选择</option>
					{foreach $leader as $k => $v}
						<option value="{$v['ca_id']}">{$v['ca_realname']}</option>
						{foreachelse}
						<option value="">无</option>
					{/foreach}
				</select>

				<br />
				<p class="text-center">
					<span class="btn-lg glyphicon glyphicon-arrow-down"></span>
				</p>

				<select id="transfer_to" class="form-control form-small" data-width="auto"
				        style="height: 40px;border-radius: 4px;font-size: 15px;">
					<option value="">请选择</option>
					{foreach $leader as $k => $v}
						<option value="{$v['ca_id']}">{$v['ca_realname']}</option>
						{foreachelse}
						<option value="">无</option>
					{/foreach}
				</select>
			</div>
			<div class="modal-footer">
				<button type="button" id="sure_transfer" class="btn btn-primary">确 定</button>
			</div>
		</div>
	</div>
</div>


<script>
	// 添加客户
	$(function () {
		$('#add_user_submit').on('click', function () {
			var re = /^[\u4e00-\u9fa5a-z0-9]+$/gi;

			if($('#add_ep_mobilephone').val().length !=11 ){
				alert('请输入正确的手机号码');
				return false;
			}

			if($.trim($('#add_ep_password').val()) == ''){
				alert('请输入密码');
				return false;
			}

			var re = /^[0-9]+$/gi;
			if (!re.test($('#add_ep_mobilephone').val())) {
				alert('请输入正确的手机号码');
				return false;
			}

			var em =  /^([\.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
			if(!em.test($('#add_ep_email').val())){
				alert('请输入正确的邮箱');
				return false;
			}

			var ep_name = $('#add_ep_name').val();
			var ca_id = "{$ca_id}";
			var ep_domain = $('#add_ep_domain').val();
			var ep_password = $('#add_ep_password').val();
			var ep_city = $('#add_ep_city').val();
			var ep_companysize = $('#add_ep_companysize').val();
			var ep_industry = $('#add_ep_industry').val();
			var ep_ref = $('#add_ep_ref').val();
			var customer_status = $('#add_customer_status').val();
			var ep_customer_level = $('#add_ep_customer_level').val();
			var ep_contact = $('#add_ep_contact').val();
			var ep_contactposition = $('#add_ep_contactposition').val();
			var ep_mobilephone = $('#add_ep_mobilephone').val();
			var ep_email = $('#add_ep_email').val();
			var bank_account = $('#add_bank_account').val();
			var ep_url = $('#add_ep_url').val();
			var ep_wxcorpid = $('#add_ep_wxcorpid').val();
			var id_number = $('#add_id_number option:selected').text();
			var ep_agent = $('#add_id_number').val();
			if (ep_agent == 0) {
				id_number = '';
			}
			var operator = "{$operator}";

			$.ajax({
				url: "{$url}" + "/cyadmin/api/company/add",
				type: "POST",
				dataType: "json",
				data: {
					ep_name: ep_name,
					ca_id: ca_id,
					ep_domain: ep_domain,
					ep_password: ep_password,
					ep_city: ep_city,
					ep_companysize: ep_companysize,
					ep_industry: ep_industry,
					ep_ref: ep_ref,
					customer_status: customer_status,
					ep_customer_level: ep_customer_level,
					ep_contact: ep_contact,
					ep_contactposition: ep_contactposition,
					ep_mobilephone: ep_mobilephone,
					ep_email: ep_email,
					bank_account: bank_account,
					ep_url: ep_url,
					ep_wxcorpid: ep_wxcorpid,
					id_number: id_number,
					ep_agent: ep_agent,
					act: 'add',
					operator: operator
				},
				success: function (data) {
					if (data.errcode != 0) {
						alert(data.errmsg);
						return false;
					} else {
						alert(data.errmsg);
						location.reload();
					}
				},
				error: function (e) {
					alert('网络错误');
					return false;
				}
			});
		});
	});
	// 消息模板
	$(function () {

		$('#batch').on('click', function () {
			var data = { page:1 };
			mb_list(data);
		});

		var fy_url = '/enterprise/news/list',
			ser_url = '/enterprise/appset';
		// 模态框分页
		$('.fy').on('click','a',function(e){
			e.preventDefault();
			var href = $(this).attr('href');
			if(href.indexOf('?')>=0){
				var n = href.indexOf('page');
				var data = {
					page: href.substring(n+5)
				};
				mb_list(data);
			}
		});

		var mb_list = function(d){

			$.ajax(fy_url, {
				type : 'get',
				data : { mo:1,page:d.page },
				dataType : 'json',
				success : function(d){
					var lis = '';
					$.each(d.list,function(i,n){
						lis+='<li><input type="radio" name="meiid[]" value="'+this.meid+'" /><span style="margin-left:12px;">'+this.title+'</span></li>';
					});
					$('.mb ul').html(lis);
					$('.fy').html($(d.page));
				}
			});
		};

		// 模态框搜索功能
		$("#basic-addon2").on('click',function(){
			var search = $("input[name=search]").val();
			search = String(search);
			$.ajax(ser_url, {
				type : 'get',
				data : { search:search },
				dataType : 'json',
				success : function(d){
					if(false != d.list){
						var lis = '';
						$.each(d.list,function(i,n){
							lis+='<li><input type="radio" name="meiid[]" value="'+this.meid+'" /><span style="margin-left:12px;">'+this.title+'</span></li>';
						});
						$('.mb ul.nav').html(lis);
						$('.fy').html($(d.multi));
					}else{
						$('.mb ul.nav').html("<p>没有找到你要搜索的模板！</p>");
						$('.fy').empty();
					}
				}
			});

		});



		$('#sure').on('click', function () {

			// 获取通知企业的范围
			var type = $('#batch_type label.active input').val();
			var selected_id = [];

			if (type == -1) {
				selected_id[0] = -1;
			} else if (type == 1) {
				// 获取选中的ID
				var allin_selected = $('input[type=checkbox][id!=select_all]:checked');
				if (allin_selected.length == 0) {
					alert('请勾选要发送的企业');
					return false;
				}
				allin_selected.each(function (i, v) {
					selected_id[i] = $(v).val();
				});
			} else {
				alert('通知类型错误');
				return false;
			}
			// 获取勾选的消息模板
			var message_id = $('.mb input[type=radio]:checked').val();
			var message_title = $('.mb input[type=radio]:checked').parentsUntil().eq(0).find('span').text();
			var en_key = "{$en_key}";
			if (message_id == '' || message_id == 'undefined') {
				alert('请选择消息模板');
				return false;
			}

			$.ajax({
				url : '/api/company/message',
				type : 'POST',
				data : { message_id : message_id, message_title : message_title, selected_id : selected_id, en_key : en_key},
				dataType : 'json',
				beforeSend : function () {
					$('#sure').prop('disabled', 'disabled');
				},
				success : function (data) {
					if (data.errcode == 0) {
						alert(data.errmsg);
						$('#message_modal').modal('hide');
						$('#sure').prop('disabled', '');
						return true;
					} else {
						alert(data.errmsg);
						return false;
					}
				},
				error : function (e) {
					alert('网络错误');
					return false;
				}
			});
		});
	});
	// 负责人
	$(function () {
		// 点击添加负责人
		$('#leader').on('click', function () {
			// 获取选中的ID
			var allin_selected = $('input[type=checkbox][id!=select_all]:checked');
			if (allin_selected.length == 0) {
				alert('请勾选企业');
				return false;
			}
			return true;
		});
		// 点击确定
		$('#sure_leader').on('click', function() {
			var leader_selected_id = [];
			var allin_selected = $('input[type=checkbox][id!=select_all]:checked');
			// 获取选中的企业ID
			allin_selected.each(function (i, v) {
				leader_selected_id[i] = $(v).val();
			});
			// 负责人
			var leading = $('#leading').val();
			if (leading == '') {
				alert('请选择负责人');
				return false;
			}
			var ca_id = "{$ca_id}";

			$.ajax({
				url : "{$url}" + '/cyadmin/api/company/leaders',
				type : 'POST',
				data : {
					ep_ids: leader_selected_id,
					leading: leading,
					ca_id: ca_id
				},
				dataType : 'json',
				success : function (data) {
					if (data.errcode == 0) {
						alert(data.errmsg);
						location.reload();
						return true;
					} else {
						alert(data.errmsg);
						return false;
					}
				},
				error : function () {
					alert('网络错误');
					return false;
				}
			});
		});
	});
	// 批量修改负责人
	$(function () {
		// 点击编辑
		$('#edit_leader').on('click', function () {
			// 获取选中的ID
			var allin_selected = $('input[type=checkbox][id!=select_all]:checked');
			if (allin_selected.length == 0) {
				alert('请勾选企业');
				return false;
			}
			return true;
		});
		// 点击确定
		$('#sure_edit_leader').on('click', function() {
			var leader_selected_id = [];
			var allin_selected = $('input[type=checkbox][id!=select_all]:checked');
			// 获取选中的企业ID
			allin_selected.each(function (i, v) {
				leader_selected_id[i] = $(v).val();
			});
			// 负责人
			var edit_lead_id = $('#edit_lead_id').val();
			if (edit_lead_id == '') {
				alert('请选择负责人');
				return false;
			}
			var ca_id = "{$ca_id}";

			$.ajax({
				url : "{$url}" + '/cyadmin/api/company/batchchange',
				type : 'POST',
				data : {
					ep_ids: leader_selected_id,
					edit_lead_id: edit_lead_id,
					ca_id: ca_id,
					operator: "{$operator}"
				},
				dataType : 'json',
				beforeSend : function () {
					$('#sure_edit_leader').hide();
				},
				success : function (data) {
					if (data.errcode == 0) {
						alert(data.errmsg);
						location.reload();
						return true;
					} else {
						alert(data.errmsg);
						return false;
					}
				},
				error : function () {
					alert('网络错误');
					return false;
				}
			});
		});
	});
	// 迁移负责人
	$(function () {
		// 点击确定
		$('#sure_transfer').on('click', function() {
			// 被迁移负责人
			var transfer_form_id = $('#transfer_form').val();
			if (transfer_form_id == '') {
				alert('请选择被迁移的负责人');
				return false;
			}
			// 迁移至负责人
			var transfer_to_id = $('#transfer_to').val();
			if (transfer_to_id == '') {
				alert('请选择迁移至的负责人');
				return false;
			}

			$.ajax({
				url : "{$url}" + '/cyadmin/api/company/transfer',
				type : 'POST',
				data : {
					ca_id_form: transfer_form_id,
					ca_id_to: transfer_to_id,
					operator: "{$operator}",
					op_ca_id: "{$ca_id}"
				},
				dataType : 'json',
				beforeSend : function () {
					$('#sure_transfer').hide();
				},
				success : function (data) {
					if (data.errcode == 0) {
						alert(data.errmsg);
						location.reload();
						return true;
					} else {
						alert(data.errmsg);
						return false;
					}
				},
				error : function () {
					alert('网络错误');
					return false;
				}
			});
		});
	});
</script>

{include file='cyadmin/footer.tpl'}

