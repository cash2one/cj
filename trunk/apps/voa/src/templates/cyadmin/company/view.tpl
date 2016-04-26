{include file='cyadmin/header.tpl'}

<style>
	.pagination {
		margin: 0px;
	}
	.text-style {
		text-align: center;
		border: 0;
	}

	.tr-style th {
		border: 0px;
		text-align: center;
		line-height: 30px;
	}

	.head-center {
		text-align: center;
	}

	.text-percent-up {
		color: red;
	}

	.text-percent-down {
		color: green;
	}

	.head-number {
		font-size: 30px;
		/*font-family: "Helvetica Neue Light", "HelveticaNeue-Light", "Helvetica Neue", Calibri, Helvetica, Arial, sans-serif;*/
	}

</style>

<script type="text/javascript" src="{$static_url}js/echarts.common.min.js"></script>

<div>

	<div class="btn btn-success" data-toggle="modal" data-target="#message_modal"
	     style="position: absolute; right: 21px; top: 9px;">发送消息
	</div>
	<a class="btn btn-success" href="/enterprise/company/"
	   style="position: absolute; right: 125px; top: 9px;"> 返 回 </a>


	{*导航*}
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active">
			<a href="#basic_information" aria-controls="basic_information" role="tab"
			   data-toggle="tab" id="basic_information_tab">基本信息</a></li>
		<li role="presentation">
			<a href="#pay_setting" aria-controls="pay_setting" role="tab" data-toggle="tab" id="pay_setting_tab">付费设置</a>
		</li>
		<li role="presentation">
			<a href="#sales_setting" aria-controls="sales_setting" role="tab"
			   data-toggle="tab" id="sales_setting_tab">销售设置</a>
		</li>
		<li role="presentation">
			<a href="#installation_record" aria-controls="installation_record" role="tab"
			   data-toggle="tab">应用安装记录</a>
		</li>
		<li role="presentation">
			<a href="#messages_record" aria-controls="messages_record" role="tab"
			   data-toggle="tab" id="messages_record_tab">消息记录</a>
		</li>
		<li role="presentation">
			<a href="#conpany_data" aria-controls="conpany_data" role="tab"
			   data-toggle="tab" id="conpany_data_tab">企业数据</a>
		</li>
	</ul>

	{*TABS*}
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="basic_information">
			<div class="form-horizontal" style="margin-top: 20px;">

				<div style="width: 49%;">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4>客户详情</h4>
							<div class="row">
								{*更改客户状态*}
								<div class="col-md-3">
									{foreach $customer_status as $k => $v}
										{if $basic_information['customer_status'] == $k}
											<p class="form-control-static">
												<kbd>{$v}</kbd>
												<kbd data-toggle="modal" data-target="#customer_status_change" style="color: #000; background-color: #FFF; cursor: pointer;">更改</kbd>
											</p>
										{/if}
									{/foreach}
								</div>
								{*负责人*}
								<div class="col-md-8">
									<p class="form-control-static">
										目前由
										{foreach $adminer_data as $k => $v}
											{if $v['ca_id'] == $basic_information['ca_id']}
												{$v['ca_realname']}
											{/if}
										{/foreach}
										负责
										{if $ca_job != 2}<kbd data-toggle="modal" data-target="#change_caid" style="color: #000; background-color: #FFF; cursor: pointer;">更改负责人</kbd>{/if}
									</p>
								</div>
								{*编辑客户详情*}
								<div class="col-md-1 form-control-static">
									<div data-toggle="modal" data-target="#edit_user" style="cursor: pointer; font-size: 20px;" class="glyphicon glyphicon-pencil"></div>
								</div>
							</div>
						</div>
						{*更改客户状态的模态框*}
						<div class="modal fade" id="customer_status_change" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header" style="border-bottom: 0; text-align: center;">
										<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
										<h4 class="modal-title">更改客户状态</h4>
									</div>
									<div class="modal-body">
										<form class="form-horizontal">
											<div class="form-group">
												<label for="change_customer_status" class="col-sm-2 control-label">客户状态</label>
												<div class="col-sm-10">
													<select id="change_customer_status" class="form-control form-small"
													        data-width="auto" style="height: 34px; border-radius: 4px;">
														{foreach $customer_status as $k => $v}
															{if $k >= $basic_information['customer_status']}
																<option value="{$k}">{$v}</option>
															{/if}
														{/foreach}
													</select>
												</div>
											</div>

											<div class="form-group">
												<label for="remark" class="col-sm-2 control-label">工作汇报</label>
												<div class="col-sm-10">
													<textarea id="remark" maxlength="255" style="resize: none;" class="form-control" placeholder="简述您的工作汇报（必填）" rows="3"></textarea>
												</div>
											</div>
										</form>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
										<button type="button" id="change_customer_status_submit" class="btn btn-primary">提交</button>
									</div>
								</div>
							</div>
						</div>
						<script>
							$(function () {
								$('#change_customer_status_submit').on('click', function () {
									// 获取数据
									var ep_id = "{$ep_id}";
									var remark = $('#remark').val();
									var operator = "{$operator}";
									var change_customer_status = $('#change_customer_status').val();
									var url = "{$url}";
									var op_ca_id = "{$op_ca_id}";

									// 判断汇报内容是否为空
									if ($.trim(remark) == '') {
										$('#remark').val('').focus();
										alert('请填写汇报内容');
										return false;
									}

									// 提交数据
									$.ajax({
										url: url + "/cyadmin/api/company/change",
										type: "POST",
										dataType: "json",
										data: {
											ep_id: ep_id,
											remark: remark,
											operator: operator,
											change_customer_status: change_customer_status,
											op_ca_id: op_ca_id
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
										error: function () {
											alert('网络错误');
											return false;
										}
									});
								})
							})
						</script>
						{*编辑客户详情 模态框*}
						<div class="modal fade" id="edit_user" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header" style="border-bottom: 0; text-align: center;">
										<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
										<h3 class="modal-title" id="myModalLabel">编 辑 详 情</h3>
									</div>
									<div class="modal-body">
										<form class="form-horizontal">
											<div class="form-group">
												<label class="col-sm-2 control-label">企业名称</label>
												<div style="margin-top: 7px;" class="col-sm-10">
													{$basic_information['ep_name']|escape}
												</div>
											</div>
											<div class="form-group">
												<label class="col-sm-2 control-label">企业账号</label>
												<div style="margin-top: 7px;" class="col-sm-10">
													{$basic_information['account']|escape}
												</div>
											</div>
											<div class="form-group">
												<label for="edit_ep_city" class="col-sm-2 control-label">企业地址</label>
												<div class="col-sm-10">
													<input type="text" class="form-control" id="edit_ep_city" placeholder="请输入地址" value="{$basic_information['ep_city']|escape}">
												</div>
											</div>
											<div class="form-group">
												<label for="edit_ep_companysize" class="col-sm-2 control-label">企业规模</label>
												<div class="col-sm-10">
													<select id="edit_ep_companysize" name="edit_ep_companysize" class="form-control form-small"
													        data-width="auto" style="height: 34px; border-radius: 4px;">
														{foreach $scale as $k => $v}
															<option value="{$v}" {if $basic_information['ep_companysize'] == $v}selected{/if}>{$v}</option>
														{/foreach}
													</select>
												</div>
											</div>
											<div class="form-group">
												<label for="edit_ep_industry" class="col-sm-2 control-label">所在行业</label>
												<div class="col-sm-10">
													<select id="edit_ep_industry" name="edit_ep_industry" class="form-control form-small"
													        data-width="auto" style="height: 34px; border-radius: 4px;">
														{foreach $industry as $k => $v}
															<option value="{$v}" {if $basic_information['ep_industry'] == $v}selected{/if}>{$v}</option>
														{/foreach}
													</select>
												</div>
											</div>
											<div class="form-group">
												<label for="edit_ep_ref" class="col-sm-2 control-label">客户来源</label>
												<div class="col-sm-10">
													<input type="text" class="form-control" id="edit_ep_ref" placeholder="请输入来源" value="{$basic_information['ep_ref']|escape}">
												</div>
											</div>
											<div class="form-group">
												<label for="edit_customer_status" class="col-sm-2 control-label">客户状态</label>
												<div class="col-sm-10">
													<select id="edit_customer_status" name="edit_customer_status" class="form-control form-small"
													        data-width="auto" style="height: 34px; border-radius: 4px;">
														{foreach $customer_status as $k => $v}
															{if $k >= $basic_information['customer_status']}
																<option value="{$k}">{$v}</option>
															{/if}
														{/foreach}
													</select>
												</div>
											</div>
											<div class="form-group">
												<label for="edit_ep_customer_level" class="col-sm-2 control-label">客户等级</label>
												<div class="col-sm-10">
													<select id="edit_ep_customer_level" name="edit_ep_customer_level" class="form-control form-small"
													        data-width="auto" style="height: 34px; border-radius: 4px;">
														{foreach $customer_level as $k => $v}
															<option value="{$k}" {if $basic_information['ep_customer_level'] == $k}selected{/if}>{$v}</option>
														{/foreach}
													</select>
												</div>
											</div>
											<div class="form-group">
												<label for="edit_ep_contact" class="col-sm-2 control-label">联系人</label>
												<div class="col-sm-10">
													<input type="text" class="form-control" id="edit_ep_contact" placeholder="必填" value="{$basic_information['ep_contact']}">
												</div>
											</div>
											<div class="form-group">
												<label for="edit_ep_contactposition" class="col-sm-2 control-label">客户职位</label>
												<div class="col-sm-10">
													<input type="text" class="form-control" id="edit_ep_contactposition" value="{$basic_information['ep_contactposition']}">
												</div>
											</div>
											<div class="form-group">
												<label for="edit_ep_mobilephone" class="col-sm-2 control-label">手机号</label>
												<div class="col-sm-10">
													<input type="number" maxlength="11" class="form-control" id="edit_ep_mobilephone" placeholder="必填" value="{$basic_information['ep_mobilephone']}">
												</div>
											</div>
											<div class="form-group">
												<label for="edit_ep_email" class="col-sm-2 control-label">电子邮箱</label>
												<div class="col-sm-10">
													<input type="text" class="form-control" id="edit_ep_email" value="{$basic_information['ep_email']}">
												</div>
											</div>
											<div class="form-group">
												<label for="edit_bank_account" class="col-sm-2 control-label">银行账户</label>
												<div class="col-sm-10">
													<input type="text" class="form-control" id="edit_bank_account" value="{$basic_information['bank_account']}">
												</div>
											</div>
											<div class="form-group">
												<label for="edit_ep_url" class="col-sm-2 control-label">公司域名</label>
												<div class="col-sm-10">
													<input type="text" class="form-control" id="edit_ep_url" value="{$basic_information['ep_url']}">
												</div>
											</div>
											<div class="form-group">
												<label for="edit_ep_wxcorpid" class="col-sm-2 control-label">CorpID</label>
												<div style="margin-top: 7px;" class="col-sm-10">
													{$basic_information['ep_wxcorpid']}
												</div>
											</div>
											<div class="form-group">
												<label for="edit_id_number" class="col-sm-2 control-label">代理商</label>
												<div style="margin-top: 7px;" class="col-sm-10">
													{$basic_information['id_number']}
												</div>
											</div>

										</form>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
										<button type="button" id="edit_user_submit" class="btn btn-primary">保存</button>
									</div>
								</div>
							</div>
						</div>
						<script>
							// 添加客户
							$(function () {
								$('#edit_user_submit').on('click', function () {
									var re = /^[\u4e00-\u9fa5a-z0-9]+$/gi;

									if($('#edit_ep_mobilephone').val().length !=11 ){
										alert('请输入正确的手机号码');
										return false;
									}

									var re = /^[0-9]+$/gi;
									if (!re.test($('#edit_ep_mobilephone').val())) {
										alert('请输入正确的手机号码');
										return false;
									}

									var em =  /^([\.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
									if(!em.test($('#edit_ep_email').val())){
										alert('请输入正确的邮箱');
										return false;
									}

									var ep_city = $('#edit_ep_city').val();
									var ep_companysize = $('#edit_ep_companysize').val();
									var ep_industry = $('#edit_ep_industry').val();
									var ep_ref = $('#edit_ep_ref').val();
									var customer_status = $('#edit_customer_status').val();
									var ep_customer_level = $('#edit_ep_customer_level').val();
									var ep_contact = $('#edit_ep_contact').val();
									var ep_contactposition = $('#edit_ep_contactposition').val();
									var ep_mobilephone = $('#edit_ep_mobilephone').val();
									var ep_email = $('#edit_ep_email').val();
									var bank_account = $('#edit_bank_account').val();
									var ep_url = $('#edit_ep_url').val();
									var operator = "{$operator}";
									var ep_id = "{$ep_id}";

									if (($.trim(ep_contact)).length > 12) {
										alert('联系人长度不得超过12个字符s');
										return false;
									}

									$.ajax({
										url: "{$url}" + "/cyadmin/api/company/add",
										type: "POST",
										dataType: "json",
										data: {
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
											operator: operator,
											act: 'edit',
											ep_id: ep_id
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
						</script>
						{*更改负责人 模态框*}
						<div class="modal fade" id="change_caid" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header" style="border-bottom: 0; text-align: center;">
										<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
										<h4 class="modal-title">更改负责人</h4>
									</div>
									<div class="modal-body">
										<form class="form-horizontal">
											<div class="form-group">
												<label for="leading_ca_id" class="col-sm-2 control-label">负责人</label>
												<div class="col-sm-10">
													<select id="leading_ca_id" class="form-control form-small"
													        data-width="auto" style="height: 34px; border-radius: 4px;">
														{foreach $adminer_data as $k => $v}
															{if $v['ca_id'] != $basic_information['ca_id']}
																{*if 去掉当前的负责人*}
																<option value="{$v['ca_id']}">{$v['ca_realname']}</option>
															{/if}
															{foreachelse}
															<option value="">无</option>
														{/foreach}
													</select>
												</div>
											</div>
										</form>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
										<button type="button" id="change_caid_submit" class="btn btn-primary">提交</button>
									</div>
								</div>
							</div>
						</div>
						<script>
							$('#change_caid_submit').on('click', function() {
								var ca_id = $('#leading_ca_id').val();
								$.ajax({
									url: "{$url}" + "/cyadmin/api/company/leader",
									type: "POST",
									dataType: "json",
									data: {
										ca_id: ca_id, // 要更换的操作人ID
										ep_id: "{$ep_id}", // 企业ID
										op_ca_id: "{$op_ca_id}", // 当前操作人ID
										operator: "{$operator}"
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
									error: function () {
										alert('网络错误');
										return false;
									}
								})
							});
						</script>


						<div class="panel-body">
							<div class="form-group">
								<label class="col-sm-2 control-label">注册日期</label>

								<div class="col-sm-10">
									<p class="form-control-static">{$basic_information['ep_created']|escape}</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">企业名称</label>

								<div class="col-sm-10">
									<p class="form-control-static">{$basic_information['ep_name']|escape}</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">企业账号</label>

								<div class="col-sm-10">
									<p class="form-control-static">{$basic_information['account']|escape}</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">企业规模</label>

								<div class="col-sm-10">
									<p class="form-control-static">{$basic_information['ep_companysize']|escape}</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">企业地址</label>

								<div class="col-sm-10">
									<p class="form-control-static">{$basic_information['ep_city']|escape}</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">所在行业</label>

								<div class="col-sm-10">
									<p class="form-control-static">{$basic_information['ep_industry']}</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">客户来源</label>

								<div class="col-sm-10">
									<p class="form-control-static">{$basic_information['ep_ref']|escape}</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">客户状态</label>

								<div class="col-sm-10">
									{foreach $customer_status as $k => $v}
										{if $basic_information['customer_status'] == $k}
											<p class="form-control-static">{$v}</p>
										{/if}
									{/foreach}
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">客户等级</label>

								<div class="col-sm-10">
									{foreach $customer_level as $k => $v}
										{if $basic_information['ep_customer_level'] == $k}
											<p class="form-control-static">{$v}</p>
										{/if}
									{/foreach}
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">联系人姓名</label>

								<div class="col-sm-10">
									<p class="form-control-static">{$basic_information['ep_adminrealname']|escape}</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">联系人手机号</label>

								<div class="col-sm-10">
									<p class="form-control-static">{$basic_information['ep_mobilephone']}</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">联系人邮箱</label>

								<div class="col-sm-10">
									<p class="form-control-static">{$basic_information['ep_email']}</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">银行账号</label>

								<div class="col-sm-10">
									<p class="form-control-static">{$basic_information['bank_account']|escape}</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">公司域名</label>

								<div class="col-sm-10">
									<p class="form-control-static">{$basic_information['ep_url']|escape}</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">CorpID</label>

								<div class="col-sm-10">
									<p class="form-control-static">
										{$basic_information['ep_wxcorpid']}
									</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">来源域名</label>

								<div class="col-sm-10">
									<p class="form-control-static">{$basic_information['ep_ref_domain']}</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">代理商编号</label>

								<div class="col-sm-10">
									<p class="form-control-static">{if $basic_information['id_number'] == ''}无{else}{$basic_information['id_number']}{/if}</p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-sm-2 control-label">使用情况</label>

								<div class="col-sm-10">
									{if $basic_information['pay_setting'] == ''}
										<p class="form-control-static">无</p>
									{else}
										{foreach $basic_information['pay_setting'] as $key => $val}
											{if $val['pay_type'] == 1}
												<div class="row">
													{*套件使用情况*}
													<div class="col-md-6">
														<p class="form-control-static">
															<kbd style="background-color: #286EDA;">{$val['cpg_name']}</kbd>
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
														</p>
														<p class="form-control-static">
															{$val['date_start']} ~ {$val['date_end']}
														</p>
													</div>
													{*试用延期按钮*}
													{if $val['pay_status'] == 5 || $val['pay_status'] == 6 || $val['pay_status'] == 7}
														<div class="col-md-3">
															<div style="margin-top: 10px;" data-toggle="modal" data-target="#extended" onclick="push_pay_id({$val['pay_id']}, {$val['cpg_id']})" class="btn btn-success">
																试用延期
															</div>
														</div>
													{/if}
													{*开关闭应用*}
													<div class="col-md-3">
														<div style="margin-top: 10px;"
														     class="btn btn-{if $val['stop_status'] == 1}success{elseif $val['stop_status'] == 0}danger{/if}"
														     onclick="stop_plugin({$val['pay_id']});">{if $val['stop_status'] == 1}开启{elseif $val['stop_status'] == 0}关闭{/if}
															服务
														</div>
													</div>
												</div>
											{else}
												{if $val['pay_type'] == 2}
													<p class="form-control-static">
														<kbd style="background-color: #286EDA;">定制服务 {$val['cpg_name']}</kbd>
														已付费
													</p>
												{elseif $val['pay_type'] == 3}
													<p class="form-control-static">
														<kbd style="background-color: #286EDA;">私有部署 {$val['cpg_name']}</kbd>
														已付费
													</p>
												{/if}
											{/if}
										{/foreach}
									{/if}
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="panel panel-default" style="width: 49%; height: 100%; position: absolute; top: 72px; right: 0;">
					<div class="panel-heading">操作记录</div>
					<div class="panel-body" style="overflow: auto; height: 94%;">
						{if $operations}
							{foreach $operations as $k => $v}
								<div class="alert" role="alert" style="word-break: break-all; border-color: #CBCBCB;">
									<span style="color: #57BA57;">{$v['operator']}</span> {if $v['customer_status'] != '0'}<kbd>{$v['customer_status']}</kbd>{/if}<br>
									<h6>{$v['created']}</h6><br>
									{$v['remark']}
								</div>
							{/foreach}
						{else}
							<div class="alert alert-warning" role="alert">暂无操作记录</div>
						{/if}
					</div>
				</div>

				<div class="btn btn-primary disabled">
					延期记录<span class="badge">{$total['trial_total']}</span>
				</div>
				<table class="table table-bordered table-hover font12">
					<colgroup>
						<col class="t-col-20"/>
						<col class="t-col-5"/>
						<col class="t-col-5"/>
						<col class="t-col-5"/>
						<col class="t-col-5"/>
					</colgroup>
					<thead>
					<tr>
						<th>试用时间</th>
						<th>套件</th>
						<th>延长时间</th>
						<th>操作人员</th>
						<th>操作时间</th>
					</tr>
					</thead>
					<tbody>
					{foreach $list['trial_list'] as $k => $v}
						<tr>
							<td>{$v['start_time']} ~ {$v['end_time']}</td>
							<td>{$v['cpg_name']}</td>
							<td>{$v['extended']}</td>
							<td>{$v['operator']}</td>
							<td>{$v['updated']}</td>
						</tr>
						{foreachelse}
						<tr>
							<td colspan="5" class="warning">暂无任何{$module_plugin['cp_name']|escape}数据</td>
						</tr>
					{/foreach}
					</tbody>
					{if $total['trial_total'] > 0}
						<tfoot>
						<tr>
							<td colspan="5" class="text-right vcy-page">{$multi['trial_multi']}</td>
						</tr>
						</tfoot>
					{/if}
				</table>
			</div>
		</div>

		<!-- Modal -->
		<div class="modal fade" id="extended" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog" role="document" style="width: 300px;">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel" style="text-align: center;">试用延期</h4></div>
					<div class="modal-body">
						<input type="number" class="form-control" placeholder="请输入日期(天)">
					</div>
					<div class="modal-footer" style="text-align: center;">
						<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
						<button type="button" class="btn btn-primary" id="extend_sure">确定</button>
					</div>
				</div>
			</div>
			<input type="hidden" value="" id="extend_pay_id">
			<input type="hidden" value="" id="extend_cpg_id">
		</div>
		<script>
			$(function () {
//				试用延期
				$('#extend_sure').on('click', function () {
					var extended = $('#extended').find('input[type=number]').val();
					var pay_id = $('#extend_pay_id').val();
					var cpg_id = $('#extend_cpg_id').val();
					var operator = "{$operator}";
					if (extended == '') {
						alert('请输入延期天数');
						return false;
					}
					$.ajax({
						url: "{$url}" + "/cyadmin/api/company/extended",
						type: "POST",
						dataType: "json",
						data: {
							extended: extended,
							operator: operator,
							pay_id: pay_id,
							cpg_id: cpg_id,
							ep_id: "{$ep_id}"
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
						error: function () {
							alert('网络错误');
							return false;
						}
					});
				});
			});

			//			用于试用延期上的, pay_id赋值
			function push_pay_id(pay_id, cpg_id) {
				$('#extend_pay_id').val(pay_id); // 付费记录ID
				$('#extend_cpg_id').val(cpg_id); // 套件ID
				return true;
			}

			//			开关服务
			function stop_plugin(pay_id) {

				var id = pay_id;
				$.ajax({
					url: "{$url}" + "/cyadmin/api/company/stop",
					type: "POST",
					dataType: "json",
					data: {
						id: id
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

				return true;
			}
		</script>
		{*基本信息结束*}

		<div role="tabpanel" class="tab-pane" id="pay_setting">
			<div class="form-horizontal" style="margin-top: 20px;">
				<div class="form-group">
					<label class="col-sm-2 control-label">付费类型</label>

					<div class="col-sm-10" id="pay_type">
						<label class="radio-inline" id="standard_product_label">
							<input id="standard_product_button" type="radio" name="pay_type" value="1" checked> 标准产品
						</label>
						<label class="radio-inline">
							<input id="customized_products_button" type="radio" name="pay_type" value="2"> 定制产品
						</label>
						<label class="radio-inline">
							<input id="private_deployment_button" type="radio" name="pay_type" value="3"> 私有部署
						</label>
					</div>
				</div>


				{*标准产品*}
				<div id="standard_product">

					<div class="form-group">
						<label class="col-sm-2 control-label">支付金额</label>

						<div class="col-sm-2">
							<input type="text" class="form-control" id="ep_money"/> <span
									style="position: absolute; top: 14px; left: 200px;">(元)</span>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-2 control-label">购买套件</label>

						<div class="col-sm-2">
							<p class="form-control-static">
								<select class="form-control" id="cpg_id">
									<option value="0">请选择</option>
									{foreach $plugin_list as $k => $v}
										{*排除已经是定制服务 或者私有部署的套件*}
										{foreach $sp_ids as $_k => $_v}
											{if $v['cpg_id'] != $_v}
												<option value="{$v['cpg_id']}">{$v['cpg_name']}</option>
											{/if}
											{foreachelse}
											<option value="{$v['cpg_id']}">{$v['cpg_name']}</option>
										{/foreach}
										{foreachelse}
										<option value="">套件信息丢失!</option>
									{/foreach}
								</select>
							</p>
						</div>
					</div>

					{*时间显示插件*}
					<script>
						$(function () {
							$('#sandbox-container .input-daterange').datepicker({
								todayHighlight: true
							});
						});
					</script>
					<div class="col-sm-11.5" id="sandbox-container">
						<div class="input-daterange" id="datepicker">
							<div class="form-group">
								<label class="col-sm-2 control-label">开始日期</label>

								<div class="col-sm-2">
									<input type="text"
									       class="form-control"
									       id="date_start"
									       style="border-radius: 4px;">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label">截止日期</label>

								<div class="col-sm-2">
									<input type="text"
									       class="form-control"
									       id="standard_date_end"
									       style="border-radius: 4px;">
								</div>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-2 control-label">
							<button id="submit_save" type="button" class="btn btn-info"> 保 存</button>
						</label>

						<div class="col-sm-10">
						</div>
					</div>

					<div class="btn btn-primary disabled">
						付费记录<span class="badge">{$total['pay_standard_total']}</span>
					</div>
					<table class="table table-bordered table-hover font12">
						<colgroup>
							<col class="t-col-3"/>
							<col class="t-col-3"/>
							<col class="t-col-3"/>
							<col class="t-col-3"/>
							<col class="t-col-5"/>
							<col class="t-col-5"/>
						</colgroup>
						<thead>
						<tr>
							<th>付费类型</th>
							<th>支付状态</th>
							<th>支付金额</th>
							<th>购买套件</th>
							<th>使用时间</th>
							<th>操作时间</th>
						</tr>
						</thead>
						<tbody>
						{foreach $list['pay_standard_list'] as $k => $v}
							<tr>
								<td>
									{if $v['pay_type'] == 1}
										标准产品
									{/if}
								</td>
								<td>
									{if $v['pay_status'] == 1}
										已付费
									{/if}
								</td>
								<td>{$v['ep_money']}</td>
								<td id="plugin_list_{$k}">
								</td>
								<script>
									$(function () {
										if ("{$v['cpg_name']}" != '') {
											$('#plugin_list_' + {$k}).append('<kbd style="background-color: #286EDA;">' + "{$v['cpg_name']}" + '</kbd>   ');
										} else {
											$('#plugin_list_' + {$k}).html('无');
										}
									});
								</script>
								<td>{$v['date_start']}~{$v['date_end']}</td>
								<td>{$v['updated']}</td>
							</tr>
							{foreachelse}
							<tr>
								<td colspan="6" class="warning">暂无任何{$module_plugin['cp_name']|escape}数据</td>
							</tr>
						{/foreach}
						</tbody>
						{if $total['pay_standard_total'] > 0}
							<tfoot>
							<tr>
								<td colspan="6" class="text-right vcy-page">{$multi['pay_standard_multi']}</td>
							</tr>
							</tfoot>
						{/if}
					</table>
				</div>

				{*定制产品*}
				<div id="customized_products" style="display: none;">
					<div class="form-group">
						<label class="col-sm-2 control-label">购买套件</label>

						<div class="col-sm-2">
							<p class="form-control-static">
								<select class="form-control" id="customized_cpg_id">
									<option value="0">请选择</option>
									{foreach $plugin_list as $k => $v}
										<option value="{$v['cpg_id']}">{$v['cpg_name']}</option>
										{foreachelse}
										<option value="">套件信息丢失!</option>
									{/foreach}
								</select>
							</p>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-2 control-label">支付金额</label>

						<div class="col-sm-2">
							<input type="text" class="form-control" id="customized_money"/> <span
									style="position: absolute; top: 14px; left: 200px;">(元)</span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"></label>

						<div class="col-sm-3">
							<input type="checkbox" id="installment_payment"/>
							<label for="installment_payment">分期付款  <span style="color: #B3B0B0;">注：最多支持五期</span></label>
						</div>
					</div>
					<div id="customized_money_area" style="display: none;">
						<div class="form-group">
							<label class="col-sm-2 control-label">一期金额</label>
							<div id="customized_add_staging" class="btn btn-primary" style="  margin-left: 35px;">
								<span  class="glyphicon glyphicon-plus-sign"></span> 添加分期
							</div>

							<div class="col-sm-2">
								<input type="text" class="form-control" id="customized_money_1" /> <span
										style="position: absolute; top: 14px; left: 200px;">(元)</span>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">定制说明</label>

						<div class="col-sm-4">
						<textarea id="sales_remark" style="height: 100px; resize: none;" class="form-control"
						          rows="3"></textarea>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-2 control-label">
							<button id="customized_save" type="button" class="btn btn-info"> 保 存</button>
						</label>

						<div class="col-sm-10">
						</div>
					</div>

					<div class="btn btn-primary disabled">
						操作记录<span class="badge">{$total['pay_special_total']}</span>
					</div>
					<table class="table table-bordered table-hover font12">
						<colgroup>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
						</colgroup>
						<thead>
						<tr>
							<th>支付总额</th>
							<th>一期金额</th>
							<th>二期金额</th>
							<th>三期金额</th>
							<th>四期金额</th>
							<th>五期金额</th>
							<th>定制说明</th>
							<th>操作人员</th>
							<th>操作时间</th>
						</tr>
						</thead>
						<tbody>
						{foreach $list['pay_special_list'] as $k => $v}
							<tr>
								<td>
									{$v['ep_money']}
								</td>
								<td>
									{$v['first_money']}
								</td>
								<td>{$v['second_money']}</td>
								<td>{$v['third_money']}</td>
								<td>{$v['fourth_money']}</td>
								<td>{$v['fifth_money']}</td>
								<td style="word-break: break-all;">{$v['remark']|escape}</td>
								<td>{$v['operator']}</td>
								<td>{$v['updated']}</td>
							</tr>
							{foreachelse}
							<tr>
								<td colspan="9" class="warning">暂无任何{$module_plugin['cp_name']|escape}数据</td>
							</tr>
						{/foreach}
						</tbody>
						{if $total['pay_special_total'] > 0}
							<tfoot>
							<tr>
								<td colspan="9" class="text-right vcy-page">{$multi['pay_special_multi']}</td>
							</tr>
							</tfoot>
						{/if}
					</table>
				</div>

				{*私有部署产品*}
				<div id="private_deployment" style="display: none;">
					<div class="form-group">
						<label class="col-sm-2 control-label">购买套件</label>

						<div class="col-sm-2">
							<p class="form-control-static">
								<select class="form-control" id="private_cpg_id">
									<option value="0">请选择</option>
									{foreach $plugin_list as $k => $v}
										<option value="{$v['cpg_id']}">{$v['cpg_name']}</option>
										{foreachelse}
										<option value="">套件信息丢失!</option>
									{/foreach}
								</select>
							</p>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-2 control-label">支付金额</label>

						<div class="col-sm-2">
							<input type="text" class="form-control" id="private_money"/> <span
									style="position: absolute; top: 14px; left: 200px;">(元)</span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"></label>

						<div class="col-sm-3">
							<input type="checkbox" id="private_installment_payment"/>
							<label for="private_installment_payment">分期付款  <span style="color: #B3B0B0;">注：最多支持五期</span></label>
						</div>
					</div>
					<div id="private_money_area" style="display: none;">
						<div class="form-group">
							<label class="col-sm-2 control-label">一期金额</label>
							<div id="private_add_staging" class="btn btn-primary" style="  margin-left: 35px;">
								<span  class="glyphicon glyphicon-plus-sign"></span> 添加分期
							</div>

							<div class="col-sm-2">
								<input type="text" class="form-control" id="private_money_1" /> <span
										style="position: absolute; top: 14px; left: 200px;">(元)</span>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">部署说明</label>

						<div class="col-sm-4">
						<textarea id="private_sales_remark" style="height: 100px; resize: none;" class="form-control"
						          rows="3"></textarea>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-2 control-label">
							<button id="private_save" type="button" class="btn btn-info"> 保 存</button>
						</label>

						<div class="col-sm-10">
						</div>
					</div>

					<div class="btn btn-primary disabled">
						操作记录<span class="badge">{$total['pay_special_total_private']}</span>
					</div>
					<table class="table table-bordered table-hover font12">
						<colgroup>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
							<col class="t-col-10"/>
						</colgroup>
						<thead>
						<tr>
							<th>支付总额</th>
							<th>一期金额</th>
							<th>二期金额</th>
							<th>三期金额</th>
							<th>四期金额</th>
							<th>五期金额</th>
							<th>部署说明</th>
							<th>操作人员</th>
							<th>操作时间</th>
						</tr>
						</thead>
						<tbody>
						{foreach $list['pay_special_list_private'] as $k => $v}
							<tr>
								<td>
									{$v['ep_money']}
								</td>
								<td>
									{$v['first_money']}
								</td>
								<td>{$v['second_money']}</td>
								<td>{$v['third_money']}</td>
								<td>{$v['fourth_money']}</td>
								<td>{$v['fifth_money']}</td>
								<td style="word-break: break-all;">{$v['remark']|escape}</td>
								<td>{$v['operator']}</td>
								<td>{$v['updated']}</td>
							</tr>
							{foreachelse}
							<tr>
								<td colspan="9" class="warning">暂无任何{$module_plugin['cp_name']|escape}数据</td>
							</tr>
						{/foreach}
						</tbody>
						{if $total['pay_special_total_private'] > 0}
							<tfoot>
							<tr>
								<td colspan="9" class="text-right vcy-page">{$multi['pay_special_multi_private']}</td>
							</tr>
							</tfoot>
						{/if}
					</table>
				</div>
			</div>
		</div>
		<script>
			$(function () {
				// URL路径
				var domain_url = '{$url}';
				var basic_param = '/enterprise/company/view/?id=';
				var ep_id = "{$ep_id}";

				$('#basic_information_tab').on('click', function () {
					location.href = domain_url + basic_param + ep_id + '&act=basic';
					return false;
				});
				$('#pay_setting_tab').on('click', function () {
					location.href = domain_url + basic_param + ep_id + '&act=pay';
					return false;
				});
				$('#sales_setting_tab').on('click', function () {
					location.href = domain_url + basic_param + ep_id + '&act=sales';
					return false;
				});
				$('#messages_record_tab').on('click', function () {
					location.href = domain_url + basic_param + ep_id + '&act=message';
					return false;
				});


				// 默认tab
				var tabs = '{$act}';
				if (tabs == 'pay') {
					$('.nav-tabs a[id="pay_setting_tab"]').tab('show');
				}
				if (tabs == 'sales') {
					$('.nav-tabs a[id="sales_setting_tab"]').tab('show');
				}
				if (tabs == 'message') {
					$('.nav-tabs a[id="messages_record_tab"]').tab('show');
				}
				if (tabs == 'trial') {
					$('.nav-tabs a[id="basic_information_tab"]').tab('show');
				}

				// 切换 页面
				$('#standard_product_button').on('click', function () {
					$('#standard_product').show();
					$('#customized_products').hide();
					$('#private_deployment').hide();
				});
				$('#customized_products_button').on('click', function () {
					$('#customized_products').show();
					$('#standard_product').hide();
					$('#private_deployment').hide();
				});
				$('#private_deployment_button').on('click', function () {
					$('#private_deployment').show();
					$('#customized_products').hide();
					$('#standard_product').hide();
				});

				// 标准产品 提交
				$('#submit_save').on('click', function () {
					var btn = $(this);
					btn.attr('disabled', 'disabled');
					btn.text('提交中...');
					var ep_money = $.trim($('#ep_money').val());
					var date_start = $('#date_start').val();
					var date_end = $('#standard_date_end').val();
					var cpg_id = $('#cpg_id').val();
					if (cpg_id == '') {
						alert('购买套件不得为空');
						btn.attr('disabled', false);
						btn.text('保 存');
						return false;
					}
					var url = "{$url}";
					var ep_id = "{$ep_id}";
					var en_key = "{$en_key}";
					var reg = /^\d+(\.\d+)?$/;

					if (reg.test(ep_money) != true) {
						$('#ep_money').val('').focus();
						alert('支付金额必须为纯数字');
						btn.attr('disabled', false);
						btn.text('保 存');
						return false;
					}
					if (ep_money == '') {
						$('#ep_money').val('').focus();
						alert('支付金额不能为空');
						btn.attr('disabled', false);
						btn.text('保 存');
						return false;
					}
					if (ep_money < 0) {
						$('#ep_money').val('').focus();
						alert('支付金额不能为负');
						btn.attr('disabled', false);
						btn.text('保 存');
						return false;
					}
					if (date_start == '') {
						alert('请选择开始日期');
						btn.attr('disabled', false);
						btn.text('保 存');
						return false;
					}
					if (date_end == '') {
						alert('请选择截止日期');
						btn.attr('disabled', false);
						btn.text('保 存');
						return false;
					}

					$.ajax({
						url: url + "/cyadmin/api/company/paysetting",
						type: "POST",
						dataType: "json",
						data: {
							pay_type: 1,
							ep_money: ep_money,
							date_start: date_start,
							date_end: date_end,
							ep_id: ep_id,
							en_key: en_key,
							cpg_id: cpg_id
						},

						success: function (data) {
							if (data.errcode != 0) {
								alert(data.errmsg);
								btn.attr('disabled', false);
								btn.text('保 存');
								return false;
							} else {
								alert(data.errmsg);
								btn.text('成 功');
								location.reload();
							}
						},
						error: function () {
							alert('网络错误');
							btn.attr('disabled', false);
							btn.text('保 存');
							return false;
						}
					});
				});


				/**
				 * 定制产品部分
				 */
					// 定制产品 提交
				$('#customized_save').on('click', function () {
					var btn = $(this);
					btn.attr('disabled', 'disabled');
					btn.text('提交中...');
					var ep_money = $.trim($('#customized_money').val()); // 支付金额
					var remark = $('#sales_remark').val(); // 备注
					var url = "{$url}"; // 目标地址
					var ep_id = "{$ep_id}"; // 公司ID
					var en_key = "{$en_key}"; // 密钥
					var operator = "{$operator}"; // 操作人
					var cpg_id = $('#customized_cpg_id').val(); // 套件ID
					var reg = /^\d+(\.\d+)?$/;

					if (reg.test(ep_money) != true) {
						$('#customized_money').val('').focus();
						alert('支付金额必须为纯数字');
						btn.attr('disabled', false);
						btn.text('保 存');
						return false;
					}
					if (ep_money == '') {
						$('#customized_money').val('').focus();
						alert('支付金额不能为空');
						btn.attr('disabled', false);
						btn.text('保 存');
						return false;
					}
					if (ep_money < 0) {
						$('#customized_money').val('').focus();
						alert('支付金额不能为负');
						btn.attr('disabled', false);
						btn.text('保 存');
						return false;
					}
					var installment_payment = []; // 分期付款金额
					// 支付总额
					var pay_money = Number(0);
					if ($('#installment_payment').prop('checked')) {
						var is_true = 1;
						$('#customized_money_area').find('input').each(function (i,v) {
							i++;
							var payment = $.trim($(v).val());
							if (payment < 0) {
								alert(i + '期金额不能为负');
								is_true = 0;
								btn.attr('disabled', false);
								btn.text('保 存');
								return false;
							}
							if (payment == '') {
								alert(i + '期金额不得为空');
								is_true = 0;
								btn.attr('disabled', false);
								btn.text('保 存');
								return false;
							}
							if (reg.test(payment) != true) {
								alert(i + '期金额必须为纯数字');
								is_true = 0;
								btn.attr('disabled', false);
								btn.text('保 存');
								return false;
							}
							pay_money += Number(payment);
							installment_payment[i] = $(v).val();
						});
						if (is_true == 0) {
							return false;
						}
						if (ep_money != pay_money) {
							alert('分期付款的总和必须等于总付款额');
							btn.attr('disabled', false);
							btn.text('保 存');
							return false;
						}
					}
					$.ajax({
						url: url + "/cyadmin/api/company/paysetting",
						type: "POST",
						dataType: "json",
						data: {
							pay_type: 2,
							ep_money: ep_money,
							remark: remark,
							ep_id: ep_id,
							en_key: en_key,
							installment_payment: installment_payment,
							operator: operator,
							cpg_id: cpg_id
						},
						success: function (data) {
							if (data.errcode != 0) {
								alert(data.errmsg);
								btn.attr('disabled', false);
								btn.text('保 存');
								return false;
							} else {
								alert(data.errmsg);
								btn.text('成 功');
								location.reload();
							}
						},
						error: function () {
							alert('网络错误');
							btn.attr('disabled', false);
							btn.text('保 存');
							return false;
						}
					});
				});
				// 定制产品 点击分期
				$('#installment_payment').on('click', function () {
					$('#customized_money_area').toggle('fast');
				});
				// 点击添加分期
				$('#customized_add_staging').on('click', function () {
					var label_len = $('#customized_money_area .form-group > label').length;
					if (label_len == 4) {
						$(this).hide();
					}
					if (label_len == 5) {
						alert('最多支持5期');
						return false;
					}

					var chinese = ''; // 中文数字
					label_len ++;
					switch (label_len) {
						case 2:
							chinese = '二';
							break;
						case 3:
							chinese = '三';
							break;
						case 4:
							chinese = '四';
							break;
						case 5:
							chinese = '五';
							break;
					}

					// 插入的div
					var input  = '<div class="form-group" id="customized_' + label_len + '">';
					input += '<label class="col-sm-2 control-label">' + chinese + '期金额</label>';
					input += '<div class="clear_input glyphicon glyphicon-trash btn-lg" style="cursor: pointer; left: 24px;"></div>';
					input += '<div class="col-sm-2">';
					input += '<input type="text" class="form-control" id="customized_money_' + label_len + ' "/> <span style="position: absolute; top: 14px; left: 200px;">(元)</span>';
					input += '</div></div>';

					$('#customized_money_area').append(input);

					// 绑定删除时间
					$('#customized_' + label_len + ' > .clear_input').bind('click', function () {
						// 删除点击的input div
						$(this).parent().remove();
						// 判断是否不等于5期
						var label_len = $('#customized_money_area .form-group > label').length;
						if (label_len != 5) {
							$('#customized_add_staging').show();
						}
						// 重新显示期数
						$('#customized_money_area').find('.form-group').each (function (k, v) {
							var k_chinese = '';
							k++;
							switch (k) {
								case 1:
									k_chinese = '一';
									break;
								case 2:
									k_chinese = '二';
									break;
								case 3:
									k_chinese = '三';
									break;
								case 4:
									k_chinese = '四';
									break;
								case 5:
									k_chinese = '五';
									break;
							}
							$(v).attr('id', 'customized_' + k);
							$(v).find('label').text(k_chinese + '期金额');
							$(v).find('input').attr('id', 'customized_money_' + k);
						});
					});
				});


				/**
				 * 私有部署部分
				 */
					// 私有部署 提交
				$('#private_save').on('click', function () {
					var btn = $(this);
					btn.attr('disabled', 'disabled');
					btn.text('提交中...');
					var ep_money = $.trim($('#private_money').val()); // 支付金额
					var remark = $('#private_sales_remark').val(); // 备注
					var url = "{$url}"; // 目标地址
					var ep_id = "{$ep_id}"; // 公司ID
					var en_key = "{$en_key}"; // 密钥
					var operator = "{$operator}"; // 操作人
					var cpg_id = $('#private_cpg_id').val(); // 套件ID
					var reg = /^\d+(\.\d+)?$/;

					if (reg.test(ep_money) != true) {
						$('#private_money').val('').focus();
						alert('支付金额必须为纯数字');
						btn.attr('disabled', false);
						btn.text('保 存');
						return false;
					}
					if (ep_money == '') {
						$('#private_money').val('').focus();
						alert('支付金额不能为空');
						btn.attr('disabled', false);
						btn.text('保 存');
						return false;
					}
					if (ep_money < 0) {
						$('#private_money').val('').focus();
						alert('支付金额不能为负');
						btn.attr('disabled', false);
						btn.text('保 存');
						return false;
					}
					var private_installment_payment = []; // 分期付款金额
					// 分期付款总额
					var pay_money = Number(0);
					if ($('#private_installment_payment').prop('checked')) {
						var is_true = 1;
						$('#private_money_area').find('input').each(function (i,v) {
							i++;
							var payment = $.trim($(v).val());
							if (payment == '') {
								alert(i + '期金额不得为空');
								is_true = 0;
								btn.attr('disabled', false);
								btn.text('保 存');
								return false;
							}
							if (payment < 0) {
								alert(i + '期金额不能为负');
								is_true = 0;
								btn.attr('disabled', false);
								btn.text('保 存');
								return false;
							}
							if (reg.test(payment) != true) {
								alert(i + '期金额必须为纯数字');
								is_true = 0;
								btn.attr('disabled', false);
								btn.text('保 存');
								return false;
							}
							private_installment_payment[i] = $(v).val();
							pay_money += Number(payment);
						});
						if (is_true == 0) {
							return false;
						}
						if (ep_money != pay_money) {
							alert('分期付款的总和必须等于总付款额');
							btn.attr('disabled', false);
							btn.text('保 存');
							return false;
						}
					}
					$.ajax({
						url: url + "/cyadmin/api/company/paysetting",
						type: "POST",
						dataType: "json",
						data: {
							pay_type: 3,
							ep_money: ep_money,
							remark: remark,
							ep_id: ep_id,
							en_key: en_key,
							installment_payment: private_installment_payment,
							operator: operator,
							cpg_id: cpg_id
						},
						success: function (data) {
							if (data.errcode != 0) {
								alert(data.errmsg);
								btn.attr('disabled', false);
								btn.text('保 存');
								return false;
							} else {
								alert(data.errmsg);
								btn.text('成 功');
								location.reload();
							}
						},
						error: function () {
							alert('网络错误');
							btn.attr('disabled', false);
							btn.text('保 存');
							return false;
						}
					});
				});
				// 私有部署 点击分期
				$('#private_installment_payment').on('click', function () {
					$('#private_money_area').toggle('fast');
				});
				// 点击添加分期
				$('#private_add_staging').on('click', function () {
					var label_len = $('#private_money_area .form-group > label').length;
					if (label_len == 4) {
						$(this).hide();
					}
					if (label_len == 5) {
						alert('最多支持5期');
						return false;
					}

					var chinese = ''; // 中文数字
					label_len ++;
					switch (label_len) {
						case 2:
							chinese = '二';
							break;
						case 3:
							chinese = '三';
							break;
						case 4:
							chinese = '四';
							break;
						case 5:
							chinese = '五';
							break;
					}

					// 插入的div
					var input  = '<div class="form-group" id="private_' + label_len + '">';
					input += '<label class="col-sm-2 control-label">' + chinese + '期金额</label>';
					input += '<div class="clear_input glyphicon glyphicon-trash btn-lg" style="cursor: pointer; left: 24px;"></div>';
					input += '<div class="col-sm-2">';
					input += '<input type="text" class="form-control" id="private_money_' + label_len + ' "/> <span style="position: absolute; top: 14px; left: 200px;">(元)</span>';
					input += '</div></div>';

					$('#private_money_area').append(input);

					// 绑定删除时间
					$('#private_' + label_len + ' > .clear_input').bind('click', function () {
						// 删除点击的input div
						$(this).parent().remove();
						// 判断是否不等于5期
						var label_len = $('#customized_money_area .form-group > label').length;
						if (label_len != 5) {
							$('#private_add_staging').show();
						}
						// 重新显示期数
						$('#private_money_area').find('.form-group').each (function (k, v) {
							var k_chinese = '';
							k++;
							switch (k) {
								case 1:
									k_chinese = '一';
									break;
								case 2:
									k_chinese = '二';
									break;
								case 3:
									k_chinese = '三';
									break;
								case 4:
									k_chinese = '四';
									break;
								case 5:
									k_chinese = '五';
									break;
							}
							$(v).attr('id', 'private_' + k);
							$(v).find('label').text(k_chinese + '期金额');
							$(v).find('input').attr('id', 'private_money_' + k);
						});
					});
				});
			});
		</script>
		{*付费设置结束*}

		<div role="tabpanel" class="tab-pane" id="sales_setting">
			<div class="form-horizontal" style="margin-top: 20px;">
				<div class="form-group">
					<label class="col-sm-2 control-label">有无代理商</label>

					<div class="col-sm-10" id="have_agent">
						<label class="radio-inline">
							<input type="radio" name="have_agent" value="1" checked> 无代理商
						</label>
						<label class="radio-inline">
							<input type="radio" name="have_agent" value="2"> 有代理商
						</label>
					</div>
				</div>

				<div class="form-group" id="ep_agent">
					<label class="col-sm-2 control-label">请选择代理商</label>

					<div class="col-sm-2">
						<select class="form-control" id="ep_agent">
							<option value="0">请选择</option>
							{foreach $all_agent as $k => $v}
								<option value="{$v['acid']}">{$v['id_number']}</option>
								{foreachelse}
								<option value="">请添加代理商</option>
							{/foreach}
						</select>
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-2 control-label">销售人员</label>

					<div class="col-sm-2">
						<select class="form-control" id="ca_id">
							<option value="0">请选择</option>
							{foreach $adminer_data as $k => $v}
								<option value="{$v['ca_id']}">{$v['ca_realname']}</option>

								{foreachelse}
								<option value="">无</option>
							{/foreach}
						</select>
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-2 control-label">客户状态</label>

					<div class="col-sm-2">
						<select class="form-control" id="customer_status">
							{foreach $customer_status as $k => $v}
								{if $k >= $basic_information['customer_status']}
									<option value="{$k}">{$v}</option>
								{/if}
							{/foreach}
						</select>
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-2 control-label">销售备注</label>

					<div class="col-sm-4">
						<textarea id="agent_sales_remark" style="height: 100px; resize: none;" class="form-control"
						          rows="3"></textarea>
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-2 control-label">
						<button id="sales_submit" type="button" class="btn btn-info"> 保 存</button>
					</label>

					<div class="col-sm-10">
					</div>
				</div>

				<div class="btn btn-primary disabled">
					备注记录<span class="badge">{$total['sales_total']}</span>
				</div>
				<table class="table table-bordered table-hover font12">
					<colgroup>
						<col class="t-col-10"/>
						<col class="t-col-10"/>
						<col class="t-col-10"/>
						<col class="t-col-10"/>
					</colgroup>
					<thead>
					<tr>
						<th>销售人员</th>
						<th>客户状态</th>
						<th>销售备注</th>
						<th>时间</th>
					</tr>
					</thead>
					<tbody>
					{foreach $list['sales_list'] as $k => $v}
						<tr>
							<td>{$v['ca_realname']}</td>
							<td>
								{foreach $customer_status as $_k => $_v}
									{if $v['customer_status'] == $_k}
										{$_v}
									{/if}
								{/foreach}
							</td>
							<td style="word-break:break-all;">{if $v['sales_remark'] == ''}无{else}{$v['sales_remark']|escape}{/if}</td>
							<td>{$v['updated']}</td>
						</tr>
						{foreachelse}
						<tr>
							<td colspan="4" class="warning">暂无任何{$module_plugin['cp_name']|escape}数据</td>
						</tr>
					{/foreach}
					</tbody>
					{if $total['sales_total'] > 0}
						<tfoot>
						<tr>
							<td colspan="4" class="text-right vcy-page">{$multi['sales_multi']}</td>
						</tr>
						</tfoot>
					{/if}
				</table>
				<script>
					$(function () {
						// 选择有无代理商, 点击 后的选择代理商选择框的隐藏显示
						$('input:radio[name=have_agent]').on('click', function () {
							var have_agent = $(this).val();
							if (have_agent == 1) {
								$('#ep_agent').hide("fast");
							} else {
								$('#ep_agent').show("fast");
							}
						});
						// 代理商选择框 的初始化
						if ($('input:radio[name=have_agent]:checked').val() == 1) {
							$('#ep_agent').hide();
						}

						// 提交操作
						$('#sales_submit').on('click', function () {
							var btn = $(this);
							btn.attr('disabled', 'disabled');
							btn.text('提交中...');
							var have_agent = $('input:radio[name=have_agent]:checked').val();
							var ep_agent = $('#ep_agent').find('option:selected').val();
							var ca_id = $('#ca_id').find('option:selected').val();
							var customer_status = $('#customer_status').find('option:selected').val();
							var sales_remark = $.trim($('#agent_sales_remark').val());
							var url = "{$url}";
							var ep_id = "{$ep_id}";
							var operator = "{$operator}";
							var ep_ca_id = "{$basic_information['ca_id']}" // 当前负责人 ID
							var op_ca_id = "{$op_ca_id}" // 当前操作人 ID

							if (ep_agent == 0 && have_agent == 2) {
								alert('请选择代理商');
								btn.attr('disabled', false);
								btn.text('保 存');
								return false;
							}
							if (ca_id == 0) {
								alert('请选择销售人员');
								btn.attr('disabled', false);
								btn.text('保 存');
								return false;
							}
							if (customer_status == 0) {
								alert('请选择客户状态');
								btn.attr('disabled', false);
								btn.text('保 存');
								return false;
							}
							$.ajax({
								url: url + "/cyadmin/api/company/salessetting",
								type: "POST",
								dataType: "json",
								data: {
									have_agent: have_agent,
									ep_agent: have_agent != 1 ? ep_agent : 0,
									ca_id: ca_id,
									customer_status: customer_status,
									sales_remark: sales_remark,
									ep_id: ep_id,
									operator: operator,
									ep_ca_id: ep_ca_id,
									op_ca_id: op_ca_id
								},
								success: function (data) {
									if (data.errcode != 0) {
										alert(data.errmsg);
										btn.attr('disabled', false);
										btn.text('保 存');
										return false;
									} else {
										alert(data.errmsg);
										btn.text('成 功');
										location.reload();
									}
								},
								error: function () {
									alert('网络错误');
									btn.attr('disabled', false);
									btn.text('保 存');
									return false;
								}
							});
						});
					});
				</script>
			</div>
		</div>
		{*销售设置结束*}

		<div role="tabpanel" class="tab-pane" id="installation_record">
			<table class="table table-bordered table-hover font12" style="margin-top: 42px;">
				<colgroup>
					<col class="t-col-10"/>
					<col class="t-col-10"/>
					<col class="t-col-10"/>
					<col class="t-col-10"/>
					<col class="t-col-10"/>
					<col class="t-col-10"/>
					<col class="t-col-10"/>
				</colgroup>
				<thead>
				<tr>
					<th>图标</th>
					<th>应用名</th>
					<th>插件ID</th>
					<th>Agent ID</th>
					<th>应用描述</th>
					<th>状态</th>
					<th>最后启用时间</th>
				</tr>
				</thead>
				<tbody>
				{foreach $app_install as $k => $v}
					<tr>
						<td><img src="http://demo.vchangyi.com/admincp/static/images/application/{$v['cp_icon']}"
						         width="50" height="50"></td>
						<td>{$v['cp_name']|escape}</td>
						<td>{$v['cp_pluginid']}</td>
						<td>{$v['cp_agentid']}</td>
						<td>{$v['cp_description']|escape}</td>
						<td>{if $v['cp_available'] == 4}已建立{/if}</td>
						<td>{$v['cp_lastopen']}</td>
					</tr>
					{foreachelse}
					<tr>
						<td colspan="7" class="warning">暂无任何{$module_plugin['cp_name']|escape}数据</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
		</div>
		{*应用安装记录结束*}

		<div role="tabpanel" class="tab-pane" id="messages_record">
			<table class="table table-bordered table-hover font12" style="margin-top: 42px;">
				<colgroup>
					<col class="t-col-10"/>
					<col class="t-col-10"/>
					<col class="t-col-10"/>
					<col class="t-col-10"/>
				</colgroup>
				<thead>
				<tr>
					<th>操作人员</th>
					<th>消息设定</th>
					<th>消息内容</th>
					<th>发送时间</th>
				</tr>
				</thead>
				<tbody>
				{foreach $list['message_list'] as $k => $v}
					<tr>
						<td>{$v['realman']}</td>
						<td>{$v['title']}</td>
						<td>{$v['content']}</td>
						<td>{$v['updated']}</td>
					</tr>
					{foreachelse}
					<tr>
						<td colspan="4" class="warning">暂无任何{$module_plugin['cp_name']|escape}数据</td>
					</tr>
				{/foreach}
				</tbody>
				{if $total['message_total'] > 0}
					<tfoot>
					<tr>
						<td colspan="4" class="text-right vcy-page">{$multi['message_multi']}</td>
					</tr>
					</tfoot>
				{/if}
			</table>
		</div>
		{*消息记录结束*}

		{*企业数据开始*}
		<div role="tabpanel" class="tab-pane" id="conpany_data">

			{*tabs*}
			<ul class="nav nav-tabs" role="tablist" style="margin-top: 3px">
				<li role="presentation" class="active"><a href="#user_data" aria-controls="user_data" role="tab"
														  data-toggle="tab" id="only_user_data">用户数据</a></li>
				<li role="presentation"><a href="#plugin_data" aria-controls="plugin_data" role="tab" data-toggle="tab" id="only_plugin_data">应用数据</a>
				</li>
			</ul>

			{*分隔线*}
			<div class="tab-content">

				{*用户数据 开始*}
				<div role="tabpanel" class="tab-pane active" id="user_data">
					<div class="panel panel-default font12">
						<div class="panel-heading">昨天数据</div>
						<table class="panel-body table table-striped table-bordered table-hover font12">
							<colgroup>
								<col class="t-col-10"/>
								<col class="t-col-10"/>
								<col class="t-col-10"/>
								<col class="t-col-9"/>
								<col class="t-col-9"/>
								<col class="t-col-9"/>
								<col class="t-col-10"/>
							</colgroup>

							<thead>
							<tr class="head-center" id="user_header">

							</tr>
							</thead>
						</table>
					</div>

					{*指标*}
					<div class="panel panel-default font12">
						<div class="panel-heading">

							<div class="panel-body">
								<form class="form-horizontal">
									<label class="control-label col-sm-1">关键指标</label>

									<div class="col-sm-2">
										<select name="ep_industry" class="form-control form-small"
												data-width="auto" style="height: 34px; border-radius: 4px;" id="user_chart_select">
											<option value="1">新增员工数</option>
											<option value="2">活跃员工数</option>
											<option value="3">已关注员工数</option>
											<option value="4">未关注员工数</option>
											<option value="5">企业员工总数</option>

										</select>
									</div>

									<label class="control-label col-sm-1">时间范围</label>

									<div class="col-sm-2">
										<select id="user_chart_time" class="form-control form-small"
												data-width="auto" style="height: 34px; border-radius: 4px;">
											<option value="7">最近7天</option>
											<option value="30">最近30天</option>
											<option value="-1">自定义时间</option>
										</select>
									</div>

									<div class="col-md-4" id="user_chart_time_custom_time" hidden>
										<div class="input-daterange input-group">
											<input type="text"
												   class="input-sm form-control"
												   value=""
												   id="user_chart_date_start">

											<span class="input-group-addon">to</span>

											<input type="text"
												   class="input-sm form-control"
												   value=""
												   id="user_chart_date_end">
										</div>
									</div>

									<div id="user_chart_submit" class="btn btn-primary">
										确 定
									</div>
									<script>
										$(function () {
											$('#user_chart_time').change(function () {
												if ($('#user_chart_time').val() == -1) {
													$('#user_chart_time_custom_time').show('fast');
												} else {
													$('#user_chart_time_custom_time').hide('fast');
												}
											});
											$('#user_chart_time_custom_time .input-daterange').datepicker({
												todayHighlight: true
											});
										});
									</script>
									<script>
										$(function () {
											$('#sandbox-container .input-daterange').datepicker({
												todayHighlight: true
											});
										});
									</script>
								</form>
							</div>

						</div>
						<div class="panel-body">
							<form class="form-inline vcy-from-search" id="list_form" role="form" action="{$formActionUrl}">
								<div class="form-row">
									<div class="form-group">

										<div class="modal-body">
											<div class="form-horizontal" role="form" method="post" action="{$form_del_url}">

												{*指标图*}
												<div style="overflow: auto;">
													<div id="user_chart" style="width: 1000px;height:400px;margin: 30px;"></div>
												</div>
												<script type="text/javascript">

												</script>
											</div>
										</div>

									</div>
								</div>
							</form>
						</div>
					</div>
					{*详情列表*}
					<div class="panel panel-default font12">
						<div class="panel-heading">
							<div style="width:100%;height:35px;margin-top:10px;">
								<div style="width:80px;height:30px;float:left;font:20px 微软雅黑;line-height:35px;">数据详情</div>
								<div data-toggle="modal" data-target="#add_agant" id="download_user" class="btn-warning"
									 style="cursor: pointer; padding:5px;width:65px;border-radius:5px;margin-right:10px;float:right;font:16px 微软雅黑;line-height:26px;text-align:center;">
									导 出
								</div>
							</div>
						</div>

						<table class="panel-body table table-striped table-bordered table-hover font12" id="user_detail_page">
							<colgroup>
								<col class="t-col-10"/>
								<col class="t-col-10"/>
								<col class="t-col-10"/>
								<col class="t-col-9"/>
								<col class="t-col-9"/>
								<col class="t-col-9"/>
							</colgroup>
							<thead>

							<tr class="tr-style">
								<th>日期</th>
								<th>新增员工数</th>
								<th>活跃员工数</th>
								<th>已关注员工数</th>
								<th>未关注员工数</th>
								<th>企业员工总数</th>
							</tr>
							</thead>

							<tfoot id="user_detail_multi">

							</tfoot>

							<tbody id="user_detail">

							</tbody>
						</table>

					</div>

				</div>
				{*用户数据 结束*}

				{*应用数据 开始*}
				<div role="tabpanel" class="tab-pane" id="plugin_data">

					{*头*}
					<div class="panel panel-default font12">
						<div class="panel-heading">昨天数据</div>
						<table class="panel-body table table-striped table-bordered table-hover font12">
							<colgroup>
								<col class="t-col-10"/>
								<col class="t-col-10"/>
								<col class="t-col-10"/>
								<col class="t-col-9"/>
								<col class="t-col-9"/>
								<col class="t-col-9"/>
								<col class="t-col-10"/>
							</colgroup>

							<thead>
							<tr class="head-center" id="plugin_header">

							</tr>
							</thead>
						</table>
					</div>

					{*指标*}
					<div class="panel panel-default font12">
						<div class="panel-heading">

							<div class="panel-body">
								<form class="form-horizontal">
									<label class="control-label col-sm-1">关键指标</label>

									<div class="col-sm-2">
										<select id="plugin_chart_select" class="form-control form-small"
												data-width="auto" style="height: 34px; border-radius: 4px;">
											<option value="1">应用安装数</option>
											<option value="2">应用主数据</option>
											<option value="3">应用总数据</option>
											<option value="4">应用活跃员工数</option>
											<option value="5">新增活跃员工数</option>
										</select>
									</div>

									<label class="control-label col-sm-1">时间范围</label>

									<div class="col-sm-2">
										<select id="plugin_chart_time" class="form-control form-small"
												data-width="auto" style="height: 34px; border-radius: 4px;">
											<option value="7">最近7天</option>
											<option value="30">最近30天</option>
											<option value="-1">自定义时间</option>
										</select>
									</div>

									<div class="col-md-4" id="plugin_chart_time_custom_time" hidden>
										<div class="input-daterange input-group">
											<input type="text"
												   class="input-sm form-control"
												   value=""
												   id="plugin_chart_date_start">

											<span class="input-group-addon">to</span>

											<input type="text"
												   class="input-sm form-control"
												   value=""
												   id="plugin_chart_date_end">
										</div>
									</div>

									<div id="plugin_chart_submit" class="btn btn-primary">
										确 定
									</div>

									<script>
										$(function () {
											$('#plugin_chart_time').change(function () {
												if ($('#plugin_chart_time').val() == -1) {
													$('#plugin_chart_time_custom_time').show('fast');
												} else {
													$('#plugin_chart_time_custom_time').hide('fast');
												}
											});
											$('#plugin_chart_time_custom_time .input-daterange').datepicker({
												todayHighlight: true
											});
										});
									</script>
								</form>
							</div>
						</div>

						{*指标图*}
						<div style="overflow: auto;">
							<div id="plugin_chart" style="width: 1000px;height:400px;margin: 30px;"></div>
						</div>
						<script type="text/javascript">

						</script>
					</div>


					{*详情*}
					<div class="panel panel-default font12">
						<div class="panel-heading">
							<div style="width:100%;height:35px;margin-top:10px;">
								<div style="width:80px;height:30px;float:left;font:20px 微软雅黑;line-height:35px;">数据详情</div>
								<div data-toggle="modal" data-target="#add_agant" id="download_plugin" class="btn-warning"
									 style="cursor: pointer; padding:5px;width:65px;border-radius:5px;margin-right:10px;float:right;font:16px 微软雅黑;line-height:26px;text-align:center;">
									导 出
								</div>
							</div>
						</div>
						<table class="panel-body table table-striped table-bordered table-hover font12" id="plugin_datail_page">
							<colgroup>
								<col class="t-col-10"/>
								<col class="t-col-10"/>
								<col class="t-col-10"/>
								<col class="t-col-9"/>
								<col class="t-col-9"/>
								<col class="t-col-9"/>
							</colgroup>

							<thead>
							<tr class="tr-style">
								<td>日期</td>
								<td>应用安装数</td>
								<td>应用主数据</td>
								<td>应用总数据</td>
								<td>应用活跃员工数</td>
								<td>新增活跃员工数</td>
							</tr>
							</thead>

							<tbody id="plugin_detail">

							</tbody>

							<tfoot id="plugin_detail_multi">

							</tfoot>
						</table>

					</div>

					{*套件数据*}
					<div class="panel panel-default font12">
						<div class="panel-heading">

							<div class="panel-body">
								<form class="form-horizontal">
									<label class="control-label col-sm-1">选择应用</label>

									<div class="col-sm-2">
										<select id="plugin_list_select" class="form-control form-small"
												data-width="auto" style="height: 34px; border-radius: 4px;">

										</select>
									</div>

									<label class="control-label col-sm-1">时间范围</label>

									<div class="col-sm-2">
										<select id="plugin_list_time" class="form-control form-small"
												data-width="auto" style="height: 34px; border-radius: 4px;">
											<option value="7">最近7天</option>
											<option value="30">最近30天</option>
											<option value="-1">自定义时间</option>
										</select>
									</div>

									<div class="col-md-4" id="plugin_list_time_custom_time" hidden>
										<div class="input-daterange input-group">
											<input type="text"
												   class="input-sm form-control"
												   id="plugin_list_date_start">

											<span class="input-group-addon">to</span>

											<input type="text"
												   class="input-sm form-control"
												   id="plugin_list_date_end">
										</div>
									</div>

									<div id="plugin_list_submit" class="btn btn-primary">
										确 定
									</div>
									<div data-toggle="modal" data-target="#add_agant" id="download_data" class="btn-warning"
										 style="cursor: pointer; padding:5px;width:65px;border-radius:5px;margin-right:10px;float:right;font:16px 微软雅黑;line-height:26px;text-align:center;">
										导出
									</div>
									<script>
										$(function () {
											$('#plugin_list_time').change(function () {
												if ($('#plugin_list_time').val() == -1) {
													$('#plugin_list_time_custom_time').show('fast');
												} else {
													$('#plugin_list_time_custom_time').hide('fast');
												}
											});
											$('#plugin_list_time_custom_time .input-daterange').datepicker({
												todayHighlight: true
											});
										});
									</script>
								</form>
							</div>
						</div>
						<table class="panel-body table table-striped table-bordered table-hover font12">
							<colgroup>
								<col class="t-col-10"/>
								<col class="t-col-10"/>
								<col class="t-col-10"/>
								<col class="t-col-9"/>
								<col class="t-col-9"/>
								<col class="t-col-9"/>
								<col class="t-col-10"/>
								<col class="t-col-10"/>
							</colgroup>

							<thead>
							<tr class="tr-style">
								<td>应用/套件名称</td>
								<td>时间</td>
								<td>新增活跃员工数</td>
								<td>应用活跃员工数</td>
								<td>应用活跃度</td>
								<td>应用主数据</td>
								<td>应用总数据</td>
								<td>人均贡献值</td>
							</tr>
							</thead>

							<tbody id="plugin_list">

							</tbody>

							<tfoot id="plugin_list_multi">

							</tfoot>
						</table>

					</div>

				</div>
				{*应用数据 结束*}
			</div>

		</div>
	</div>

</div>

{*消息模板 Modal*}
<div class="modal fade" id="message_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
							aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">选择消息模板</h4>
			</div>
			<div class="modal-body">
				<div class="mb">
					<form>
						<div class="input-group" style="width:80%;margin-left:40px;">
							<input class="form-control" type="text" name="search"/>
							<span class="input-group-addon" id="basic-addon2">搜一搜</span>
						</div>
					</form>

					<ul class="nav" style="margin-left:40px;margin-top:15px;" sign="">
						{foreach $data1 as $k=>$val}
							<li><input type="radio" name="meiid[]" value="{$val['meid']}"/><span
										style="margin-left:12px;">{$val['title']}</span></li>
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

<script type="text/template" id="tpl_user_datail">

	<% if (!jQuery.isEmptyObject(list)) { %>
	<% $.each(list,function(n,val){ %>
	<tr>
		<td><%=val['date']%></td>
		<td><%=val['add']%></td>
		<td><%=val['active_count']%></td>
		<td><%=val['attention']%></td>
		<td><%=val['unattention']%></td>
		<td><%=val['all']%></td>
	</tr>
	<% }) %>
	<% }else{ %>
	<tr>
		<td colspan="8" class="warning">
			暂无信息
		</td>
	</tr>
	<% } %>

</script>

<script type="text/template" id="tpl_user_detail_multi">

	<tr>
		<td colspan="7" class="text-right">
			<% if(multi != null){ %>
			<%=multi%>
			<% } %>
		</td>
	</tr>

</script>

<script type="text/template" id="tpl_user_header">

	<% $.each(header,function(k,val){ %>
	<td>
		<%=val['name']%>
		<br>
			<span class="head-number">
				<%=val['count']%>
			</span>
		<br>
		<% if (val['percent'] > 0) { %>
				<span class="text-percent-up">
			<% } else if (val['percent'] < 0) { %>
				<span class="text-percent-down">
			<% } %>

				<%=val['percent']%> %

			<% if (val['percent'] > 0) { %>
				<span class="glyphicon glyphicon-chevron-up"></span>
			<% } else if (val['percent'] < 0) { %>
				<span class="glyphicon glyphicon-chevron-down"></span>
			<% } else { %>
				<span class="glyphicon glyphicon-minus"></span>
			<% } %>
			</span>
	</td>
	<% }) %>
</script>

<script type="text/template" id="tpl_user_total">
	<tr>
		<td></td>
		<td colspan="12" class="text-right"><%=multi%></td>
	</tr>
</script>

<script type="text/template" id="tpl_plugin_header">

	<% $.each(header,function(k,val){ %>
	<td>
		<%=val['name']%>
		<br>
			<span class="head-number">
				<%=val['count']%>
			</span>
		<br>
		<% if (val['percent'] > 0) { %>
				<span class="text-percent-up">
			<% } else if (val['percent'] < 0) { %>
				<span class="text-percent-down">
			<% } %>

				<%=val['percent']%> %

			<% if (val['percent'] > 0) { %>
				<span class="glyphicon glyphicon-chevron-up"></span>
			<% } else if (val['percent'] < 0) { %>
				<span class="glyphicon glyphicon-chevron-down"></span>
			<% } else { %>
				<span class="glyphicon glyphicon-minus"></span>
			<% } %>
			</span>
	</td>
	<% }) %>
</script>

<script type="text/template" id="tpl_plugin_detail">

	<% if (!jQuery.isEmptyObject(list)) { %>
	<% $.each(list,function(n,val){ %>
	<tr>
		<td><%=val['date']%></td>
		<td><%=val['install_plugin']%></td>
		<td><%=val['count_index']%></td>
		<td><%=val['count_all']%></td>
		<td>0</td>
		<td>0</td>
	</tr>
	<% }) %>
	<% }else{ %>
	<tr>
		<td colspan="8" class="warning">
			暂无信息
		</td>
	</tr>
	<% } %>

</script>
<script type="text/template" id="tpl_plugin_detail_multi">

	<tr>
		<td colspan="7" class="text-right">
			<% if(multi != null){ %>
			<%=multi%>
			<% } %>
		</td>
	</tr>

</script>

<script type="text/template" id="tpl_plugin_list">

	<% if (!jQuery.isEmptyObject(list)) { %>

	<% $.each(list,function(k,val){ %>
	<tr>
		<td><%=val['pg_name']%></td>
		<td><%=val['date']%></td>
		<td>0</td>
		<td><%=val['active_staff']%></td>
		<td>0</td>
		<td><%=val['count_index']%></td>
		<td><%=val['count_all']%></td>
		<td><%=val['pre_devote']%></td>
	<% }) %>
	<% }else{ %>
	<tr>
		<td colspan="10" class="warning">
			暂无信息
		</td>
	</tr>
	<% } %>
</script>

<script type="text/template" id="tpl_plugin_list_multi">

	<tr>
		<td colspan="10" class="text-right">
			<% if(multi != null){ %>
			<%=multi%>
			<% } %>
		</td>
	</tr>

</script>

<script type="text/template" id="tpl_plugin_list_select">

	<option value="">请选择</option>
	<% $.each(select,function(k,val){ %>
	<option value="<%=k%>"><%=val%></option>
	<% }) %>

</script>

{*消息模板 js*}
<script>
	// 消息模板
	$(function () {
		var ep_id = {$ep_id}
		var selected_id = [];
		selected_id[0] = "{$ep_id}";

		var fy_url = '/enterprise/news/list';
		// 模态框分页
		$('.fy').on('click', 'a', function (e) {
			e.preventDefault();
			var href = $(this).attr('href');
			if (href.indexOf('?') >= 0) {
				var n = href.indexOf('page');
				var data = { page: href.substring(n + 5) };
				mb_list(data);
			}
		});

		var mb_list = function (d) {

			$.ajax(fy_url, {
				type: 'get',
				data: { mo:1, page:d.page },
				dataType: 'json',
				success: function (d) {
					var lis = '';
					$.each(d.list, function (i, n) {
						lis += '<li><input type="radio" name="meiid[]" value="' + this.meid + '" /><span style="margin-left:12px;">' + this.title + '</span></li>';
					});
					$('.mb ul').html(lis);
					$('.fy').html($(d.page));
				}
			});
		};

		$('#sure').on('click', function () {
			// 获取勾选的消息模板
			var message_id = $('.mb input[type=radio]:checked').val();
			var message_title = $('.mb input[type=radio]:checked').parentsUntil().eq(0).find('span').text();
			var en_key = "{$en_key}";
			if (message_id == '' || message_id == 'undefined') {
				alert('请选择消息模板');
				return false;
			}
			$.ajax({
				url: '/api/company/message',
				type: 'POST',
				data: { message_id: message_id, message_title: message_title, selected_id: selected_id, en_key: en_key },
				dataType: 'json',
				success: function (data) {
					if (data.errcode == 0) {
						alert(data.errmsg);
						$('#message_modal').modal('hide');
						return true;
					} else {
						alert(data.errmsg);
						return false;
					}
				},
				error: function (e) {
					alert('网络错误');
					return false;
				}
			});
		});

		//用户数据chart
		//var selected_id = [];
		//selected_id[0] = "{$ep_id}";
		//用户数据 详情分页
		function _page() {
			$('#user_detail_multi .pagination a').on('click', function () {
				var page = 'page';
				var result = $(this).attr('href').match(new RegExp("[\?\&]" + page + "=([^\&]+)", "i"));
				if (result == null || result.length < 1) {
					return '';
				}
				initial_plugin(result[1]);
				return false;
			});
		}
		//应用数据 详情分页
		function _page_plugin_view() {
			$('#plugin_detail_multi .pagination a').on('click', function () {
				var page = 'page';
				var result = $(this).attr('href').match(new RegExp("[\?\&]" + page + "=([^\&]+)", "i"));
				if (result == null || result.length < 1) {
					return '';
				}
				initial_plugin(result[1]);
				return false;
			});
		}
		//应用数据 应用/套件分页
		function _page_plugin_data() {
			$('#plugin_list_multi .pagination a').on('click', function () {
				var page = 'page';
				var result = $(this).attr('href').match(new RegExp("[\?\&]" + page + "=([^\&]+)", "i"));
				if (result == null || result.length < 1) {
					return '';
				}
				initial_plugin_data(result[1]);
				return false;
			});
		}
		$('#plugin_datail_page .pagination a').on('click', function () {
			var page = 'page';
			var result = $(this).attr('href').match(new RegExp("[\?\&]" + page + "=([^\&]+)", "i"));
			if (result == null || result.length < 1) {
				return '';
			}
			show_plugin_info(result[1]);
			return false;
		});
		//用户数据初始化
		$('#only_user_data').on('click', function() {
			initial_plugin();
		});
		//应用数据初始化
		$('#only_plugin_data').on('click', function() {

			initial_plugin();
			var data = {};
			data.field = $('#plugin_chart_select').val();
			data.range = $('#plugin_chart_time').val();
			data.s_time = $('#plugin_chart_date_start').val();
			data.e_time = $('#plugin_chart_date_end').val();
			data.ep_id = ep_id;
			$.ajax({
				url:'/Stat/Apicp/Plugin/PluginViewChart',
				dataType:'json',
				type:'get',
				data:data,
				success:function(result){
					data = result.result;
					plugin_chart.setOption({
						xAxis: {
							type: 'category',
							axisLabel: {
								interval: 0,
								rotate: 45,
								margin: 2,
								textStyle: {
									color: "#222"
								}
							},
							data: data.days,
						},
						series: [{
							name: data.name,
							type: 'line',
							data: data.count
						}]
					});
				}
			});
		});
		//用户数据 图表确定事件
		$('#user_chart_submit').on('click', function() {
			_page();
			show_user_info();
		});
		//应用数据 图表确定事件
		$('#plugin_chart_submit').on('click', function(){
			initial_plugin_only();
			show_plugin_info();

		});
		//初始化用户数据图表
		function show_user_info(page) {
			initial();
			var data = {};
			data.act = $('#user_chart_select').val();
			data.range = $('#user_chart_time').val();
			data.s_time = $("#user_chart_date_start").val();
			data.e_time = $("#user_chart_date_end").val();
			data.ep_id = ep_id;
			data.page = page;
			// 时间范围
			if (data.range == -1) {
				var s_time = $('#user_chart_date_start').val();
				var e_time = $('#user_chart_date_end').val();
				if (s_time == '' || e_time == '') {
					alert('自定义时间不能为空');
					return false;
				}
			}
			$.ajax({
				url:'/Stat/Apicp/User/UserViewChart',
				dataType:'json',
				type:'get',
				data:data,
				success:function(result){
					var data = result.result;
					user_chart.setOption({
						xAxis: {
							type: 'category',
							axisLabel: {
								interval: 0,
								rotate: 45,
								margin: 2,
								textStyle: {
									color: "#222"
								}
							},
							data:data.days
						},
						series: [{
							name: data.name,
							type: 'line',
							data: data.count
						}]
					});
				}
			});

			//单个企业 用户数据 数据详情
			$.ajax({
				url: '/Stat/Apicp/User/UserDetail',
				dataType: 'json',
				data: {
					ep_id: ep_id,
					page: page,
					days: $('#user_chart_time').val(),
					start: $('#user_chart_date_start').val(),
					end: $('#user_chart_date_end').val()
				},
				success: function (result) {

					$('#user_detail').html(txTpl('tpl_user_datail', result.result));
					$('#user_detail_multi').html(txTpl('tpl_user_total', result.result));
					_page();
				}

			});
		}
		//初始化数据
		$('#conpany_data_tab').on('click', function(){
			initial();
			initial_plugin();
			initial_plugin_data();
			plugin_name();
			var data = {};
			data.ep_id = ep_id;
			$.ajax({
				url:'/Stat/Apicp/User/UserViewChart',
				dataType:'json',
				type:'get',
				data:data,
				success:function(result){
					console.log(result);
					var data = result.result;
					user_chart.setOption({
						xAxis: {

							data:data.days,
						},
						series: [{
							name: data.name,
							type: 'line',
							data: data.count
						}]
					});
				}
			});
			//用户数据昨天数据
			$.ajax({
				url: '/Stat/Apicp/User/UserHeader',
				dataType: 'json',
				data: {
					ep_id: ep_id,
				},
				success: function (result) {
					$('#user_header').html(txTpl('tpl_user_header', result.result));
				}
			});
			//应用数据昨天数据
			$.ajax({
				url: '/Stat/Apicp/Plugin/PluginYestaday',
				dataType: 'json',
				data: {
					ep_id: ep_id
				},
				success: function (result) {
					$('#plugin_header').html(txTpl('tpl_plugin_header', result.result));
				}
			});

		});

		//清空用户数据图标
		function initial() {
			// 基于准备好的dom，初始化echarts实例
			user_chart = echarts.init(document.getElementById('user_chart'));

			// 指定图表的配置项和数据
			var option = {
				tooltip: {
					trigger: 'axis'
				},
				legend: {},
				grid: {
					left: '3%',
					right: '4%',
					bottom: '3%',
					containLabel: true
				},
				toolbox: {
					show: true,
					feature: {
						saveAsImage: {},
						magicType: {
							type: ['line', 'bar']
						}
					}
				},
				yAxis: [
					{
						type: 'value'
					}
				],
				xAxis: [
					{
						axisLabel: {
							interval: 0,
							rotate: 45,
							margin: 2,
							textStyle: {
								color: "#222"
							}
						},
						type: 'category',
						boundaryGap: false,
						data: ['']
					}
				],

			};

			// 使用刚指定的配置项和数据显示图表。
			user_chart.setOption(option);
		}

		//初始化应用图表
		function show_plugin_info(page) {
			initial();
			var data = {};
			data.type = $('#plugin_chart_select').val();
			data.range = $('#plugin_chart_time').val();
			data.s_time = $("#plugin_chart_date_start").val();
			data.e_time = $("#plugin_chart_date_end").val();
			data.ep_id = ep_id;
			data.page = page;
			// 时间范围
			if (data.range == -1) {
				var s_time = $('#plugin_chart_date_start').val();
				var e_time = $('#plugin_chart_date_end').val();
				if (s_time == '' || e_time == '') {
					alert('自定义时间不能为空');
					return false;
				}
			}


			//应用数据 数据详情
			$.ajax({
				url: '/Stat/Apicp/Plugin/PluginView',
				dataType: 'json',
				data: {
					ep_id: ep_id,
					page:page,
					days: $('#plugin_chart_time').val(),
					start: $('#plugin_chart_date_start').val(),
					end: $('#plugin_chart_date_end').val(),
				},
				success: function (result) {

					$('#plugin_detail').html(txTpl('tpl_plugin_detail', result.result));
					$('#plugin_detail_multi').html(txTpl('tpl_plugin_detail_multi', result.result));
					_page_plugin_view();
				}

			});
		}
		//应用/套件 确定事件
		function plugin_list_submit() {
			$('#plugin_list_submit').on('click', function() {
			    	initial_plugin_data();


			});
		}
		plugin_list_submit();
		function initial_plugin_only() {
			// 基于准备好的dom，初始化echarts实例
			plugin_chart = echarts.init(document.getElementById('plugin_chart'));

			// 指定图表的配置项和数据
			var option = {
				tooltip: {
					trigger: 'axis'
				},
				legend: {},
				grid: {
					left: '3%',
					right: '4%',
					bottom: '3%',
					containLabel: true
				},
				toolbox: {
					show: true,
					feature: {
						saveAsImage: {},
						magicType: {
							type: ['line', 'bar']
						}
					}
				},
				yAxis: [
					{
						type: 'value'
					}
				],
				xAxis: [
					{
						axisLabel: {
							interval: 0,
							rotate: 45,
							margin: 2,
							textStyle: {
								color: "#222"
							}
						},
						type: 'category',
						boundaryGap: false,
						data: ['']
					}
				],

			};

			// 使用刚指定的配置项和数据显示图表。
			plugin_chart.setOption(option);
			var data = {};
			data.field = $('#plugin_chart_select').val();
			data.range = $('#plugin_chart_time').val();
			data.s_time = $('#plugin_chart_date_start').val();
			data.e_time = $('#plugin_chart_date_end').val();
			data.ep_id = ep_id;
			$.ajax({
				url:'/Stat/Apicp/Plugin/PluginViewChart',
				dataType:'json',
				type:'get',
				data:data,
				success:function(result){
					data = result.result;
					plugin_chart.setOption({
						xAxis: {
							type: 'category',
							axisLabel: {
								interval: 0,
								rotate: 45,
								margin: 2,
								textStyle: {
									color: "#222"
								}
							},
							data: data.days,
						},
						series: [{
							name: data.name,
							type: 'line',
							data: data.count
						}]
					});
				}
			});
		}

			function initial_plugin(page) {
			// 基于准备好的dom，初始化echarts实例
			plugin_chart = echarts.init(document.getElementById('plugin_chart'));

			// 指定图表的配置项和数据
			var option = {
				tooltip: {
					trigger: 'axis'
				},
				legend: {},
				grid: {
					left: '3%',
					right: '4%',
					bottom: '3%',
					containLabel: true
				},
				toolbox: {
					show: true,
					feature: {
						saveAsImage: {},
						magicType: {
							type: ['line', 'bar']
						}
					}
				},
				yAxis: [
					{
						type: 'value'
					}
				],
				xAxis: [
					{
						axisLabel: {
							interval: 0,
							rotate: 45,
							margin: 2,
							textStyle: {
								color: "#222"
							}
						},
						type: 'category',
						boundaryGap: false,
						data: ['']
					}
				],

			};

			// 使用刚指定的配置项和数据显示图表。
			plugin_chart.setOption(option);

			//单个企业 用户数据 数据详情
			$.ajax({
				url: '/Stat/Apicp/User/UserDetail',
				dataType: 'json',
				data: {
					ep_id: ep_id,
					page: page,
					days: $('#user_chart_time').val(),
					start: $('#user_chart_date_start').val(),
					end: $('#user_chart_date_end').val(),
				},
				success: function (result) {

					$('#user_detail').html(txTpl('tpl_user_datail', result.result));
					$('#user_detail_multi').html(txTpl('tpl_user_total', result.result));
					_page();
				}

			});

			//应用数据 数据详情
			$.ajax({
				url: '/Stat/Apicp/Plugin/PluginView',
				dataType: 'json',
				data: {
					ep_id: ep_id,
					page:page,
					days: $('#plugin_chart_time').val(),
					start: $('#plugin_chart_date_start').val(),
					end: $('#plugin_chart_date_end').val(),
				},
				success: function (result) {

					$('#plugin_detail').html(txTpl('tpl_plugin_detail', result.result));
					$('#plugin_detail_multi').html(txTpl('tpl_plugin_detail_multi', result.result));
					_page_plugin_view();
				}

			});

		}

		function initial_plugin_data(page) {
			var identifier = $('#plugin_list_select').val();

			//应用/套件 数据详情
			$.ajax({
				url: '/Stat/Apicp/Plugin/PluginData',
				dataType: 'json',
				data: {
					days:$('#plugin_list_time').val(),
					start: $('#plugin_list_date_start').val(),
					end: $('#plugin_list_date_end').val(),
					ep_id: ep_id,
					page:page,
					identifier:identifier,
				},
				success: function (result) {
					$('#plugin_list').html(txTpl('tpl_plugin_list', result.result));
					$('#plugin_list_multi').html(txTpl('tpl_plugin_list_multi', result.result));
					_page_plugin_data();

				}
			});
		}
		//应用下拉列表
		function plugin_name() {
			var identifier = $('#plugin_list_select').val();

			$.ajax({
				url: '/Stat/Apicp/Plugin/PluginSelect',
				dataType: 'json',
				data: {
					days:$('#plugin_list_time').val(),
					start: $('#plugin_list_date_start').val(),
					end: $('#plugin_list_date_end').val(),
					ep_id: ep_id,
					identifier:identifier,
				},
				success: function (result) {
				$('#plugin_list_select').html(txTpl('tpl_plugin_list_select', result.result));
					_page_plugin_data();

				}
			});
		}

		//用戶数据导出
		$('#download_user').on('click', function () {
			var data = {};
			data.ep_id = ep_id;
			var days = $('#user_chart_time').val();
			var start = $('#user_chart_date_start').val();
			var end = $('#user_chart_date_end').val();
			var url = '/Stat/Apicp/User/Dump_user_detail?days='+ days + '&start=' + start + '&end=' + end + '&ep_id=' + data.ep_id;
			window.location.href = url;
		})
		//应用数据导出
		$('#download_plugin').on('click', function () {
			var data = {};
			data.ep_id = ep_id;
			var days = $('#plugin_chart_time').val();
			var start = $('#plugin_chart_date_start').val();
			var end = $('#plugin_chart_date_end').val();
			var url = '/Stat/Apicp/Plugin/Download_plugin_detail?days='+ days + '&start=' + start + '&end=' + end + '&ep_id=' + data.ep_id;
			window.location.href = url;
		})
		//套件/应用数据导出
		$('#download_data').on('click', function () {
			var data = {};
			data.ep_id = ep_id;
			data.identifier = $('#plugin_list_select').val();
			var days = $('#plugin_list_time').val();
			var start = $('#plugin_list_date_start').val();
			var end = $('#plugin_list_date_end').val();
			var url = '/Stat/Apicp/Plugin/Download_plugin_data?days='+ days + '&start=' + start + '&end=' + end + '&identifier=' + data.identifier + '&ep_id=' + data.ep_id;
			window.location.href = url;
		})

	});

</script>

{include file='cyadmin/footer.tpl'}