{include file="$tpl_dir_base/header.tpl" css_file="jobtrain.css"}
<script src="http://qzonestyle.gtimg.cn/open/qcloud/js/vod/sdk/uploaderh5.js" charset="utf-8"></script>
<script src="http://qzonestyle.gtimg.cn/open/qcloud/video/h5/h5connect.js" charset="utf-8" ></script>
<div class="panel panel-default font12">
	<div class="panel-body">
		<form class="form-horizontal font12" role="form" action="{$form_action_url}" method="post" data-ng-app="ng.poler.plugins.pc" onsubmit="return checkForm();">
			<input type="hidden" name="formhash" value="{$formhash}" />
			{if $result}<input type="hidden" name="id" value="{$result.id}">{/if}
			<input type="hidden" name="pid" value="{$pid}">
			<input type="hidden" name="video_id" id="video_id" value="{$result.video_id}">
			<span id="auditor_choose" style="display: none;"></span>

			<div class="form-group">
				<label class="control-label col-sm-2">标题*</label>
				<div class="col-sm-9">
					<input type="text" class="form-control form-small" name="title" placeholder="不超过25个汉字" maxlength="25" required="required" value="{$result.title}"/>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">作者</label>
				<div class="col-sm-9">
					<input type="text" class="form-control form-small" name="author" placeholder="不超过8个汉字" value="{$result.author}" maxlength="8" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">内容类型</label>
				<div class="col-sm-9">
					{foreach $types as $k => $v}
					<label class="radio-inline"><input type="radio" name="type" value="{$k}" required="required" {if $k == $result['type']} checked{/if}> {$v}</label>
					{/foreach}
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">摘要</label>
				<div class="col-sm-9">
					<textarea  class="form-control form-small" id="id_title" name="summary" placeholder="不超过80个汉字" maxlength="80" rows="4" >{$result['summary']}</textarea>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">内容分类*</label>
				<div class="col-sm-9" style="padding-bottom: 5px; border-bottom: 1px dashed #ccc">
					<button type="button" class="btn btn-info pull-left" data-toggle="modal" data-target="#cataModal"><i class="fa fa-plus"></i>选择分类</button>
					<p class="form-control-static" id="cata_title"></p>
				</div>
				<div id="cataModal" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
								<h4 class="modal-title">选择分类</h4>
							</div>
							<div class="modal-body">
								<ul class="cata-chose">
									{foreach $catas as $k => $v}
									<li><i></i><label class="radio-inline"><input type="radio" class="form-small" name="cid" value="{$v['id']}" data-title="{$v['title']}" /> {$v['title']}</label></li>
									<ul style="display:none">
										{foreach $v['childs'] as $_k => $_v}
										<li><label class="radio-inline"><input type="radio" class="form-small" name="cid" value="{$_v['id']}" data-title="{$_v['title']}" /> {$_v['title']}</label></li>
										{/foreach}
									</ul>
									{/foreach}
								</ul>
							</div>
							<div class="modal-footer text-center">
								<center>
									<button type="button" class="btn btn-default btn-lg" data-dismiss="modal">取消</button>
									<button type="button" class="btn btn-success btn-lg" data-dismiss="modal" onclick="cataSelect();">确认</button>
								</center>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">适用范围</label>
				<div class="col-sm-9">
					
					<p class="form-control-static text-warning">（注：内容的适用范围即所选分类的范围，如需修改，请直接编辑修改分类的范围！）</p>
					<br />
					<div>						
						<input type="hidden" class="form-small" id="is_all" name="is_all" />
						<button type="button" class="btn disabled" id="all_btn">全公司</button>
						&nbsp;&nbsp;&nbsp;&nbsp;
						<button type="button" class="btn disabled" id="specified_btn">指定对象</button>
						<span class="is_push_box">
							<label class="radio-inline">
								<input type="checkbox" class="form-small" name="is_push" value="1" checked/>
								&nbsp;&nbsp;发送消息提醒
							</label>
						</span>
					</div>

					<div id="user_dep_container">
						<hr>
						<div class="row">
							<label class="col-sm-2 text-right padding-sm">部门：</label>
							<div class="col-sm-8">
								<pre id="dep_deafult_data" style="font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>
							</div>
						</div>
						<br>
						<div class="row">
							<label class="col-sm-2 text-right padding-sm">人员：</label>
							<div class="col-sm-8">
								<pre id="m_uid_deafult_data" style="font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>
							</div>
						</div>
					</div>

				</div>
			</div>

			<div class="form-group">
				<label class="control-label  col-sm-2 " for="id_author">消息保密</label>
				<div class="col-sm-6">
					<label class="radio-inline">
						<input type="radio" class=" form-small" name="is_secret" value="0"{if $result['is_secret'] == 0} checked="checked"{/if} /> 关闭
					</label>
					<label class="radio-inline">
						<input type="radio" class=" form-small" name="is_secret" value="1"{if $result['is_secret'] == 1} checked="checked"{/if}/> 开启
					</label>
				</div>

				<div class="col-sm-2">
					<a data-toggle="modal" data-target="#mymessage">什么是消息保密？</a>
				</div>
				<div id="mymessage" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
								<h4 class="modal-title" id="myModalLabel">什么是消息保密？</h4>
							</div>
							<div class="modal-body">
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
							<div class="modal-footer text-center">
								<center><button type="button" class="btn btn-default btn-lg" data-dismiss="modal">知道了</button></center>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">封面图片*</label>
				<div class="col-sm-9">
					{cycp_upload
						inputname='cover_id'
						hidedelete=1
						tip='(推荐尺寸 480x230)'
						attachid = $result['cover_id']
					}
				</div>
			</div>

			

			<div class="form-group article-container" id="article_container_1" style="display:none">
				<label class="control-label col-sm-2">音图内容*</label>
				<div class="col-sm-9">
					<div>
						<button type="button" class="btn btn-success" onclick="addAudimg();"><i class="glyphicon glyphicon-plus"></i></i>上传音图</button>
						<span class="is_push_box" style="display: none">
							<label class="radio-inline">
								<input type="checkbox" class="form-small" name="is_loop" value="1" checked/>
								&nbsp;&nbsp;支持循环播放页面
							</label>
						</span>
						<p class="form-control-static">提示:请上传16:9比例的图片，音频支持MP3/AMR/M4A格式，文件大小不超过50M。</p>
					</div>

					<div class="audimg-upload">
						<div class="i-list">
							<ul id="audimg_list"></ul>
						</div>
						<div class="i-detail">
							<div class="i-ctrl">
								<span class="btn fileinput-button" id="audimg_img_upload_btn">
									<i class="glyphicon glyphicon-plus"></i>
									<span>上传图片</span>
									<input id="imgUpload" type="file" name="img_file" accept="image/*">
								</span>
								<span class="btn fileinput-button" id="audimg_audio_upload_btn">
									<i class="glyphicon glyphicon-plus"></i>
									<span>上传语音</span>
									<input id="audioUpload" type="file" name="audio_file" accept="audio/*">
								</span>
								<button type="button" class="btn btn-danger" id="audimg_audio_del_btn" onclick="delAudimgAudio();"><i class="glyphicon glyphicon-trash"></i></i>删除语音</button>
								<button type="button" class="btn" id="audimg_up_btn" onclick="moveAudimg(-1);"><i class="glyphicon glyphicon-arrow-up"></i></i>上移</button>
								<button type="button" class="btn" id="audimg_down_btn" onclick="moveAudimg(1);"><i class="glyphicon glyphicon-arrow-down"></i></i>下移</button>
								<button type="button" class="btn btn-danger" id="audimg_del_btn" onclick="delAudimg();"><i class="glyphicon glyphicon-trash"></i></i>删除</button>

								<div id="audimg_progress" class="progress" style="display:none">
				                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="50" style="width: 0%">
				                    </div>
				                </div>
							</div>
							<div class="i-content"><audio src="" id="audimg_audio_src" controls="controls">您的浏览器不支持 audio 标签。</audio></div>
							<div class="i-content"><img src="/admincp/static/images/img_default.gif" id="audimg_img_src"></div>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group article-container" id="article_container_2" style="display:none">
				<label class="control-label col-sm-2">视频内容*</label>
				<div class="col-sm-9">
					<button type="button" class="btn btn-success" id="video_upload_btn"><i class="glyphicon glyphicon-plus"></i></i>上传视频</button>
					<p class="form-control-static">提示：请上传200M以内的视频文件，否则将会影响视频上传和播放速度。<a data-toggle="modal" data-target="#videoMsg">格式说明</a></p>
					<div id="video_progress" class="progress" style="display:none;margin-top:5px">
	                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="50" style="width: 0%">
	                    </div>
	                </div>
	                <p class="form-control-static" id="video_status"></p>
	                <div id="video_player" style="width:320px"></div>
				</div>
				<div id="videoMsg" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
								<h4 class="modal-title" id="myModalLabel">视频格式说明</h4>
							</div>
							<div class="modal-body">
								<p>
									目前上传文件支持如下几类格式：<br />
									微软格式：WMV，WM，ASF，ASX；<br />
									REAL格式：RM, RMVB，RA，RAM；<br />
									MPEG格式：MPG，MPEG，MPE，VOB，DAT；<br />
									其他格式：MOV，3GP，MP4，MP4V，M4V，MKV，AVI，FLV，F4V
								</p>
								<p class="form-control-static text-warning">视频上传完成后需要一段时间转码才能播放！</p>
							</div>
							<div class="modal-footer text-center">
								<center><button type="button" class="btn btn-default btn-lg" data-dismiss="modal">知道了</button></center>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2"><span id="ue_label">正文</span><span id="content_required">*</span></label>
				<div class="col-sm-9">{$ueditor_output}</div>
			</div>

			<div class="form-group">
				<div class="col-sm-3 col-sm-offset-2">
					<label for="is_comment">
						<input type="checkbox" class="form-small" id="is_comment" name="is_comment" value="1"{if $result['is_comment'] == 1} checked{/if}/>
						&nbsp;&nbsp;开启评论功能
					</label>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2">上传附件</label>
				<div class="col-sm-9">
					<div class="row">
						<div class="col-sm-2">
							<div class="uploader_box">
								<span class="btn btn-info fileinput-button">
									<i class="glyphicon glyphicon-plus"></i>
									<span>选择文件</span>
									<input id="attachUpload" type="file" name="file" multiple>
								</span>
							</div>
						</div>
						<div class="col-sm-9">
							<p class="form-control-static">支持txt,pdf,doc,ppt,xls,docx,pptx,xlsx等格式，单个文件大小不超过20M。</p>
						</div>
						
					</div>

					<div id="attach_progress" class="progress" style="display:none;margin-top:5px">
	                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="50" style="width: 0%">
	                    </div>
	                </div>

					<ul id="attach_list" class="jt-attach-list"></ul>

					
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-3 col-sm-offset-2">
					<label for="is_preview">
						<input type="checkbox" class="form-small" id="is_preview"  name="is_preview" value="1" />
						&nbsp;&nbsp;开启预览功能
					</label>
				</div>
			</div>

			<div id="preview_warp" style="display:none">
				
				<div class="form-group">
					<label class="control-label col-sm-2">预览说明</label>
					<div class="col-sm-9"><textarea  class="form-control form-small news-form" name="preview_summary" placeholder="最多输入120个字符" maxlength="120" rows="4">{$result['preview_summary']}</textarea></div>
				</div>
				<div class="from-group">
					<label class="control-label col-sm-2 " for="users_check">预览人*</label>
					<div id="users_preview" class="col-sm-9">
						<!-- angular 选人组件 begin -->
						<div class="angularjs-area" data-ng-controller="ChooseShimCtrl" >
							<a class="btn btn-defaul" data-ng-click="selectPerson('auditor','selectedAuditorCallBack')">选择人员</a>
						</div>
						<!-- angular 选人组件 end -->
						<br />

						<pre id="preview_deafult_data" style="display: none; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;"></pre>

						{*{include*}
						{*file="$tpl_dir_base/common_selector_member.tpl"*}
						{*input_type='checkbox'*}
						{*input_name='preview_id[]'*}
						{*selector_box_id='users_preview'*}
						{*allow_member=true*}
						{*allow_department=false*}
						{*}*}
					</div>
				</div>

				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-9">
						<input type="submit" name="previewsubmit" class="btn btn-primary col-sm-2" value="提交预览">
					</div>
				</div>
			</div>

			<div class="form-group" id="submit_warp">
				<div class="col-sm-offset-2 col-sm-9">
					<input type="submit" name="draftsubmit" class="btn btn-primary col-sm-2" value="保存草稿">
					<input type="submit" name="pubsubmit" class="btn btn-info col-sm-2 col-sm-offset-1" value="发布">
					<button type="button" onclick="javascript:history.go(-1);" class="btn btn-default col-sm-offset-1 col-sm-2">返回</button>
				</div>
			</div>

		</form>
	</div>
