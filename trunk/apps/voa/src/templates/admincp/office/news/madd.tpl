{include file="$tpl_dir_base/header.tpl" }
<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<div class="left-col left-col-more">
				<div class="profile-block">
					<div class="panel profile-photo">
						<div class="img-box img-box-more dbclick news-thumbnail selected" id="fill_cover">
							<div class="img-text">封面图片</div>
							<div class="img-title">标题</div>
						</div>
						<div class="title-position news-thumbnail">
							<span class="panel-title">标题</span>
							<span class="showimage title-thumbnail"></span>
						</div>
						<div id="last" class="added title-position"><i class="fa fa-plus page-header-icon add-icon"></i></div>
					</div>
				</div>
			</div>
			<div class="right-col">
				<div class="panel tl-body">
					<form class="form-horizontal font12" role="form" action="{$form_action_url}" method="post" data-ng-app="ng.poler.plugins.pc">

						<span id="author_choose" style="display: none;"></span>
						<span id="auditor_choose" style="display: none;"></span>
						<span id="m_uid_choose" style="display: none;"></span>
						<span id="cd_id_choose" style="display: none;"></span>

						<input type="hidden" name="formhash" value="{$formhash}" />
						<div class="form-group">
							<label class="control-label col-sm-2 " for="id_title">标题*</label>
							<div class="col-sm-9">
								<input type="text" class="form-control form-small" id="id_title" name="title" placeholder="最多输入64个字符" maxlength="64"  required="required"/>
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
							<label class="control-label col-sm-2 " for="id_title">摘要</label>
							<div class="col-sm-9">
								<textarea  class="form-control form-small" id="id_summary" name="summary" placeholder="最多输入120个字符" maxlength="120" rows=4></textarea>
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

							<div class="col-sm-3">
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
										<label class="col-sm-3 text-right padding-sm">选择部门：</label>

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
										<label class="col-sm-3 text-right padding-sm">选择人员：</label>
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
										<input type="hidden" class="form-small" id="is_publish" name="is_publish" value="0"/>
										<div class="col-md-4">
											<button type="button" class="btn btn-primary col-md-9" style="display:none" data-toggle="modal" id="preview_btn">提交预览</button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="form-group" id="btn-box">
							<div class="col-sm-offset-2 col-sm-9">
								<div class="row">
									<div class="col-md-4">
										<button type="button" class="btn btn-primary  col-md-9" id="draft_btn">保存草稿</button>
									</div>
									<div class="col-md-4">
										<button type="button" class="btn btn-primary col-md-9" id="publish_btn">发布</button>
									</div>
								</div>
							</div>
						</div>
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
			author_choose += '<input name="author" value="' + data[i]['m_uid'] + '" type="hidden">';
			select_m_uid_name += data[i]['m_username'] + ' ';
		}
		$('#author_choose').html(author_choose);

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

	//数据保存基类
	var news = [];

	$(function(){
		var navActive = $(".nav li a:contains('添加公告')").parent('li');
		navActive.addClass('active');
		//添加新的公告
		$('.added').on('click', function(){
			var news_thumbnail = $('.news-thumbnail');
			var _string = '';
			var _news_gallery = news_thumbnail.length; //公告数展示
			if(news.length > 9) {
				alert('最多添加10条数据!');
				return false;
			}
			//integrate(); //创建数据字典
			var selected = $('.selected').index('.news-thumbnail');
			if(news.length < (selected + 1)) {
				//空的时候进行数据插入 或 当前未保存
				integrate();
			} else {
				updated(selected);
			}
			news_thumbnail.removeClass("selected");
			$(".news-thumbnail:eq("+news.length+")").addClass("selected");

			if(news.length > 1) {
				_string +='<div class="title-position news-thumbnail del selected" >';
				_string += '<a href="javascript:void(0);" class="text-danger delete" data-index="'+_news_gallery+'"><i class="fa fa-times"></i></a>'
				_string +='<span class="panel-title">标题</span><span class="showimage title-thumbnail"></span></div>';
				$('#last').before(_string);
			}
			data_ins();
			if(news.length < _news_gallery) {
				//空的时候进行数据插入 或 当前未保存
				integrate();
			}
		});

		var newtitlebox = $('.profile-photo');
		//显示删除按钮
		newtitlebox.on('mouseover', '.del', function(){
			$(this).children('.delete').show();
		});
		//隐藏删除按钮
		newtitlebox.on('mouseout', '.del', function () {
			$(this).children('.delete').hide();
		});
		newtitlebox.on('click', 'a', function (event) {
			//以保存的公告数
			var _index = news.length;
			//展示的公告数
			var selected = $('.selected').index('.news-thumbnail'); //选中
			var _news_gallery = $('.news-thumbnail').length;
			var news_index = $(this).data('index');
			$(this).parent('.del').remove();
			news.splice(news_index, 1);
			if(selected == news_index){
				var news_thumbnail = $('.news-thumbnail');
				news_thumbnail.eq(0).addClass("selected");
			}
			event.stopPropagation(); //阻止事件冒泡
		});

		//修改数据
		newtitlebox.on('click', '.news-thumbnail', function() {
			var news_thumbnail = $('.news-thumbnail');
			var selected = $('.selected').index('.news-thumbnail');
			var index = $(this).index();

			if(news.length < (selected + 1)) {
				//空的时候进行数据插入 或 当前未保存
				integrate();
			} else {
				updated(selected);
			}
			/*if(news.length < selected) {
			 data_ins();
			 } else {
			 data_upd(index);

			 }*/
			news_thumbnail.removeClass("selected");
			$(this).addClass("selected");
			var now_selected = $('.selected').index('.news-thumbnail');
			if(news.length < (now_selected + 1)) {
				//空的时候进行数据插入 或 当前未保存
				data_ins();
				integrate();
			}else {
				data_upd(now_selected);
			}
		});

		//当阅读对象为指定对象时
		$('#specified_btn').bind('click',function(){
			$('#user_dep_container').show();
			$(this).addClass('btn-primary');
			$('#all_btn').removeClass('btn-primary');
			$('#is_all').val(0);
		});
		//当阅读对象为全公司时
		$('#all_btn').bind('click', function(){
			$('#user_dep_container').hide();
			$(this).addClass('btn-primary');
			$('#specified_btn').removeClass('btn-primary');
			$('#is_all').val(1);
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


		//当点击保存草稿时
		$('#draft_btn').bind('click', function(){
			if(news.length < 1) {
				alert('公告未编辑数少于2条!');
				return false;
			}
			var selected = $('.selected').index('.news-thumbnail');
			updated(selected);

			var my_data_index = false;
			var my_data_error = '';
			for(var i = 0, max = news.length; i < max ; i++ ){
				if (news[i].title == '') {
					my_data_index = i;
					my_data_error = '请输入标题！';
					break;
				}
				if (news[i].cover_id == '') {
					my_data_index = i;
					my_data_error = '请选择封面图片！';
					break;
				}
				if (news[i].nca_id == '') {
					my_data_index = i;
					my_data_error = '请选择类型！';
					break;
				}
				if (news[i].content == '') {
					my_data_index = i;
					my_data_error = '请输入正文！';
					break;
				}
			}
			switch (my_data_index) {
				case false:
					break;
				default :
					var news_thumbnail = $('.news-thumbnail');
					news_thumbnail.removeClass("selected");
					news_thumbnail.eq(my_data_index).addClass("selected");
					data_upd(my_data_index);
					alert(my_data_error);
					return false;
					break;
			}
			if(author.length == 0) {
				alert('请选择作者！');
				return false;
			}
			var _cd_ids = '',_m_uids = '',_author = '';
			var _is_all = $('input[name="is_all"]').val(); //阅读权限
			if(_is_all == '1') {
				_cd_ids = 0;
				_m_uids = 0;
			} else {
				$('input[name="cd_ids[]"]').each(function(){
					var value = $(this).val();
					_cd_ids += value + ',';
				});
				$('input[name="m_uids[]"]').each(function(){
					var value = $(this).val();
					_m_uids += $(this).val() + ',';
				});
				$('input[name="author"]').each(function(){
					var value = $(this).val();
					_author += $(this).val() + ',';
				})
			}

			var is_push = $('input[name="is_push"]:checked').val();
			$.ajax({
				type : 'POST',
				url : '{$api_action_url}',
				data : {
					'mydata': news,
					'is_publish': '0',
					'is_check': '0',
					'author':_author,
					'is_all':_is_all,
					'is_push':is_push,
					'cd_ids':_cd_ids,
					'm_uids':_m_uids
				},
				success: function(data){
					if(data.errcode == '0') {
						window.location.href = data.result.url;
					} else {
						alert(data.errmsg);

						return false;
					}
				}
			});
		});
		//当点击发布时
		$('#publish_btn').bind('click', function(){

			if(news.length < 1) {
				alert('公告未编辑数少于2条!');
				return false;
			}
			var selected = $('.selected').index('.news-thumbnail');
			updated(selected);

			var my_data_index = false;
			var my_data_error = '';
			for(var i = 0, max = news.length; i < max ; i++ ){
				if (news[i].title == '') {
					my_data_index = i;
					my_data_error = '请输入标题！';
					break;
				}
				if (news[i].cover_id == '') {
					my_data_index = i;
					my_data_error = '请选择封面图片！';
					break;
				}
				if (news[i].nca_id == '') {
					my_data_index = i;
					my_data_error = '请选择类型！';
					break;
				}
				if (news[i].content == '') {
					my_data_index = i;
					my_data_error = '请输入正文！';
					break;
				}
			}
			switch (my_data_index) {
				case false:
					break;
				default :
					var news_thumbnail = $('.news-thumbnail');
					news_thumbnail.removeClass("selected");
					news_thumbnail.eq(my_data_index).addClass("selected");
					data_upd(my_data_index);
					alert(my_data_error);
					return false;
					break;
			}
			if(author.length == 0) {
				alert('请选择作者！');
				return false;
			}

			var _cd_ids = '',_m_uids = '',_author = '';
			var _is_all = $('input[name="is_all"]').val(); //阅读权限
			if(_is_all == '1') {
				_cd_ids = 0;
				_m_uids = 0;
			} else {
				$('input[name="cd_ids[]"]').each(function(){
					var value = $(this).val();
					_cd_ids += value + ',';
				});
				$('input[name="m_uids[]"]').each(function(){
					var value = $(this).val();
					_m_uids += $(this).val() + ',';
				});
				$('input[name="author"]').each(function(){
					var value = $(this).val();
					_author += $(this).val() + ',';
				})
			}
			$('#is_publish').val(1);
			var is_push = $('input[name="is_push"]:checked').val();
			$.ajax({
				type : 'POST',
				url : '{$api_action_url}',
				data : {
					'mydata': news,
					'is_publish': '1',
					'is_check': '0',
					'author':_author,
					'is_all':_is_all,
					'is_push':is_push,
					'cd_ids':_cd_ids,
					'm_uids':_m_uids
				},
				success: function(data){
					if(data.errcode == '0') {
						window.location.href = data.result.url;
					}
				}
			});
		});
		//审核发布
		$("#preview_btn").on('click', function(){

			if(news.length < 1) {
				alert('公告未编辑数少于2条!');
				return false;
			}
			var selected = $('.selected').index('.news-thumbnail');
			updated(selected);

			var my_data_index = false;
			var my_data_error = '';
			for(var i = 0, max = news.length; i < max ; i++ ){
				if (news[i].title == '') {
					my_data_index = i;
					my_data_error = '请输入标题！';
					break;
				}
				if (news[i].cover_id == '') {
					my_data_index = i;
					my_data_error = '请选择封面图片！';
					break;
				}
				if (news[i].nca_id == '') {
					my_data_index = i;
					my_data_error = '请选择类型！';
					break;
				}
				if (news[i].content == '') {
					my_data_index = i;
					my_data_error = '请输入正文！';
					break;
				}
			}
			switch (my_data_index) {
				case false:
					break;
				default :
					var news_thumbnail = $('.news-thumbnail');
					news_thumbnail.removeClass("selected");
					news_thumbnail.eq(my_data_index).addClass("selected");
					data_upd(my_data_index);
					alert(my_data_error);
					return false;
					break;
			}
			if(author.length == 0) {
				alert('请选择作者！');
				return false;
			}
			var _person = '';
			$('input[name="check_id[]"]').each(function(){
				_person += $(this).val() + ',';
			});
			var _check_summary = $('textarea[name="check_summary"]').val();
			var _is_publish = 0;
			if (_person == '' || _check_summary == '') {
				alert('请选择审批人！');
				return false;
			}
			var _cd_ids = '',_m_uids = '',_author = '';
			var _is_all = $('input[name="is_all"]').val(); //阅读权限
			if(_is_all == '1') {
				_cd_ids = 0;
				_m_uids = 0;
			} else {
				$('input[name="cd_ids[]"]').each(function(){
					var value = $(this).val();
					_cd_ids += value + ',';
				});
				$('input[name="m_uids[]"]').each(function(){
					var value = $(this).val();
					_m_uids += $(this).val() + ',';
				});
				$('input[name="author"]').each(function(){
					var value = $(this).val();
					_author += $(this).val() + ',';
				})
			}
			var is_push = $('input[name="is_push"]:checked').val();
			$.ajax({
				type : 'POST',
				url : '{$api_action_url}',
				data : {
					'mydata': news,
					'is_publish': _is_publish,
					'check_id': _person,
					'check_summary': _check_summary,
					'is_check': '1',
					'author': _author,
					'is_all': _is_all,
					'is_push':is_push,
					'cd_ids': _cd_ids,
					'm_uids': _m_uids
				},
				success: function(data){
					if(data.errcode == '0') {
						window.location.href = data.result.url;
					}
				}
			});
		})
		//预览保存 end
	});
	//模板-数据清空
	function data_ins() {
		var redio_secred = $('input[name="is_secret"]');
		$('input[name="title"]').val(''); //标题
		$('textarea[name="summary"]').val(''); //摘要
		$('select[name="nca_id"]').get(0).options[0].selected = true;//类型
		redio_secred.eq(0).prop("checked", true);
		$('input[name="cover_id"]').val('');//封面图片
		$('._showimage').empty();
		ue.setContent('');
	}
	//模板数据-迭代
	function data_upd(index) {
		var newsdata = news[index];
		$('input[name="title"]').val(newsdata.title); //标题
		$('textarea[name="summary"]').val(newsdata.summary); //摘要
		//$('select[name="nca_id"] option:selected').val();//类型
		var count = $('select[name="nca_id"] option').length;
		for (var i = 0; i < count; i++) {
			if ($('select[name="nca_id"]').get(0).options[i].value == newsdata.nca_id) {
				$('select[name="nca_id"]').get(0).options[i].selected = true;
				break;
			}
		}
		var redio_secred = $('input[name="is_secret"]');
		if (newsdata.is_secret == 1) {
			redio_secred.eq(1).prop("checked", true);//消息保密
		} else {
			redio_secred.eq(0).prop("checked", true);
		}
		$('._showimage').empty();
		if(newsdata.cover_id != '') {
			$('input[name="cover_id"]').val(newsdata.cover_id);//封面图片
			$('._showimage').html('<a href="http://' + window.location.host + '/attachment/read/' + newsdata.cover_id + '" target="_blank"><img src="http://' + window.location.host + '/attachment/read/' + newsdata.cover_id + '/45" border="0" style="max-width:64px;max-height:32px"></a>');
		}
		ue.setContent(newsdata.content);
		if (newsdata.is_comment == 1) {
			$('input[name="is_comment"]').prop("checked", true);
		}
	}


	//数据整合--插入
	function integrate() {

		//当前类型数据集合
		var _dataset = {};
		var _title = $('input[name="title"]').val(); //标题
		var _summary = $('textarea[name="summary"]').val(); //摘要
		var _nca_id = $('select[name="nca_id"] option:selected').val();//类型
		var _is_secret = $('input[name="is_secret"]:checked').val(); //消息保密
		var _cover_id = $('input[name="cover_id"]').val();//封面图片
		var _content = ue.getContent();
		var _is_comment = $('input[name="is_comment"]:checked').val();//开启评论

		/*if ( _title === '') {
		 alert('请输入标题！');
		 return false;
		 }
		 if ( _nca_id === '') {
		 alert('请选择类型！');
		 return false;
		 }
		 if ( _cover_id === '') {
		 alert('请选择封面图片！');
		 return false;
		 }*/
		_dataset = {
			'title':_title,
			'summary':_summary,
			'nca_id':_nca_id,
			'is_secret':_is_secret,
			'cover_id':_cover_id,
			'content':_content,
			'is_comment':_is_comment
		}

		news.push(_dataset); //插入数据集

		return true;
	}
	//数据整合--修改
	function updated(selected) {

		//当前类型数据集合
		//当前类型数据集合
		var _dataset = {};
		var _title = $('input[name="title"]').val(); //标题
		var _summary = $('textarea[name="summary"]').val(); //摘要
		var _nca_id = $('select[name="nca_id"] option:selected').val();//类型
		var _is_secret = $('input[name="is_secret"]:checked').val(); //消息保密
		var _cover_id = $('input[name="cover_id"]').val();//封面图片
		var _content = ue.getContent();
		var _is_comment = $('input[name="is_comment"]:checked').val();//开启评论

		/*	if ( _title === '') {
		 alert('请输入标题！');
		 return false;
		 }
		 if ( _nca_id === '') {
		 alert('请选择类型！');
		 return false;
		 }
		 if ( _cover_id === '') {
		 alert('请选择封面图片！');
		 return false;
		 }*/
		_dataset = {
			'title':_title,
			'summary':_summary,
			'nca_id':_nca_id,
			'is_secret':_is_secret,
			'cover_id':_cover_id,
			'content':_content,
			'is_comment':_is_comment
		}

		news[selected] = _dataset ; //插入数据集

		// 删除页面图片ID 防止写入没有图片ID的对象里
		$('input[name="cover_id"]').val('');

		return true;
	}
	//填充标题
	$('#id_title').bind('blur', function(){
		var title = $(this).val();
		//判断当前的指针
		var selected = $('.selected').index('.news-thumbnail');
		switch(selected) {
			case 0:
				$('.img-title').html(title);
				break;
			default:
				$('.news-thumbnail').eq(selected).children('.panel-title').html(title);
		}
	});
	//填充封面
	function fill_cover(result){
		var url = result.list[0].url;
		//判断当前的指针
		var selected = $('.selected').index('.news-thumbnail');
		switch(selected) {
			case 0:
				$('.img-text').html('<img src="'+url+'"/>');
				break;
			default:
				$('.news-thumbnail').eq(selected).children('.title-thumbnail').html('<img src="'+url+'"/>');
		}

	}
</script>
{include file="$tpl_dir_base/footer.tpl"}