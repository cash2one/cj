{include file="$tpl_dir_base/header.tpl" css_file=array("exam/exam.css", "bootstrap-datetimepicker.min.css") expand_js=array("bootstrap-datetimepicker.min.js", "bootstrap-datetimepicker.zh-CN.js")}
<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<form class="form-horizontal font12" name="submit_form" role="form" action="" method="post" data-ng-app="ng.poler.plugins.pc" onsubmit="return checkForm();">
				<input type="hidden" name="formhash" value="{$formhash}" />
				<input type="hidden" name="submitype" value="" />
				<span id="m_uid_choose" style="display: none;"></span>
				<span id="cd_id_choose" style="display: none;"></span>

				<div class="form-group">
					<label class="control-label col-sm-2" for="id_title"></label>
					<div class="col-sm-8">
						<ul class="op-step clearfix">
							<li class="col-sm-2">
								<em>1</em>
								<h3>模式设置</h3>
							</li>
							<li class="i-border col-sm-3"><em></em></li>
							<li class="col-sm-2">
								<em>2</em>
								<h3>选择题目</h3>
							</li>
							<li class="i-border col-sm-3"><em></em></li>
							<li class="col-sm-2 i-active">
								<em>3</em>
								<h3>基本设置</h3>
							</li>
						</ul>
					</div>
					<div class="col-sm-2"></div>
				</div>

				<div class="form-group">
					<label class="control-label col-sm-2" for="id_rights">考试范围*</label>
					<div class="col-sm-9">
						<div>
							<input type="hidden" class="form-small" id="is_all" name="is_all" value="{$paper['is_all']}"/>
							<button type="button" class="btn  {if $paper['is_all'] == 1}btn-primary{/if}" id="all_btn">全公司</button>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<button type="button" class="btn {if $paper['is_all'] == 0}btn-primary{/if}" id="specified_btn">指定对象</button>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<input type="checkbox" name="notifynow" value="1"{if $paper.notifynow == 1} checked{/if}>&nbsp;&nbsp;发送考试通知提醒
						</div>
						<div id="user_dep_container"{if $paper['is_all'] == 1} style="display: none" {/if}>
							<hr>
							<div class="row">
								<label class="col-sm-2 text-right padding-sm">选择部门：</label>

								<div id="deps_container" class="col-sm-8">

									<!-- angular 选人组件 begin -->
									<div class="angularjs-area " data-ng-controller="ChooseShimCtrl">
										<a class="btn btn-defaul" data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')">选择部门</a>
									</div>
									<!-- angular 选人组件 end -->

									<pre id="dep_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

									{*{include *}
											{*file="$tpl_dir_base/common_selector_member.tpl"*}
											{*input_type='checkbox'*}
											{*input_name_department='cd_ids[]'*}
											{*selector_box_id='deps_container'*}
											{*allow_member=false*}
											{*allow_department=true	*}
										{*}*}
								</div>
							</div>
							<br>
							<div class="row">
								<label class="col-sm-2 text-right padding-sm">选择人员：</label>
								<div id="users_container" class="col-sm-8">

									<!-- angular 选人组件 begin -->
									<div class="angularjs-area" data-ng-controller="ChooseShimCtrl" >
										<a class="btn btn-defaul" data-ng-click="selectPerson('m_uid','selectedMuidCallBack')">选择人员</a>
									</div>
									<!-- angular 选人组件 end -->

									<pre id="m_uid_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

									{*{include *}
										{*file="$tpl_dir_base/common_selector_member.tpl"*}
										{*input_type='checkbox'*}
										{*input_name='m_uids[]'*}
										{*selector_box_id='users_container'*}
										{*allow_member=true*}
										{*allow_department=false	*}
									{*}*}
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label  col-sm-2 " for="id_author">封面图片*</label>
					<div class="col-sm-9">
						{cycp_upload
								inputname='cover_id'
								hidedelete=1
								tip='(推荐尺寸 480x230)'
								attachid = $paper['cover_id']
							}
					</div>
				</div>

				<div class="form-group" >
					<label class="control-label  col-sm-2 " for="id_author">考试时间*</label>
					<div class="col-sm-5">
						<table>
							<tr>
								<td><input type="text" class="form-control" name="begin_date" placeholder="开始日期" value="{if $paper['begin_time']}{rgmdate($paper['begin_time'], 'Y/m/d')}{/if}" autocomplete="off" required="required" /></td>
								<td><input type="text" class="form-control" name="begin_time" placeholder="开始时间" value="{if $paper['begin_time']}{rgmdate($paper['begin_time'], 'H:i')}{/if}" autocomplete="off" required="required" /></td>
								<td class="input-group-addon">至</td>
								<td><input type="text" class="form-control" name="end_date" placeholder="结束日期" value="{if $paper['end_time']}{rgmdate($paper['end_time'], 'Y/m/d')}{/if}" autocomplete="off" required="required" /></td>
								<td><input type="text" class="form-control" name="end_time" placeholder="结束时间" value="{if $paper['end_time']}{rgmdate($paper['end_time'], 'H:i')}{/if}" autocomplete="off" required="required" /></td>
							</tr>
						</table>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-sm-2 ">考试时长*</label>
					<div class="col-sm-9">
						<input type="text" class="form-control form-small" name="paper_time" placeholder="输入整数，单位是分钟" maxlength="64"  required="required" value="{$paper.paper_time}"/>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-sm-2 ">考试提醒</label>
					<div class="col-sm-9">
						<input type="checkbox" name="is_notify" value="1" onclick="show_time(this)" {if $paper.is_notify == 1} checked{/if}/>&nbsp;&nbsp;开启考试提醒功能
						<div id="time_wrap"{if $paper.is_notify == 0} style="display:none;"{/if}>
							<p>考试开始前<input type="text" name="notify_begin" value="{$paper['notify_begin']}"/>分钟提醒</p>
							<p>考试结束前<input type="text" name="notify_end" value="{$paper['notify_end']}"/>分钟提醒</p>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-sm-2 ">及格分数*</label>
					<div class="col-sm-9">
						<input type="text" class="form-control form-small" name="pass_score" placeholder="输入整数" maxlength="64" required="required" value="{$paper.pass_score}"/>
						注：当前试卷总分为：{$paper.total_score} 分
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-sm-2 ">考试说明*</label>
					<div class="col-sm-9">
						<textarea  class="form-control form-small" name="intro" placeholder="最多输入500个字符" maxlength="500" required="required" rows="4" >{$paper.intro}</textarea>
					</div>
				</div>

				<div class="form-group" id="btn-box">
					<div class="col-sm-2"></div>
					<div class="col-sm-9">
						<div class="row">
							<div class="col-md-3">
								<input type="button" value="上一步" class="btn btn-default  col-md-9 submit" onclick="noPubSubmit('goback');" />
							</div>

							<div class="col-md-3">
								<input type="button" value="预览" class="btn btn-primary  col-md-9 submit" onclick="noPubSubmit('preview');" />
							</div>

							<div class="col-md-3">
								<input type="submit" name="draftsubmit" value="保存草稿" class="btn btn-primary  col-md-9 submit" onclick="noPubSubmit('');" />
							</div>

							<div class="col-md-3">
								<input type="submit" name="pubsubmit" value="发布" class="btn btn-primary  col-md-9 submit" onclick="noPubSubmit('');" />
							</div>
						</div>
					</div>
				</div>
				{if $paper}<input type="hidden" name="id" value="{$paper.id}">{/if}
			</form>
		</div>
	</div>
