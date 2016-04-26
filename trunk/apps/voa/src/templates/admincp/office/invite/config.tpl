{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
	<div class="panel-body">
		<ul class="nav nav-tabs nav-tabs-xs">
			<li class="active">
				<a id="id_introduction" href="#dashboard-recent-threads" data-toggle="tab">企业号介绍</a>
			</li>
			<li>
				<a id="id_invite" href="#dashboard-recent-comments" data-toggle="tab">邀请设置</a>
			</li>
		</ul>
	</div>

{*企业号介绍*}
	<div id="introduction">
		<form class="form-horizontal font12" role="form" method="post" action="{$formActionUrl}">
			<input type="hidden" name="formhash" value="{$formhash}" />

			<div class="form-group">
				<label for="logo" class="col-sm-2 control-label">企业号Logo：</label>
				<div class="col-sm-10" id="logo">
					{cycp_upload
						inputname='logo[at_id]'
					}
					{if $data['logo']}
						<a href="{$logo}" target="_Blank">
							<img src="{$logo}" width="80px" height="80px" style="margin-top: 15px;" />
						</a>
					{/if}
				</div>
			</div>
			{*缓存的logo id，如果没有新上传图片就会上传这里的id号判断本地没有上传新的logo*}
			<input id="setting_logo" value="{$data['logo']}" type="hidden">

			<div class="form-group">
				<label class="col-sm-2 control-label">简介：</label>
				<div class="col-sm-8">
					{$ueditor_output}
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-primary" id="submit_two">保存</button>
					&nbsp;&nbsp;
					<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
				</div>
			</div>

			<input type="hidden" name="is_introduction" value="1">
		</form>
	</div>
{*邀请设置*}
	<div id="invite" style="display: none;">
		<form class="form-horizontal font12" role="form" method="post" action="{$formActionUrl}" data-ng-app="ng.poler.plugins.pc">
			<input type="hidden" name="formhash" value="{$formhash}" />
			
			<span id="m_uid_choose" style="display: none;"></span>
			<span id="cd_id_choose" style="display: none;"></span>
			
			<div class="form-group">
				<label for="mr_name" class="col-sm-2 control-label">可发送邀请人员：</label>
				<div class="col-sm-10">

					<!-- angular 选人组件 begin -->
					<div class="angularjs-area" data-ng-controller="ChooseShimCtrl" >
						<a class="btn btn-defaul" data-ng-click="selectPerson('m_uid','selectedMuidCallBack')">选择人员</a>
					</div>
					<!-- angular 选人组件 end -->

					<pre id="m_uid_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

					{*{include*}
					{*file="$tpl_dir_base/common_selector_member.tpl"*}
					{*input_type='checkbox'*}
					{*input_name='m_uid[]'*}
					{*selector_box_id='primary_id'*}
					{*default_data=$data['primary_id']*}
					{*allow_member=true*}
					{*allow_department=false*}
					{*}*}
				</div>
			</div>
			<div class="form-group">
				<label for="short_paragraph" class="col-sm-2 control-label">邀请语：</label>
				<div class="col-sm-5" style="position: relative;">
					<textarea
						maxlength="80"
						style="	height: 100px;
								margin: 0px 57.75px 0px 0px;
								width: 100%;
								resize:none;
								"
						class="form-control"
						id="short_paragraph"
						name="short_paragraph"
						placeholder="邀请语， 如：Hi,你好！我在微信企业号建立了XXX企业号，邀请你加入。最多输入80字"
						required="required"
					>{$data['short_paragraph']|escape}</textarea>

					<div id="short_paragraph_length"
					     style="  position: absolute;
								  right: 23px;
								  top: 84px;">
					</div>
				</div>
			</div>
			<div class="form-group">
				<label for="mr_address" class="col-sm-2 control-label">审批设置：</label>
				<div class="col-sm-10">
					<input type="checkbox" id="is_approval_checkbox"/>&nbsp;&nbsp;
					<label for="is_approval_checkbox">是否需要审批&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（无须审批时邀请人员进入默认部门）</label>
					<input type="hidden" value="{$data['is_approval']}" id="is_approval" name="is_approval" >
				</div>
			</div>
			<div class="form-group" id="default_department">
				<label class="col-sm-2 control-label">默认部门：</label>
				<div class="col-sm-10">

					<!-- angular 选人组件 begin -->
					<div class="angularjs-area " data-ng-controller="ChooseShimCtrl">
						<a class="btn btn-defaul" data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')">选择部门</a>
					</div>
					<!-- angular 选人组件 end -->

					<pre id="dep_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

					{*{include*}
					{*file="$tpl_dir_base/common_selector_member.tpl"*}
					{*input_type='checkbox'*}
					{*input_name_department='cd_id[]'*}
					{*selector_box_id='cd_id'*}
					{*default_data=$data['cd_id']*}
					{*allow_member=false*}
					{*allow_department=true*}
					{*}*}
				</div>
			</div>
			<div class="form-group">
				<label for="overdue" class="col-sm-2 control-label">二维码/链接有效期限：</label>
				<div class="col-sm-10">
					<input type="text" value="{$data['overdue']['tian']}" class="form-control form-small" name="overdue[tian]" id="voerdue_tian" style=" text-align: center; width: 60px; float: left;" />
					<label for="voerdue_tian" style="float: left; margin-top: 15px;">（天）</label>
					<script>
						init.push(function () {
							var options3 = {
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto',
								defaultDate: '+1d',
								startDate: new Date()
							};
							$('#voerdue_shi').timepicker({
								showMeridian:false
							});
						});
					</script>
					<input  type="text" value="{$data['overdue']['shi']}" id="voerdue_shi" name="overdue[shi]" class="input-sm form-control" style="width: 80px; text-align: center; float:left;" />
					<label for="voerdue_shi" style="float: left; margin-top: 15px;">（小时：分钟）</label>
				</div>
			</div>
			<div class="form-group">
				<label for="custom_field" class="col-sm-2 control-label">通讯录字段信息设置：</label>
				<p style="margin-top: 8px; color: #AEAEAE;">提示：如需增加字段信息请在“人员管理>设置属性”中进行设置</p>
				<div id="btn_show" style="margin-left: 188px;">
					<button class="btn btn-info active">姓名	</button>
					<button class="btn btn-info active">性别</button>
					<button class="btn btn-info active">邮箱</button>
					<button class="btn btn-info active">手机号</button>
					<button class="btn btn-info active">微信号</button>
					<button class="btn btn-info active">职位</button>
				</div>
				<div class="col-sm-10" id="custom" style="position: absolute;left: 166px;bottom: 75px;">
					{foreach $custom as $key => $val}
						<button type="button" class="btn btn-default" id="{$key}">{$val['desc']}</button>
					{/foreach}
					{*数据库自定义字段设置*}
					{if $data['custom']}
					{foreach $data['custom'] as $k => $v}
						<div id="button_{$k}">
							<input type="hidden" name="custom[{$k}][desc]" value="{$v['desc']}">
							<input type="hidden" name="custom[{$k}][required]" value="{$v['required']}">
						</div>
					{/foreach}
					{/if}
				</div>
			</div>
			<script type="text/javascript">
				$(function () {
					// 自定义字段区域
					var custom = $('#custom');

					/**
					 * 自定义按钮的是否选中（状态class切换），上传的值写入
					 */
					$('#custom').find('button').on('click', function () {
						// 点击的按钮
						var button_onclick = $(this);
						// 如果是固定的设置，则直接返回
						if (button_onclick.attr('id') == 'name') {
							alert('此设置必须存在');
							return false;
						}
						// 如果没有选择 else 被选择
						if (button_onclick.hasClass('btn-default')) {
							// 插入div区域，区分不同的按钮值
							var button_div = '<div id="button_' + button_onclick.attr('id') + '"></div>';
							custom.append(button_div);

							/**
							 *  div区域插入数据
							 */
							var button_id = '#button_' + button_onclick.attr('id'); // 要插入的div id
							// 询问是否必填
							var required = 0;
							if (confirm('是否必填？')){
								required = 1;
							}
							var hidden_input = '<input type="hidden" name="custom[' + button_onclick.attr('id') + '][desc]" value="' + button_onclick.html() + '">';
							hidden_input+= '<input type="hidden" name="custom[' + button_onclick.attr('id') + '][required]" value="' + required + '">';
							$(button_id).append(hidden_input);

							// 更换class
							button_onclick.removeClass('btn-default');
							button_onclick.addClass('btn-primary');
						} else {
							/**
							 * 如果被选择，则是取消选择
							 */
							if (button_onclick.hasClass('btn-primary')) {
								// 将要被移出的div
								var remove_div_id = '#button_' + button_onclick.attr('id');
								$(remove_div_id).remove();

								// 更换class
								button_onclick.removeClass('btn-primary');
								button_onclick.addClass('btn-default');
							}
						}
					});

					/**
					 * 页面数据库自定义设置数据初始化（按钮的状态和上传值初始化）
					 */
					var sql_custom_div = $('#custom').find('div');
					sql_custom_div.each(function (i,v) {
						// 和初始化的按钮匹配，更改初始化按钮的状态 btn-default->btn-primary
						var btn_id = $(v).attr('id').substr(7); // 和按钮匹配的ID
						custom.find('#' + btn_id).removeClass('btn-default').addClass('btn-primary'); // 更换状态
					});

					/**
					 * 页面初始化 判断隐藏的提交值  是否合法存在
					 */
					var hide_div = $("div[id^='button_']");
					var custom_button_id = $('#custom > button');
					hide_div.each(function (i0, v0) {
						var is_set = 0;
						custom_button_id.each(function (i1, v1) {
							if ($(v0).attr('id').substr(7) == $(v1).attr('id')) {
								is_set = 1;
							}
						});
						if (is_set != 1) {
							$('#button_' + $(v0).attr('id').substr(7)).remove();
						}
					});
				});
			</script>

			<div class="form-group"  style="  margin-top: 70px;">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-primary" id="submit_one">保存</button>
					&nbsp;&nbsp;
					<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
				</div>
			</div>

			<input type="hidden" name="is_invite" value="1">
		</form>
	</div>

</div>

<script type="text/javascript">
//	页面初始化

	/* 选人组件 */
	var m_uid = [];
	var dep_arr = [];
	var m_uid_choose = '';
	var cd_id_choose = '';

	/* 选人组件默认值 */
	// 部门默认值
	dep_arr = {$data['cd_id']};
	if (dep_arr.length != 0) {
		cd_id_choose = '';
		var select_dep_name = '';
		for (var i = 0; i < dep_arr.length; i ++) {
			cd_id_choose += '<input name="cd_id[]" value="' + dep_arr[i]['id'] + '" type="hidden">';
			select_dep_name += dep_arr[i]['name'] + ' ';
		}
		$('#cd_id_choose').html(cd_id_choose);

		// 展示
		if (select_dep_name != '') {
			$('#dep_deafult_data').html(select_dep_name).show();
		} else {
			$('#dep_deafult_data').hide();
		}
	}
	// 可审批人默认值
	m_uid = {$data['primary_id']};
	if (m_uid.length != 0) {
		m_uid_choose = '';
		var select_uid_name = '';
		for (var i = 0; i < m_uid.length; i ++) {
			m_uid_choose += '<input name="m_uid[]" value="' + m_uid[i]['m_uid'] + '" type="hidden">';
			select_uid_name += m_uid[i]['m_username'] + ' ';
		}
		$('#m_uid_choose').html(m_uid_choose);

		// 展示
		if (select_uid_name != '') {
			$('#m_uid_deafult_data').html(select_uid_name).show();
		} else {
			$('#m_uid_deafult_data').hide();
		}
	}

	// 可发送人员选择回调
	function selectedMuidCallBack(data, id) {
		m_uid = data;

		// 页面埋入 选择的值
		m_uid_choose = '';
		var select_uid_name = '';
		for (var i = 0; i < data.length; i ++) {
			m_uid_choose += '<input name="m_uid[]" value="' + data[i]['m_uid'] + '" type="hidden">';
			select_uid_name += data[i]['m_username'] + ' ';
		}
		$('#m_uid_choose').html(m_uid_choose);

		// 展示
		if (select_uid_name != '') {
			$('#m_uid_deafult_data').html(select_uid_name).show();
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
			cd_id_choose += '<input name="cd_id[]" value="' + data[i]['id'] + '" type="hidden">';
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

	$(function () {
		$('#btn_show').on('click', function () {
			return false;
		});

		// '邀请设置'和'企业号介绍'切换显示
		$('#id_invite').on('click', function () {
			$('#invite').show();
			$('#introduction').hide();
		});
		$('#id_introduction').on('click', function () {
			$('#introduction').show();
			$('#invite').hide();
		});

		// '是否需要邀请'的勾选状态和值
		$('#is_approval_checkbox').on('click', function () {
			if (this.checked) {
				$('#is_approval').val(1);
				$('#default_department').hide();
			} else {
				$('#is_approval').val(0);
				$('#default_department').show();
			}
		});

		// 当值为1时，'是否需要邀请'复选框为选中状态
		if ($('#is_approval').val() == 1) {
			$('#is_approval_checkbox').prop('checked', 'checked');
		}

		// 当'是否需要邀请'是勾选状态时'默认部门'隐藏，反之……
		if ($('#is_approval').val() == 1) {
			$('#default_department').hide();
		} else {
			$('#default_department').show();
		}

		// 一开始的字数显示
		var strat_length = $('#short_paragraph').val().length;
		$('#short_paragraph_length').html(strat_length + '/80');

		// 输入字数显示
		$('#short_paragraph').bind('input propertychange', function () {
			var length = $('#short_paragraph').val().length;
			$('#short_paragraph_length').html(length + '/80');
		});
	});

// 页面提示
	$(function() {
		// 当邀请设置提交时,判断一些值是否合法
		$('#submit_one').on('click', function(){
//			if ($.trim($('#primary_id .js-contacts-form-row').html()) == '') {
			if (m_uid == '') {
				alert('可邀请人不能为空');
				return false;
			}
			if ($('#is_approval').val() == 0) {
//				if ($.trim($('#cd_id .js-contacts-form-row').html()) == '') {
				if (dep_arr == '') {
					$('#is_approval').val(0);
					$('#default_department').show();
					alert('默认部门不能为空');
					return false;
				}
			}
			if ($('#short_paragraph').val() == '') {
				alert('邀请语不能为空');
				$('#short_paragraph').focus();
				return false;
			}
			return true;
		});

		$('#submit_two').on('click', function () {
			var content = $.trim(ue.getContentTxt());
			if (content == '') {
				ue.setContent('');
				ue.focus();
				alert('简介不能为空');
				return false;
			}
			var logo = $('#logo').find('input').eq(0).val();
			if (logo == '') {
				var setting_logo = $('#setting_logo').val();
				$('#logo').find('input').eq(0).val(setting_logo);
			}
			return true;
		});

	});
</script>
{include file="$tpl_dir_base/footer.tpl"}
