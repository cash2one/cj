{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索{$module_plugin['cp_name']|escape}</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" id="list_form" method="post" role="form" action="{$formActionUrl}">
			<div class="form-row">
				<div class="form-group" id="body_form">

					<label class="vcy-label-none" for="id_aft_type">流程类型：</label>
					<select id="id_aft_type" name="id_aft_type" class="form-control form-small" data-width="auto">
						<option value="-1">不限</option>
						<option value="0">自由流程</option>
						<option value="1">固定流程</option>
					</select>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_aft_id">流程名称：</label>
					<select id="id_aft_id" name="id_aft_id" class="form-control form-small" data-width="auto">

					</select>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_cd_id">所在部门：</label>
					<select id="id_cd_id" name="id_cd_id" style="width:130px;" class="form-control form-small" data-width="auto">


					</select>
					<span class="space"></span>

					<label class="vcy-label-none" for="id_af_status">审批状态：</label>
					<select id="id_af_status" name="id_af_status" class="form-control form-small" data-width="auto">

					</select>
					<span class="space"></span>

				</div>
			</div>
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_m_username">申请人：</label>
					<input type="text" class="form-control form-small" id="s_m_username" name="s_m_username"
					       placeholder="申请人用户名" maxlength="54"/>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_af_subject">主题：</label>
					<input type="text" class="form-control form-small" id="id_af_subject" name="id_af_subjectq"
					       placeholder="申请主题" maxlength="255"/>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_ac_time_after">发起时间范围：</label>
					<script>
						init.push(function () {
							var options2 = {
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range').datepicker(options2);
						});
					</script>
					<div class="input-daterange input-group"
					     style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
						<input type="text" class="input-sm form-control" id="s_begin" name="s_begin" placeholder="开始日期"
						       value="{$search_by['begin']|escape}">
						<span class="input-group-addon">至</span>
						<input type="text" class="input-sm form-control" id="s_end" name="s_end" placeholder="结束日期"
						       value="{$search_by['end']|escape}">
					</div>
					<span class="space"></span>
					<button id="issearch" class="btn btn-info form-small form-small-btn margin-left-12"><i
								class="fa fa-search"></i> 搜索
					</button>
					<span class="space"></span>
					<button id="isdownload" class="btn btn-warning form-small form-small-btn margin-left-12"><i
								class="fa fa-cloud-download"></i> 导出
					</button>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">审批列表</div>
	</div>
	<div class="form-horizontal" role="form" method="post" action="{$form_del_url}">
		<input type="hidden" name="formhash" value="{$formhash}"/>
		<table class="table table-striped table-bordered table-hover font12" id="table_mul">
			<colgroup>
				<col class="t-col-5"/>

				<col class="t-col-10"/>
				<col class="t-col-10"/>
				<col class="t-col-9"/>
				<col class="t-col-9"/>
				<col class="t-col-9"/>
				<col class="t-col-15"/>
			</colgroup>
			<thead>

			<tr>
				<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px"
				                                                     onchange="javascript:checkAll(this,'delete');"/><span
								class="lbl">全选</span></label></th>
				<th>申请人</th>
				<th>申请时间</th>
				<th>部门</th>
				<th>审批标题</th>
				<th>审批状态</th>
				<th>操作</th>
			</tr>
			</thead>

			<tfoot id="tbody-page">

			</tfoot>

			<tbody id="tbdoy_id">

			</tbody>
		</table>

	</div>
</div>


<script type="text/javascript">

	var limit = 10;
	$(function () {

		function _think(page) {

			$('#table_mul .pagination a').on('click', function (e) {
				e.preventDefault();
				var page = 'page';
				var result = $(this).attr('href').match(new RegExp("[\?\&]" + page + "=([^\&]+)", "i"));
				if (result == null || result.length < 1) {
					return '';
				}

				var data = {};
				data.page = result[1];
				data.limit = limit;
				data.s_begin = $('#s_begin').val();
				data.s_end = $('#s_end').val();
				data.id_aft_type = $('#id_aft_type').val();
				data.id_aft_id = $('#id_aft_id').val();
				data.id_cd_id = $('#id_cd_id').val();
				data.id_af_status = $('#id_af_status').val();
				data.s_m_username = $('#s_m_username').val();
				data.id_af_subject = $('#id_af_subject').val();
				_post(data);
				return false;
			});

		}

		function _post(data) {
			$.ajax({
				url: '/Askfor/Apicp/Askfor/List',
				dataType: 'json',
				//jsonp: 'callback',
				data: data,
				type: 'get',
				success: function (result) {
					console.log(result.result.list);
					$('#tbdoy_id').html(txTpl('tpl-list', result.result));
					$('#tbody-page').html(txTpl('tpl-page', result.result));
					$('.delete').on('click', function (e) {
						e.preventDefault();
						$.ajax({
							url: '/Askfor/Apicp/Askfor/Delete',
							dataType: 'json',
							data: 'af_id=' + $(this).attr('af-id'),
							type: 'post',
							success: function () {
								alert('删除成功!');
								window.location.href = window.location.href;
							}
						});

					});
					$('#delete_list').on('click', function () {
						if (!confirm("确定删除吗？")) {
							return false;
						}
						var value = [];
						$('input[name="delete"]:checked').each(function () {
							value.push(parseInt($(this).val()));
						});

						var da = {}
								da.af_id = value;
						$.ajax({
							url: '/Askfor/Apicp/Askfor/Delete',
							dataType: 'json',
							data: da,
							type: 'post',
							success: function () {
								alert('删除成功!');
								window.location.href = window.location.href;
							}
						});
					});
					_think(result.result.page);
					return false;
				}
			});
		}

		$.ajax({
			url: '/Askfor/Apicp/Askfor/List',
			dataType: 'json',
			//jsonp: 'callback',
			data: 'limit=' + limit,
			type: 'get',
			success: function (result) {
				if (result.errcode == 0) {
					$('#tbdoy_id').html(txTpl('tpl-list', result.result));
					$('#id_aft_id').html(txTpl('tpl-template', result.result));
					$('#id_cd_id').html(txTpl('tpl-department', result.result));
					$('#id_af_status').html(txTpl('tpl-status', result.result));
					$('#tbody-page').html(txTpl('tpl-page', result.result));
					_think(result.result.page);
					$('.delete').on('click', function (e) {
						if (!confirm("确定删除吗？")) {
							return false;
						}
						e.preventDefault();
						$.ajax({
							url: '/Askfor/Apicp/Askfor/Delete',
							dataType: 'json',
							data: 'af_id=' + $(this).attr('af-id'),
							type: 'post',
							success: function () {
								alert('删除成功!');
								window.location.href = window.location.href;
							}
						});

					});
					$('#issearch').on('click', function () {
						var data = {};
						data.page = result.result.page;
						data.limit = limit;
						data.s_begin = $('#s_begin').val();
						data.s_end = $('#s_end').val();
						data.id_aft_type = $('#id_aft_type').val();
						data.id_aft_id = $('#id_aft_id').val();
						data.id_cd_id = $('#id_cd_id').val();
						data.id_af_status = $('#id_af_status').val();
						data.s_m_username = $('#s_m_username').val();
						data.id_af_subject = $('#id_af_subject').val();
						_post(data);
						return false;
					});
					$('#delete_list').on('click', function () {
						if (!confirm("确定删除吗？")) {
							return false;
						}
						var value = [];
						$('input[name="delete"]:checked').each(function () {
							value.push(parseInt($(this).val()));
						});

						var da = {}
								da.af_id = value;
						$.ajax({
							url: '/Askfor/Apicp/Askfor/Delete',
							dataType: 'json',
							data: da,
							type: 'post',
							success: function () {
								alert('删除成功!');
								window.location.href = window.location.href;
							}
						});
					});
				} else {
					alert(result.errcode + ' : ' + result.errmsg);
				}
			}
		})

	});

	function search() {
		var actname = document.getElementById('id_actname').value;
		alert(actname);
	}
</script>
{literal}
	<script type="text/template" id="tpl-list">

		<% if (!jQuery.isEmptyObject(list)) { %>
		<% $.each(list,function(n,val){ %>
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete"
			                                                      value="<%=val['af_id']%>"/><span class="lbl"> </span></label>
			</td>
			<td><%=val['m_username']%></td>
			<td><%=val['_created']%></td>
			<td><%=val['cd_name']%></td>
			<td><%=val['af_subject']%></td>

			<td class="text-<%=val['_tag']%>"><%=val['_status']%></td>
			<td>
				<a href="/admincp/office/askfor/view/pluginid/6/?af_id=<%=val['af_id']%>"><i class="fa fa-eye"></i>
					详情</a>
				<a class="delete" af-id="<%=val['af_id']%>" style="color:#337AB7;"><i class="fa fa-times" style="color:#337AB7;"></i>删除</a>
			</td>
		</tr>

		<% }) %>
		<% }else{ %>
		<tr>
			<td colspan="8" class="warning">
				暂无审批信息
			</td>
		</tr>
		<% } %>

	</script>
	<script type="text/template" id="tpl-template">
		<option value="-1">不限</option>
		<% if (!jQuery.isEmptyObject(template)) { %>
		<% $.each(template,function(n,val){ %>
		<option value="<%=val['aft_id']%>"><%=val['name']%></option>
		<% }) %>
		<% } %>
	</script>
	<script type="text/template" id="tpl-department">
		<option value="0">所有部门</option>
		<% if (!jQuery.isEmptyObject(department)) { %>
		<% $.each(department,function(n,val){ %>
		<option value="<%=val['cd_id']%>"><%=val['cd_name']%></option>
		<% }) %>
		<% } %>
	</script>
	<script type="text/template" id="tpl-status">
		<option value="0">不限</option>
		<% if (!jQuery.isEmptyObject(status)) { %>
		<% $.each(status,function(n,val){ %>
		<option value="<%=n%>"><%=val%></option>
		<% }) %>
		<% } %>
	</script>
	<script type="text/template" id="tpl-page">
		<% if(multi != null){%>
		<tr>
			<td colspan="2" class="text-left">
				<button id="delete_list" class="btn btn-danger">批量删除</button>
			</td>
			<td colspan="6" class="text-right">
				<% if(multi != null){%>
				<%=multi%>
				<% } %>
			</td>
		</tr>
		<% } %>
	</script>
{/literal}
{include file="$tpl_dir_base/footer.tpl"}