</div>
<script type="text/template" id="tpl-attach">
	<%
		var name = item['name'];
		var filetype = name.substring(name.lastIndexOf('.')+1, name.length).toLowerCase();
	%>
    <li>
    	<input type="hidden" name="attachs[<%=key%>][id]" value="<%=item['id']%>">
    	<input type="hidden" name="attachs[<%=key%>][name]" value="<%=name%>">
    	<input type="hidden" name="attachs[<%=key%>][size]" value="<%=item['size']%>">
    	<i class="ext-<%=filetype%>"><%=filetype%></i>
    	<p><%=name%></p>
    	<p>
    		<a href="<%=item['url']%>">下载</a>
    		<a href="javascript:;" class="fa fa-times text-danger" onclick="attachDel(this)">删除</a>
    	</p>
    </li>
</script>
<script type="text/template" id="tpl-audimg">
    <li onclick="selecAudimg(<%=key%>)" id="audimg_<%=key%>">
		<input type="hidden" name="audimgs[<%=key%>][audio_id]" value="<%=item['audio_id']%>">
		<input type="hidden" name="audimgs[<%=key%>][img_id]" value="<%=item['img_id']%>">
		<input type="hidden" name="audimgs[<%=key%>][img_src]" value="<%=item['img_src']%>">
		<input type="hidden" name="audimgs[<%=key%>][audio_src]" value="<%=item['audio_src']%>">
		<input type="hidden" name="audimgs[<%=key%>][audio_duration]" value="<%=item['audio_duration']%>">
		<i></i>
		<div class="i-thumb"><img src="<%=item['img_src']%>"></div>
	</li>
