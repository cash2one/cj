{include file='cyadmin/header.tpl'}

<style>
	.tr-style th {
		border: 0px;
		text-align: center;
		line-height: 30px;
	}

	.head-center {
		text-align: center;
		border: 0;
	}

	.pagination {
		margin: 0px;
	}

	.text-percent-up {
		color: green;
	}

	.text-percent-down {
		color: red;
	}

	.head-number {
		font-size: 30px;
		font-family: "Helvetica Neue Light", "HelveticaNeue-Light", "Helvetica Neue", Calibri, Helvetica, Arial, sans-serif;
	}

	#table_follow tr td {
		border-right: 1px solid #ddd;
	}

	#table_follow tr th {
		border-right: 1px solid #ddd;
	}

	.download {
		float: right;
	}

	.over {
		overflow: auto;
		white-space: nowrap;
	}
</style>

<script type="text/javascript" src="{$static_url}js/echarts.common.min.js"></script>

<div>

	<!-- Nav -->
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="dropdown active">
			<a href="#" id="myTabDrop1" class="dropdown-toggle" data-toggle="dropdown"
			   aria-controls="myTabDrop1-contents" aria-expanded="false">用户数据
				<span class="caret"></span>
			</a>
			<ul class="dropdown-menu" aria-labelledby="myTabDrop1" id="myTabDrop1-contents">
				<li class="active" id="user_data_btn">
					<a href="#user_data" aria-controls="user_data" role="tab" data-toggle="tab">用户数据</a>
				</li>
				<li class="" id="follow_up_btn">
					<a href="#follow_up" role="tab" data-toggle="tab" aria-controls="follow_up">
						跟进人数据
					</a></li>
			</ul>
		</li>
		<li role="presentation"><a id="plugin_data_nav" href="#plugin_data" aria-controls="plugin_data" role="tab"
		                           data-toggle="tab">应用数据</a>
		</li>
	</ul>

	<!-- Tabs -->
	<div class="tab-content">

		{*用户数据 开始*}
		<div role="tabpanel" class="tab-pane active" id="user_data">
			<div class="panel panel-default font12">
				<div class="panel-heading"><strong>昨天数据</strong></div>
				<div class="panel-body">
					<div class="form-group">
						<div class="form-horizontal" role="form" method="post" action="{$form_del_url}">
							<table id="table_mul">
								<colgroup>
									<col class="t-col-5"/>
									<col class="t-col-10"/>
									<col class="t-col-10"/>
									<col class="t-col-9"/>
									<col class="t-col-9"/>
									<col class="t-col-9"/>
									<col class="t-col-9"/>
								</colgroup>
								<thead>
								<tr class="tr-style" id="header_data">

								</tr>
								</thead>

							</table>
						</div>
					</div>
				</div>
			</div>

			<div class="panel panel-default font12">
				<div class="panel-heading">

					<div class="panel-body">
						<div class="form-horizontal">
							<label class="control-label col-sm-1">关键指标</label>

							<div class="col-sm-2">
								<select name="ep_industry" class="form-control form-small"
								        data-width="auto" style="height: 34px; border-radius: 4px;" id="act">
									<option value="company_count">新增企业</option>
									<option value="add_member">新增员工</option>
									<option value="active_company">活跃企业</option>
									<option value="pay_percent">付费转化率</option>
									<option value="lose_percent">用户流失率</option>
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
									       id="s_time">

									<span class="input-group-addon">to</span>

									<input type="text"
									       class="input-sm form-control"
									       value=""
									       id="e_time">
								</div>
							</div>

							<div id="user_data_submit" class="btn btn-primary">
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
						</div>
					</div>

				</div>
				<div class="panel-body">
					<div class="form-inline vcy-from-search" id="list_form" role="form" action="{$formActionUrl}">
						<div class="form-row">
							<div class="form-group">

								<div class="modal-body">
									<div class="form-horizontal" role="form" method="post" action="{$form_del_url}">
										{*指标图*}
										<div style="overflow: auto;">
											<div id="user_chart" style="width: 1000px;height:400px;margin: 30px;"></div>
										</div>

									</div>
								</div>

							</div>
						</div>
					</div>
				</div>
			</div>

			{*详情列表部分*}
			<div class="panel panel-default font12">
				<div class="panel-heading">
					<div style="width:100%;height:35px;margin-top:10px;">
						<div style="width:80px;height:30px;float:left;font:20px 微软雅黑;line-height:35px;">数据详情</div>
						<div data-toggle="modal" data-target="#add_agant" id="company_list" class="btn-warning"
						     style="cursor: pointer; padding:5px;width:65px;border-radius:5px;margin-right:10px;float:right;font:16px 微软雅黑;line-height:26px;text-align:center;">
							导出
						</div>
					</div>
				</div>


				<table class="panel-body table table-striped table-bordered table-hover font12" id="table_view_list">

					<thead>

					<tr class="tr-style">
						<th>时间</th>
						<th>新增企业数</th>
						<th>新增员工数</th>
						<th>新增活跃员工数</th>
						<th>活跃企业数</th>
						<th>活跃员工数</th>
						<th>企业流失数</th>
						<th>企业流失率</th>
						<th>激活企业数</th>
						<th>激活率</th>
						<th>新增付费企业数</th>
						<th>付费转化率</th>
						<th>总员工数</th>
						<th>总企业数</th>
					</tr>
					</thead>

					<tfoot id="tbody-total">

					</tfoot>

					<tbody id="tbody_view_list">

					</tbody>
				</table>

			</div>

		</div>

		{*跟进人数据部分*}
		<div role="tabpanel" class="tab-pane" id="follow_up">

			<div class="panel panel-default font12">
				<div class="panel-heading">

					<div class="panel-body">
						<div class="form-horizontal">
							<div class="col-sm-2">
								<select id="follow_adminer" class="form-control form-small"
								        data-width="auto" style="height: 34px; border-radius: 4px;">
								</select>
							</div>

							<label class="control-label col-sm-1">时间范围</label>

							<div class="col-sm-2">
								<select id="follow_time" class="form-control form-small"
								        data-width="auto" style="height: 34px; border-radius: 4px;">
									<option value="7">最近7天</option>
									<option value="30">最近30天</option>
									<option value="-1">自定义时间</option>
								</select>
							</div>

							<div class="col-md-4" id="follow_custom_time" hidden>
								<div class="input-daterange input-group">
									<input type="text"
									       class="input-sm form-control"
									       value=""
									       id="follow_s_time">

									<span class="input-group-addon">to</span>

									<input type="text"
									       class="input-sm form-control"
									       value=""
									       id="follow_e_time">
								</div>
							</div>

							<div id="follow_submit" class="btn btn-primary">
								确 定
							</div>
							<script>
								$(function () {
									$('#follow_time').change(function () {
										if ($('#follow_time').val() == -1) {
											$('#follow_custom_time').show('fast');
										} else {
											$('#follow_custom_time').hide('fast');
										}
									});
									$('#follow_custom_time .input-daterange').datepicker({
										todayHighlight: true
									});
								});
							</script>
						</div>
					</div>
				</div>
			</div>

			<div class="panel panel-default font12" id="follow_up_data">
				<div class="panel-heading">
					<div style="width:100%;height:35px;margin-top:10px;">
						<div style="width:80px;height:30px;float:left;font:20px 微软雅黑;line-height:35px;">数据详情</div>
						<div data-toggle="modal" data-target="#add_agant" id="dump_follow" class="btn-warning"
						     style="cursor: pointer; padding:5px;width:65px;border-radius:5px;margin-right:10px;float:right;font:16px 微软雅黑;line-height:26px;text-align:center;">
							导出
						</div>
					</div>
				</div>
				<div style="overflow: auto;border:0">
					<table class="panel-body table table-striped table-hover font12 over" id="table_follow">

						<thead>

						<tr class="tr-style">
							<th>时间</th>
							<th>负责人</th>
							<th>新增企业数</th>
							<th>新增员工数</th>
							<th>新增活跃员工数</th>
							<th>活跃企业数</th>
							<th>活跃员工数</th>
							<th>企业流失数</th>
							<th>企业流失率</th>
							<th>激活企业数</th>
							<th>激活率</th>
							<th>新增付费企业数</th>
							<th>付费转换率</th>
							<th>总员工数</th>
							<th>总企业数</th>
						</tr>
						</thead>

						<tfoot id="tbody-page">

						</tfoot>

						<tbody id="tbdoy_follow_up">

						</tbody>
					</table>
				</div>
			</div>
		</div>
		{*用户数据 结束*}

		{*应用数据 开始*}
		<div role="tabpanel" class="tab-pane" id="plugin_data">

			{*头*}
			<div class="panel panel-default font12">
				<div class="panel-heading">昨天数据</div>
				<table class="panel-body table table-striped table-hover font12">
					<colgroup>
						<col class="t-col-10"/>
						<col class="t-col-10"/>
						<col class="t-col-10"/>
						<col class="t-col-10"/>
						<col class="t-col-10"/>
						<col class="t-col-10"/>
					</colgroup>

					<thead>
					<tr class="head-center" id="plugin_header">

					</tr>
					</thead>
				</table>
			</div>

			{*指标*}
			<div class="panel panel-default">
				<div class="panel-heading">

					<div class="panel-body">
						<form class="form-horizontal">
							<label class="control-label col-sm-1">关键指标</label>

							<div class="col-sm-2">
								<select id="plugin_chart_select" class="form-control form-small"
								        data-width="auto" style="height: 34px; border-radius: 4px;">
									<option value="1">应用主数据</option>
									<option value="2">应用总数据</option>
									<option value="3">活跃应用数</option>
									<option value="4">活跃企业数</option>
									<option value="5">活跃员工数</option>
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
					// 基于准备好的dom，初始化echarts实例
					var plugin_chart = echarts.init(document.getElementById('plugin_chart'));

					initial_plugin_chart();

					// 初始化图表
					function initial_plugin_chart() {

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
							]
						};

						// 使用刚指定的配置项和数据显示图表。
						plugin_chart.setOption(option);
					}
				</script>
			</div>

			{*详情*}
			<div class="panel panel-default font12">
				<div class="panel-heading">
					<form class="form-horizontal clearfix">
						<label class="control-label col-sm-1">数据详情</label>

						<div id="download_detail" class="btn btn btn-warning download">
							导 出
						</div>

						<script>
							$(function () {
								$('#download_detail').on('click', function () {
									var days = $('#plugin_chart_time').val();
									var start = $('#plugin_chart_date_start').val();
									var end = $('#plugin_chart_date_end').val();
									var url = '/Stat/Apicp/Plugin/Download_detail?days=' + days + '&start=' + start + '&end=' + end;
									window.location.href = url;
								});
							});
						</script>
					</form>
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
					</colgroup>

					<thead>
					<tr class="tr-style">
						<td>日期</td>
						<td>应用主数据</td>
						<td>应用总数据</td>
						<td>活跃应用数</td>
						<td>活跃企业数</td>
						<td>活跃员工数</td>
						<td>新增应用安装数</td>
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
						<form class="form-horizontal clearfix" method="get" action="/Stat/Apicp/Plugin/Download_plugin">
							<label class="control-label col-sm-1">选择应用</label>

							<div class="col-sm-2">
								<select id="plugin_list_select" name="identifier" class="form-control form-small"
								        data-width="auto" style="height: 34px; border-radius: 4px;">

								</select>
							</div>

							<label class="control-label col-sm-1">时间范围</label>

							<div class="col-sm-2">
								<select id="plugin_list_time" name="days" class="form-control form-small"
								        data-width="auto" style="height: 34px; border-radius: 4px;">
									<option value="7">最近7天</option>
									<option value="30">最近30天</option>
									<option value="-1">自定义时间</option>
								</select>
							</div>

							<div class="col-md-4" id="plugin_list_time_custom_time" hidden>
								<div class="input-daterange input-group">
									<input type="text"
									       name="start"
									       class="input-sm form-control"
									       value=""
									       id="plugin_list_date_start">

									<span class="input-group-addon">to</span>

									<input type="text"
									       name="end"
									       class="input-sm form-control"
									       value=""
									       id="plugin_list_date_end">
								</div>
							</div>

							<div id="plugin_list_submit" class="btn btn-primary">
								确 定
							</div>

							<button class="btn btn btn-warning download">
								导 出
							</button>

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
						<col class="t-col-10"/>
						<col class="t-col-10"/>
					</colgroup>

					<thead>
					<tr class="tr-style">
						<td>应用</td>
						<td>时间</td>
						<td>安装企业总数</td>
						<td>应用活跃人数</td>
						<td>应用活跃度</td>
						<td>应用主数据</td>
						<td>应用总数据</td>
						<td>人均贡献值</td>
						<td>新增安装企业数</td>
						<td>新增活跃员工数</td>
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

