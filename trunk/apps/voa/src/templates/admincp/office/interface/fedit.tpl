{include file="$tpl_dir_base/header.tpl"}
<div class="panel panel-default font12">
	<div class="panel-body">
		<form class="form-horizontal font12" role="form"
			action="{$form_action_url}" method="post">
			<input type="hidden" name="formhash" value="{$formhash}" />
			<input type="hidden" name="f_id" value="{$view['f_id']}" />

			<div class="form-group">
				<label class="control-label col-sm-2 ">流程名称</label>
				<div class="col-sm-9">
					<input type="text" class="form-control form-small" id="id_name"
						name="f_name" placeholder="最多输入25个字符" maxlength="25"
						value="{$view.f_name}" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2 ">流程描述</label>
				<div class="col-sm-9">
					<textarea class="form-control form-small" id="id_desc" name="f_desc"
						placeholder="最多输入100个字符" maxlength="100" rows=4>{$view.f_desc}</textarea>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2 " for="id_label_tc_id">选择应用</label>
				<span class="space"></span>
				<div class="col-sm-2">
					<select name="cp_pluginid" class="form-control form-small"
						data-width="auto" onchange="change(this)">
						<option value="" selected="selected">选择应用</option> 
						{foreach $plugins as $_key => $_val}
						<option value="{$_val['cp_pluginid']}" {if $view.cp_pluginid == $_val['cp_pluginid']}selected="selected"{/if}>{$_val['cp_name']}</option>
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
var cp_pluginid = "{$view.cp_pluginid}";
var f_id = "{$view.f_id}";

{literal} 
if (cp_pluginid > 0) {
	ajax_list(cp_pluginid);
}
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
		'url': '/api/interface/get/list?cp_pluginid=' + val+'&f_id='+f_id,
		'success': function(data, status, xhr) {
			var str =" ";
			var count = data.result.count;
			if (count > 0) {
				var list = data.result.list;
				console.log(list);
			    $.each(list, function(key,val){
				    str +="<div class='form-group'  id='step'>"       
	                    +"<label class='control-label col-sm-2'  style='padding-top:15px;'></label>"
	                    +"<div class='col-sm-10 form-inline'>"
				    	+"<label class='radio-inline'>"
				    	+"<input type='hidden' value='"+val['n_id']+"' name='n_id["+val['n_id']+"]'/>";
				    	
			        if (typeof(val['s_id']) != 'undefined') {
			        	str+="<input type='hidden' value='"+val['s_id']+"' name='s_id["+val['n_id']+"]'/>";
				     } 
				    	
			    	if (val['checked'] == 'checked') {
			    		str+="<input class='px' type='checkbox' name='interface["+key+"]' onchange='checkbox(this)' value='"+val['n_id']+"' checked='checked'>";
			    	} else {
			    		str+="<input class='px' type='checkbox' name='interface["+key+"]' onchange='checkbox(this)' value='"+val['n_id']+"'>";
			    	}
			    	
					str	+="<span class='lbl' style='width:150px;'>"+val['name']+"</span>"
					    +"</label>"
					    +"<span class='space'></span>"
				        +"<label class='radio-inline' style='margin-left:0px;'>"
				        +"<span class='lbl'>执行顺序</span>"
				        +"<span class='space'></span>";
				        
				    if (typeof(val['sort']) == 'undefined') {
				    	 str+="<input class='form-control' style='width:100px;' type='text' name='order["+val['n_id']+"]' />";	
				    } else {
				    	 str+="<input class='form-control' style='width:100px;' type='text' name='order["+val['n_id']+"]' value='"+val['sort']+"'/>";	
				    }  
				    
				    str+="</label>" 
				    	+"<span class='space'></span>"
				        +"<label class='radio-inline' style='margin-left:0px;'>"
				        +"<span class='lbl'>登录人uid</span>"
				        +"<span class='space'></span>";
				       
				    if (typeof(val['login_uid']) == 'undefined') {
				    	str+="<input class='form-control' style='width:100px;' type='text' name='login_uid["+val['n_id']+"]'/>";
				    } else {
				    	str+="<input class='form-control' style='width:100px;' type='text' name='login_uid["+val['n_id']+"]' value='"+val['login_uid']+"'/>";	
				    }
				    
				    str+="</label>" 
				       +"</div>"
				       +"</div>";
				 });

			    $('#step').html(str);
				$('#step').show();
			} else {
				$('#step').hide();
				alert("请添加接口");
			}
		}
	});
}


function checkbox(obj) {
    if (obj.checked==true) {
    	$(obj).attr('checked',true);
    } else {
    	$(obj).attr('checked',false);
    }
}

{/literal} 
</script>

{include file="$tpl_dir_base/footer.tpl"}
