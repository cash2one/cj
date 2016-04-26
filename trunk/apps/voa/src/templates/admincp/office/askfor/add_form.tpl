{include file="$tpl_dir_base/header.tpl"}
<link rel="stylesheet" href="{$CSSDIR}askfor.css">

<!-- mobile visual start -->
<div class="askfor-mobile">
	<ul class="askfor-mobile-ul js-visual-box">
		<li class="askfor-mobile__li">
			<span class="askfor-mobile__title">流程名称</span>
			<span id="mobile_title" class="js-askfor-visual"></span>
		</li>
		<li class="askfor-mobile__li">
			<span class="askfor-mobile__title">审批内容</span>
			<textarea class="askfor-mobile__textarea" maxlength="15" ></textarea>
		</li>

		<li class="askfor-mobile__li">
			<span class="askfor-mobile__title" style="font-size: 10px;">抄送人:</span>
			<span class="js-askfor-visual" id="mobile_copy_data"></span>
		</li>

		<li>
			<ul class="js-askfor-clone askfor-mobile-child-ul"></ul>
		</li>

		{*自定义字段显示*}
		<div id="mobile_custom_area"></div>

		<li class="askfor-mobile__li js-askfor-visual askfor-mobile__upload" ask-show="1" ask-visual="upload">
			<span class="askfor-mobile__img"></span>
		</li>

		{*审批人显示*}
		<li class="askfor-mobile__li">
			<span class="askfor-mobile__title" style="font-size: 10px;">一级审批人:</span>
			<span class="js-askfor-visual" id="mobile_approver_0"></span>
		</li>

		<div id="mobile_approver_area"></div>

		<li class="askfor-mobile__li askfor-mobile__last">
			<a href="javascript:void(0)" class="askfor-mobild__btn">提交</a>
		</li>
	</ul>
</div>