<script type="text/template" id="tpl-user-list">

	<% if (!jQuery.isEmptyObject(view_list)) { %>
	<% $.each(view_list,function(n,val){ %>
	<tr>
		<td><%=val['_time']%></td>
		<td><a href="/enterprise/stat/company?act=new_company&s_time=<%=val['_time']%>&e_time=<%=val['_time']%>"><%=val['company_count']%></a>
		</td>
		<td><%=val['add_member']%></td>
		<td>0</td>
		<td><%=val['active_company']%></td>
		<td>0</td>
		<td><%=val['lose_number']%></td>
		<td><%=val['lose_percent']%>%</td>
		<td><%=val['activation_count']%></td>
		<td><%=val['activation_percent']%>%</td>
		<td><a href="/enterprise/stat/company?act=new_pay&s_time=<%=val['_time']%>&e_time=<%=val['_time']%>"><%=val['pay_count']%></a>
		</td>
		<td><%=val['pay_percent']%>%</td>
		<td><%=val['count_member']%></td>
		<td><%=val['all_company']%></td>
	</tr>
	<% }) %>
	<% }else{ %>
	<tr>
		<td colspan="14" class="warning">
			暂无信息
		</td>
	</tr>
	<% } %>

</script>

<script type="text/template" id="tpl-user-total">
	<tr>
		<td colspan="14" class="text-right"><%=multi%></td>
	</tr>