</script>
<script type="text/javascript">
// 初始化 begin
var auditor = [];
var auditor_choose = '';

var audimg_key = {count($result['audimgs'])};
var audimg_act = 0;

var attach_key = {count($result['attachs'])};

if(audimg_key == 0) {
	addAudimg();
}

changeType({$result['type']});

var cid = "{$result['cid']}";

if(cid){
	$(":radio[name=cid][value="+cid+"]").attr('checked',true);
	cataSelect();
}


var data_json = {};
var audimgs_json = {$result['audimgs_json']};
if(audimgs_json.length > 0) {
	for (var i in audimgs_json) {
		data_json={
			key: i,
			item: audimgs_json[i]
		}
		$('#audimg_list').append(txTpl('tpl-audimg', data_json));
		audimg_key++;
	}
	orderAudimg();
	selecAudimg(0);
}

var attachs_json = {$result['attachs_json']};
if(attachs_json.length > 0) {
	for (var i in attachs_json) {
		data_json = {
			key: attach_key,
			item: attachs_json[i]
		}
		$('#attach_list').append( txTpl('tpl-attach', data_json) );
		attach_key++;
	}
}

// 初始化 end
// 设置语音上传开关
var is_uploading = false;

$(function(){
	// 附件上传
	$('#attachUpload').fileupload({
		dataType: 'json',
		url: '/admincp/api/attachment/upload/?file=file&is_attach=1',
		limitMultiFileUploads: 1,
		sequentialUploads: true,
		change: function (e, data) {
			for (var i = 0; i < data.files.length; i++) {
				if(data.files[i].size>20000000){
		        	alert('文件超过大小限制');
		        	return false;
		        }
			}
        	
        },
		start: function (e, data) {
        	$('#attach_progress .progress-bar').css('width','0%');
        	$('#attach_progress').show();
        },
        done: function (e, data) {
        	if(data.result.errcode == 0) {
        		var result=data.result.result;
        		var data = {
        			key: attach_key,
        			item: {
						id: result.id,
						name: result.file[0]['name'],
						//url: result.file[0]['url'],
						url: 'http://{$domain}/Jobtrain/Apicp/Attach/download?aid='+result.id,
						size: result.file[0]['size']
					}
        		}
        		$('#attach_list').append( txTpl('tpl-attach', data) );
        		attach_key++;
        	}
        	$('#attach_progress').hide();
        },
        progressall: function (e, data) {
	        var progress = parseInt(data.loaded / data.total * 100, 10);
	        $('#attach_progress .progress-bar').css('width', progress + '%');
	    }
    });

    // 音图图片上传
	$('#imgUpload').fileupload({
		dataType: 'json',
		url: '/admincp/api/attachment/upload/?file=img_file',
		sequentialUploads: true,
		start: function (e, data) {
			is_uploading = true;
        	$('#audimg_progress .progress-bar').css('width','0%');
        	$('#audimg_progress').show();
        },
        done: function (e, data) {
        	is_uploading = false;
        	if(data.result.errcode == 0) {
        		var result=data.result.result;
        		$('#audimg_img_src').attr('src', result.img_file[0].url);
        		$("input[name='audimgs["+audimg_act+"][img_src]']").val(result.img_file[0].url);
        		$("input[name='audimgs["+audimg_act+"][img_id]']").val(result.id);
        		$("#audimg_"+audimg_act+" img").attr('src', result.img_file[0].url);
        		$('#audimg_progress').hide();
        		selecAudimg(audimg_act);
        	}
        	
        },
        progressall: function (e, data) {
	        var progress = parseInt(data.loaded / data.total * 100, 10);
	        $('#audimg_progress .progress-bar').css('width', progress + '%');
	    }
    });

    // 音图语音上传
	$('#audioUpload').fileupload({
		dataType: 'json',
		url: '/admincp/api/attachment/upload/?file=audio_file&is_attach=1',
		sequentialUploads: true,
		start: function (e, data) {
			is_uploading = true;
        	$('#audimg_progress .progress-bar').css('width','0%');
        	$('#audimg_progress').show();
        },
        done: function (e, data) {
        	//console.log(e, data);
        	is_uploading = false;
        	if(data.result.errcode == 0) {
        		var result=data.result.result;
        		$('#audimg_audio_src').show();
        		$('#audimg_audio_src').attr('src', result.audio_file[0].url);
        		$("input[name='audimgs["+audimg_act+"][audio_src]']").val(result.audio_file[0].url);
        		$("input[name='audimgs["+audimg_act+"][audio_id]']").val(result.id);
        		// 获取语音文件时长
        		var audio = document.getElementById('audimg_audio_src');
        		if(!audio.paused) {                 
                    audio.pause();
                }  
        		//var audio = new Audio();
				//audio.src = result.audio_file[0].url;
				/*
				audio.addEventListener('loadedmetadata', function() {
				    $("input[name='audimgs["+audimg_act+"][audio_duration]']").val(audio.duration);
				});
				*/
				audio.addEventListener('durationchange', function(ele) {
                    $("input[name='audimgs["+audimg_act+"][audio_duration]']").val(ele.target.duration);
                });
                selecAudimg(audimg_act);
        	}else{
        		alert(data.result.errmsg);
        	}
        	$('#audimg_progress').hide();
        	
        },
        progressall: function (e, data) {
	        var progress = parseInt(data.loaded / data.total * 100, 10);
	        $('#audimg_progress .progress-bar').css('width', progress + '%');
	    }
    });

	// 改变文件类型
    $("input[name='type']").click( function() {
		changeType($(this).val());
	});

	// is_preview
	$('#is_preview').click( function() {
		if ($(this).is(':checked')) {
			$('#preview_warp').show();
			$('#submit_warp').hide();
		}else{
			$('#preview_warp').hide();
			$('#submit_warp').show();
		}
	});

	// 子分类显示
	$('.cata-chose li i').click( function() {
		var subj = $(this).parent().next('ul');
		if(subj.is(":visible")==false) {
			$(this).addClass('active');
			subj.show();
		}else{
			$(this).removeClass('active');
			subj.hide();
		}
	});
});