<!-- mobile visual end -->
<div class="panel panel-default font12 askfor-parent" >
	<div class="panel-body">
		<form class="form-horizontal font12" role="form" id="add-form" data-ng-app="ng.poler.plugins.pc">

			<div class="form-group">
				<div class="row col-sm-8">
					<label class="control-label col-sm-2 text-danger askfor-label" for="temp_title">流程名称*</label>

					<div class="col-sm-3 askfor-group-40">
						<input  type="text"
						        class="form-control js-askfor-input form-small askfor-input" id="temp_title"
						        placeholder="最多输入15个汉字" maxlength="15"
						        required="required" value="{$temp_default_data['name']}"/>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="row col-sm-8">
					<label class="control-label col-sm-2 askfor-label" for="orderid">序号</label>

					<div class="col-sm-3 askfor-input-group">
						<input type="text" class="form-control  form-small" id="orderid"
						       placeholder="请输入数字" maxlength="3" value="{$temp_default_data['orderid']}"/>
					</div>
				</div>
			</div>

			<div class="form-group">
				<div class="row col-sm-8">
					<label class="control-label col-sm-2 askfor-label">权限范围</label>

					<div class="col-sm-3 askfor-group-40">

						<!-- angular 选人组件 begin -->
						<div class="angularjs-area " data-ng-controller="ChooseShimCtrl">
							<a class="btn btn-defaul" data-ng-click="selectDepartment('dep_arr','selectedDepartmentCallBack')">选择部门(没选为全公司)</a>
						</div>
						<!-- angular 选人组件 end -->

					</div>
				</div>
			</div>

			<pre id="dep_deafult_data" style="display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

			<hr>

			<div class="form-group">
				<div class="row col-sm-8">
					<label class="control-label col-sm-2 askfor-label">默认抄送人</label>

					<div class="col-sm-3 askfor-group-40">

						<!-- angular 选人组件 begin -->
						<div class="angularjs-area" data-ng-controller="ChooseShimCtrl" >
							<a class="btn btn-defaul" data-ng-click="selectPerson('copy_arr','selectedCopyPersonCallBack')">选取抄送人</a>
						</div>
						<!-- angular 选人组件 end -->

					</div>
				</div>
			</div>

			<pre id="copy_deafult_data" style="display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

			<hr>
			<div id="customer_columns" class="clearfix">

				<div class="askfor-custom-header">
					<ul class="askfor-custom-ul">
						<li class="askfor-custom__li">
							<span>类型</span>
							<select class="askfor-custom-select js-custom-select" id="custom_type">
								<option data-couple="false" data-type="text" value="1">文本</option>
								{*<option data-couple="false" data-type="number" value="2">数字</option>*}
								<option data-couple="true" data-type="time" value="3">日期</option>
								<option data-couple="true" data-type="time" value="4">时间</option>
								<option data-couple="true" data-type="time" value="5">日期、时间</option>
							</select>
						</li>
						<li class="askfor-custom__li">
							<a id="add_colums" class="askfor-custom__btn">添加字段</a>
						</li>
					</ul>
				</div>

				{foreach $custom as $id => $data}
					<div id="custom_cols_{$id}" class="form-group">
						<div class="row col-sm-8">
							<label class="control-label col-sm-2 askfor-label">
								{if $data['type'] == 1}
									文本
								{elseif $data['type'] == 2}
									数字
								{elseif $data['type'] == 3}
									日期
								{elseif $data['type'] == 4}
									时间
								{elseif $data['type'] == 5}
									日期.时间
								{/if}
							</label>

							<div class="col-sm-3 askfor-group-40"><input type="text" id="custom_cols_input_{$id}" value="{$data['name']}"
							                                             class="form-control form-small askfor-input"
							                                             data-input="{$data['type']}" placeholder="字段名称,最多输入8个汉字"
							                                             maxlength="8" required="required"></div>
							<label style="padding-top:7px;">是否必填 </label>&nbsp; <input type="checkbox" {if $data['required'] == 1}checked="checked"{/if}></div>
						<div class="btn btn-danger" onclick="delete_column_cols({$id})">删除</div>
					</div>
				{/foreach}

			</div>

			<hr>
			<div id="approver_columns" class="js-askfor-approver">
				<div class="form-group col-sm-8" id="ngclick_0">
					<label class="control-label col-sm-2 askfor-label askfor-approver  text-danger">一级审批人*</label>

					<div class="col-sm-3" style=" margin-right: 10px;">
						<div class="col-sm-3" style="margin-top: 8px;" data-diag="one">

							<!-- angular 选人组件 begin -->
							<div class="angularjs-area" data-ng-controller="ChooseShimCtrl" >
								<a class="btn btn-defaul" data-ng-click="selectPerson('user_arr[0]','selectedPersonCallBack', 0)">选取审批人</a>
							</div>
							<!-- angular 选人组件 end -->

						</div>
					</div>

					<pre id="approver_deafult_name_0" style="height: 47px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

				</div>
				<label class="col-sm-12  askfor-approver text-danger askfor-label-w120" style="padding-top: 5px;" >(审批人不能重复)</label>
				<div class="col-sm-2">
					<a href="javascript:void(0);" role="button" style="margin-top: 8px;" class="btn btn-defaul askfor-approver__add-btn"
					   onclick="add_approver()">添加审核人</a>
				</div>
			</div>

		</form>
		<form class="form-horizontal font12" id="approver_activite">
		</form>

	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-9">
			<a onclick="add_temp()" class="btn btn-primary">保存</a>
			&nbsp;&nbsp;
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</div>

