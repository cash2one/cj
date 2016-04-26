{include file='cyadmin/header.tpl'}

<style>
	.text-style {
		text-align: center;
		border: 0;
	}

	.pagination {
		margin: 0px;
	}

	.tr-style th {
		border: 0px;
		text-align: center;
		line-height: 30px;
	}

	.over {
		overflow: auto;
		white-space: nowrap;

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

	#table_new_company tr th {
		border-right: 1px solid #ddd;
	}
	#table_new_company tr td {
		border-right: 1px solid #ddd;
	}

	#table_new_pay tr th {
		border-right: 1px solid #ddd;
	}
	#table_new_pay tr td {
		border-right: 1px solid #ddd;
	}
	.head-number {
		font-size: 30px;
		font-family: "Helvetica Neue Light", "HelveticaNeue-Light", "Helvetica Neue", Calibri, Helvetica, Arial, sans-serif;
	}

	.width-100 {
		width: 100%;
	}
</style>

<script type="text/javascript" src="{$static_url}js/Chart.min.js"></script>

<div>

	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">
		<li id="tab_new_company" role="presentation" {if $act=='new_company'}class='active'{/if}><a href="#new_company"
		                                                                                            aria-controls="new_company"
		                                                                                            role="tab"
		                                                                                            data-toggle="tab">新增企业</a>
		</li>
		<li id="tab_new_pay" role="presentation" {if $act=='new_pay'}class='active'{/if}><a href="#new_pay"
		                                                                                    aria-controls="new_pay"
		                                                                                    role="tab"
		                                                                                    data-toggle="tab">新增付费企业</a>
		</li>
		<a href="javascript:history.go(-1);" class="btn btn-default" style="float:right;margin-right:20px;">返回</a>
	</ul>


	<!-- Tab panes -->
	<div class="tab-content">

		{*新增企业 开始*}
		<div role="tabpanel" class="tab-pane {if $act=='new_company'}active{/if}" id="new_company">
			<div class="panel-body">

				<div class="panel panel-default font12">

					<div class="panel-heading">

						<label class="control-label col-sm-1">时间范围</label>

						<div class="col-sm-2">
							<select id="new_company_range" class="form-control form-small"
							        data-width="auto" style="height: 34px; border-radius: 4px;">
								<option value="7">最近7天</option>
								<option value="30">最近30天</option>
								<option value="-1" selected>自定义时间</option>
							</select>
						</div>

						<div class="col-md-4" id="new_company_custom_time" hidden>
							<div class="input-daterange input-group">
								<input type="text"
								       class="input-sm form-control"
								       value="{$s_time}"
								       id="s_time">

								<span class="input-group-addon">to</span>

								<input type="text"
								       class="input-sm form-control"
								       value="{$e_time}"
								       id="e_time">
							</div>
						</div>

						<div id="form_submit" class="btn btn-primary">
							确 定
						</div>
						<script>
							$(function () {
								if ($('#new_company_range').val() == -1) {
									$('#new_company_custom_time').show('fast');
								}
								$('#new_company_range').change(function () {
									if ($('#new_company_range').val() == -1) {
										$('#new_company_custom_time').show('fast');
									} else {
										$('#new_company_custom_time').hide('fast');
									}
								});
								$('#new_company_custom_time .input-daterange').datepicker({
									todayHighlight: true
								});
							});
						</script>

						<button class="btn btn-warning" id="dump_company">导出</button>
					</div>
					<div style="overflow: auto;border:0">
						<table class="panel-body table table-striped table-hover font12 over"
						       id="table_new_company">

							<thead>

							<tr class="tr-style">
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

							</tr>
							</thead>

							<tfoot id="page_new_company">

							</tfoot>

							<tbody id="tbdoy_id">

							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		{*新增企业 结束*}




		{*新增付费企业 开始*}
		<div role="tabpanel" class="tab-pane {if $act=='new_pay'}active{/if}" id="new_pay">
			<div class="panel-body">
				<div class="panel panel-default font12">
					<div class="panel-heading">

						<label class="control-label col-sm-1">时间范围</label>

						<div class="col-sm-2">
							<select id="new_pay_range" class="form-control form-small"
							        data-width="auto" style="height: 34px; border-radius: 4px;">
								<option value="7">最近7天</option>
								<option value="30">最近30天</option>
								<option value="-1" selected>自定义时间</option>
							</select>
						</div>

						<div class="col-md-4" id="new_pay_custom_time" hidden>
							<div class="input-daterange input-group">
								<input type="text"
								       class="input-sm form-control"
								       value="{$s_time}"
								       id="pay_s_time">

								<span class="input-group-addon">to</span>

								<input type="text"
								       class="input-sm form-control"
								       value="{$e_time}"
								       id="pay_e_time">
							</div>
						</div>

						<div id="form_submit_pay" class="btn btn-primary">
							确 定
						</div>
						<script>
							$(function () {
								if ($('#new_pay_range').val() == -1) {
									$('#new_pay_custom_time').show('fast');
								}
								$('#new_pay_range').change(function () {
									if ($('#new_pay_range').val() == -1) {
										$('#new_pay_custom_time').show('fast');
									} else {
										$('#new_pay_custom_time').hide('fast');
									}
								});
								$('#new_pay_custom_time .input-daterange').datepicker({
									todayHighlight: true
								});
							});
						</script>

						<button class="btn btn-warning" id="dump_pay">导出</button>
					</div>
					<div style="overflow: auto;border:0">
						<table class="panel-body table table-striped table-hover font12 over" id="table_new_pay">

							<thead>

							<tr class="tr-style">
								<th>付费时间</th>
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
								<th>创建及注册时间</th>
								<th>最后更新时间</th>
							</tr>
							</thead>

							<tfoot id="page_new_pay">

							</tfoot>

							<tbody id="tbdoy_pay">
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		{*新增付费企业 结束*}
	</div>