</div>

{include file="$tpl_dir_base/footer.tpl"}
<script type="text/javascript">


function on_sel_type(obj) {
	obj.value == 0 ? jQuery('#rulewrap').hide() : jQuery('#rulewrap').show();
	obj.value == 0 ? jQuery('#use_all_wrap').show() : jQuery('#use_all_wrap').hide();
}

/* 选人组件 */
var dep_arr = [];
var m_uid = [];
var cd_id_choose = '';
var m_uid_choose = '';

{if !empty($paper.cd_ids)}
dep_arr = {$default_departments};
if (dep_arr.length != 0) {
	cd_id_choose = '';
	var select_dep_name = '';
	for (var i = 0; i < dep_arr.length; i ++) {
		cd_id_choose += '<input name="cd_ids[]" value="' + dep_arr[i]['id'] + '" type="hidden">';
		select_dep_name += dep_arr[i]['cd_name'] + ' ';
	}
	$('#cd_id_choose').html(cd_id_choose);

	// 展示
	if (select_dep_name != '') {
		$('#dep_deafult_data').html(select_dep_name).show();
	} else {
		$('#dep_deafult_data').hide();
	}
}
{/if}

{if !empty($paper.m_uids)}
m_uid = {$default_users};
if(m_uid){
	if (m_uid.length != 0) {
		m_uid_choose = '';
		var select_m_uid_name = '';
		for (var i = 0; i < m_uid.length; i ++) {
			m_uid_choose += '<input name="m_uids[]" value="' + m_uid[i]['m_uid'] + '" type="hidden">';
			select_m_uid_name += m_uid[i]['m_username'] + ' ';
		}
		$('#m_uid_choose').html(m_uid_choose);

		// 展示
		if (select_m_uid_name != '') {
			$('#m_uid_deafult_data').html(select_m_uid_name).show();
		} else {
			$('#m_uid_deafult_data').hide();
		}
	}
}
{/if}

