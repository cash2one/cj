{include file="$tpl_dir_base/header.tpl"}

<style type="text/css">
.datepicker-orient-top{
	z-index: 999999!important;
}
</style>

<div class="panel panel-default font12">
	<div class="panel-body">
		<form class="form-horizontal font12" role="form" id="edit-form"  method="post" action="{$formActionUrl}" data-ng-app="ng.poler.plugins.pc">
			<input type="hidden" name="formhash" value="{$formhash}" />

			<span id="m_uid_choose" style="display: none;"></span>

			<span id="cd_id_choose" style="display: none;"></span>
			<span id="users_choose" style="display: none;"></span>

			<div class="form-group">
				<label class="control-label col-sm-2" for="title">活动主题</label>
				<div class="col-sm-9">
					<input type="text" class="form-control form-small" id="title" name="title" placeholder="最多输入15个汉字"  maxlength="15"  required="required"/>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">开始时间</label>
				<script>
					init.push(function () {
						var options1 = {
							todayBtn: "linked",
							orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto',
							startDate: new Date()
						};
						$('#start_data').datepicker(options1);
						$('#start_time').timepicker({
							showMeridian:false
						});
					});
				</script>
				<div class="col-sm-9">
					<div class="input-daterange input-group" style="width: 600px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
						<div style="width: 300px">
							<input value="" required="required" type="text" class="input-sm form-control" id="start_data" name="start_time[data]" placeholder="开始日期" style="width: 150px"/>
							<input value="" required="required" type="text" class="input-sm form-control" id="start_time" name="start_time[time]" style="width: 150px"/>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">结束时间</label>
				<script>
					init.push(function () {
						var options2 = {
							todayBtn: "linked",
							orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto',
							startDate: new Date()
						};
						$('#end_data').datepicker(options2);
						$('#end_time').timepicker({
							showMeridian:false
						});
					});
				</script>
				<div class="col-sm-9">
					<div class="input-daterange input-group" style="width: 600px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
						<div style="width: 300px">
							<input value="" required="required" type="text" class="input-sm form-control" id="end_data" name="end_time[data]" placeholder="结束日期" style="width: 150px"/>
							<input value="" required="required" type="text" class="input-sm form-control" id="end_time" name="end_time[time]" style="width: 150px"/>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">报名截止</label>
				<script>
					init.push(function () {
						var options3 = {
							todayBtn: "linked",
							orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto',
							defaultDate: '+1d',
							startDate: new Date()
						};
						$('#cut_off_data').datepicker(options3);
						$('#cut_off_time').timepicker({
							showMeridian:false
						});
					});
				</script>
				<div class="col-sm-9">
					<div class="input-daterange input-group" style="width: 600px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
						<div style="width: 300px">
							<input  value="" type="text" class="input-sm form-control _datepicker-contorl" id="cut_off_data" name="cut_off_time[data]"   placeholder="截止日期" style="width: 150px" required="1" />
							<input  value="" type="text" id="cut_off_time" class="input-sm form-control" name="cut_off_time[time]"  style="width: 150px" />
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2" for="id_rights">发起人</label>
				<div class="col-sm-9">
					<div id="user_dep_container">
						<div class="row">

							<!-- angular 选人组件 begin -->
							<div class="angularjs-area" data-ng-controller="ChooseShimCtrl" >
								<a class="btn btn-defaul" data-ng-click="selectPerson('m_uid','selectedMuidCallBack')">选择发起人</a>
							</div>
							<!-- angular 选人组件 end -->

							<pre id="m_uid_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

							{*<div id="sponsor_users_container" class="col-sm-8">*}
								{*{include*}
								{*file="$tpl_dir_base/common_selector_member.tpl"*}
								{*input_type='radio'*}
								{*input_name='m_uid'*}
								{*selector_box_id='sponsor_users_container'*}
								{*allow_member=true*}
								{*allow_department=false*}
								{*}*}
							{*</div>*}
						</div>
					</div>
				</div>
			</div>
  
			<div class="form-group">
				<label class="control-label col-sm-2" for="id_rights">邀请内部人员</label>
				<div class="col-sm-9">
					<div>
						<button type="button" class="btn" id="all_btn">全公司</button>
						<input type="hidden" id="all_company" name="all_company" value="0">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<button type="button" class="btn" id="specified_btn">指定对象</button>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<button type="button" class="btn" id="cel_btn">取消邀请内部人员</button>
					</div>

					<div id="invite_user_dep_container" style="display: none">
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

								{*{include*}
								{*file="$tpl_dir_base/common_selector_member.tpl"*}
								{*input_type='checkbox'*}
								{*input_name_department='dp[]'*}
								{*selector_box_id='invite_deps_container'*}
								{*allow_member=false*}
								{*allow_department=true*}
								{*}*}
							</div>
						</div>
						<br>
						<div class="row">
							<label class="col-sm-2 text-right padding-sm">选择人员：</label>
							<div id="users_container" class="col-sm-8">

								<!-- angular 选人组件 begin -->
								<div class="angularjs-area" data-ng-controller="ChooseShimCtrl" >
									<a class="btn btn-defaul" data-ng-click="selectPerson('users_arr','selectedUsersCallBack')">选择被邀请人</a>
								</div>
								<!-- angular 选人组件 end -->

								<pre id="users_arr_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

								{*{include*}
								{*file="$tpl_dir_base/common_selector_member.tpl"*}
								{*input_type='checkbox'*}
								{*input_name='users[]'*}
								{*selector_box_id='invite_users_container'*}
								{*allow_member=true*}
								{*allow_department=false*}
								{*}*}
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2" for="address">活动地点</label>
				<div class="col-sm-9">
					<input type="text" class="form-control form-small" id="address" name="address" maxlength="100" />
				</div>
			</div>


            
			<div class="form-group">
				<label class="control-label col-sm-2" for="np">限制人数</label>
				<div class="col-sm-9">
					<input type="number" class="form-control form-small" id="np" placeholder="不填为无限制" name="np" maxlength="5" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">活动设置</label>
				<div class="col-sm-9">
					<input type="checkbox" id="outsiderto" name="outsiderto" />&nbsp;&nbsp;
					<label for="outsiderto">是否允许外部人员参与</label>
					<input type="hidden" value="0" id="outsider" name="outsider" >
				</div>
			</div>

			<div class="form-group" id="apply_message" style="display: none;">
				<label class="control-label col-sm-2" for="np">报名信息</label>
				<div class="col-sm-9">
					<div>
						<div id="buttondel" class="btn btn-primary unit-right">删除</div>

						<div class="form-group ui-apply-top" id="form-group-tag">
							<div class="btn btn-primary ui-margin-right tag">
								<div class="tagclass0">姓名</div>
								<input class="tagclass1" type="hidden" name="outfield[outname][name]" value="名字" />
								<input class="tagclass2" type="hidden" name="outfield[outname][require]" value="1" />
								<input class="tagclass3" type="hidden" name="outfield[outname][open]" value="1" />
								<input class="tagclass4" type="hidden" name="outfield[outname][type]" value="text" />
								<div class="ui-badge-cornernum"></div>
							</div>
							<div class="btn btn-primary ui-margin-right tag">
								手机号
								<input type="hidden" name="outfield[outphone][name]" value="手机号" />
								<input type="hidden" name="outfield[outphone][require]" value="1" />
								<input type="hidden" name="outfield[outphone][open]" value="1" />
								<input type="hidden" name="outfield[outphone][type]" value="number" />
								<div class="ui-badge-cornernum"></div>
							</div>
							<div class="btn btn-primary ui-margin-right tag">
								备注
								<input type="hidden" name="outfield[remark][name]" value="备注" />
								<input type="hidden" name="outfield[remark][require]" value="1" />
								<input type="hidden" name="outfield[remark][open]" value="1" />
								<input type="hidden" name="outfield[remark][type]" value="text" />
								<div class="ui-badge-cornernum"></div>
							</div>
							<button type="button" id="listadd" class="btn btn-default" style="border: #aaa 2px solid;width: 60px;text-align: center;color: #999;font-size: 30px;">
								+
							</button>
						</div>
					</div>
				</div>
			</div>

			<div id="tpl-area" style="display: none">
				<hr />

				<div id="tpl-in"></div>

				<div class="btn btn-primary ui-margin-right" style="margin-left: 190px;">
					<div id="suresure">添加</div>
				</div>
				<div class="btn btn-default ui-margin-right">
					<div id="celcel">取消</div>
				</div>
				<hr />
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2" for="content">活动内容</label>
				<div class="col-sm-9">
					{$ueditor_output}
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-6">
					<button id="push" type="submit" class="btn btn-primary">发布</button>
					&nbsp;&nbsp;
					<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
				</div>
			</div>
		</form>
	</div>
