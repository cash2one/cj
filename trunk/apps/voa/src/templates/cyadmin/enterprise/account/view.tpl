{include file='cyadmin/header.tpl'}
<script>
	$(function () {
		$('#sandbox-container .input-daterange').datepicker({
			todayHighlight: true
		});
	});
</script>
<div>
	<a class="btn btn-success" href="/enterprise/account/"
	   style="position: absolute; right: 21px; top: 9px;"> 返 回 </a>

	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active"><a href="#basic_information" aria-controls="basic_information" role="tab"
		                                          data-toggle="tab">基本信息</a></li>
		<li role="presentation"><a href="#proxy_settings" id="proxy_settings_tab" aria-controls="proxy_settings" role="tab" data-toggle="tab">代理设置</a>
		</li>
		<li role="presentation"><a href="#acting_client" aria-controls="acting_client" role="tab"
		                           data-toggle="tab">代理客户</a></li>
	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
		{*基本信息*}
		<div role="tabpanel" class="tab-pane active" id="basic_information">

			<div class="form-horizontal" style="margin-top: 20px;">
				<div class="form-group">
					<label class="col-sm-2 control-label">联系人姓名</label>

					<div class="col-sm-10">
						<p class="form-control-static">{$activity['link_name']|escape}</p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">联系人电话</label>

					<div class="col-sm-10">
						<p class="form-control-static">{$activity['link_phone']|escape}</p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">邮箱</label>

					<div class="col-sm-10">
						<p class="form-control-static">{$activity['email']|escape}</p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">代理区域</label>

					<div class="col-sm-10">
						<p class="form-control-static">{$activity['area']|escape}</p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">公司名称</label>

					<div class="col-sm-10">
						<p class="form-control-static">{$activity['co_name']|escape}</p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">公司地址</label>

					<div class="col-sm-10">
						<p class="form-control-static">{$activity['co_address']|escape}</p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">公司简介</label>

					<div class="col-sm-10">
						<p class="form-control-static">{$activity['intro']|escape}</p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">提交IP</label>

					<div class="col-sm-10">
						<p class="form-control-static">{$activity['post_ip']}</p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">提交时间</label>

					<div class="col-sm-10">
						<p class="form-control-static">{$activity['updated']}</p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">付费时间</label>

					<div class="col-sm-10">
						<p class="form-control-static">{if $activity['pay_time'] == 0}无{else}{$activity['pay_time']}{/if}</p>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">代理年限</label>

					<div class="col-sm-10">
						<p class="form-control-static">{$activity['deadline']} (年)</p>
					</div>
				</div>
			</div>

		</div>

		{*代理设置*}
		<div role="tabpanel" class="tab-pane" id="proxy_settings">

			<form class="form-horizontal" style="margin-top: 20px;">
				<div class="form-group">
					<label for="inputPassword" class="col-sm-2 control-label">代理编号</label>

					<div class="col-sm-3">
						<label class="radio-inline">
							{$activity['id_number']|escape}
						</label>
					</div>
				</div>
				<div class="form-group">
					<label for="inputPassword" class="col-sm-2 control-label">付费状态</label>

					<div class="col-sm-3" id="pay_status">
						<label class="radio-inline">
							<input type="radio" name="pay_status" value="1" checked> 未付费
						</label>
						<label class="radio-inline">
							<input type="radio" name="pay_status" value="2"> 已付费
						</label>
					</div>
				</div>
				<div class="form-group">
					<label for="inputPassword" class="col-sm-2 control-label">代理期限</label>

					<div class="col-sm-3">
						<select id="deadline" class="form-control">
							<option>1</option>
							<option>2</option>
							<option>3</option>
							<option>4</option>
							<option>5</option>
						</select>
					</div>
					<span>(年)</span>
				</div>
				<div class="form-group">
					<label for="inputPassword" class="col-sm-2 control-label">付费时间</label>

					<div class="col-md-4" id="sandbox-container">
						<div class="input-daterange input-group" id="datepicker">
							<input type="text" class="input-sm form-control" id="pay_time_data">
						</div>
						<input type="time" style="  width: 83px; position: absolute; left: 190px; top: 0;"
						       class="input-sm form-control" id="pay_time_hour">
					</div>
				</div>
				<div class="form-group">
					<label for="inputPassword" class="col-sm-2 control-label">跟进销售</label>

					<div class="col-sm-3">
						<select class="form-control" id="ca_id" name="ca_id">
							<option value="0">请选择</option>
							{foreach $users as $k => $v}
								<option value="{$v['ca_id']}" {if $searchBy['ca_id'] == $v['ca_id']} selected="selected" {/if}>{$v['ca_realname']}</option>
								{foreachelse}
								<option value="">无</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="inputPassword" class="col-sm-2 control-label">销售备注</label>

					<div class="col-sm-3">
						<textarea id="salesremark" class="form-control" rows="3" style="
						margin: 0px -101px 0px 0px;
						width: 376px;
						height: 130px;
						resize: none;"></textarea>
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-2 control-label"></label>

					<div class="col-sm-3">
						<div id="agant_submit" class="btn btn-success">保存</div>
					</div>
				</div>
			</form>

			<div class="btn btn-primary disabled">
				备注记录 <span class="badge">{$agant_total}</span>
			</div>
			<table class="table table-bordered table-hover font12">
				<colgroup>
					<col class="t-col-15" />
					<col class="t-col-50" />
					<col class="t-col-10" />
				</colgroup>
				<thead>
				<tr>
					<th>销售人员</th>
					<th>销售备注</th>
					<th>时间</th>
				</tr>
				</thead>
				<tbody>
				{foreach $agant_list as $k => $v}
					<tr>
						<td>{$v['ca_realname']|escape}</td>
						<td style="word-break: break-all;">{$v['salesremark']|escape}</td>
						<td>{$v['updated']|escape}</td>
					</tr>
					{foreachelse}
					<tr>
						<td colspan="6" class="warning">暂无任何{$module_plugin['cp_name']|escape}数据</td>
					</tr>
				{/foreach}
				</tbody>
				{if $agant_total > 0}
					<tfoot>
					<tr>
						<td colspan="3" class="text-right vcy-page">{$agant_multi}</td>
					</tr>
					</tfoot>
				{/if}
			</table>
		</div>

		{*代理客户*}
		<div role="tabpanel" class="tab-pane" id="acting_client">

			<button class="btn btn-primary disabled" type="button" style="margin-top: 20px;">
				客户列表 <span class="badge">{$client_total}</span>
			</button>

			<hr style="margin-top: 0; margin-bottom: 10px;"/>

			<div style="width:100%; height: 41px; position: relative;">
				<form action="{$form_url}">
					<input type="hidden" name="acid" value="{$acid}" />
					<button style="position: absolute; right: 0;" name="export" value="export" type="submit" class="btn btn-warning  input-sm">导 出</button>
				</form>
			</div>

			<table class="table table-bordered table-hover font12">
				<colgroup>
					<col class="t-col-10" />
					<col class="t-col-10" />
					<col class="t-col-10" />
					<col class="t-col-10" />
					<col class="t-col-10" />
					<col class="t-col-10" />
					<col class="t-col-10" />
					<col class="t-col-10" />
				</colgroup>
				<thead>
				<tr>
					<th>企业名称</th>
					<th>联系人姓名</th>
					<th>联系人手机号</th>
					<th>邮箱</th>
					<th>是否绑定</th>
					<th>状态</th>
					<th>付费金额</th>
					<th>注册时间</th>
				</tr>
				</thead>
				<tbody>
				{foreach $client_list as $k => $v}
					<tr>
						<td>{$v['ep_name']|escape}</td>
						<td>{$v['ep_contact']|escape}</td>
						<td>{$v['ep_mobilephone']|escape}</td>
						<td>{$v['ep_email']|escape}</td>
						<td>{if $v['ep_wxcorpid'] == ''}未绑定{else}已绑定{/if}</td>
						<td>
							{if $v['pay_type'] == 1}
								{foreach $v['pay_list'] as $key => $val}
									<b style="  color: #ED2C2C; font-size: 15px;">
										{$val['cpg_name']}
									</b>
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
									<br>
								{/foreach}
								{*如果是定制产品 或者 私有部署, 就是已付费*}
							{elseif $_ca['pay_type'] == 2 || $_ca['pay_type'] == 3}
								<b style="  color: #ED2C2C; font-size: 15px;">
									{$val['cpg_name']}
								</b> 已付费
							{/if}
						</td>
						<td>{$v['ep_money']}</td>
						<td>{$v['ep_created']}</td>
					</tr>
					{foreachelse}
					<tr>
						<td colspan="8" class="warning">暂无任何{$module_plugin['cp_name']|escape}数据</td>
					</tr>
				{/foreach}
				</tbody>
				{if $client_total > 0}
					<tfoot>
					<tr>
						<td colspan="8" class="text-right vcy-page">{$client_multi}</td>
					</tr>
					</tfoot>
				{/if}
			</table>
		</div>
	</div>
	<input type="hidden" value="{$acid}" id="acid"/>