</script>
<script type="text/template" id="tpl-follow-page">

	<% if (!jQuery.isEmptyObject(list)) { %>
	<tr>
		<td colspan="15" class="text-right">
			<%=multi%>
		</td>
	</tr>
	<% } %>

</script>

<script type="text/template" id="tpl-follow-adminer">

	<option value="">请选择跟进人...</option>
	<% $.each(adminer,function(n,val){ %>
	<option value="<%=val['ca_id']%>">
		<%=val['ca_realname']%>
	</option>
	<% }) %>


</script>
<script type="text/template" id="tpl-user-header">
	<% if (!jQuery.isEmptyObject(header)) { %>
	<% $.each(header,function(n,val){ %>
	<th><%=val['field_name']%><br>
		<span class="head-number"><%=val['number']%></span><br>
		<% if(val['percent'] != 'delete'){ %>
		<% if(val['percent'] > 0) { %>
		<span class="text-percent-up">+<%=val['percent']%>%</span>
		<span class="glyphicon glyphicon-chevron-up"></span>

		<% } else { %>
		<span class="text-percent-down"><%=val['percent']%>%</span>
		<span class="glyphicon glyphicon-chevron-down"></span>
		<% } %>
		<% } else { %>
		<span class="text-percent-up"></span>
		<span class="glyphicon"></span>

		<% } %>
	</th>
	<% }) %>
	<% } %>
