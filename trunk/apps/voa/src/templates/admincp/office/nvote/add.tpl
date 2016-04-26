{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-body">
		<form class="form-horizontal font12" role="form" action="{$form_action_url}" method="post" data-ng-app="ng.poler.plugins.pc">
			<input type="hidden" name="formhash" value="{$formhash}"/>

			<span id="m_uid_choose" style="display: none;"></span>
			<span id="cd_id_choose" style="display: none;"></span>

			<div class="form-group">
				<label class="control-label col-sm-2" for="id_title">投票标题：</label>

				<div class="col-sm-5">
					<input type="text" id="id_title" class="form-control form-small" name="nvote[subject]"
					       placeholder="最多输入30个汉字" value="" maxlength="30" required="required"/>
				</div>
				{cycp_upload
				inputname='nvote[at_id]'
				}
			</div>
			<div id="options_container">
				<div class="form-group div_options">
					<label class="control-label  col-sm-2">投票选项：</label>

					<div class="col-sm-5">
						<div class="div_group">
							<input type="text" class="form-control form-small option" name="options[0][option]"
							       placeholder="最多输入30个汉字" value="" maxlength="30" required="required"/>
						</div>
					</div>
					{cycp_upload
					inputname='options[0][at_id]'
					}
				</div>
				<div class="form-group div_options">
					<label class="control-label  col-sm-2">投票选项：</label>

					<div class="col-sm-5">
						<div class="div_group">
							<input type="text" class="form-control form-small option" name="options[1][option]"
							       placeholder="最多输入30个汉字" value="" maxlength="30" required="required"/>
						</div>
					</div>
					{cycp_upload
					inputname='options[1][at_id]'
					}
				</div>
				<div class="form-group">
					<label class="control-label  col-sm-2"></label>
					<a class="col-sm-2" href="javascript:;" id="a_add_option">添加一个选项</a>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2" for="id_title">投票设置：</label>

				<div class="col-sm-10 form-inline">
					<label class="vcy-label-none" for="id_end_time">结束日期：</label>
					<script>
						init.push(function () {
							var options2 = {
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto',
								defaultDate: '+1d',
								startDate: new Date()
							};
							$('#id_end_time').datepicker(options2);
							$('#id_time').timepicker({
								showMeridian: false
							});
						});
					</script>
					<div class="input-daterange input-group" style="display: inline-table;vertical-align:middle;">
						{$end_time = $timestamp + 86400}
						<input type="text" class="input-sm form-control" id="id_end_time" name="nvote[end_date]"
						       placeholder="结束日期" value="{rgmdate($end_time,'Y-m-d')}"/>
					</div>

					<input type="text" id="id_time" class="input-sm form-control" name="nvote[end_time]"/>
				</div>

			</div>

			<div class="form-group">
				<label class="control-label col-sm-2" for="id_title"></label>

				<div class="col-sm-10 form-inline">
					<label class="vcy-label-none" for="id_cab_realname_author">选项：</label>
					<label class="radio-inline">
						<input type="radio" name="nvote[is_single]" class="px" value="1" checked="checked">
						<span class="lbl">单选</span>
					</label>
					<span class="space"></span>
					<label class="radio-inline">
						<input type="radio" class="px" name="nvote[is_single]" value="2">
						<span class="lbl">多选</span>
					</label>
				</div>

			</div>

			<div class="form-group">
				<label class="control-label col-sm-2" for="id_title"></label>

				<div class="col-sm-10 form-inline">
					<label class="vcy-label-none" for="id_cab_realname_author">类型：</label>
					<label class="radio-inline">
						<input type="radio" name="nvote[is_show_name]" value="1" class="px" checked="checked">
						<span class="lbl">实名投票</span>
					</label>
					<span class="space"></span>
					<label class="radio-inline">
						<input type="radio" class="px" name="nvote[is_show_name]" class="px" value="2">
						<span class="lbl">匿名投票</span>
					</label>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2" for="id_title"></label>

				<div class="col-sm-10 form-inline">
					<label class="vcy-label-none" for="id_cab_realname_author">允许重复投票：</label>
					<label class="radio-inline">
						<input type="radio" name="nvote[is_repeat]" value="2" class="px" checked="checked">
						<span class="lbl">不允许</span>
					</label>
					<span class="space"></span>
					<label class="radio-inline">
						<input type="radio" class="px" name="nvote[is_repeat]" class="px" value="1">
						<span class="lbl">允许</span>
					</label>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2" for="id_title"></label>

				<div class="col-sm-10 form-inline">
					<label class="pull-left vcy-label-none" for="id_cab_realname_author">范围：</label>

					<div class="pull-left" id="contact_container">

						<!-- angular 选人组件 begin -->
						<div class="angularjs-area" data-ng-controller="ChooseShimCtrl" >
							<a class="btn btn-defaul" data-ng-click="selectPerson('m_uid','selectedMuidCallBack')">选择人员</a>
						</div>
						<!-- angular 选人组件 end -->

						<pre id="m_uid_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

						<br />

						<!-- angular 选人组件 begin -->
						<div class="angularjs-area " data-ng-controller="ChooseShimCtrl">
							<a class="btn btn-defaul" data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')">选择部门</a>
						</div>
						<!-- angular 选人组件 end -->

						<pre id="dep_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

						{*{include*}
						{*file="$tpl_dir_base/common_selector_member.tpl"*}
						{*input_type='checkbox'*}
						{*input_name='m_uids[]'*}
						{*selector_box_id='contact_container'*}
						{*input_name_department='cd_ids[]'*}
						{*allow_member=true*}
						{*allow_department=true*}
						{*}*}
					</div>
				</div>
			</div>


			<div class="form-group">
				<label class="control-label col-sm-2" for="id_title"></label>

				<div class="col-sm-10 form-inline">
					<label class="col-sm-3 checkbox-inline">
						<input class="px" type="checkbox" name="nvote[is_show_result]" value="2">
						<span class="lbl">投票之后即可查看结果</span>
					</label>
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-6">
					<button type="submit" id="btn_submit" class="btn btn-primary">添加</button>
					&nbsp;&nbsp;
					<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
				</div>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">

	/* 选人组件 */
	var m_uid = [];
	var dep_arr = [];
	var m_uid_choose = '';
	var cd_id_choose = '';

	// 可发送人员选择回调
	function selectedMuidCallBack(data, id) {
		m_uid = data;

		// 页面埋入 选择的值
		m_uid_choose = '';
		var select_uid_name = '';
		for (var i = 0; i < data.length; i ++) {
			m_uid_choose += '<input name="m_uids[]" value="' + data[i]['m_uid'] + '" type="hidden">';
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

	$(function () {
		//提交时判断是否有重复的选项
		$('#btn_submit').click(function () {
			//遍历判断选项中的值
			var $inputs = $('#options_container input[type=text]');
			var $input_size = $inputs.size();
			for (var $i = 0; $i < $input_size; $i++) {
				for (var $j = $i + 1; $j < $input_size; $j++) {
					if ($inputs.eq($i).val() == $inputs.eq($j).val()) {
						alert('不能有重复的选项');
						return false;
					}
				}
			}
			//判断参与人选择
			if (dep_arr.length < 1 && m_uid.length < 1) {
				alert('请选择参与人或参与部门')
				return false;
			}
		});

		//添加选项
		$('#a_add_option').click(function () {
			var option = $('div.div_options').eq(0).clone();
			option.find('input').val('');
			option.find('.span_delete').remove();
			option.find('._showdelete').hide();
			option.find('._showimage').html('');
			option.find('.control-label').text('投票选项：');
			$(this).parents('.form-group').before(option);
			//每添加一个重新编下标
			$('div.div_options input.option').each(function (index, self) {
				$(self).attr('name', 'options[' + index + '][option]');
			});
			$('div.div_options input[type=hidden]').each(function (index, self) {
				$(self).attr('name', 'options[' + index + '][at_id]');
			});
			var html_del = '<span class="input-group-addon span_delete"><i class="fa fa-times"></i></span>';
			$('div.div_options').each(function () {
				//判断是否添加过删除按钮
				if ($(this).find('.span_delete').size() == 0) {
					$(this).find('.option').after(html_del);
					$(this).find('.div_group').addClass('input-group');
					//绑定删除选项事件
					$(this).find('.span_delete').click(function () {
						$(this).parents('div.div_options').remove();
						if ($('div.div_options').size() < 3) {
							$('div.div_options .span_delete').remove();
							$('div.div_options .input-group').removeClass('input-group');
						}
					});
				}
			});
		});
	});
</script>
{include file="$tpl_dir_base/footer.tpl"}