</div>

<script type="text/template" id="tpl-list">

	<% if (!jQuery.isEmptyObject(list)) { %>
	<% $.each(list,function(n,val){ %>
	<tr>
		<td><%=val['ep_name']%></td>
		<td><%=val['ep_mobilephone']%></td>
		<td><%=val['ep_industry']%></td>
		<td><%=val['_customer_status']%></td>

		<td><%=val['_level']%></td>
		<td><%=val['ep_companysize']%></td>
		<td><%=val['ep_ref']%></td>
		<td><%=val['_ep_wxcorpid']%></td>
		<td><%=val['ca_name']%></td>
		<td><%=val['pay_status']%></td>
		<td><%=val['_created']%></td>
		<td><%=val['_updated']%></td>

	</tr>

	<% }) %>
	<% }else{ %>
	<tr>
		<td colspan="12" class="warning">
			暂无公司信息
		</td>
	</tr>
	<% } %>

</script>

<script type="text/template" id="tpl-list-pay">

	<% if (!jQuery.isEmptyObject(list)) { %>
	<% $.each(list,function(n,val){ %>
	<tr>
		<td><%=val['_time']%></td>
		<td><%=val['ep_name']%></td>
		<td><%=val['ep_mobilephone']%></td>
		<td><%=val['ep_industry']%></td>
		<td><%=val['_customer_status']%></td>
		<td><%=val['_level']%></td>
		<td><%=val['ep_companysize']%></td>
		<td><%=val['ep_ref']%></td>
		<td><%=val['_ep_wxcorpid']%></td>
		<td><%=val['ca_name']%></td>
		<td><%=val['pay_status']%></td>
		<td><%=val['_created']%></td>
		<td><%=val['_updated']%></td>

	</tr>

	<% }) %>
	<% }else{ %>
	<tr>
		<td colspan="13" class="warning">
			暂无公司信息
		</td>
	</tr>
	<% } %>

</script>
<script type="text/template" id="tpl-company-page">

	<% if (!jQuery.isEmptyObject(list)) { %>
	<tr>
		<td colspan="13" class="text-right">
			<%=multi%>
		</td>
	</tr>
	<% } %>

</script>
<script type="text/template" id="tpl-pay-page">

	<% if (!jQuery.isEmptyObject(list)) { %>
	<tr>
		<td colspan="13" class="text-right">
			<%=multi%>
		</td>
	</tr>
	<% } %>

</script>