// 预览人员选择回调
function selectedAuditorCallBack(data, id) {
	auditor = data;
	auditor_choose = '';
	var select_auditor_name = '';
	for (var i = 0; i < data.length; i ++) {
		auditor_choose += '<input name="preview_id[]" value="' + data[i]['m_uid'] + '" type="hidden">';
		select_auditor_name += data[i]['m_username'] + ' ';
	}
	$('#auditor_choose').html(auditor_choose);
	// 展示
	if (select_auditor_name != '') {
		$('#preview_deafult_data').html(select_auditor_name).show();
	} else {
		$('#preview_deafult_data').hide();
	}
}

// 改变文件类型
function changeType(type){
	if(type==0){
		$('#ue_label').html('正文');
		$('#content_required').show();
	}else{
		$('#ue_label').html('内容描述');
		$('#content_required').hide();
	}
	$('.article-container').hide();
	$('#article_container_'+type).show();
}


// 删除附件
function attachDel(obj){
	var _this=$(obj);
	_this.parent().parent().remove();
}

// 选择分类
function cataSelect(){
	var cid=$("input[name='cid']:checked").val();
	$('#cata_title').html('&nbsp;&nbsp;&nbsp;' + $("input[name='cid']:checked").attr('data-title'));
	$.ajax({
        url:'/Jobtrain/Apicp/Category/auths_get',
        dataType: 'json',
        data:{
        	cid: cid
        },
        type:'get',
        success:function(data){
            if(data.errcode == 0){
                if(parseInt(data.result.is_all)==1) {
                	$('#is_all').val(1);
                	$('#all_btn').addClass('btn-primary');
                	$('#specified_btn').removeClass('btn-primary');
                	$('#user_dep_container').hide();
                }else{
                	$('#is_all').val(0);
                	$('#all_btn').removeClass('btn-primary');
                	$('#specified_btn').addClass('btn-primary');
                	$('#user_dep_container').show();
                	var m_uid_name = '';
                	var dep_name = '';
                	$.each(data.result.departments, function (index, item) {
			            dep_name += item + ' ';
			        });
			        $.each(data.result.members, function (index, item) {
			            m_uid_name += item + ' ';
			        });
			        if(dep_name != '') {
			        	$('#dep_deafult_data').html(dep_name).show();
			        }else{
			        	$('#dep_deafult_data').html('').hide();
			        }
			        if(m_uid_name != '') {
			        	$('#m_uid_deafult_data').html(m_uid_name).show();
			        }else{
			        	$('#m_uid_deafult_data').html('').hide();
			        }
                }
            }
        }
    });
}

