{include file='cyadmin/header.tpl'}
<div style="width:100%;height:35px;margin-top:10px;">
	<div style="width:80px;height:30px;float:left;font:20px 微软雅黑;line-height:35px;">代理列表</div>
	<div data-toggle="modal" data-target="#add_agant" class="btn-success" style="cursor: pointer; padding:5px;width:100px;border-radius:5px;margin-right:10px;float:right;font:18px 微软雅黑;line-height:26px;text-align:center;">添加代理</div>
</div>
<hr style=" margin: 10px 0px;  width: 100%;height:3px;border:none;border-top:2px solid #666666;"/>
<div class="panel panel-default">
	<div class="panel-heading">搜索</div>
	<div class="panel-body">
		<form class="form-horizontal" action="{$form_url}">
			<input type="hidden" name="issearch" value="1" />

			<div class="form-group ">
				<label class="control-label col-sm-1">代理编号:</label>
				<div class="col-sm-2">
					<input type="text"
					       name="id_number"
					       value="{$searchBy['id_number']}"
					       class="input-sm form-control" />
				</div>

				<label class="control-label col-sm-1">公司名称:</label>
				<div class="col-sm-2">
					<input type="text"
					       name="co_name"
					       value="{$searchBy['co_name']}"
					       class="input-sm form-control" />
				</div>

				<label class="col-xs-2" style="width: 183px; text-align: right; margin-top: 0px; margin-bottom: 0px; padding-top: 7px; margin-left: -94px;">联系人姓名:</label>
				<div class="col-sm-2">
					<input type="text"
					       name="link_name"
					       value="{$searchBy['link_name']}"
					       class="input-sm form-control">
				</div>
			</div>
			<script>
				$(function () {
					$('#sandbox-container .input-daterange').datepicker({
						todayHighlight: true
					});
				});
			</script>
			<div class="form-group ">

				<label class="col-xs-2" style="width: 183px; text-align: right; margin-top: 0px; margin-bottom: 0px; padding-top: 7px; margin-left: -94px;">付费状态:</label>

				<div class="col-sm-2">
					<select id="pay_status" name="pay_status" class="form-control" data-width="auto">
						<option value=""{if $searchBy['pay_status'] == ''} selected="selected"{/if}>请选择</option>
						<option value="2"{if $searchBy['pay_status'] == 2} selected="selected"{/if}>已付费</option>
						<option value="1"{if $searchBy['pay_status'] == 1} selected="selected"{/if}>未付费</option>
					</select>
				</div>

				<label class="col-xs-2" style="width: 183px; text-align: right; margin-top: 0px; margin-bottom: 0px; padding-top: 7px; margin-left: -94px;">联系人电话:</label>
				<div class="col-sm-2">
					<input type="text"
					       name="link_phone"
					       value="{$searchBy['link_phone']}"
					       class="input-sm form-control">
				</div>


				<label class="col-sm-1 control-label">跟进销售:</label>
				<div class="col-sm-2">
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

			<div class="form-group ">
				<label class="control-label col-sm-1" for="title">提交时间:</label>
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

				<button name="submit" type="submit" class="btn btn-primary">搜 索</button>
				<a class="btn btn-default" href="/enterprise/account/">全部记录</a>
				<button name="export" value="export" type="submit" class="btn btn-warning">导 出</button>
			</div>
		</form>

	</div>

</div>
<div class="panel panel-default">
<div class="panel-heading">列表
	<!-- Button trigger modal -->

	<!-- Modal -->
	{*添加代理*}
	<div class="modal fade" id="add_agant" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">添加代理</h4>
				</div>
				<div class="modal-body">
					<form class="form-horizontal">
						<div class="form-group">
							<label for="ca_id" class="col-sm-2 control-label">跟进销售</label>
							<div class="col-sm-5">
								<select id="caa_id" class="form-control">
									<option>请选择</option>
									{foreach $users as $k => $v}
										<option value="{$v['ca_id']}">{$v['ca_username']}</option>
										{foreachelse}
										<option value="">无</option>
									{/foreach}
								</select>
							</div>
						</div>

						<div class="form-group">
							<label for="id_number" class="col-sm-2 control-label">代理编号</label>
							<div class="col-sm-5">
								<input type="text" class="form-control" id="id_number" name="id_number" />
							</div>
						</div>

						<div class="form-group ">
							<label for="link_name" class="col-xs-2" style=" font-size: 13px; padding-top: 6px;">联系人姓名</label>
							<div class="col-sm-5">
								<input type="text" id="link_name" name="link_name" class="input-sm form-control">
							</div>
						</div>

						<div class="form-group ">
							<label for="link_phone" class="col-xs-2" style=" font-size: 13px; padding-top: 6px;">联系人电话</label>
							<div class="col-sm-5">
								<input type="text" id="link_phone" name="link_phone" class="input-sm form-control">
							</div>
						</div>

						<div class="form-group ">
							<label for="email" class="col-xs-2 control-label">邮箱</label>
							<div class="col-sm-5">
								<input type="text" id="email" name="email" class="input-sm form-control">
							</div>
						</div>

						<div class="form-group ">
							<label for="area" class="col-xs-2 control-label">代理区域</label>
							<div class="col-sm-5">
								<input type="text" id="area" name="area" class="input-sm form-control">
							</div>
						</div>

						<div class="form-group">
							<label for="co_name" class="col-sm-2 control-label">公司名称</label>
							<div class="col-sm-5">
								<input type="text" name="co_name" class="form-control" id="co_name" />
							</div>
						</div>

						<div class="form-group">
							<label for="co_address" class="col-sm-2 control-label">公司地址</label>
							<div class="col-sm-5">
								<input type="text" name="co_address" class="form-control" id="co_address" />
							</div>
						</div>

						<div class="form-group">
							<label for="intro" class="col-sm-2 control-label">公司简介</label>
							<div class="col-sm-5">
								<textarea id="intro" name="intro" class="form-control" rows="3" style="
										margin: 0px -101px 0px 0px;
										width: 376px;
										height: 130px;
										resize: none;"></textarea>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer" style="  margin-top: -17px;">
					<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
					<button type="button" id="add_post" class="btn btn-primary">保存</button>
				</div>
			</div>
		</div>
	</div>