</script>

<script type="text/template" id="tpl-follow">

	<% if (!jQuery.isEmptyObject(list)) { %>
	<% $.each(list,function(n,val){ %>
	<tr>
		<td><%=val['date']%></td>
		<td><%=val['username']%></td>

		<td>
			<a href="/enterprise/stat/company?act=new_company&ca_id=<%=val['ca_id']%>&type=adminer&s_time=<%=val['date']%>&e_time=<%=val['date']%>"><%=val['company_count']%></a>
		</td>
		<td><%=val['add_member']%></td>
		<td>0</td>
		<td><%=val['active_company']%></td>
		<td>0</td>
		<td><%=val['lose_number']%></td>
		<td><%=val['lose_percent']%></td>
		<td><%=val['activation_count']%></td>
		<td><%=val['activation_percent']%></td>
		<td>
			<a href="/enterprise/stat/company?act=new_pay&ca_id=<%=val['ca_id']%>&type=adminer&s_time=<%=val['date']%>&e_time=<%=val['date']%>"><%=val['pay_count']%></a>
		</td>
		<td><%=val['pay_percent']%></td>
		<td><%=val['count_member']%></td>
		<td><%=val['all_company']%></td>
	</tr>
	<% }) %>
	<% }else{ %>
	<tr>
		<td colspan="15" class="warning">
			暂无信息
		</td>
	</tr>
	<% } %>

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

	<% $.each(list,function(k,val){ %>
	<tr>
		<td><%=val['date']%></td>
		<td><%=val['count_index']%></td>
		<td><%=val['count_all']%></td>
		<td><%=val['active_plugin']%></td>
		<td><%=val['active_ep']%></td>
		<td><%=val['active_staff']%></td>
		<td><a href="/enterprise/stat/plugin?s_time=<%=val['date']%>&e_time=<%=val['date']%>"><%=val['new_install']%></a></td>
	</tr>
	<% }) %>
	<% }else{ %>
	<tr>
		<td colspan="7" class="warning">
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
		<td><%=val['time']%></td>
		<td><%=val['install_count']%></td>
		<td><%=val['active_staff']%></td>
		<td><%=val['active_degree']%></td>
		<td><%=val['count_index']%></td>
		<td><%=val['count_all']%></td>
		<td><%=val['pre_devote']%></td>
		<td><a href="/enterprise/stat/plugin?s_time=<%=val['time']%>&e_time=<%=val['time']%>&select_identifier=<%=val['pg_identifier']%>"><%=val['new_install']%></a></td>
		<td><%=val['new_active_staff']%></td>
	</tr>
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

	{*<% if (!jQuery.isEmptyObject(list)) { %>*}
	<option value="">请选择</option>
	<% $.each(select,function(k,val){ %>
	<option value="<%=k%>"><%=val%></option>
	<% }) %>

</script>

<script>
	$(function () {

		function _page_view_list() {

			$('#table_view_list .pagination a').on('click', function () {
				var page = 'page';
				var result = $(this).attr('href').match(new RegExp("[\?\&]" + page + "=([^\&]+)", "i"));
				if (result == null || result.length < 1) {
					return '';
				}
				show_user_info(result[1]);
				return false;
			});
		}

		function _page_follow() {
			$('#table_follow .pagination a').on('click', function () {
				var page = 'page';
				var result = $(this).attr('href').match(new RegExp("[\?\&]" + page + "=([^\&]+)", "i"));
				if (result == null || result.length < 1) {
					return '';
				}
				follow_post(result[1]);
				return false;
			});
		}

		$('#user_data_btn').on('click', function () {
			show_user_info();
		});

		$('#follow_up_btn').on('click', function () {
			follow_post_first();
		});

		$('#follow_submit').on('click', function () {
			follow_post();
		});

		function follow_post(page) {
			var follow_data = {};
			follow_data.s_time = $('#follow_s_time').val();
			follow_data.e_time = $('#follow_e_time').val();
			follow_data.range = $('#follow_time').val();
			follow_data.adminer = $('#follow_adminer').val();
			follow_data.page = page;
			$.ajax({
				'url': '/Stat/Apicp/User/Follow',
				'dataType': 'json',
				data: follow_data,
				success: function (result) {
					$('#tbdoy_follow_up').html(txTpl('tpl-follow', result.result));
					$('#tbody-page').html(txTpl('tpl-follow-page', result.result));
					_page_follow();
				}
			});

		}

		//跟进人数据
		function follow_post_first() {
			var follow_data = {};
			follow_data.s_time = $('#follow_s_time').val();
			follow_data.e_time = $('#follow_e_time').val();
			follow_data.range = $('#follow_time').val();
			follow_data.adminer = $('#follow_adminer').val();

			$.ajax({
				'url': '/Stat/Apicp/User/Follow',
				'dataType': 'json',
				data: follow_data,
				success: function (result) {
					$('#follow_adminer').html(txTpl('tpl-follow-adminer', result.result));
					$('#tbdoy_follow_up').html(txTpl('tpl-follow', result.result));
					$('#tbody-page').html(txTpl('tpl-follow-page', result.result));
					_page_follow();
				}
			});
		}

		$('#user_data_submit').on('click', function () {
			show_user_info();

		});
		show_user_info();
		user_header();
		//用户信息趋势图
		function show_user_info(page) {

			//初始化趋势图
			initial_chart();
			var data = {};
			data.act = $('#act').val();
			data.range = $('#user_chart_time').val();
			data.s_time = $("#s_time").val();
			data.e_time = $("#e_time").val();
			data.page = page;

			// 时间范围
			if (data.range == -1) {
				var s_time = $('#s_time').val();
				var e_time = $('#e_time').val();
				if (s_time == '' || e_time == '') {
					alert('自定义时间不能为空');
					return false;
				}
			}
			//头部信息
			$.ajax({
				'url': '/Stat/Apicp/User/User_data',
				'dataType': 'json',
				data: data,
				success: function (result) {
					$('#tbody_view_list').html(txTpl('tpl-user-list', result.result));
					$('#tbody-total').html(txTpl('tpl-user-total', result.result));

					var data = result.result.chart_data;
					user_chart.setOption({
						xAxis: {
							data: data.time_list,
						},
						series: [{
							name: data.chart_name,
							type: 'line',
							data: data.data_list
						}]
					});
					_page_view_list();
				}
			});

		}

		//初始化方法
		function initial_chart() {
			user_chart = echarts.init(document.getElementById('user_chart'));
			user_chart.hideLoading();

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
				]

			};

			// 使用刚指定的配置项和数据显示图表。
			user_chart.setOption(option);
		}

		//用户数据头信息
		function user_header() {

			$.ajax({
				url: '/Stat/Apicp/User/Header',
				dataType: 'json',
				success: function (result) {
					$('#header_data').html(txTpl('tpl-user-header', result.result));
				}
			});

			return true;
		}

		$('#company_list').on('click', function () {
			var d_range = $('#user_chart_time').val();
			var d_s_time = $("#s_time").val();
			var d_e_time = $("#e_time").val();
			var url = '/Stat/Apicp/User/Dump_company_list?range=' + d_range + '&s_time=' + d_s_time + '&e_time=' + d_e_time;
			window.location.href = url;
		});

		$('#dump_follow').on('click', function () {
			var f_time = $('#follow_s_time').val();
			var f_e_time = $('#follow_e_time').val();
			var f_range = $('#follow_time').val();
			var adminer = $('#follow_adminer').val();

			var url = '/Stat/Apicp/User/Dump_follow_list?range=' + f_range + '&s_time=' + f_time + '&e_time=' + f_e_time + '&adminer=' + adminer;
			window.location.href = url;
		});
		/**
		 * 应用纬度JS开始
		 */

		// 是否点击过应用纬度tab
		var had_click_plugin_tab = false;
		// 应用纬度数据初始化
		$('#plugin_data_nav').click(function () {
			if (!had_click_plugin_tab) {
				// 如果没有点击过, 初始化应用纬度数据
				get_plugin_header();
				get_plugin_detail();
				get_plugin_chart_data();
				get_plugin_list();
				get_plugin_list_select();
				// 绑定查询按钮
				$('#plugin_chart_submit').on('click', function () {
					// 获取应用图表数据
					get_plugin_chart_data();
				});
				$('#plugin_list_submit').on('click', function () {
					// 获取应用数据列表
					get_plugin_list(true);
				});

				// 点击过应用纬度tab
				had_click_plugin_tab = true;
			}
		});

		// 应用数据头
		function get_plugin_header() {
			$.ajax({
				url: '/Stat/Apicp/Plugin/PluginHeader',
				dataType: 'json',
				success: function (result) {
					$('#plugin_header').html(txTpl('tpl_plugin_header', result.result));
				}
			});

			return true;
		}

		// 获取图标数据 并 更新
		function get_plugin_chart_data() {

			// 选择的应用
			var type = $('#plugin_chart_select').val();
			// 选择的时间
			var days = $('#plugin_chart_time').val();
			// 时间范围
			if (days == -1) {
				var start_time = $('#plugin_chart_date_start').val();
				var end_time = $('#plugin_chart_date_end').val();
				if (start_time == '' || end_time == '') {
					alert('自定义时间不能为空');

					return false;
				}
			}

			plugin_chart.showLoading();

			// 更新详情列表
			get_plugin_detail();

			initial_plugin_chart();

			$.ajax({
				url: '/Stat/Apicp/Plugin/PluginChart',
				dataType: 'json',
				data: {
					type: type,
					days: days,
					start: start_time,
					end: end_time
				},
				success: function (result) {
					plugin_chart.hideLoading();

					var data = result.result;

					plugin_chart.setOption({
						xAxis: {
							data: data.days
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

		// 初始化 应用详细数据列表
		function get_plugin_detail() {

			var url = '/Stat/Apicp/Plugin/PluginDetail';
			var id = ['plugin_detail_multi', 'plugin_detail'];
			var start_time = $('#plugin_chart_date_start').val();
			var end_time = $('#plugin_chart_date_end').val();
			var days = $('#plugin_chart_time').val();
			var data = {
				days: days,
				start: start_time,
				end: end_time
			};
			// 获取列表数据
			_list(url, id, data);

			return true;
		}

		// 获取应用列表数据
		function get_plugin_list(except_select) {

			// 选择的应用
			var identifier = $('#plugin_list_select').val();
			// 选择的时间
			var days = $('#plugin_list_time').val();
			// 时间范围
			if (days == -1) {
				var start_time = $('#plugin_list_date_start').val();
				var end_time = $('#plugin_list_date_end').val();
				if (start_time == '' || end_time == '') {
					alert('自定义时间不能为空');

					return false;
				}
			}

			var url = '/Stat/Apicp/Plugin/PluginList';
			var id = [
				'plugin_list_multi',
				'plugin_list',
			];
			var data = {
				identifier: identifier,
				days: days,
				start: start_time,
				end: end_time
			};

			var except = except_select ? except_select : false;

			_list(url, id, data, except);
		}

		// 获取应用列表应用列表
		function get_plugin_list_select(except_select) {

			// 选择的应用
			var identifier = $('#plugin_list_select').val();
			// 选择的时间
			var days = $('#plugin_list_time').val();
			// 时间范围
			if (days == -1) {
				var start_time = $('#plugin_list_date_start').val();
				var end_time = $('#plugin_list_date_end').val();
				if (start_time == '' || end_time == '') {
					alert('自定义时间不能为空');

					return false;
				}
			}

			var url = '/Stat/Apicp/Plugin/PluginList';
			var id = [
				'plugin_list_select'
			];
			var data = {
				identifier: identifier,
				days: days,
				start: start_time,
				end: end_time
			};

			var except = except_select ? except_select : false;

			_list(url, id, data, except);
		}

		/**
		 * 获取列表数据 和 分页数据
		 * @param url
		 * @param id ['分页模板外面的DIV ID', '']
		 * @param data {} 要给接口的参数数据
		 * @private
		 */
		function _list(url, id, data, except_select) {

			var except = except_select ? except_select : false;

			$.ajax({
				url: url,
				dataType: 'json',
				data: data,
				success: function (result) {
					for (var i = 0; i < id.length; i++) {
						// 是否需要跳过select的
						if (except) {
							if (id[i].substr(-7) == '_select') {
								continue;
							}
						}
						$('#' + id[i]).html(txTpl('tpl_' + id[i], result.result));
					}
					// 绑定分页参数点击事件
					_multi(url, id, data);
				}
			});
		}

		// 绑定点击事件
		function _multi(url, id, data) {
			$('#' + id[0] + ' a').on('click', function () {
				var page = 'page';
				var result = $(this).attr('href').match(new RegExp("[\?\&]" + page + "=([^\&]+)", "i"));
				if (result == null || result.length < 1) {
					return false;
				}
				data.page = result[1]; // 页数
				// 获取列表数据
				_list(url, id, data);
				return false;
			});

			return true;
		}
	});
</script>

{include file='cyadmin/footer.tpl'}