// 添加音图
function addAudimg() {
	var data={
		key: audimg_key,
		item: {
			audio_id: '',
			audio_src: '',
			audio_duration: 0,
			img_id: '',
			img_src: '/admincp/static/images/img_default.gif'
		}
	}
	$('#audimg_list').append(txTpl('tpl-audimg', data));
	orderAudimg();
	selecAudimg(audimg_key);
	audimg_key++;
}

// 选择音图
function selecAudimg(key){

	if(is_uploading){
		alert("请等待上传完毕再切换！");
		return false;
	}

	audimg_act=key;
	$('#audimg_list i').removeClass('active');
	$('#audimg_'+key+' i').addClass('active');

	var audio_src = $("input[name='audimgs["+key+"][audio_src]'").val();
	var img_src = $("input[name='audimgs["+key+"][img_src]'").val();

	$('#audimg_audio_src').attr('src', audio_src);
	$('#audimg_img_src').attr('src', img_src);

	if(audio_src == ''){
		// 更新上传语音按钮
		$('#audimg_audio_upload_btn i').removeClass('glyphicon-edit');
		$('#audimg_audio_upload_btn i').addClass('glyphicon-plus');
		$('#audimg_audio_upload_btn span').html('上传语音');
		// 禁止删除语音按钮
		$('#audimg_audio_del_btn').addClass('disabled');
		$('#audimg_audio_src').hide();
	}else{
		// 更新上传语音按钮
		$('#audimg_audio_upload_btn i').removeClass('glyphicon-plus');
		$('#audimg_audio_upload_btn i').addClass('glyphicon-edit');
		$('#audimg_audio_upload_btn span').html('更新语音');
		// 允许删除语音按钮
		$('#audimg_audio_del_btn').removeClass('disabled');
		$('#audimg_audio_src').show();
	}
	if(img_src == ''||img_src=='/admincp/static/images/img_default.gif'){
		// 更新上传图片按钮
		$('#audimg_img_upload_btn i').removeClass('glyphicon-edit');
		$('#audimg_img_upload_btn i').addClass('glyphicon-plus');
		$('#audimg_img_upload_btn span').html('上传图片');
	}else{
		// 更新上传图片按钮
		$('#audimg_img_upload_btn i').removeClass('glyphicon-plus');
		$('#audimg_img_upload_btn i').addClass('glyphicon-edit');
		$('#audimg_img_upload_btn span').html('更新图片');
	}

	if($('#audimg_'+audimg_act).next().length > 0){
		$('#audimg_down_btn').removeClass('disabled');
	}else{
		$('#audimg_down_btn').addClass('disabled');
	}
	if($('#audimg_'+audimg_act).prev().length > 0){
		$('#audimg_up_btn').removeClass('disabled');
	}else{
		$('#audimg_up_btn').addClass('disabled');
	}
}