<script type="text/javascript">

	// 左边手机预览
	// 标题输入
	$('#temp_title').bind('input propertychange', function () {
		$('#mobile_title').html($('#temp_title').val());
	});
	$('#mobile_title').html($('#temp_title').val());

	var customIndex = 0;
	// 自定义字段id
	var custom_id = 0;

	/*选人组件*/
	var user_arr  = [];
	user_arr[0] = [];
	var copy_arr = [];
	var _cur_index = 0;
	/*选部门组件*/
	var dep_arr = [];

	// 等级
	var numIndex = { 1: '一', 2: '二', 3: '三', 4: '四', 5: '五', 6: '六', 7: '七', 8: '八', 9: '九', 10: '十' };
	// 编辑时的 抄送人 审批人 默认数据
	var _cur_index_for = 1;

	if ("{$act}" == 'edit') {
		// 抄送人默认数据
		copy_arr = {$copy};
		// 审批人默认数据
		var user_temp = [];
		user_temp = {$approver_default_data};
		// 适用部门默认数据
		dep_arr = {$dep_arr};

		// 展示选中的抄送人
		if (copy_arr != '') {
			var copy_deafult_name = '';
			for (var q = 0;q < copy_arr.length; q ++ ) {
				copy_deafult_name += copy_arr[q]['m_username'] + ' ';
			}
			$('#copy_deafult_data').html(copy_deafult_name).show();
		} else {
			$('#copy_deafult_data').html(copy_deafult_name).hide();
		}

		// 展示选中的部门
		if (dep_arr != '') {
			var select_dep_name = '';
			for (var i = 0; i < dep_arr.length; i ++) {
				select_dep_name += dep_arr[i]['name'] + ' ';
			}
			$('#dep_deafult_data').html(select_dep_name).show();
		} else {
			$('#dep_deafult_data').hide();
		}

		// 自定义字段 手机端预览
		var custom_cols_area_length = $('#customer_columns .form-group').length;
		for(var i = 1; i <= custom_cols_area_length; i ++) {
			// 手机预览
			var mobile_custom_title =   '<li class="askfor-mobile__li" style="border-top: 0px;">' +
					'<span id="mobile_custom_title_' + i + '"></span>' +
					'<span class="js-askfor-visual"></span></li>';
			// 加入DOM
			$('#mobile_custom_area').append(mobile_custom_title);

			// 右侧输入端和 手机预览端绑定
			$('#custom_cols_input_' + i).bind('input propertychange', function () {
				var custom_id = $(this).attr('id').substr(18);
				$('#mobile_custom_title_' + custom_id).html($('#custom_cols_input_' + custom_id).val());
			});
			$('#mobile_custom_title_' + i).html($('#custom_cols_input_' + i).val());
		}

		// 获取等级 [审批人]
		var approver_level = user_temp.length;
		// 先赋值第一个审批人默认数据
		user_arr = user_temp;
		// 根据审批人等级数循环
		if (approver_level > 1) { // 如果级数大于1
			for (var i = 0; i < approver_level - 1; i ++) {

				// 要插入的按钮
				var tem =   '<div class="form-group col-sm-8" id="ngclick_' + _cur_index_for + '">' +
						'<label class="control-label col-sm-2 askfor-label askfor-approver  text-danger">' +
						numIndex[2 + i] + '级审批人*' +
						'</label><div class="col-sm-3"><div class="col-sm-3">' +
						'<div class="angularjs-area">' +
						'<a style="margin-top: 10px;" class="btn btn-defaul" data-ng-controller="ChooseShimCtrl" data-ng-click="selectPerson(' +
						"'user_arr[" + _cur_index_for + "]','selectedPersonCallBack', '" + _cur_index_for + "'" +
						')">选取审批人</a></div></div></div>' +
						'<pre id="approver_deafult_name_' + (i + 1) + '"style="height: 47px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>' +
						'</div><div class="col-sm-1">' +
						'<a href="javascript:void(0);" style="margin-top:10px;" role="button" class="btn btn-danger js-custom-del js-askfor-input"' +
						'ask-del="' + _cur_index_for + '" ask-visual="title" onclick="delete_approver(this)">删除</a>';

				// 页面插入
				$('#approver_activite').append(tem);
				//由于是动态添加的DOM，angularjs 绑定的ng-click不起作用，手动angular化
				angular.bootstrap($('#ngclick_' + _cur_index_for), ['ng.poler.plugins.pc']);

				// DOM id 也是审批人数组的 键值
				_cur_index_for ++;
			}
		}
		// 展示选中的审批人
		var select_user_name = '';
		// 按有多少级审批人循环
		for(var l = 0; l < approver_level; l ++) {
			// 按一级里审批人数循环
			for(var a = 0; a < user_temp[l].length; a ++) {
				if (user_temp[l][a] != 'undefinded') {
					// 获取审批人名称
					select_user_name += user_temp[l][a].m_username + ' ';
				}
			}
			$('#approver_deafult_name_' + l).html(select_user_name).show();
			select_user_name = '';
		}

		/*审批人 手机端预览*/
		// 第一级审批人预览数据
		$('#approver_deafult_name_0').bind('input perprotychange', function () {
			$('#mobile_approver_0').html($('#approver_deafult_name_0').val());
		});
		// 赋值第一级审批人数据
		$('#mobile_approver_0').html($('#approver_deafult_name_0').html());
		// 其他级数的默认数据
		$('#approver_activite').children('.form-group').each(function (i, v) {
			// 审批人的DOm
			var str =   '<li class="askfor-mobile__li"><span class="askfor-mobile__title" style="font-size: 10px;">' + numIndex[i + 2] + '级审批人:</span>' +
						'<span class="js-askfor-visual" id="mobile_approver_' + (i + 1) + '"></span></li>';
			// 加入手机端预览
			$('#mobile_approver_area').append(str);
			// 默认选中的人 显示在左边手机端
			$('#mobile_approver_' + (i + 1)).html($(v).find('pre').html());
		})

		// 抄送人手机端预览数据
		$('#mobile_copy_data').html($('#copy_deafult_data').html());
	}

	// 添加自定义字段
	$('#add_colums').on('click', function () {

		// 如果已经存在有自定义字段设置
		if ($('#customer_columns .form-group').length != 0) {
			custom_id = $('#customer_columns .form-group:last').attr('id').substr(12);
		}

		var custom_type = $('#custom_type').val();
		var custom_name = $('#custom_type option:selected').text();
		custom_id ++;

		var str =   '<div id="custom_cols_' + custom_id + '" class="form-group">' +
				'<div class="row col-sm-8"><label class="control-label col-sm-2 askfor-label">' + custom_name + '</label>' +
				'<div class="col-sm-3 askfor-group-40">' +
				'<input  type="text" id="custom_cols_input_' + custom_id + '" class="form-control form-small askfor-input" data-input="' + custom_type +
				'" placeholder="字段名称,最多输入8个汉字" maxlength="8" required="required"/></div>' +
				'<label style="padding-top:7px;">是否必填 </label>&nbsp; <input type="checkbox"></div>' +
				'<div class="btn btn-danger" onclick="delete_column_cols(' + custom_id + ')">删除</div></div></div>';

		$('#customer_columns').append(str);

		// 手机预览
		var mobile_custom_title =   '<li class="askfor-mobile__li" style="border-top: 0px;">' +
				'<span id="mobile_custom_title_' + custom_id + '"></span>' +
				'<span class="js-askfor-visual"></span></li>';
		// 加入DOM
		$('#mobile_custom_area').append(mobile_custom_title);

		// 右侧输入端和 手机预览端绑定
		$('#custom_cols_input_' + custom_id).bind('input propertychange', function () {
			var custom_id = $(this).attr('id').substr(18);
			$('#mobile_custom_title_' + custom_id).html($('#custom_cols_input_' + custom_id).val());
		});
	});

	// 删除自定义字段
	function delete_column_cols(id) {
		$('#custom_cols_' + id).remove();
		// 删除对应的手机预览
		$('#mobile_custom_title_' + id).parent().remove();
	}

	// 抄送人回调
	function selectedCopyPersonCallBack(data, id) {
		copy_arr = data;

		// 展示选中的人
		if (copy_arr != '') {
			var copy_deafult_name = '';
			for (var i = 0;i < copy_arr.length; i++ ) {
				copy_deafult_name += copy_arr[i]['m_username'] + ' ';
			}
			$('#copy_deafult_data').html(copy_deafult_name).show();
			$('#mobile_copy_data').html($('#copy_deafult_data').html());
		} else {
			$('#copy_deafult_data').html(copy_deafult_name).hide();
			$('#mobile_copy_data').html($('#copy_deafult_data').html());
		}
	}

	// 选择部门回调
	function selectedDepartmentCallBack(data){
		dep_arr = data;

		var select_dep_name = '';
		for (var i = 0; i < data.length; i ++) {
			select_dep_name += data[i]['name'] + ' ';
		}
		if (select_dep_name != '') {
			$('#dep_deafult_data').html(select_dep_name).show();
		} else {
			$('#dep_deafult_data').hide();
		}
	}

	// 选择人员回调
	function selectedPersonCallBack(data, id) {

		var select_user_name = '';

		if (data.length > 5) {
			alert('一级审批人里的最多5人');
			return false;
		}

		// 展示选中的人
		if (data != '') {
			for (var i = 0;i < data.length; i++ ) {
				select_user_name += data[i]['m_username'] + ' ';
			}

			$('#approver_deafult_name_' + id).html(select_user_name).show();
			// 手机端预览
			$('#mobile_approver_' + id).html(select_user_name).show();
		} else {
			$('#approver_deafult_name_' + id).html(select_user_name).hide();
			$('#mobile_approver_' + id).html(select_user_name).hide();
		}

		// 数据录入
		user_arr[id] = data;

	}

	//	添加审批人
	function add_approver() {

		// 级数
		var index = $('#approver_activite').children('.form-group').length + 1;

		// 级数限制
		if (index > 9) {
			alert('不能添加更多审批人');
			return;
		}

		// 页面生成的审批人按钮数量(用于区分id) 如果有增加的审批人级数,那么id 直接根据最后一条的id加1
		if ($('#approver_activite .form-group').length != 0) {
			_cur_index = parseInt($('#approver_activite .form-group:last').attr('id').substr('8')) + 1;
		} else {
			_cur_index ++;
		}

		// 要插入的按钮
		user_arr[_cur_index] = [];
		var tem =   '<div class="form-group col-sm-8" id="ngclick_' + _cur_index + '">' +
				'<label class="control-label col-sm-2 askfor-label askfor-approver  text-danger">' +
				numIndex[index + 1] + '级审批人*' +
				'</label><div class="col-sm-3"><div class="col-sm-3">' +
				'<div class="angularjs-area">' +
				'<a style="margin-top: 10px;" class="btn btn-defaul" data-ng-controller="ChooseShimCtrl" data-ng-click="selectPerson(' +
				"'user_arr[" + _cur_index + "]','selectedPersonCallBack', '" + _cur_index + "'" +
				')">选取审批人</a></div></div></div>' +
				'<pre id="approver_deafult_name_' + _cur_index + '"style="height: 47px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>' +
				'</div><div class="col-sm-1">' +
				'<a href="javascript:void(0);" style="margin-top:10px;" role="button" class="btn btn-danger js-custom-del js-askfor-input"' +
				'ask-del="' + _cur_index + '" ask-visual="title" onclick="delete_approver(this)">删除</a>';

		// 页面插入
		$('#approver_activite').append(tem);

		//由于是动态添加的DOM，angularjs 绑定的ng-click不起作用，手动angular化
		angular.bootstrap($('#ngclick_' + _cur_index), ['ng.poler.plugins.pc']);

		// 手机端预览
		var mobile_approver =   '<li class="askfor-mobile__li"><span class="askfor-mobile__title" style="font-size: 10px;">' + numIndex[index + 1] + '级审批人:</span>' +
				'<span class="js-askfor-visual" id="mobile_approver_' + _cur_index + '"></span></li>';

		$('#mobile_approver_area').append(mobile_approver);
	}

	//	删除审批人
	function delete_approver(obj) {

		var id = $(obj).attr('ask-del');
		// 去除元素
		$('#ngclick_' + id).remove();
		// 去除数组值
		delete user_arr[id];

		// 去除按钮
		$(obj).remove();
		// 去除手机端预览
		$('#mobile_approver_' + id).parent().remove();

		// 重新显示审批人等级
		$('#approver_activite').children('.form-group').each(function (i, v) {
			$(v).find('label').html(numIndex[i + 2] + '级审批人');
		});
		// 重新显示手机端审批人预览等级
		$('#mobile_approver_area').children().each(function (i, v) {
			$(v).find('span').eq(0).html(numIndex[i + 2] + '级审批人');
		});
	}

	// 提交
	function add_temp() {

		if (user_arr == '') {
			alert('审批人不能为空');

			return false;
		}
		if ($('#temp_title').val() == '') {
			alert('流程名称不得为空');

			return false;
		}

		// 获取标题
		var temp_title = $('#temp_title').val();
		// 序号
		var orbderid = $('#orderid').val();
		// 自定义字段
		var customer_cols = new Object();
		var customer_id = '';
		// 获取自定义字段 数据
		var each_status = true;
		$('#customer_columns .form-group').each(function (i, v) {
			var cols = new Object();
			// 获取名称
			cols['name'] = $($(v).find("input")[0]).val();
			// 自定义字段id
			customer_id = $(v).attr('id').substr(12);
			if (cols['name'] == '') {
				alert('第' + (i + 1) + '个自定义字段名称不得为空');
				$(v).focus();
				each_status = false;
				return false;
			}

			// 是否必填
			if ($($(v).find("input")[1]).prop('checked')) {
				cols['required'] = 1; // 获取名称
			} else {
				cols['required'] = 0; // 获取名称
			}
			// 自定义字段类型
			cols['type'] = $($(v).find("input")[0]).attr('data-input');
			// 放入自定义字段数组
			customer_cols[customer_id] = cols;
		});
		if (!each_status) {
			return false;
		}

		// 提交
		$.ajax({
			url: '/Askfor/Apicp/Template/Temp',
			dataType: 'json',
			data: {
				'approver': user_arr, // 审批人
				'title': temp_title, // 标题
				'id_title': orbderid, // 序号
				'custom': customer_cols, // 自定义字段
				'copy': copy_arr, // 抄送人
				'create_id': "{$create_id}", // 创建人id
				'create_username': "{$create_username}", // 创建人名称
				'bu_id': dep_arr, // 允许
				'act': "{$act}", // 操作
				'aft_id': "{$aft_id}" // 模板ID
			},
			type: 'post',
			success: function (data) {
				if (data.errcode != 0) {
					alert(data.errmsg);

					return false;
				}

				if ("{$act}" == 'add') {
					alert('新建成功');
				} else if ("{$act}" == 'edit') {
					alert('更新成功');
				} else {
					alert('操作成功');
				}
				location.href = "{$templist_url}";
				return true;
			},
			error: function (e) {
				alert(e.errmsg);
				return false;
			}
		});
	}

</script>
<script type="text/javascript" src="{$JSDIR}askfor.js"></script>
{include file="$tpl_dir_base/footer.tpl"}
