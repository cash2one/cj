{include file="$tpl_dir_base/header.tpl"}
<style>
.left-col {
	width: 339px;
}
#overtime {
	width: 150px;
	display: inline;
}
#time {
	height: 32px;
	width: 80px;
}
.gray {
	color: #999;
}
#edui1 {
	z-index: 9!important;
}
.profile-photo {
	position: relative;
}
#mobile_body {
	position: absolute;
	top: 150px;
	left: 32px;
	width: 265px;
	height: 418px;
	overflow: hidden;
}
#prev_content {
	width: 100%;
}
#prev_content div img {
	width: 267px;
}
#prev_title {
	position: absolute;
	margin: 0;
	padding: 0;
	top: 131px;
	color: #fff;
	text-align: center;
	font-size: 14px;
	width: 175px;
	margin-left: 87px;
	overflow: hidden;
	height: 15px;
}
</style>
<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<div class="left-col">
				<div class="profile-block">
					<div class="panel profile-photo">
						<h1 id="prev_title">{$act.subject}</h1>
						<img id="preview" src="/admincp/static/images/act_preview.png"/>
						<div id="mobile_body">
							<div id="prev_content">
								<div>{$act.content}</div>
								<img id="reg_img" src="/admincp/static/images/reg.png"/>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="right-col">
				<div class="panel tl-body">
					<form id="form" class="form-horizontal font12" role="form" method="post" action="javascript:;">
						<input type="hidden" name="formhash" value="{$formhash}" />
						<div class="form-group">
							<label class="col-sm-2 control-label">活动主题</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" id="subject" name="subject" value="{$act.subject|escape}" maxlength="30" required="required" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">活动类型</label>
							<span class="space"></span>
							<div class="col-sm-10">
								<select id="typeid" name="typeid" class="form-control form-small" data-width="auto"  required="required">
									<option value="" selected="selected">请选择类型</option>
									{foreach $cats as $k => $v}
									<option value="{$k}"{if $act.typeid == $k} selected="selected"{/if}>{$v}</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">活动封面</label>
							<div class="col-sm-10">
								<span class="gray">（在活动列表中显示的活动图片，图片建议尺寸：900像素 * 500像素）</span><br/><br/>
								{cycp_upload
								inputname='cover'
								attachid = $act.cover
								showdelete=0
								}
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">开始日期</label>
							<div class="col-sm-10">
								<input type="text" class="input-sm form-control" id="begintime" name="begintime" style="width:150px;display:inline;" value="{$act._begintime|escape}" required="required" />
								<select id="btime" name="btime">
									<option>全天</option>
									{foreach $times as $k => $t}
									<option value="{$t}"{if $t == $act._btime} selected{/if}>{$t}</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">截止日期</label>
							<div class="col-sm-10">
								<input type="text" class="input-sm form-control" id="overtime" name="overtime" value="{$act._overtime|escape}" required="required" />
								<select id="time" name="time">
									<option>全天</option>
									{foreach $times as $k => $t}
									<option value="{$t}"{if $t == $act._time} selected{/if}>{$t}</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">通知对象</label>
							<div class="col-sm-10">
								<p class="gray">（活动发出后，会第一时间推送消息的对象,若不选择会通知全部人员）</p>
								{include
								file="$tpl_dir_base/common_selector_member.tpl"
								input_type='checkbox'
								input_name_department='deps[]'
								selector_box_id='contact_container'
								allow_member=false
								allow_department=true
								default_data=$deps
								}
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">活动内容</label>
							<div class="col-sm-10">
								{$ueditor_output}
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-10">
								是否可自定义报名信息
								<input type="radio" name="is_custom" value="1"{if $act.is_custom == 1} checked{/if}/> 是 　
								<input type="radio" name="is_custom" value="0"{if $act.is_custom == 0} checked{/if}/> 否
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label"></label>
							<div class="col-sm-10">
								是否需要报名
								<input type="radio" name="needsign" value="1"{if $act.needsign == 1} checked{/if}/> 需要 　
								<input type="radio" name="needsign" value="0"{if $act.needsign == 0} checked{/if}/> 不需要
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10" style="text-align:center">
								<input type="hidden" name="id" value="{$act.id}"/>
								{if $act.id && $act.is_push}
								<button name="draft" type="submit" class="btn btn-primary">暂停活动</button>
								{else}
								<button name="draft" type="submit" class="btn btn-primary">保存草稿</button>
								{/if}
								&nbsp;&nbsp;
								<button name="push" type="submit" class="btn btn-primary">发布</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="myModal" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="myModalLabel">预览</h4>
			</div>
			<div class="modal-body padding-sm">
				<h4 id="preview_title">标题</h4>
				<p class="text-default text-sm">2014-12-12 12:11  上海畅移</p>

				<hr>

				<div id="preview_content">内容</div>
			</div>
			<!-- / .modal-body -->
		</div>
		<!-- / .modal-content -->
	</div>
	<!-- / .modal-dialog -->
</div>
<!-- /.modal -->

<script>
	$(function (){
		init.push(function () {
			$('#overtime').datepicker();
			$('#begintime').datepicker();
		});
		$('button[name=draft], button[name=push]').click(function (){
			var data = $('#form').serializeArray();
			//根据按钮name,判断是发布还是草稿
			data.push({
				name : 'is_push',
				value : this.name=='push' ? 1 : 0
			});
			for(k in data) {
				if(data[k].name == 'content') {
					if(data[k].value.length < 1) {
						return alert('活动内容不能为空');
					}
				}
			}
			$.post('{$editUrlBase}', data, function (json){
				if(json.state) {
					alert('保存成功');
					location.href = json.info;
				}else{
					alert(json.info);
				}
			}, 'json');
		});
		//隐藏/显示预览图中的报名信息
		$('input[name=is_custom]').click(reg_img);
		reg_img();

		//预览处理
		setInterval(preview, 1000);
		$('#subject').change(function (){
			$('#prev_title').text(this.value);
		});
	});
	var content;
	//预览
	function preview()
	{
		var data = $('#form').serializeArray();
		for(k in data) {
			if(data[k].name == 'content') {
				var v = data[k].value;
			}
		}
		if(v != content) {
			content = v;
			$('#prev_content>div').html(v);
		}
	}
	//报名信息隐藏/显示
	function reg_img()
	{
		if($('input[name=is_custom]:checked').val() == 1) {
			$('#reg_img').show();
		}else{
			$('#reg_img').hide();
		}
	}
</script>
{include file="$tpl_dir_base/footer.tpl"}