// 删除音图
function delAudimg(){
	$('#audimg_'+audimg_act).remove();
	orderAudimg();
	var id=$("#audimg_list li:eq(0)").attr('id');
	var idarr=id.split('audimg_');
	selecAudimg(idarr[1]);
}
// 删除音图语音
function delAudimgAudio(){
	$("input[name='audimgs["+audimg_act+"][audio_src]'").val('');
	$("input[name='audimgs["+audimg_act+"][audio_id]'").val('');
	selecAudimg(audimg_act);
}
// 音图移动
function moveAudimg(dirc){
	if(is_uploading){
		alert("请等待上传完毕再切换！");
		return false;
	}
	if(dirc==1){
		if($('#audimg_'+audimg_act).next().length > 0){
			$('#audimg_'+audimg_act).next().after($('#audimg_'+audimg_act));
		}
	}else{
		if($('#audimg_'+audimg_act).prev().length > 0){
			$('#audimg_'+audimg_act).prev().before($('#audimg_'+audimg_act));
		}
	}
	selecAudimg(audimg_act);
	orderAudimg();
}
// 音图序号重整
function orderAudimg(){
	$("#audimg_list li").each(function(i){
		$(this).children('i').html(i+1);
	});
	var count=$("#audimg_list li").size();
	if(count>1){
		$('#audimg_del_btn').removeClass('disabled');
	}else{
		$('#audimg_del_btn').addClass('disabled');
	}
}