</div>
{*添加代理结束*}
<div class="panel-body">
	<table class="table table-striped table-hover font12">
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
			<col class="t-col-10"/>
			<col class="t-col-5"/>
		</colgroup>
		<thead>
		<tr>
			<th>代理编号</th>
			<th>公司名称</th>
			<th>代理区域</th>
			<th>联系人姓名</th>
			<th>联系人电话</th>
			<th>邮箱</th>
			<th>跟进销售</th>
			<th>付费状态</th>
			<th>代理成交额</th>
			<th>提交时间</th>
			<th>操作</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="11" class="text-right">{$multi}</td>
		</tr>
		</tfoot>
		<tbody>
		{foreach $list as $val}
			<tr>
				<td>{$val['id_number']|escape}</td>
				<td>{$val['co_name']|escape}</td>
				<td>{$val['area']|escape}</td>
				<td>{$val['link_name']|escape}</td>
				<td>{$val['link_phone']|escape}</td>
				<td>{$val['email']|escape}</td>
				<td>{$val['ca_realname']}</td>
				<td>{if $val['pay_status'] == 1}未付费{elseif $val['pay_status'] == 2}已付费{else}未选择{/if}</td>
				<td>{$val['money']}</td>
				<td>{$val['updated']}</td>
				<td>
					{$base->show_link($view_url_base, $val['acid'], '详情', 'fa-eye')}
				</td>
			</tr>
			{foreachelse}
			<tr>
				<td colspan="11"
				    class="warning">{if $issearch}未搜索到指定条件的{$module_plugin['cp_name']|escape}数据{else}暂无任何{$module_plugin['cp_name']|escape}数据{/if}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	<div class="control-label col-sm-1">
	</div>
</div>

{*添加代理*}
<script>
	$(function() {
		$('#add_post').on('click', function () {
			var re = /^[\u4e00-\u9fa5a-z0-9]+$/gi;


			if($('#link_phone').val()==''){
				alert('请填写手机号码');
				return false;
			}
			if($('#link_phone').val().length !=11 ){
				alert('请输入正确的手机号码');
				return false;
			}


			var re = /^[0-9]+$/gi;
			if (!re.test($('#link_phone').val())) {
				alert('请输入正确的手机号码');
				return false;
			}


			var em =  /^([\.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
			if(!em.test($('#email').val())){
				alert('请输入正确的邮箱');
				return false;
			}

			var btn = $(this);
			btn.attr('disabled', 'disabled');
			btn.text('提交中...');
			var ca_id = $("#caa_id").find('option:selected').val();
			var id_number = $('#id_number').val();
			var link_name = $('#link_name').val();
			var link_phone = $('#link_phone').val();
			var email = $('#email').val();
			var area = $('#area').val();
			var co_name = $('#co_name').val();
			var co_address = $('#co_address').val();
			var intro = $('#intro').val();
			var url = "{$url}";
			var en_key = "{$en_key}";

			if (ca_id == '请选择') {
				alert('请选择跟进销售');
				btn.attr('disabled', false);
				btn.text('保 存');
				return false;
			}
			{literal}
			$.ajax({
				type: "POST",
				url: url + "/cyadmin/api/account/add",
				dataType: "json",
				data: {
					ca_id: ca_id,
					id_number: id_number,
					link_name: link_name,
					link_phone: link_phone,
					email: email,
					area: area,
					co_name: co_name,
					co_address: co_address,
					intro: intro,
					en_key: en_key
				},
				success: function(data){
					if (data.errcode == 0) {
						alert(data.result);
						btn.text('成 功');
						location.reload();
					} else {
						alert(data.errmsg);
						btn.attr('disabled', false);
						btn.text('保 存');
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