// 可发送人员选择回调
function selectedMuidCallBack(data, id) {
	m_uid = data;

	// 页面埋入 选择的值
	m_uid_choose = '';
	var select_m_uid_name = '';
	for (var i = 0; i < data.length; i ++) {
		m_uid_choose += '<input name="m_uids[]" value="' + data[i]['m_uid'] + '" type="hidden">';
		select_m_uid_name += data[i]['m_username'] + ' ';
	}
	$('#m_uid_choose').html(m_uid_choose);

	// 展示
	if (select_m_uid_name != '') {
		$('#m_uid_deafult_data').html(select_m_uid_name).show();
	} else {
		$('#m_uid_deafult_data').hide();
	}
}

// 选择部门回调
function selectedDepartmentCallBack(data){
	dep_arr = data;

	// 页面埋入 选择的值
	cd_id_choose = '';
	var select_dep_name = '';
	for (var i = 0; i < data.length; i ++) {
		cd_id_choose += '<input name="cd_ids[]" value="' + data[i]['id'] + '" type="hidden">';
		select_dep_name += data[i]['name'] + ' ';
	}
	$('#cd_id_choose').html(cd_id_choose);

	// 展示
	if (select_dep_name != '') {
		$('#dep_deafult_data').html(select_dep_name).show();
	} else {
		$('#dep_deafult_data').hide();
	}
}

//填充封面
function fill_cover(result){
	var url = result.list[0].url;
	$('#fill_cover').html('<img src="'+url+'"/>');
}

$(function(){
	//当考试对象为指定对象时
	$('#specified_btn').bind('click', function () {
		$('#user_dep_container').show();
		$(this).addClass('btn-primary');
		$('#all_btn').removeClass('btn-primary');
		$('#is_all').val(0);
	});
	//当考试对象为全公司时
	$('#all_btn').bind('click', function () {
		$('#user_dep_container').hide();
		$(this).addClass('btn-primary');
		$('#specified_btn').removeClass('btn-primary');
		$('#is_all').val(1);
	});
});

var options_time={
	language: 'zh-CN',
    format: "hh:ii",
    autoclose: true,
    startView:1
}
var options_date={
	language: 'zh-CN',
    format: "yyyy-mm-dd",
    autoclose: true,
    todayBtn: true,
    minView:2
}
$("input[name='begin_date']").datetimepicker(options_date);
$("input[name='begin_time']").datetimepicker(options_time);
$("input[name='end_date']").datetimepicker(options_date);
$("input[name='end_time']").datetimepicker(options_time);


function show_time(obj) {
	$('#time_wrap').css('display', obj.checked ? '' : 'none');
}

function checkForm(){
	if( parseInt($("#is_all").val())==0&&$("input[name='cd_ids[]']").val()==undefined&&$("input[name='m_uids[]']").val()==undefined ){
		alert('请选择部门或者人员');
		return false;
	}

	if($("input[name='submitype']").val()=="preview"||$("input[name='submitype']").val()=="goback"){
		return true;
	}
	
	var cover_id = $('input[name="cover_id"]').val();
	if (cover_id == '') {
		alert('请选择封面图片！');
		return false;
	}

	

	var beginDate=$("input[name='begin_date']").val()+' '+$("input[name='begin_time']").val();  
	var endDate=$("input[name='end_date']").val()+' '+$("input[name='end_time']").val();   
	var d1 = new Date(beginDate.replace(/\-/g, "\/"));  
	var d2 = new Date(endDate.replace(/\-/g, "\/"));
	var nowTime="{rgmdate($timestamp, 'Y/m/d H:i')}";
	var d3 = new Date(nowTime.replace(/\-/g, "\/"));

	if(beginDate!=""&&endDate!=""&&d1 >=d2)  
	{  
		alert("考试结束时间不能早于考试开始时间！");  
		return false;  
	}
	if(d1<d3)  
	{  
		alert("考试开始时间不能早于当前时间！"); 
		return false;  
	}

	var paper_time=$("input[name='paper_time']").val();
	if(paper_time<0||!isInteger(paper_time)){
		alert("考试时长必须是大于0的整数"); 
		return false;  
	}

	
	var pass_score = $('input[name="pass_score"]').val();
	if(!isInteger(pass_score)||pass_score<0){
		alert("及格分数必须是大于0的整数"); 
		return false;  
	}

	
	if(pass_score > {$paper.total_score}){
		alert('及格分数不能大于总分');
		return false;
	}

	return true;
}

function noPubSubmit(type){
	$('form[name="submit_form"]').attr('target','_self');
	$("input[name='submitype']").val(type);
	if(type){
		if(type=='preview'){
			$('form[name="submit_form"]').attr('target','_blank');
		}
		$('form[name="submit_form"]').submit();
	}
}

function IsNum(s){
    if (s!=null && s!="")
    {
        return !isNaN(s);
    }
    return false;
}

// 是否正整数
function isInteger(obj) {
    return IsNum(obj) && obj%1 === 0;
}



</script>