</div>

<script>
	$(function () {
		// 默认tab
		var tabs = '{$act}';
		if (tabs == 'proxy') {
			$('.nav-tabs a[href="#proxy_settings"]').tab('show');
		}
		if (tabs == 'acting') {
			$('.nav-tabs a[href="#acting_client"]').tab('show');
		}

		var domain_url = '{$url}';
		var basic_param = '/enterprise/account/view/?acid=';
		var acid = '{$acid}';

		$('#proxy_settings_tab').on('click', function () {
			location.href = domain_url + basic_param + acid + '&act=proxy';
		});

		$('#agant_submit').on('click', function () {
			var btn = $(this);
			btn.attr('disabled', 'disabled');
			btn.text('提交中...');
			var pay_status = $('input:radio[name="pay_status"]:checked').val();
			var deadline = $('#deadline').find('option:selected').text();
			var pay_time = $.trim($('#pay_time_data').val() + ' ' + $('#pay_time_hour').val());
			var ca_id = $('#ca_id').find('option:selected').val();
			var salesremark = $('#salesremark').val();
			var acid = $('#acid').val();
			var url = "{$url}";
			var en_key = "{$en_key}";

			if (ca_id == '请选择') {
				alert('请选择跟进销售');
				btn.attr('disabled', false);
				btn.text('保 存');
				return false;
			}
			// 付费状态为已付费时 才会判断付费时间是否为空
			if (pay_status == 2) {
				if (pay_time == '') {
					alert('付费时间不能为空');
					btn.attr('disabled', false);
					btn.text('保 存');
					return false;
				}
				if ($('#pay_time_data').val() == '') {
					alert('付费日期不能为空');
					btn.attr('disabled', false);
					btn.text('保 存');
					return false;
				}
				if ($('#pay_time_hour').val() == '') {
					alert('付费时间不能为空');
					btn.attr('disabled', false);
					btn.text('保 存');
					return false;
				}
			}

			{literal}
			$.ajax({
				type: "POST",
				url: url + "/cyadmin/api/account/agant",
				dataType: "json",
				data: {
					pay_status: pay_status,
					deadline: deadline,
					pay_time: pay_time,
					ca_id: ca_id,
					salesremark: salesremark,
					acid: acid,
					en_key: en_key
				},
				success: function (data) {
					if (data.errcode == 0) {
						alert(data.result);
						btn.text('成 功');
						location.reload();
					} else {
						if (data.errcode == '30005' && data.errmsg == '丢失重要参数') {
							alert(data.errmsg);
							location.reload();
						} else {
							alert(data.errmsg);
							btn.attr('disabled', false);
							btn.text('保 存');
						}
					}
				},
				error: function () {
					alert('网络发生错误');
					btn.attr('disabled', false);
					btn.text('保 存');
				}
			});
			{/literal}
			return false;
		});
	});
</script>

{include file='cyadmin/footer.tpl'}