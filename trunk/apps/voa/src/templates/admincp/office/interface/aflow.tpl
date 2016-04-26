{include file="$tpl_dir_base/header.tpl"}
<div class="panel panel-default font12">
	<div class="panel-body">
		<form class="form-horizontal font12" role="form"
			action="{$form_action_url}" method="post">
			<input type="hidden" name="formhash" value="{$formhash}" />

			<div class="form-group">
				<label class="control-label col-sm-2 " for="id_name">流程名称</label>
				<div class="col-sm-9">
					<input type="text" class="form-control form-small" id="id_name"
						name="f_name" placeholder="最多输入25个字符" maxlength="25"
						value="{$result.title}" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2 " for="id_summary">流程描述</label>
				<div class="col-sm-9">
					<textarea class="form-control form-small" id="id_desc" name="f_desc"
						placeholder="最多输入100个字符" maxlength="100" rows=4>{$result.summary}</textarea>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2 " for="id_label_tc_id">选择应用</label>
				<span class="space"></span>
				<div class="col-sm-2">
					<select name="cp_pluginid" class="form-control form-small"
						data-width="auto" onchange="change(this)">
						<option value="" selected="selected">选择应用</option> {foreach
						$plugins as $_key => $_val}
						<option value="{$_val['cp_pluginid']}">{$_val['cp_name']}</option>
						{/foreach}
					</select>
				</div>
			</div>
			
		    <div class="form-group"  id="step" style="display:none"></div>
	
			<span class="space"></span>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10" style="text-align: center;">
					<button name="push" type="submit" class="btn btn-primary">提交</button>
					&nbsp;&nbsp; <a href="javascript:history.go(-1);" role="button"
						class="btn btn-default">返回</a>
				</div>
			</div>

		</form>
	</div>
</div>

<script type="text/javascript">
{literal} 
function change(obj) {
	$(obj).find('option').each(function () {
		if ($(this).prop('selected')) {
			ajax_list($(this).val());
		}
	});
}

function ajax_list(val){
	$.ajax({
		'type': 'GET',
		'url': '/api/interface/get/list?cp_pluginid=' + val,
		'success': function(data, status, xhr) {
			var str =" ";
			var count = data.result.count;
			if (count > 0) {
				var list = data.result.list;
				
			    $.each(list, function(key,val){
				    str +="<div class='form-group'  id='step'>"       
	                    +"<label class='control-label col-sm-2'  style='padding-top:15px;'></label>"
	                    +"<div class='col-sm-10 form-inline'>"
				    	+"<label class='radio-inline'>"
				    	+"<input type='hidden' value='"+val['n_id']+"' name='n_id["+val['n_id']+"]'/>"
						+"<input class='px' type='checkbox' name='interface["+key+"]' value='"+val['n_id']+"'/>"
						+"<span class='lbl' style='width:150px;'>"+val['name']+"</span>"
					    +"</label>"
					    +"<span class='space'></span>"
				        +"<label class='radio-inline' style='margin-left:0px;'>"
				        +"<span class='lbl'>执行顺序</span>"
				        +"<span class='space'></span>"	
				        +"<input class='form-control' style='width:100px;' type='text' name='order["+val['n_id']+"]'/>"	
				        +"</label>" 
					    +"<span class='space'></span>"
				        +"<label class='radio-inline' style='margin-left:0px;'>"
				        +"<span class='lbl'>登录人uid</span>"
				        +"<span class='space'></span>"	
				        +"<input class='form-control' style='width:100px;' type='text' name='login_uid["+val['n_id']+"]'/>"	
				        +"</label>" 
				        +"</div>"
				        +"</div>"
				 });
			    console.log(str);
			    $('#step').html(str);
				$('#step').show();
			} else {
				$('#step').hide();
				alert("请添加接口");
			}
		}
	});
}
{/literal} 
</script>

{include file="$tpl_dir_base/footer.tpl"}
