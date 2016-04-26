{include file='mobile/header.tpl' css_file='app_activity.css'}

<form name="frmpost" id="frmpost" method="post" action="/api/activity/post/new">
	<div class="ui-top-border"></div>
	<div class="ui-form">
		{cyoa_input_text attr_type='text' attr_placeholder ='不超过15个字' title='主题' attr_maxlength='15' attr_id='title' attr_name='title' attr_value=$data.title div_class='ui-form-item  ui-border-b'}
		{cyoa_textarea attr_placeholder ='请输入活动内容...'  attr_id='content' attr_name='content' attr_value=$data.content styleid=1 div_style='height:96px'  attr_style='height:72px'}
		{cyoa_upload_image title='上传图片' attr_id='upload_image' name='atids' attachs=$data['image'] progress=1}
	</div>
	<div class="ui-form">
		{cyoa_input_datetime
			attr_name='start'
			attr_value=$data['start_time']
			div_class='ui-form-item'
			title='活动开始'
			all=true
		}
		{cyoa_input_datetime
			attr_name='end'
			attr_value=$data['end_time']
			title='活动结束'
			all=true
		}
		{cyoa_input_datetime
			attr_name='cut'
			attr_value=$data['cut_off_time']
			title='报名截止'
			all=true
		}
		{cyoa_input_text
			attr_type='text'
			attr_placeholder ='请输入'
			title='活动地点'
			attr_id='address'
			attr_max='64'
			attr_name='address'
			attr_value=$data.address
		}
		{if $acid}
		 <div class="ui-form-item ui-border-t">
			<label for="#">限制人数</label>
			{if $data['np'] == 0}<p>无限制</p>{else}
			<p>{$data['np']}&nbsp;&nbsp;人</p>
			{/if}
		</div>
		{cyoa_user_show
			title='邀请人员'
			userids=$data['users1']
			dpids=$data['dps1']
			styleid=1
		}
		{else}
		{cyoa_input_text
			attr_type='phone'
			attr_placeholder ='不填为无限制'
			title='限制人数'
			attr_id='np'
			attr_name='np'
			attr_value=$data.np
		}
	</div>
	<div class="ui-form">
		{cyoa_user_selector
		title='邀请人员'
		user_max=-1
		user_input='users'
		dp_max=-1
		dp_input='dp'
		div_class='ui-form-item ui-form-contacts'
		dp_name='选择部门'
		selectall=1
		}
	</div>
	{/if}

{if !$data['title']}
	<div class="ui-form ui-border-t">
		{$outsider_open = $data['outsider']}
		{cyoa_input_switch
			title="是否邀请外部人员"
			attr_id="outsider"
			attr_value=0
			attr_name="outsider"
			label_class="activity-label-width"
			open=$outsider_open
		}
	</div>
		<div class="ui-form" id="outlist" style="display: none;">
		<div class="ui-form-item ui-border-b">
			<label for="#">报名信息</label>
			<span id="buttondel" class="ui-form-item-unit">删除</span>
		</div>
		<div class="upload">
			<div class="ui-btn-lg activity-ui-btn-lg ui-btn-primary tag">
				<div class="tagclass0">姓名</div>
				<input class="tagclass1" type="hidden" name="outfield[outname][name]" value="名字" />
				<input class="tagclass2" type="hidden" name="outfield[outname][require]" value="1" />
				<input class="tagclass3" type="hidden" name="outfield[outname][open]" value="1" />
				<input class="tagclass4" type="hidden" name="outfield[outname][type]" value="text" />
			</div>
			<div class="ui-btn-lg activity-ui-btn-lg ui-btn-primary tag">
				手机号
				<input type="hidden" name="outfield[outphone][name]" value="手机" />
				<input type="hidden" name="outfield[outphone][require]" value="1" />
				<input type="hidden" name="outfield[outphone][open]" value="1" />
				<input type="hidden" name="outfield[outphone][type]" value="number" />
			</div>
			<div class="ui-btn-lg activity-ui-btn-lg ui-btn-primary tag">
				备注
				<input type="hidden" name="outfield[remark][name]" value="备注" />
				<input type="hidden" name="outfield[remark][require]" value="1" />
				<input type="hidden" name="outfield[remark][open]" value="1" />
				<input type="hidden" name="outfield[remark][type]" value="text" />
			</div>
			<div id="outlistadd" class="activity-ui-btn-lg  activity-label">
				<li class="ui-border">+</li>
			</div>
			<div class="clearfix"></div>
		</div>
		<p class="activity-note">注：用户报名信息为活动详情页报名表内容，可以根据需求添加字段。</p>
	</div>
{/if}
	{if $data['title']}
		<input type="hidden" id="is_edit" name="is_edit"  value="1"/>
		<input type="hidden" id="acid" name="acid"  value="{$acid}"/>
	{/if}
	<div class="ui-btn-group-tiled ui-btn-wrap">
		<button id="push" class="ui-btn-lg ui-btn-primary" style="margin: 0 auto;">{if !$data['title']}发布{else}修改{/if}</button>
	</div>
</form>