</div>

<script type="text/template" id="outsiderto-tpl">
	<div class="form-group">
		<label class="control-label col-sm-2" for="address">选项名称</label>
		<div class="col-sm-9">
			<input id="dialogtitle" value=" " name="dialogtitle" class="form-control form-small" type="text" placeholder="让报名人填写的信息" required="required" maxlength="5" />
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-2" for="address">是否必填</label>
		<div class="col-sm-9">
			<input type="checkbox" id="dialogcheckbox1" />
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-2">填写类型</label>
		<div class="col-sm-9" id="dialogradio">
			<label class="radio-inline">
				<input type="radio" name="radioadd" value="number"> 数字
			</label>
			<label class="radio-inline">
				<input type="radio" name="radioadd" value="text"> 文字
			</label>
		</div>
	</div>
</script>

<script>

	/* 选人组件 */
	var m_uid = [];

	// 发起人选择回调
	function selectedMuidCallBack(data, id) {

		if (data.length == 0 || data.length > 1) {
			alert('发起人有且只有一位');

			return false;
		}

		// 页面埋入 选择的值
		var m_uid_choose = '<input name="m_uid" value="' + data[0]['m_uid'] + '" type="hidden">';
		$('#m_uid_choose').html(m_uid_choose);

		// 展示
		var select_uid_name = '';
		select_uid_name += data[0]['m_username'];
		if (select_uid_name != '') {
			$('#m_uid_deafult_data').html(select_uid_name).show();
		} else {
			$('#m_uid_deafult_data').hide();
		}

		m_uid = data;
	}

	/* 邀请部分的选择 */
	// 选择部门回调
	var dep_arr = [];
	function selectedDepartmentCallBack(data){
		dep_arr = data;

		// 页面埋入 选择的值
		var cd_id_choose = '';
		var select_dep_name = '';
		for (var i = 0; i < data.length; i ++) {
			cd_id_choose += '<input name="dp[]" value="' + data[i]['id'] + '" type="hidden">';
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
	// 选择人员部分回调
	var users_arr = [];
	function selectedUsersCallBack(data){
		users_arr = data;

		// 页面埋入 选择的值
		var users_choose = '';
		var select_uid_name = '';
		for (var i = 0; i < data.length; i ++) {
			users_choose += '<input name="users[]" value="' + data[i]['m_uid'] + '" type="hidden">';
			select_uid_name += data[i]['m_username'] + ' ';
		}
		$('#users_choose').html(users_choose);

		// 展示
		if (select_uid_name != '') {
			$('#users_arr_deafult_data').html(select_uid_name).show();
		} else {
			$('#users_arr_deafult_data').hide();
		}
	}

	var d_del = '<div id="dodelete" class="ui-badge-cornernum fa fa-minus-circle"></div>';

	$(function () {

		// 邀请人员
		$('#specified_btn').on('click',function(){
			$('#invite_user_dep_container').show();
			$('#all_company').val(0);
			$(this).addClass('btn-primary');
			$('#all_btn').removeClass('btn-primary');
		});
		// 邀请全公司
		$('#all_btn').on('click',function(){
			$('#invite_user_dep_container .photo-scrollable').html('');
			$('#all_company').val(1);
			$('#invite_user_dep_container').hide();
			$(this).addClass('btn-primary');
			$('#specified_btn').removeClass('btn-primary');
		});
		// 取消邀请
		$('#cel_btn').on('click', function() {
			$('#all_company').val(1);
			$('#invite_user_dep_container .photo-scrollable').html('');
			$('#invite_user_dep_container').hide();
			$('#all_btn').removeClass('btn-primary');
			$('#specified_btn').removeClass('btn-primary');
		});

		//隐藏/显示报名信息
		$("#outsiderto").on('click', function() {
			if (this.checked) {
				$('#apply_message').show();
			} else {
				$('#apply_message').hide();
				$('#tpl-area').hide();
			}
		});

		//按钮删除
		$('#buttondel').on('click', function () {
			var $t = $(this);
			if ($t.hasClass('buttonsure')) {
				$('.tag1').removeClass('canbedel');
				$t.html('删除').removeClass('buttonsure');
				$('#listadd').show();
				$('.tag1').find('div[id=dodelete]').remove();
			} else {
				$t.html('确定').addClass('buttonsure');
				$('#listadd').hide();
				$('#tpl-area').hide();
				$('.tag1').append(d_del).addClass('canbedel');
			}
		});

		$('body').on('click', '.canbedel', function() {
			$(this).remove();
		});

		//允许外部人参与赋值
		$("#outsiderto").on('click', function() {
			if (this.checked) {
				$("#outsider").val("1");
			}else{
				$("#outsider").val("0");
			}
		});

		//隐藏或显示添加按钮界面
		$("#listadd").on('click', function() {
			$('#tpl-in').html(txTpl('outsiderto-tpl'));
			$('#tpl-area').toggle();
			$('#dialogtitle').val('');
		});

		//添加按钮界面取消
		$("#celcel").on('click', function() {
			$('#tpl-in').html(txTpl('outsiderto-tpl'));
			$('#tpl-area').toggle();
		});

		//确认添加按钮
		$("#suresure").on('click', function() {
			if (m_uid.length == 0 || m_uid.length > 1) {
				alert('发起人有且只有一位');

				return false;
			}

			var title = $("#dialogtitle").val();
			if ($.trim(title) == '') {
				$("#dialogtitle").val("");
				$("#dialogtitle").focus();
				return false;
			}
			var dialogcheckbox1 = "0";
			if ($('#dialogcheckbox1').prop('checked')) {
				dialogcheckbox1 = 1;
			}

			var dialogcheckbox2 = "1";
			var radio = $("#dialogradio").find('input[type=radio]:checked').val();
			if (radio == '' || radio == undefined) {
				$("#dialogradio").focus();
				return false;
			}
			var tag = $('.tag').eq(0).clone().removeClass('tag').addClass('tag1');
			tag.find('.tagclass0').text($.trim(title));
			tag.find('.tagclass1').attr('value', $.trim(title));
			tag.find('.tagclass2').attr('value', dialogcheckbox1);
//			tag.find('.tagclass3').attr('value', dialogcheckbox2);
			tag.find('.tagclass4').attr('value', radio);
			$('#listadd').before(tag);
			set_button();
			$('#tpl-in').html(txTpl('outsiderto-tpl'));
			$('#tpl-area').toggle();
		});

		$('form').submit(function () {

			if (m_uid.length == 0 || m_uid.length > 1) {
				alert('发起人有且只有一位');

				return false;
			}

		});
	});

	function set_button() {
		$('.tag1').each(function(index, self) {
			$(self).find('.tagclass1').attr('name', 'outfield[' + index + '][name]');
			$(self).find('.tagclass2').attr('name', 'outfield[' + index + '][require]');
			$(self).find('.tagclass3').attr('name', 'outfield[' + index + '][open]');
			$(self).find('.tagclass4').attr('name', 'outfield[' + index + '][type]');
		});
	}

</script>

{include file="$tpl_dir_base/footer.tpl"}
