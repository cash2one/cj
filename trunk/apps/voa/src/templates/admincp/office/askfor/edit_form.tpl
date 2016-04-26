{include file="$tpl_dir_base/header.tpl"}
<link rel="stylesheet"    href="{$CSSDIR}askfor.css">   
<!-- mobile visual start -->
<div class="askfor-mobile">
	<ul class="askfor-mobile-ul js-visual-box">
		<li class="askfor-mobile__li">
			<span class="askfor-mobile__title">流程名称</span>
			<span ask-visual="name" class="js-askfor-visual">{$template['name']|escape}</span>
		</li>		
		<li class="askfor-mobile__li">
			<span class="askfor-mobile__title">审批内容</span>
			<textarea class="askfor-mobile__textarea"  maxlength="15" placeholder="输入审批内容，最多500个字"></textarea>
		</li>	
		<li>
			<ul class="js-askfor-clone askfor-mobile-child-ul">
                        
				<li class="askfor-mobile__li js-askfor-visual" ask-clone="true">
					<span class="askfor-mobile__title js-askfor-visual" ask-index="0" ask-visual="title">{$cols_first['name']|escape}</span>
				</li>
                         
				{if !empty($cols)}
					{foreach $cols as $key => $val}
						<li class="askfor-mobile__li js-askfor-visual" ask-clone="true">
							<span class="askfor-mobile__title js-askfor-visual" ask-index="{$key+1}" ask-visual="title">{$val['name']|escape}</span>
						</li>
					{/foreach}
				{/if}
			</ul>
		</li>
		<li class="askfor-mobile__li js-askfor-visual askfor-mobile__upload" {if $template['upload_image'] == 1}style="display:block;"{/if} ask-show="1" ask-visual="upload">
			<span class="askfor-mobile__img"></span>
		</li>
		<li class="askfor-mobile__li">
			<span class="askfor-mobile__title">审批人:</span>
			<span class="js-askfor-visual" ask-visual="person">
				{$users_first[0]['name']}
				{if count($users) > 0 }					
					{foreach $users as $user}
						{'&gt;'}{$user[0]['name']}
					{/foreach}
				{/if}
			</span>
		</li>
		<li class="askfor-mobile__li askfor-mobile__last">
			<a href="javascript:void(0)" class="askfor-mobild__btn">提交</a>
		</li>
	</ul>
	<div style="display:none;">
		<li class="askfor-mobile__li js-askfor-visual" ask-clone="true">
			<span class="askfor-mobile__title js-askfor-visual" ask-index="0" ask-visual="title"></span>
		</li>
	</div>
</div>