<script>
	$(function () {
		var type = "{$type}";
		var company_url = '/Stat/Apicp/User/New_company';
		var pay_url = '/Stat/Apicp/User/New_pay';

		if (type == 'adminer') {
			var ca_id = "{$ca_id}";
			var company_url = '/Stat/Apicp/User/Adminer_New_pay';
			var pay_url = '/Stat/Apicp/User/Adminer_New_pay';
			var dump_url = '/Stat/Apicp/User/Dump_adminer_company';
		}
		function _page_new_company() {
			$('#table_new_company .pagination a').on('click', function () {
				var page = 'page';
				var result = $(this).attr('href').match(new RegExp("[\?\&]" + page + "=([^\&]+)", "i"));
				if (result == null || result.length < 1) {
					return '';
				}
				post_new_company(result[1]);
				return false;
			});
		}

		function _page_new_pay() {
			$('#table_new_pay .pagination a').on('click', function () {
				var page = 'page';
				var result = $(this).attr('href').match(new RegExp("[\?\&]" + page + "=([^\&]+)", "i"));
				if (result == null || result.length < 1) {
					return '';
				}
				post_new_pay(result[1]);
				return false;
			});
		}

		$('#form_submit').on('click', function () {
			post_new_company();
			dump_new_company();
		});


		$('#form_submit_pay').on('click', function () {
			post_new_pay();
		});

		if ($('#new_company').attr('class') == 'tab-pane active') {
			post_new_company();
			dump_new_company();
		}
		$('#tab_new_company').on('click', function () {
			post_new_company();
			dump_new_pay();
		});

		if ($('#new_pay').attr('class') == 'tab-pane active') {
			post_new_pay();
			dump_new_pay();
		}
		$('#tab_new_pay').on('click', function () {
			post_new_pay();
			dump_new_pay();
		});
		function dump_new_company() {
			$('#dump_company').on('click', function () {
				var s_time = $('#s_time').val();
				var e_time = $('#e_time').val();
				var range = $('#new_company_range').val();

				if (type == 'adminer') {
					var url = dump_url+'?range=' + range + '&s_time=' + s_time + '&e_time=' + e_time;
				} else {
					var url = '/Stat/Apicp/User/Dump_new_company?range=' + range + '&s_time=' + s_time + '&e_time=' + e_time;
				}
				if (type == 'adminer') {
					url = url + '&ca_id='+ca_id+'&adminer_type=company';
				}
				window.location.href = url;
			});
		}

		function dump_new_pay() {
			$('#dump_pay').on('click', function () {
				var s_time = $('#pay_s_time').val();
				var e_time = $('#pay_e_time').val();
				var range = $('#new_pay_range').val();

				if (type == 'adminer') {
					var url = dump_url+'?range=' + range + '&s_time=' + s_time + '&e_time=' + e_time;
				} else {
					var url = '/Stat/Apicp/User/Dump_new_pay?range=' + range + '&s_time=' + s_time + '&e_time=' + e_time;
				}
				if (type == 'adminer') {
					url = url + '&ca_id='+ca_id+'&adminer_type=pay';
				}
				window.location.href = url;
			});
		}

		function post_new_company(page) {

			var data = {};
			data.s_time = $('#s_time').val();
			data.e_time = $('#e_time').val();
			data.range = $('#new_company_range').val();

			data.page = page;
			if (type == 'adminer') {
				data.ca_id = ca_id;
			}
			$.ajax({
				'url': company_url,
				'type': 'get',
				'dataType': 'json',
				data: data,
				success: function (result) {
					//console.log(result);
					$('#tbdoy_id').html(txTpl('tpl-list', result.result));
					$('#page_new_company').html(txTpl('tpl-company-page', result.result));

					_page_new_company();
				}
			});
		}

		function post_new_pay(page) {

			var data = {};
			data.s_time = $('#pay_s_time').val();
			data.e_time = $('#pay_e_time').val();
			data.range = $('#new_pay_range').val();
			data.page = page
			if (type == 'adminer') {
				data.ca_id = ca_id;
				data.adminer_type = 'pay';
			}
			$.ajax({
				'url': pay_url,
				'type': 'get',
				'dataType': 'json',
				data: data,
				success: function (result) {
					//console.log(result);
					$('#tbdoy_pay').html(txTpl('tpl-list-pay', result.result));
					$('#page_new_pay').html(txTpl('tpl-pay-page', result.result));

					_page_new_pay();
				}
			});
		}


	});
</script>

{include file='cyadmin/footer.tpl'}