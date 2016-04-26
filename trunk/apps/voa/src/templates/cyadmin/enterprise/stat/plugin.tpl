{include file='cyadmin/header.tpl'}

<style>
	.download {
		float: right;
	}
	.pagination {
		margin: 0px;
	}
</style>

<div>

	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">
		<li id="company_nav" role="presentation" class="active"><a href="#company" aria-controls="company" role="tab" data-toggle="tab">新增安装企业</a>
		</li>
		<li id="plugin_nav" role="presentation"><a href="#plugin" aria-controls="plugin" role="tab" data-toggle="tab">新增安装应用</a></li>
		<a href="javascript:history.go(-1);" class="btn btn-default" style="float:right;margin-right:20px;">返回</a>

	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
		{*新增安装企业*}
		<div role="tabpanel" class="tab-pane active" id="company">

			<div class="panel panel-default font12">
				<div class="panel-heading">
					<form class="form-horizontal clearfix" method="get" action="/Stat/Apicp/Plugin/Download_new_install_ep">
						<label class="control-label col-sm-1">选择应用</label>

						<div class="col-sm-2">
							<select id="ep_select" name="identifier" class="form-control form-small"
							        data-width="auto" style="height: 34px; border-radius: 4px;">
							</select>
						</div>

						<label class="control-label col-sm-1">时间范围</label>

						<div class="col-sm-2">
							<select id="ep_days" name="days" class="form-control form-small"
							        data-width="auto" style="height: 34px; border-radius: 4px;">
								<option value="7">最近7天</option>
								<option value="30">最近30天</option>
								<option value="-1" selected>自定义时间</option>
							</select>
						</div>

						<div class="col-md-4" id="ep_date" hidden>
							<div class="input-daterange input-group" id="datepicker">
								<input name="start" id="time_start" type="text"
									   value="{$s_time}"
								       class="input-sm form-control">

								<span class="input-group-addon">to</span>

								<input name="end" id="time_end" type="text"
									   value="{$e_time}"
								       class="input-sm form-control">
							</div>
						</div>

						<div id="ep_submit" class="btn btn-primary">
							确 定
						</div>

						<button class="btn btn btn-warning download">
							导 出
						</button>

						<script>
							$(function () {
								if ($('#ep_days').val() == -1) {
									$('#ep_date').show('fast');
								}
								$('#ep_days').change(function () {
									if ($('#ep_days').val() == -1) {
										$('#ep_date').show('fast');
									} else {
										$('#ep_date').hide('fast');
									}
								});
								$('#ep_date .input-daterange').datepicker({
									todayHighlight: true
								});
							});
						</script>
					</form>
				</div>
				<table class="panel-body table table-striped table-bordered table-hover font12">
					<colgroup>
						<col class="t-col-7"/>
						<col class="t-col-7"/>
						<col class="t-col-7"/>
						<col class="t-col-7"/>
						<col class="t-col-7"/>
						<col class="t-col-7"/>
						<col class="t-col-7"/>
						<col class="t-col-7"/>
						<col class="t-col-7"/>
						<col class="t-col-7"/>
						<col class="t-col-7"/>
						<col class="t-col-7"/>
						<col class="t-col-7"/>
						<col class="t-col-7"/>
					</colgroup>

					<thead>
					<tr class="tr-style">
						<td>应用名</td>
						<td>公司名称</td>
						<td>手机号</td>
						<td>所在行业</td>
						<td>客户状态</td>
						<td>客户等级</td>
						<td>企业规模</td>
						<td>客户来源</td>
						<td>是否绑定</td>
						<td>负责人</td>
						<td>付费状态</td>
						<td>注册及创建时间</td>
						<td>最后更新时间</td>
					</tr>
					</thead>

					<tfoot id="ep_multi">

					</tfoot>

					<tbody id="ep_list">

					</tbody>
				</table>

			</div>

		</div>

		{*新增安装应用*}
		<div role="tabpanel" class="tab-pane" id="plugin">
			<div class="panel panel-default font12">
				<div class="panel-heading">
					<form class="form-horizontal clearfix" method="get" action="/Stat/Apicp/Plugin/Download_new_install">
						<label class="control-label col-sm-1">时间范围</label>

						<div class="col-sm-2">
							<select name="days" id="plugin_days" class="form-control form-small"
							        data-width="auto" style="height: 34px; border-radius: 4px;">
								<option value="7">最近7天</option>
								<option value="30">最近30天</option>
								<option value="-1" selected>自定义时间</option>
							</select>
						</div>

						<div class="col-md-4" id="plugin_date" hidden>
							<div class="input-daterange input-group" id="datepicker">
								<input name="start" type="text"
								       class="input-sm form-control"
									   value="{$s_time}"
								       id="plugin_time_start">

								<span class="input-group-addon">to</span>

								<input name="end" type="text"
								       class="input-sm form-control"
									   value="{$e_time}"
								       id="plugin_time_end">
							</div>
						</div>

						<div id="plugin_submit" class="btn btn-primary">
							确 定
						</div>

						<button class="btn btn btn-warning download">
							导 出
						</button>

						<script>
							$(function () {
								if ($('#plugin_days').val() == -1) {
									$('#plugin_date').show('fast');
								}
								$('#plugin_days').change(function () {
									if ($('#plugin_days').val() == -1) {
										$('#plugin_date').show('fast');
									} else {
										$('#plugin_date').hide('fast');
									}
								});
								$('#plugin_date .input-daterange').datepicker({
									todayHighlight: true
								});
							});
						</script>
					</form>
				</div>
				<table class="panel-body table table-striped table-bordered table-hover font12">
					<colgroup>
						<col class="t-col-6"/>
						<col class="t-col-6"/>
						<col class="t-col-6"/>
						<col class="t-col-6"/>
						<col class="t-col-6"/>
						<col class="t-col-6"/>
						<col class="t-col-6"/>
						<col class="t-col-6"/>
						<col class="t-col-6"/>
						<col class="t-col-6"/>
						<col class="t-col-6"/>
						<col class="t-col-6"/>
						<col class="t-col-6"/>
						<col class="t-col-6"/>
					</colgroup>

					<thead>
					<tr class="tr-style">
						<td>应用名称</td>
						<td>安装时间</td>
						<td>公司名称</td>
						<td>手机号</td>
						<td>所在行业</td>
						<td>客户状态</td>
						<td>客户等级</td>
						<td>企业规模</td>
						<td>客户来源</td>
						<td>是否绑定</td>
						<td>负责人</td>
						<td>付费状态</td>
						<td>注册及创建时间</td>
						<td>最后更新时间</td>
					</tr>
					</thead>

					<tfoot id="plugin_multi">

					</tfoot>

					<tbody id="plugin_list">

					</tbody>
				</table>

			</div>
		</div>
	</div>


	<script type="text/template" id="tpl_ep_list">

		<% if (!jQuery.isEmptyObject(list)) { %>

		<% $.each(list,function(k,val){ %>
		<tr>
			<td><%=val['pg_name']%></td>
			<td><%=val['ep_name']%></td>
			<td><%=val['ep_mobilephone']%></td>
			<td><%=val['ep_industry']%></td>
			<td><%=val['customer_status']%></td>
			<td><%=val['ep_customer_level']%></td>
			<td><%=val['ep_companysize']%></td>
			<td><%=val['ep_ref']%></td>
			<td><%=val['bangding']%></td>
			<td><%=val['ca_name']%></td>
			<td><%=val['pay_status']%></td>
			<td><%=val['ep_created']%></td>
			<td><%=val['ep_updated']%></td>
		</tr>
		<% }) %>
		<% }else{ %>
		<tr>
			<td colspan="13" class="warning">
				暂无信息
			</td>
		</tr>
		<% } %>
	</script>
	<script type="text/template" id="tpl_ep_multi">

		<tr>
			<td colspan="13" class="text-right">
				<% if(multi != null){ %>
				<%=multi%>
				<% } %>
			</td>
		</tr>

	</script>
	<script type="text/template" id="tpl_ep_select">

		<option value="">请选择</option>

		<% $.each(select,function(k,val){ %>

		<option value="<%=k%>"
		<% if(k == "{$select_identifier}") { %>
		selected
		<% } %>
		><%=val%></option>
		<% }) %>

	</script>

	<script type="text/template" id="tpl_plugin_list">

		<% if (!jQuery.isEmptyObject(list)) { %>

		<% $.each(list,function(k,val){ %>
		<tr>
			<td><%=val['pg_name']%></td>
			<td><%=val['time']%></td>
			<td><%=val['ep_name']%></td>
			<td><%=val['ep_mobilephone']%></td>
			<td><%=val['ep_industry']%></td>
			<td><%=val['customer_status']%></td>
			<td><%=val['ep_customer_level']%></td>
			<td><%=val['ep_companysize']%></td>
			<td><%=val['ep_ref']%></td>
			<td><%=val['bangding']%></td>
			<td><%=val['ca_name']%></td>
			<td><%=val['pay_status']%></td>
			<td><%=val['ep_created']%></td>
			<td><%=val['ep_updated']%></td>
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
	<script type="text/template" id="tpl_plugin_multi">

		<tr>
			<td colspan="15" class="text-right">
				<% if(multi != null){ %>
				<%=multi%>
				<% } %>
			</td>
		</tr>

	</script>

	<script>

		// 是否点击过应用nav
		var had_click_plugin_nav = false;

		$('#plugin_nav').on('click', function () {
			if (!had_click_plugin_nav) {

				// 获取新增安装应用的企业数据
				list_new_install_plugin_list();

				$('#plugin_submit').on('click', function () {
					//安装企业
					list_new_install_plugin_list();
				});

	lick_plugin_nav = true;
			}
		});

		// 获取新增安装应用企业的数据
		$('#ep_submit').on('click', function () {
			list_new_install_ep_list(true);
		});

		$.ajax({
			'url': '/Stat/Apicp/Plugin/New_install_ep_list',
			'type': 'get',
			'dataType': 'json',
			data: '',
			success: function (result) {
				$('#ep_select').html(txTpl('tpl_ep_select', result.result));
				list_new_install_ep_list();
			}
		});
		function list_new_install_ep_list(except_select) {

			var except = except_select ? except_select : false;

			var select = $('#ep_select').val();
			var days = $('#ep_days').val();
			var time_start =$('#time_start').val();
			var time_end =$('#time_end').val();

			var url = '/Stat/Apicp/Plugin/New_install_ep_list';
			var id = ['ep_multi', 'ep_list', 'ep_select'];
			var data = {
				days: days,
				start: time_start,
				end: time_end,
				identifier: select,
				page: 1
			};

			_list(url, id, data, except);
		}

		function list_new_install_plugin_list() {
			var days = $('#plugin_days').val();
			var time_start =$('#plugin_time_start').val();
			var time_end =$('#plugin_time_end').val();

			var url = '/Stat/Apicp/Plugin/New_install_plugin';
			var id = ['plugin_multi', 'plugin_list'];
			var data = {
				days: days,
				start: time_start,
				end: time_end,
				page: 1
			};

			_list(url, id, data);
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
					for (var i = 0; i < id.length; i ++) {
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
			$('#' + id[0] + ' a').on('click', function(){
				var page = 'page';
				var result =$(this).attr('href').match(new RegExp("[\?\&]" + page+ "=([^\&]+)", "i"));
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

	</script>

</div>

{include file='cyadmin/footer.tpl'}