<!-- mobile visual end -->
<!-- right start  -->
<div class="panel panel-default font12 askfor-parent">
	<div class="panel-body">
		<form class="form-horizontal font12" role="form" action="{$form_action_url}" id="add-form" method="post">
			<!-- formhash start -->
				<input type="hidden" name="formhash" value="{$formhash}" />
			<!-- formhash end -->
			<!-- name start  -->
				<div class="form-group">
					<div class="row col-sm-8">
						<label class="control-label col-sm-2 text-danger askfor-label" for="id_title">流程名称*</label>
						<div class="col-sm-3 askfor-group-40">
							<input type="text" class="form-control js-askfor-input askfor-input form-small" name="name"  ask-visual="name" placeholder="最多输入15个汉字" value="{$template['name']|escape}" maxlength="15"  required="required"/>			
						</div>
					</div>
				</div>
			<!-- name end -->
			<!-- order start  -->
				<div class="form-group">
					<div class="row col-sm-8">
						<label class="control-label col-sm-2 askfor-label " for="id_title">显示编号</label>
						<div class="col-sm-3">
							<input type="text"  class="form-control form-small  " id="id_title" name="orderid" value="{$template['orderid']}" placeholder="请输入数字" maxlength="3" />			
						</div>
					</div>
				</div>
			<!-- order end -->

			<!-- upload start  -->
				<div class="form-group">
					<div class="row col-sm-8">
						<label class="control-label col-sm-2 askfor-label " for="id_title">是否上传图片</label>
						<div class="col-sm-3">
							&nbsp;&nbsp;&nbsp;&nbsp;
							<input type="radio"  ask-visual="upload" value="1" class="js-askfor-input" name="upload_image" {if $template['upload_image'] == 1}checked{/if}/>是	
							&nbsp;&nbsp;&nbsp;&nbsp;
							<input type="radio"  ask-visual="upload" value="0" class="js-askfor-input" name="upload_image" {if $template['upload_image'] == 0}checked{/if}/>否		
						</div>
					</div>
				</div>
			<!-- upload end -->
			<hr>
			<!-- custom field start -->
				<div id="customer_columns"  class="clearfix">
					<!-- header start  -->
						<div class="askfor-custom-header">
							<ul class="askfor-custom-ul">							
								<li class="askfor-custom__li">
									<span>类型</span>
									<select class="askfor-custom-select js-custom-select">
										<option data-couple="false" data-type="text" value="1">文本</option>
										<option data-couple="false" data-type="number" value="2">数字</option>
										<option data-couple="true" data-type="time" data-length="2"  value="3">日期</option>
										<option data-couple="true" data-type="time" data-length="2" value="4">时间</option>
										<option data-couple="true" data-type="time" data-length="2" value="5">日期、时间</option>
									</select>
								</li>
								<li class="askfor-custom__li">
									<a href="javascript:void(0)" ask-clone="true"  class="js-custom-field js-askfor-input askfor-custom__btn">添加字段</a>
									<span class="askfor-custom__error" id="js-custom-error">时间、日期、日期+时间,类型不能再添加</span>
								</li>							
							</ul>												
						</div>
					<!-- header end  -->
					<!-- first start  -->
                                                {if !empty($cols_first)}
						<div class="form-group col-sm-8 js-custom-group  askfor-form-group">
							<label class="control-label col-sm-2 askfor-label-w18 text-danger" for="id_title">自定义字段*</label>
							<div class="col-sm-3 askfor-input-group">
								<input type="text" class="form-control form-small js-askfor-input"  name="cols[0][name]" value="{$cols_first['name']|escape}" placeholder="最多输入4个汉字" ask-visual="title"  ask-index="0" maxlength="4"  required="required"/>			
							</div>
							<label class="control-label col-sm-2 askfor-label-w10" for="id_title">类型</label>
							<div class="col-sm-3 askfor-input-group">							
								{if $cols_first['type'] == 1}{$text='文本'}{$mark = 'text'}{/if}
								{if $cols_first['type'] == 2}{$text='数字'}{$mark = 'number'}{/if}
								{if $cols_first['type'] == 3}{$text='日期'}{$mark = 'time'}{/if}
								{if $cols_first['type'] == 4}{$text='时间'}{$mark = 'time'}{/if}
								{if $cols_first['type'] == 5}{$text='日期、时间'}{$mark = 'time'}{/if}
								<input type="text" class="form-control form-small js-custom-read" disabled="disabled" value="{$text}" data-mark="{$mark}" />
								<input type="hidden" name="cols[0][type]" class="js-custom-hidden"  value="{$cols_first['type']}"/>
							</div>
							<div class="col-sm-2 askfor-label-w10 askfor-p0">
								<input type="checkbox" name="cols[0][required]" value="1" {if $cols_first['required']}checked{/if}/>
								<label>必填</label>			
							</div>
                            <div class="col-sm-1">
                                <a href="javascript:void(0);" role="button" class="btn js-custom-del  btn-default" onclick="delete_column(this)">删除</a>
                            </div>
						</div>
                                                {/if}
					<!-- first end  -->
					<!-- foreach start  -->
						{if !empty($cols)}
							{foreach $cols as $key => $val}
								<div class="form-group js-custom-group askfor-form-group  col-sm-8">
									<label class="control-label col-sm-2 askfor-label-w18 text-danger" for="id_title">自定义字段*</label>
									<div class="col-sm-3 askfor-input-group">
										<input type="text" class="form-control js-askfor-input  form-small"  name="cols[{$key+1}][name]" value="{$val['name']|escape}" placeholder="最多输入4个汉字"  maxlength="4" ask-visual="title"  ask-index="{$key+1}" required="required"/>			
									</div>
									<label class="control-label col-sm-2 askfor-label-w10" for="id_title">类型</label>
									<div class="col-sm-3 askfor-input-group">
										{if $val['type'] == 1}{$vtext='文本'}{$mark = 'text'}{/if}
										{if $val['type'] == 2}{$vtext='数字'}{$mark = 'number'}{/if}
										{if $val['type'] == 3}{$vtext='日期'}{$mark = 'time'}{/if}
										{if $val['type'] == 4}{$vtext='时间'}{$mark = 'time'}{/if}
										{if $val['type'] == 5}{$vtext='日期、时间'}{$mark = 'time'}{/if}
										<input type="text" class="form-control form-small js-custom-read" disabled="disabled" data-mark="{$mark}" value="{$vtext}" />
										<input type="hidden"  class="js-custom-hidden" name="cols[{$key+1}][type]" value="{$val['type']}">
									</div>
									<div class="col-sm-2 askfor-label-w10 askfor-p0">
										<input type="checkbox" name="cols[{$key+1}][required]" value="1" {if $val['required']}checked{/if}/>
										<label>必填</label>			
									</div>
									<div class="col-sm-1">
										<a href="javascript:void(0);" role="button" class="btn js-custom-del  btn-default" onclick="delete_column(this)">删除</a>		
									</div>
								</div>
							{/foreach}
						{/if}
					<!-- foreach end  -->
				</div>
			<!-- custom field end -->
			<!-- approver start  -->
				<hr>
				<div id="approver_columns" class="js-askfor-approver">
					<div class="form-group  js-custom-group col-sm-8">
						<label class="control-label col-sm-2 askfor-label askfor-approver  text-danger" for="id_title">一级审批人*</label>
						<div class="col-sm-3" style="margin-right: 10px;">
							<input type="radio" class="js-contact-radio" name="contact_container" id="one" {if $tag==1}checked {/if} data-value="one" value="1" >按人员选择
							<div data-diag="one" class="col-sm-3" {if $tag==2}style="display:none" {/if} id="contact_container">
								{if $tag == 1}
								{include 
									file="$tpl_dir_base/common_selector_member.tpl"
									input_type='radio'
									input_name='m_uid[]'
									selector_box_id='contact_container'	
									default_data={rjson_encode($users_first)}						
									allow_member=true
									allow_department=false
									
								}
								{else}
								{include 
									file="$tpl_dir_base/common_selector_member.tpl"
									input_type='radio'
									input_name='m_uid[]'
									selector_box_id='contact_container'						
									allow_member=true
									allow_department=false	
								}
								{/if}		
							</div>
						</div>
						<div class="col-sm-3" style="display:none;">
							<input type="radio" class="js-contact-radio" name="contact_container" id="two" {if $tag==2}checked{/if} data-value="two" class="change" value="2">按职务选择
							<select data-diag="two" {if $tag==1}style="display:none"{/if}  class="askfor-custom-select js-approver-job" ask-index="0" name="position[]">
									<option value="0">选择职务</option>
								{if !empty($position)}
									{foreach $position as $val}
									<option value="{$val['mp_id']}" {if $users_first[0]['mp_id'] ==$val['mp_id']}selected="true"{/if}>{$val["mp_name"]}</option>
									{/foreach}
								{/if}

							</select>
						</div>
					</div>
					<div class="col-sm-2 col-sm-offset-2">
						<a href="javascript:void(0);" role="button" class="btn btn-default " onclick="add_approver()">添加审核人</a>
					</div>
					<!-- foreach data start  -->
						{if (!empty($users))}
							{foreach $users as $k => $v}

							<div class="form-group col-sm-8">
								<label class="control-label col-sm-2  askfor-label askfor-approver  text-danger" for="id_title">{$numIndex[$k+2]}级审批人*</label>
								<div class="col-sm-3">
									<input type="radio" class="js-contact-radio" name="contact_container_{$k}" disabled {if $tag==1}checked{/if} data-value="one" value="1" >按人员选择
									<div data-diag="one" class="col-sm-3" {if $tag==2}style="display:none"{/if} id="contact_container_{$k}">
										{if $tag == 1}
										{include 
											file="$tpl_dir_base/common_selector_member.tpl"
											input_type='radio'
											input_name='m_uid[]'
											selector_box_id="contact_container_$k"	
											default_data={rjson_encode($v)}						
											allow_member=true
											allow_department=false
											
										}	
										{else}
										{include 
											file="$tpl_dir_base/common_selector_member.tpl"
											input_type='radio'
											input_name='m_uid[]'
											selector_box_id="contact_container_$k"					
											allow_member=true
											allow_department=false	
										}
										{/if}		
									</div>
								</div>
								<div class="col-sm-3" style="display:none;">
									<input type="radio" class="js-contact-radio" name="contact_container_{$k}" disabled {if $tag==2}checked{/if} data-value="two" class="change" value="2">按职务选择
									<select {if $tag==1}style="display:none"{/if} data-diag="two" class="askfor-custom-select js-approver-job" ask-index="0" name="position[]">
											<option value="0">选择职务</option>
										{if !empty($position)}
											{foreach $position as $val}
											<option value="{$val['mp_id']}" {if isset($v[0]['mp_id'])}{if $v[0]['mp_id'] == $val['mp_id']}selected="true"{/if}{/if}>{$val["mp_name"]}</option>
											{/foreach}
										{/if}

									</select>
								</div>
								<div class="col-sm-3">
								<label class="col-sm-12  text-danger" for="id_title"></label>	
								</div>
								<div class="col-sm-1">
									<a href="javascript:void(0);" role="button" class="btn btn-default js-person-del" onclick="delete_approver(this)">删除</a>
								</div>
							</div>	
							{/foreach}
						{/if}
					<!-- foreach data end  -->
				</div>
			<!-- approver end -->
			<!-- submit start -->
			<div class="form-group">
					<div class="col-sm-offset-2 col-sm-9">
						<button type="submit" class="btn btn-primary">编辑</button>
						&nbsp;&nbsp;
						<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
					</div>
			</div>
			<!-- submit end -->
		</form>
	</div>
