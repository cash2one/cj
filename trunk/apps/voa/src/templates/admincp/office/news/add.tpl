{include file="$tpl_dir_base/header.tpl"}
<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<div class="left-col">
				<div class="profile-block">
					<div class="panel profile-photo">
						<div class="padding-sm">
							<span class="panel-title" id="fill_title">标题</span>
						</div>
						<div class="img-box" id="fill_cover">封面图片</div>
					</div>
				</div>
			</div>
			<div class="right-col">
				<div class="panel tl-body">
					<form class="form-horizontal font12" role="form" action="{$form_action_url}" method="post" data-ng-app="ng.poler.plugins.pc">
						<input type="hidden" name="formhash" value="{$formhash}" />

						<span id="author_choose" style="display: none;"></span>
						<span id="auditor_choose" style="display: none;"></span>
						<span id="m_uid_choose" style="display: none;"></span>
						<span id="cd_id_choose" style="display: none;"></span>

						<div class="form-group">
							<label class="control-label col-sm-2 " for="id_title">标题*</label>
							<div class="col-sm-9">
								<input type="text" class="form-control form-small" id="id_title" name="title" placeholder="最多输入64个字符" maxlength="64"  required="required" value="{$result.title}"/>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2" for="">发布人*</label>
							<div class="col-sm-9">

								<!-- angular 选人组件 begin -->
								<div class="angularjs-area" data-ng-controller="ChooseShimCtrl" >
									<a class="btn btn-defaul" data-ng-click="selectPerson('author','selectedAuthorCallBack')">选择发布人</a>
								</div>
								<!-- angular 选人组件 end -->

								<pre id="author_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

								{*{include*}
								{*file="$tpl_dir_base/common_selector_member.tpl"*}
								{*input_type='radio'*}
								{*input_name='author'*}
								{*selector_box_id='author'*}
								{*allow_member=true*}
								{*allow_department=false*}
								{*}*}
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2 " for="id_summary">摘要</label>
							<div class="col-sm-9">
								<textarea  class="form-control form-small" id="id_summary" name="summary" placeholder="最多输入120个字符" maxlength="120" rows=4 >{$result.summary}</textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2 " for="id_label_tc_id">公告类型*</label>
							<span class="space"></span>
							<div class="col-sm-9">
								<select id="id_nca_id" name="nca_id" class="form-control form-small" data-width="auto"  required="required">
									<option value="" selected="selected">请选择类型</option>
									{foreach $categories as $_key => $_val}
										<optgroup label="{$_val['name']}">
											{if isset($_val['nodes'])}
												{foreach $_val['nodes'] as $_sv}
													<option value="{$_sv['nca_id']}">{$_sv['name']}</option>
												{/foreach}
											{else}
												<option value="{$_val['nca_id']}">{$_val['name']}</option>
											{/if}
										</optgroup>
									{/foreach}
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label  col-sm-2 " for="id_author">消息保密</label>
							<div class="col-sm-6">
								<label class="radio-inline">
									<input type="radio" class=" form-small" id="is_secret" name="is_secret" value="0" checked/>
									&nbsp;&nbsp;关闭
								</label>
								<label class="radio-inline">
									<input type="radio" class=" form-small" id="is_secret" name="is_secret" value="1"/>
									&nbsp;&nbsp;开启
								</label>
							</div>

							<div class="col-sm-2">
								<a class="" data-toggle="modal" data-target="#mymessage">什么是消息保密？</a>
							</div>
							<div id="mymessage" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
											<h4 class="modal-title" id="myModalLabel">什么是消息保密？</h4>
										</div>
										<div class="modal-body">
											<!-- <h4>什么是消息保密？</h4> -->
											<p>

												1.消息保密开启后，这条消息或公告将无法分享到朋友圈；<br>

												2.消息保密开启后，用户在微信端收到的消息详情页将会加上姓名水印，一定程度上防止客户用手机“截屏”泄密。
												<br>
												例如：
											</p>
											<p><center>
												<img src="/admincp/static/images/weixin_pic.gif">
											</center></p>

										</div>
										<!-- / .modal-body -->
										<div class="modal-footer text-center">
											<center><button type="button" class="btn btn-default btn-lg" data-dismiss="modal">知道了</button></center>
										</div>
									</div>
									<!-- / .modal-content -->
								</div>
								<!-- / .modal-dialog -->
							</div>
						</div>
						<div class="form-group">
							<label class="control-label  col-sm-2 " for="id_author">封面图片*</label>
							<div class="col-sm-9">
								{cycp_upload
								inputname='cover_id'
								callback='fill_cover'
								hidedelete=1
								tip='(推荐尺寸 480x230)'
								}
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-sm-2" for="id_rights">阅读权限*</label>
							<div class="col-sm-9">
								<div>
									<input type="hidden" class="form-small" id="is_all" name="is_all" value="1"/>
									<button type="button" class="btn btn-primary" id="all_btn">全公司</button>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<button type="button" class="btn" id="specified_btn">指定对象</button>
									<span class="is_push_box">
										<label class="radio-inline">
											<input type="checkbox" class=" form-small" id="is_push" name="is_push" value="1" checked/>
											&nbsp;&nbsp;消息推送
										</label>
									</span>
								</div>
								<div id="user_dep_container" style="display: none">
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
						<div class="form-group" style="margin-left: 25px">
							<div class="col-sm-11">{$ueditor_output}</div>
						</div>
						<div class="form-group">
							<div class="col-sm-3 col-sm-offset-2">
								<input type="checkbox" class="form-small"  name="is_comment" value="1" checked/>
								&nbsp;&nbsp;开启评论功能
							</div>
							<div class="col-sm-3 col-sm-offset-2">
								<input type="checkbox" class="form-small"  name="is_like" value="1" checked/>
								&nbsp;&nbsp;开启点赞功能
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-3 col-sm-offset-2">
								<label for="is_check">
									<input type="checkbox" class="form-small" id="is_check"  name="is_check" value="1" />
									&nbsp;&nbsp;开启预览功能
								</label>
							</div>
						</div>

						<div class="check">
							<div class="from-group">
								<label class="control-label col-sm-2 " for="check_summary">预览说明*</label>
								<div class="col-sm-9">
									<input type="hidden" class="form-small" id="is_check" name="is_check" value="1"/>
									<textarea  class="form-control form-small news-form" id="check_summary" name="check_summary" placeholder="最多输入120个字符" maxlength="120" rows=4></textarea>
								</div>
							</div>
							<div class="from-group">
								<label class="control-label col-sm-2 " for="users_check">预览人*</label>
								<div class="col-sm-9">

									<br />

									<!-- angular 选人组件 begin -->
									<div class="angularjs-area" data-ng-controller="ChooseShimCtrl" >
										<a class="btn btn-defaul" data-ng-click="selectPerson('auditor','selectedAuditorCallBack')">选择人员</a>
									</div>
									<!-- angular 选人组件 end -->

									<br />

									<pre id="auditor_deafult_data" style="margin-top: 10px; display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

									{*{include*}
									{*file="$tpl_dir_base/common_selector_member.tpl"*}
									{*input_type='checkbox'*}
									{*input_name='check_id[]'*}
									{*selector_box_id='users_check'*}
									{*allow_member=true*}
									{*allow_department=false*}
									{*}*}
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-2 col-sm-9">
									<div class="row">
										<div class="col-md-4">
											<button type="submit" class="btn btn-primary col-md-9"  data-toggle="modal" id="preview_btn">提交预览</button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="form-group" id="btn-box">
							<div class="col-sm-offset-2 col-sm-9">
								<div class="row">
									<div class="col-md-4">
										<button type="submit" class="btn btn-primary  col-md-9" id="draft_btn">保存草稿</button>
									</div>
									<div class="col-md-4">
										<button type="submit" class="btn btn-primary col-md-9" id="publish_btn">发布</button>
									</div>
								</div>
							</div>
						</div>
						<input type="hidden" class="form-small" id="is_publish" name="is_publish" value="0"/>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">

	/* 选人组件 */
	var author = [];
	var auditor = [];
	var dep_arr = [];
	var m_uid = [];
	var author_choose = '';
	var cd_id_choose = '';
	var m_uid_choose = '';
	var auditor_choose = '';

	// 作者选择回调
	function selectedAuthorCallBack(data, id) {
		if (data.length > 1) {
			alert('作者只能有一人');

			return false;
		}

		author = data;

		// 页面埋入 选择的值
		m_uid_choose = '';
		var select_m_uid_name = '';
		for (var i = 0; i < data.length; i ++) {
			m_uid_choose += '<input name="author" value="' + data[i]['m_uid'] + '" type="hidden">';
			select_m_uid_name += data[i]['m_username'] + ' ';
		}
		$('#author_choose').html(m_uid_choose);

		// 展示
		if (select_m_uid_name != '') {
			$('#author_deafult_data').html(select_m_uid_name).show();
		} else {
			$('#author_deafult_data').hide();
		}
	}

	// 审核人选择回调
	function selectedAuditorCallBack(data, id) {
		auditor = data;

		// 页面埋入 选择的值
		auditor_choose = '';
		var select_auditor_name = '';
		for (var i = 0; i < data.length; i ++) {
			auditor_choose += '<input name="check_id[]" value="' + data[i]['m_uid'] + '" type="hidden">';
			select_auditor_name += data[i]['m_username'] + ' ';
		}
		$('#auditor_choose').html(auditor_choose);

		// 展示
		if (select_auditor_name != '') {
			$('#auditor_deafult_data').html(select_auditor_name).show();
		} else {
			$('#auditor_deafult_data').hide();
		}
	}

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

		{if $result.cover_url}
		$('._showimage').append('<a target="_blank" href="{$result.cover_url}"><img border="0" style="max-width:64px;max-height:32px" src="{$result.cover_url}"></a><input id="temp-img" name="temp-img" value="{$result.cover_url}" type="hidden"/> ');
		$('#fill_cover').html('<img src="{$result.cover_url}"/>');
		{/if}
		var navActive = $(".nav li a:contains('添加公告')").parent('li');
		navActive.addClass('active');
		//填充标题
		$('#id_title').bind('blur', function () {
			var title = $(this).val();
			$('#fill_title').html(title);
		});
		//当阅读对象为指定对象时
		$('#specified_btn').bind('click', function () {
			$('#user_dep_container').show();
			$(this).addClass('btn-primary');
			$('#all_btn').removeClass('btn-primary');
			$('#is_all').val(0);
		});
		//当阅读对象为全公司时
		$('#all_btn').bind('click', function () {
			$('#user_dep_container').hide();
			$(this).addClass('btn-primary');
			$('#specified_btn').removeClass('btn-primary');
			$('#is_all').val(1);
		});
		//当点击保存草稿时
		$('#draft_btn').bind('click', function () {
			if (author.length == 0) {
				alert('请选择作者！');
				return false;
			}
			var cover_id = $('input[name="cover_id"]').val();
			var temp_img = $('#temp-img').val();
			if (cover_id == '' && temp_img == '') {
				alert('请选择封面图片！');
				return false;
			}
			$('#is_publish').val(0);
			$('#is_check').val(0);
		});
		//开启/关闭审批推送
		var check_check = $('#is_check');
		check_check.on('click', function() {
			if(check_check.is(':checked')){
				$('.check').show();
				$('#preview_btn').show();
				$('#btn-box').hide();
			}else{
				$('.check').hide();
				$('#preview_btn').hide();
				$('#btn-box').show();
			}
		});
		//当点击发布时
		$('#publish_btn').bind('click', function () {
			if (author.length == 0) {
				alert('请选择作者！');
				return false;
			}
			var cover_id = $('input[name="cover_id"]').val();
			var temp_img = $('#temp-img').val();
			if (cover_id == '' && temp_img == '') {
				alert('请选择封面图片！');
				return false;
			}
			$('#is_publish').val(1);
			$('#is_check').val(0);
		});
		//预览保存
		$('#preview_btn').bind('click', function () {
			if (author.length == 0) {
				alert('请选择作者！');
				return false;
			}
			var cover_id = $('input[name="cover_id"]').val();
			var temp_img = $('#temp-img').val();
			if (cover_id == '' && temp_img == '') {
				alert('请选择封面图片！');
				return false;
			}
			if (auditor.length == 0) {
				alert('请选择审批人！');
				return false;
			}
		});
	});
</script>
{include file="$tpl_dir_base/footer.tpl"}