// 发布检测
function checkForm(){

	var type = parseInt($("input[name='type']:checked").val());
	var is_check=true;

	if( $("input[name='cid']:checked").val() == undefined ) {
		alert('请选择分类');
		return false;
	}

	if ($('input[name="cover_id"]').val() == '') {
		alert('请选择封面图片！');
		return false;
	}

	if ($('#is_preview').is(':checked')) {
		if($("input[name='preview_id[]']").val() == undefined){
			alert('请选择预览人员');
			return false;
		}
	}

	if(type == 1){
		// 检查音图文件是否上传
		var arr_s=[];
		var audio_ids = '';
		$("#audimg_list li").each(function(i){
			arr_s=$(this).attr('id').split('audimg_');
			if($("input[name='audimgs["+arr_s[1]+"][img_id]'").val() == ''){
				is_check=false;
				alert('音图[ '+(i+1)+' ]图片文件没有上传');
				return false;
			}
			audio_ids += $("input[name='audimgs["+arr_s[1]+"][audio_id]'").val();
		});
		if( is_check == false ) {
			return false;
		}
		if(audio_ids==''||audio_ids==undefined) {
			alert('音图至少上传一个语音文件');
			return false;
		}
	}else if(type == 2) {
		if($("#video_id").val() == ''){
			alert('视频文件没有上传');
			return false;
		}
	}

	if(!UE.getEditor('content').hasContents()&&type==0){
		alert('请输入正文');
		return false;
	}
	return true;
}