<!--弹出框-->
<div id="outmessage" class="ui-dialog">
	<div class="ui-dialog-cnt">
		<div class="ui-dialog-bd">
			<form id="dialogadd">
				<div class="ui-form-item">
					<label for="#">选项名称</label>
					<input id="dialogtitle" type="text" placeholder="让报名人填写的信息" required="required" maxlength="5" />
				</div>
				<div class="ui-form-item ui-form-item-textarea">
					<label for="#">是否必填</label>
					<label for="#" class="ui-switch">
						<input id="dialogcheckbox1" value="0" type="checkbox" />
					</label>
				</div>
				<div id="dialogradio" class="ui-form-item ui-form-item-show ui-conten-more">
					<label>填写类型</label>
					<div class="activity-radio">
						<p>
							<label class="ui-radio" name="radio" style="float: left">
								<input value="number" type="radio" name="radio" style="width: auto;padding-left: 30px">数字
							</label>
						</p>
						<p>
							<label class="ui-radio" name="radio" style="float: left">
								<input value="text" type="radio" name="radio" style="width: auto;padding-left: 30px">文字
							</label>
						</p>
					</div>
					<div class="clearfix"></div>
				</div>
			</form>
		</div>
		<div class="ui-dialog-ft ui-btn-group">
			<button type="button" class="select" id="message_cancel">取消</button>
			<button type="button" class="select" id="message_sure">添加</button>
		</div>
	</div>
</div>

{literal}
<script type="text/javascript">

require(["zepto", "underscore", "submit", "frozen"], function($, _,submit) {

	//删除按钮右上角样式
	var d_del = '<div id="dodelete" class="ui-badge-cornernum"></div>';

	//显示自定义按钮编辑弹出框
	$('#outlistadd').on('click', function() {
		$('#outmessage').dialog('show');
	});

	//限制自定义按钮个数
	$('#push').on('click', function() {
		var title_len = $('#title').val().length;
		if (title_len >15) {
			$.tips({content:'标题字数不能大于15字'});
			$('#title').val('');
			return false;
		}
		var users = $('#users').val();
		var dps = $('#dp').val();
		if (users == '' && dps == '' && $('#outsider').val() == 0) {
			$.tips({content:'不能创建没人参与的活动'});
			return false;
		}
		var np = $('#np').val();
		if (np != '' && np <= 0) {
			$.tips({content:'限制人数不能为负'});
			$('#np').val('').focus();
			return false;
		};
	});

	//按钮删除
	$('#buttondel').on('click', function () {
		var $t = $(this);
		if ($t.hasClass('buttonsure')) {
			$('.tag1').removeClass('canbedel');
			$t.html('删除').removeClass('buttonsure');
			$('#outlistadd').show();
			$('.tag1').find('div[id=dodelete]').remove();
		} else {
			$t.html('确定').addClass('buttonsure');
			$('#outlistadd').hide();
			$('.tag1').append(d_del).addClass('canbedel');
		}
	});

	$('#cyoa-body').on('click', '.canbedel', function() {
		$(this).remove();
	});

	//确定增加按钮
	$('#message_sure').on('click',function() {
		var tag1 = $('.tag1').size();
		if (tag1 == 10) {
			$.tips({content:'最多添加10个'});
			$('#outmessage').dialog('hide');
			return false;
		}
		var dialogcheckbox1 = "0";
		if ($('#dialogcheckbox1').prop('checked')) {
			dialogcheckbox1 = 1;
		}
		var dialogcheckbox2 = "1";


		var dialogradio = $('#dialogradio').find('input[type=radio]:checked').val();

		var title = $('#dialogtitle').val();
		if ($.trim(title) == '') {
			$.tips({content:'请填写选项名称'});
			$('#dialogtitle').focus();
			return false;
		}

		if (dialogradio == '' || dialogradio == undefined) {
			$.tips({content:'请选择填写类型'});
			return false;
		}

		//新建按钮
		var c_tag = $('.tag').eq(0).clone().removeClass('tag').addClass('tag1');
		c_tag.find('.tagclass0').text($.trim(title));
		c_tag.find('.tagclass1').attr('value', $.trim(title));
		c_tag.find('.tagclass2').attr('value', dialogcheckbox1);
		c_tag.find('.tagclass3').attr('value', dialogcheckbox2);
		c_tag.find('.tagclass4').attr('value', dialogradio);
		$('.activity-label').before(c_tag);
		set_button();
		//重置表单
		dialogcheckbox1 = '0';
		dialogcheckbox2 = '0';
		$('#dialogadd')[0].reset();
		$('#outmessage').dialog("hide");
	});

	//取消增加按钮
	$('#message_cancel').on('click',function() {
		$('#dialogadd')[0].reset();
		$('#outmessage').dialog("hide");
	});

	//设置新增按钮
	function set_button() {
		$('.tag1').each(function(index, self) {
			$(self).find('.tagclass1').attr('name', 'outfield[' + index + '][name]');
			$(self).find('.tagclass2').attr('name', 'outfield[' + index + '][require]');
			$(self).find('.tagclass3').attr('name', 'outfield[' + index + '][open]');
			$(self).find('.tagclass4').attr('name', 'outfield[' + index + '][type]');
		});
	};

	var sbt = new submit();
	sbt.init({"form": $("#frmpost")});

	$("#cancel").on('click', function(e) {
		return false;
	});

	//允许外部人员报名的提交赋值
	$("#outsider").on('click', function() {
		$("#outlist").toggle();
		if(this.checked){
			$("#outsider").val("1");
		}else{
			$("#outsider").val("0");
		}
	});

});
</script>
{/literal}

{include file='mobile/footer.tpl'}