</div>
<!-- right end  -->
<script type="text/javascript">
var customIndex = $('.js-custom-group .askfor-input-group .js-askfor-input:last').attr('ask-index');
customIndex = Number(customIndex) +1;
 function add_column() {
 	var selector = $('#customer_columns .form-group');
	 	var length = selector.length
	 		index = customIndex;	 	
	 	$.askfor.customClone(this,customIndex);
	 	if (length > 9) {
	 		$.askfor.custom_is_add = false;
	 		alert('不能添加更多自定义字段');
	 		return false;
	 	}
 	var str = '';
 	str += 	'<div class="form-group js-custom-group  askfor-form-group  col-sm-8">'+
			'<label class="control-label col-sm-2 askfor-label-w18 text-danger">自定义字段*</label>'+
			'<div class="col-sm-3 askfor-input-group">'+
			'	<input type="text" class="form-control form-small js-askfor-input " ask-index="'+index+'" name="cols['+index+'][name]" placeholder="最多输入4个汉字" value="{$category['title']|escape}" ask-visual="title"  maxlength="4"  required="required"/>'+			
			'</div>'+
			'<label class="control-label col-sm-2  askfor-label-w10 ">类型</label>'+
			'<div class="col-sm-3 askfor-input-group">'+
				'<input type="text" class="form-control form-small js-custom-read" disabled="disabled"/>'+
				'<input type="hidden" class="js-custom-hidden" name="cols['+index+'][type]" value=""/>'+
			'</div>'+
			'<div class="col-sm-2 askfor-label-w10 askfor-p0">'+
			'	<input type="checkbox" name="cols['+index+'][required]" value="1"/>'+
			'	<label>必填</label>'+
			'</div>'+
			'<div class="col-sm-1">'+
			'	<a href="javascript:void(0);" role="button" ask-del="'+index+'" class="btn js-custom-del  btn-default" onclick="delete_column(this)">删除</a>	'+	
			'</div>'+
			'</div>';
	$('#customer_columns').append(str);
        
	++customIndex;
 }
 
 	function delete_column(obj){
 		if(confirm("确认删除吗？")) {
 			// $(obj).parents('.form-group').first().remove();
 			$.askfor.__customDel(obj);	
 		}
 	}
 	
 	function add_approver() {
                var radio_status = $("input[name=contact_container]:checked").attr("id");
		var numIndex = { 1: '一',2: '二',3: '三',4: '四',5: '五',6: '六',7: '七',8: '八',9: '九',10: '十' };
		var index = $('#approver_columns').children('.form-group').length;
		if(index > 9){			
			alert('不能添加更多审批人');
			return;
		}
		var str = '<div class="form-group col-sm-8">'+
			'<label class="control-label col-sm-2 askfor-label  askfor-approver  text-danger" for="id_title">'+numIndex[index+1]+'级审批人*</label>'+
			'<div class="col-sm-3">'+
			'<input type="radio" class="js-contact-radio" name="contact_container_'+index+'" data-value="one" disabled value="1">按人员选择'+
				'<div class="col-sm-2" data-diag="one" id="contact_container_'+index+'">	'+
				'	{literal}	'+
				'	<script type="text/javascript">	'+
				'	requirejs(["jquery", "views/contacts"], function($, contacts) {'+
				'		$(function () {'+
				'			view_member["contact_container_'+index+'"] = new contacts();'+
				'			view_member["contact_container_'+index+'"].render({'+
				'				"input_type": "radio",'+
				'				"input_name_contacts": "m_uid[]",'+
				'				"container": "#contact_container_'+index+'",'+
				'				"sct_callback": check_selector,'+
				'				"deps_enable": false,'+
				'				"contacts_enable": true'+
				'			});'+
				'		});'+
				'	});		'+
				'	<\/script>'+
				'	{/literal}'+
				'</div>'+
			'</div>'+
			'<div class="col-sm-3" style="display:none;">'+
				'<input type="radio" class="js-contact-radio" name="contact_container_'+index+'" data-value="two" disabled value="2" >按职务选择'+
				'<select data-diag="two" class="askfor-custom-select js-approver-job" ask-index="'+index+'" name="position[]">'+
				'<option value="0">选择职务</option>'+
				{if !empty($position)}
					{foreach $position as $val}
				'<option value="{$val['mp_id']}">{$val["mp_name"]}</option>'+
					{/foreach}
				{/if}
				'</select>'+
			'</div>'+
			'<div class="col-sm-3">'+
			'<label class="col-sm-12  askfor-approver text-danger askfor-label-w120" for="id_title">（各级不能重复）</label>	'+
                        '	<a href="javascript:void(0);" role="button" class="btn askfor-approver__del-btn btn-default js-person-del" onclick="delete_approver(this)">删除</a>	'+
			'</div>'+
			'</div>';
		$('#approver_columns').append(str);	
                $("input[data-value="+radio_status+"]").prop("checked","true");
                if (radio_status == "one") {
                    $("select[data-diag=two]").hide();
                } else {
                    $("div[data-diag=one]").hide();;
                }
		
	}
	
	function delete_approver(obj){
		function DelFn(){
			$.askfor.delPerson = confirm("确认删除吗？");
			if($.askfor.delPerson) {
				var numIndex = { 1: '一',2: '二',3: '三',4: '四',5: '五',6: '六',7: '七',8: '八',9: '九',10: '十' };
				$(obj).parents('.form-group').first().remove();
				$('#approver_columns').children('.form-group').each(function(index,e){
					$(e).children().first().html(numIndex[index+1]+'级审批人*');
				});
			}
		}
		setTimeout(DelFn,100);
	}
	
	function check_selector() {
		var users = new Array();
		$('input[name="m_uid[]"]').each(function(index,e){
			var user = $(e).val();
			users.push(user); 
		})
		var nary = users.sort();  
		 for(var i=0;i<nary.length;i++){  
			 if (nary[i]==nary[i+1]){  
			  	alert("审批人不能重复");  
			  	return false;
			 } 
			 return true;
		}	  
	}
	function check_position() {
	 	var postions = new Array();
		$('select[name="position[]"]').each(function(index,e){
			var postion = $(e).val();
			postions.push(postion); 
		})	

		var pos = postions.sort();
		for(var i=0; i<pos.length; i++) {
			if (pos[i] == 0) {
				alert('请选择职务');
				return false;
			}
			if (pos[i] == pos[i+1]) {
				alert('职务不能重复');
				return false;
			}
		}
		return true;
	}
	$(function(){
		var numIndex = { 1: '一',2: '二',3: '三',4: '四',5: '五',6: '六',7: '七',8: '八',9: '九',10: '十' };


		$("#one").click(function(){
			var fields = $(this).attr("data-value");
			$("input[date-value=two]").prop("checked","false");
                        $("select[data-diag=two]").hide();
                        $("div[data-diag=one]").show();
			$("input[data-value="+fields+"]").prop("checked","true");
			// 清除审批人
			$.askfor.clearPerson();
			$.askfor.personAddFn();
		});

		$("#two").click(function(){
			var fields = $(this).attr("data-value");
			$("input[date-value=one]").prop("checked","false");
                        $("div[data-diag=one]").hide();
                        $("select[data-diag=two]").show();
			$("input[data-value="+fields+"]").prop("checked","true");
			// 清除审批人
			$.askfor.clearPerson();
			$.askfor.jobEach();
			
		})
		$('#add-form').submit(function(){
			var one = $("input[data-value=one]:checked").length;
			var two = $("input[data-value=two]:checked").length;
			if (one > 0) {
				var div_length = $('#approver_columns').children('.form-group').length;
				var uid_length =  $('input[name="m_uid[]"]').length;

				if (div_length != uid_length) {
					alert('请选择审批人');
					return false;
				}
		 		if(!check_selector()){
		 			return false;
		 		}	
			}

			if (two > 0) {
		 		// 判断职务是否重复 
		 		if (!check_position()) {
		 			return false;
		 		}
			}

			if (one == 0 && two == 0) {
				alert('请选择审批人或职务');
				return false;
			}
		});
	});	
</script>
<script type="text/javascript" src="{$JSDIR}askfor.js"></script>
<script type="text/javascript">
	$.askfor.page = 'edit';
	$.askfor.editPageInit();
</script>
{include file="$tpl_dir_base/footer.tpl"}