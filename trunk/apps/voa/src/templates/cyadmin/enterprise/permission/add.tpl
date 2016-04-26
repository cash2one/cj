{include file='cyadmin/header.tpl'}
<script>
	$(function(){
		$('#sandbox-container .input-daterange').datepicker({
			todayHighlight: true
		});
	});
</script>
<h3>
	添加后台账号
</h3>
<div class="modal-body">
	<form action="{$form_url}" id="addform" class="form-horizontal" method="post" id="addform">


			<div class="form-group">
				<label for="add_ep_name" class="col-sm-2 control-label">管理员姓名<i style="color:red;">*</i></label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="realname" style="width:300px;" required="required" maxlength="10"/>
				</div>
			</div>
			<div class="form-group">
				<label for="add_ep_name" class="col-sm-2 control-label">管理员电话<i style="color:red;">*</i></label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="tell" name="tell" style="width:300px;" required="required"/>
				</div>
			</div>
			<div class="form-group">
				<label for="add_ep_name" class="col-sm-2 control-label">管理员电子邮箱<i style="color:red;">*</i></label>
				<div class="col-sm-10">
					<input type="email" class="form-control" name="email" style="width:300px;" required="required"/>
				</div>
			</div>
			<div class="form-group">
				<label for="add_ep_name" class="col-sm-2 control-label">管理员分组<i style="color:red;">*</i></label>
				<div class="col-sm-10">
					<select name="cag_id"  class="form-control" style="width:300px;">
						{foreach $group_list as $val}
							<option value = "{$val['cag_id']}" >{$val['cag_title']}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group">
				<label for="add_ep_name" class="col-sm-2 control-label">管理员职位<i style="color:red;">*</i></label>
				<div class="col-sm-10">
					{foreach $job_list as $key=>$val}
						<label><input type="radio" class="form-radio" name="job" {if $key == 2}checked{/if} value="{$key}"/>{$val}</label>
					{/foreach}
				</div>
			</div>
			<div class="form-group">
				<label for="add_ep_name" class="col-sm-2 control-label">上级领导</label>
				<div class="col-sm-10">
					<select name="sub_id" class="form-control" style="width:300px;">
						<option value="">无</option>
						{foreach $leader as $k => $v}
							<option value="{$k}">{$v}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group">
				<label for="add_ep_name" class="col-sm-2 control-label">管理员登录账号<i style="color:red;">*</i></label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="username" style="width:300px;" maxlength="12" required="required"/>
				</div>
			</div>
			<div class="form-group">
				<label for="add_ep_name" class="col-sm-2 control-label">登录密码<i style="color:red;">*</i></label>
				<div class="col-sm-10">
					<input type="password" class="form-control" name="password" style="width:300px;" required="required"/>
				</div>
			</div>
			<div class="form-group">
				<label for="add_ep_name" class="col-sm-2 control-label">确认登录密码<i style="color:red;">*</i></label>
				<div class="col-sm-10">
					<input type="password" class="form-control" name="repassword" style="width:300px;" required="required"  />
				</div>
			</div>
			<div class="form-group">
				<label for="add_ep_name" class="col-sm-2 control-label"></label>
				<div class="col-sm-10">
					<input type="submit" class="btn btn-primary"/>
				</div>
			</div>



	</form>

</div>

<script>
	$(function(){
		$('#addform').submit(function(){

			var re = /^[\u4e00-\u9fa5a-z0-9]+$/gi;

			if($('#tell').val().length !=11 ){
				alert('请输入正确的手机号码');
				return false;
			}

			var re = /^[0-9]+$/gi;
			if (!re.test($('#tell').val())) {
				alert('请输入正确的手机号码');
				return false;
			}

		});
	});
</script>

{include file='cyadmin/footer.tpl'}