/**
 * 构造播放器
 */
var player;
var video_id = "{$result['video_id']}";
if(video_id != '') {
	videoPlay(video_id);
}
//videoPlay('16092504232103514290');

function videoPlay(file_id){
	player = new qcVideo.Player(
		'video_player',//页面放置播放位置的元素 ID
		{
			//视频 ID (必选参数)
			'file_id': file_id,
			//应用 ID (必选参数)，同一个账户下的视频，该参数是相同的
			'app_id': '{$app_id}',
			'width': 320,
			'height': 240
		}
	);
}

/**
 * @param upBtnId 上传按钮ID
 * @param secretId 云api secretId
 * @param isTranscode 是否转码
 * @param isWatermark 是否设置水印
 * @param [transcodeNotifyUrl] 转码成功后的回调
 * @param [classId] 分类ID
 */
var accountDone = function (upBtnId, secretId, isTranscode, isWatermark, transcodeNotifyUrl, classId) {

    var ErrorCode = qcVideo.get('ErrorCode')
        , Log = qcVideo.get('Log')
        , JSON = qcVideo.get('JSON')
        , util = qcVideo.get('util')
        , Code = qcVideo.get('Code')
        , Version = qcVideo.get('Version')
        ;
    qcVideo.uploader.init(
        {
            web_upload_url: 'http://vod.qcloud.com/v2/index.php',
            secretId: secretId, // 云api secretId
            //secretKey: '', // 测试完了请删除
            getSignature: function(argStr,done){
	            $.ajax({
	                'dataType': 'json',
	                'url': 'http://{$domain}/Jobtrain/Apicp/Qcloud/signature_get?args='+encodeURIComponent(argStr),
	                'success': function(d){
	                    done(d['result']);
	                }
	            });
	        },
            upBtnId: upBtnId, //上传按钮ID（任意页面元素ID）
            isTranscode: isTranscode,//是否转码
            isWatermark: isWatermark//是否设置水印
            ,after_sha_start_upload: true//sha计算完成后，开始上传 (默认非立即上传)
    		,sha1js_path: 'http://{$domain}/admincp/static/javascripts/calculator_worker_sha1.js'
            ,disable_multi_selection: false //禁用多选 ，默认为false
            ,transcodeNotifyUrl: transcodeNotifyUrl
            ,classId: classId
        }, {
            onFileUpdate: function (args) {
                if(args.code == Code.UPLOAD_DONE){
                	$('#video_progress').hide();
                    $('#video_id').val(args.serverFileId);
                    // 设置播放器
                    if(player == undefined) {
                    	videoPlay(args.serverFileId);
                    }else{
                    	player.changeVideo({
	                    	'file_id': args.serverFileId
	                    });
                    }
                    
                }else{
                	$('#video_progress').show();
                }
                $('#video_progress .progress-bar').css('width', args.percent + '%');
                $('#video_status').html(''
                    + '文件名：' + args.name
                    //+ ' >> 大小：' + util.getHStorage(args.size)
                    //+ ' >> 状态：' + util.getFileStatusName(args.status) + ''
                    + ( args.percent ? ' >> 进度：' + args.percent + '%' : '')
                    + ( args.speed ? ' >> 速度：' + args.speed + '' : '')
                );
            },
            onFileStatus: function (info) {
            },
            onFilterError: function (args) {
            	var msg = 'message:' + args.message + (args.solution ? (';solution==' + args.solution) : '');
				alert(msg);
            }
        }
    );
};

accountDone('video_upload_btn', '{$secret_id}', true, false, null, null);



</script>

{include file="$tpl_dir_base/footer.tpl"}