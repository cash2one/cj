{include file="$tpl_dir_base/header.tpl" css_file="jobtrain.css"}
{if $result['type']==2}
<script src="http://qzonestyle.gtimg.cn/open/qcloud/video/h5/h5connect.js" charset="utf-8" ></script>
{/if}
<ul class="nav nav-tabs font12">
	<li class="active">
		<a href="#papaer_base" data-toggle="tab">
			内容详情&nbsp;
		</a>
	</li>
	<li>
		<a href="#papaer_questions" data-toggle="tab">
			讨论记录&nbsp;
		</a>
	</li>
</ul>

<div class="tab-content">
	<div class="tab-pane active" id="papaer_base">
	
		<div class="panel panel-default">
			<div class="panel-body exam-paper-view">
				
				<div class="form-group">
					<label class="control-label col-sm-2 text-right">标题:</label>
					<div class="col-sm-9">{$result['title']}</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2 text-right">作者:</label>
					<div class="col-sm-9">{$result['author']}</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2 text-right">内容类型:</label>
					<div class="col-sm-9">{$types[$result['type']]}</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2 text-right">摘要:</label>
					<div class="col-sm-9">{$result['summary']}</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2 text-right">消息保密:</label>
					<div class="col-sm-9">{if $result['is_secret']}是{else}否{/if}</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2 text-right">内容分类:</label>
					<div class="col-sm-9">{$catas[$result['cid']]['title']}</div>
				</div>
				{if $result['type']==1}
				<div class="form-group">
					<label class="control-label col-sm-2 text-right">音图:</label>
					<div class="col-sm-9">
						
						<div class="audimg-upload">
							<div class="i-list">
								<ul id="audimg_list"></ul>
							</div>
							<div class="i-detail">
								
								<div class="i-content"><audio src="" id="audimg_audio_src" controls="controls">您的浏览器不支持 audio 标签。</audio></div>
								<div class="i-content"><img src="/admincp/static/images/img_default.gif" id="audimg_img_src"></div>
							</div>
						</div>
					</div>
				</div>
				{/if}
				{if $result['type']==2}
				<div class="form-group">
					<label class="control-label col-sm-2 text-right">视频:</label>
					<div class="col-sm-9">
		                <div id="video_player" style="width:320px"></div>
					</div>
				</div>
				{/if}
				<div class="form-group">
					<label class="control-label col-sm-2 text-right">封面图片:</label>
					<div class="col-sm-9">
						<img src="{$result['picurl']}" width="200">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2 text-right">目标对象:</label>
					<div class="col-sm-9">
						{if $catas[$result['cid']]['is_all']}
							全公司
						{else}
							{if $departments}<pre>{$departments}</pre>{/if}
							{if $members}<pre>{$members}</pre>{/if}
						{/if}
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2 text-right">讨论:</label>
					<div class="col-sm-9">
						{if $result['is_comment']}开启{else}未开启{/if}
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-sm-2 text-right">附件:</label>
					<div class="col-sm-9">
						<ul id="attach_list" class="jt-attach-list">无</ul>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-sm-2 text-right">正文:</label>
					<div class="col-sm-9">{$result['content']}</div>
				</div>
				
			</div>
		</div>

	</div>

	<div class="tab-pane" id="papaer_questions">
		
		<div class="panel panel-default">


			<div class="panel-body" id="comments_list"></div>

			<div id="comments_page"></div>
			
		</div>

	</div>
</div>

<div class="form-group">
	<div class="col-sm-offset-2 col-sm-10">
		<div class="row">
			<a href="javascript:history.go(-1);" class="btn btn-default col-md-2">返回</a>
			<a href="{$edit_url}?id={$result.id}" class="btn btn-primary col-sm-offset-1 col-md-2">编辑</a>
			<a href="{$del_url}?id={$result.id}" onclick="return aritcleDel();" class="btn btn-danger col-sm-offset-1 col-md-2">删除</a>
		</div>
	</div>
</div>

<div id="myModal" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="myModalLabel">回复</h4>
			</div>
			<div class="modal-body padding-sm">
				<textarea class="form-control form-small" name="content" id="comment_content" rows="4" ></textarea>
				
			</div>
			
			<!-- / .modal-body -->
			<div class="modal-footer text-right">
				<button type="button" class="btn btn-default btn-sm btn-primary" data-dismiss="modal" onclick="commentPost()">确定</button>
			</div>
		</div>
		<!-- / .modal-content -->
	</div>
	<!-- / .modal-dialog -->
</div>
<!-- /.modal -->

<script type="text/template" id="tpl-attach">
	<%
		var name = item['name'];
		var filetype = name.substring(name.lastIndexOf('.')+1, name.length).toLowerCase();
	%>
    <li>
    	<i class="ext-<%=filetype%>"><%=filetype%></i>
    	<p><%=name%></p>
    	<p>
    		<a href="<%=item['url']%>">下载</a>
    	</p>
    </li>
</script>
<script type="text/template" id="tpl-audimg">
    <li onclick="selecAudimg(<%=key%>)" id="audimg_<%=key%>">
		<input type="hidden" name="audimgs[<%=key%>][audio_src]" value="<%=item['audio_src']%>">
		<input type="hidden" name="audimgs[<%=key%>][img_src]" value="<%=item['img_src']%>">
		<i><%=no%></i>
		<div class="i-thumb"><img src="<%=item['img_src']%>"></div>
	</li>
</script>
<script type="text/template" id="tpl-comments">
	<% if (!jQuery.isEmptyObject(list)) { %>
	<ul class="jt-comments">
		<% $.each(list,function(n,val){ %>
		<li>
			<img src="<%=val['m_face']%>" onerror="ImgNotExist();" class="i-avatar">
			<div class="i-right">
				<div class="i-author">
					<%=val['m_username']%> &nbsp;&nbsp;发表于 &nbsp;&nbsp; <%=val['created_u']%>
				</div>
				<div class="i-text"><%=val['content']%></div>
				<% if(val['r_username']) { %>
				<div class="i-reply"><%=val['r_username']%>：<%=val['r_content']%></div>
				<% } %>
			</div>
			<div class="i-ctrl">
				<a href="javascript:;" onclick="commentReply(<%=val['id']%>,'<%=val['m_username']%>',<%=val['m_uid']%>)"><i class="fa fa-comment"></i>回复</a>
				<a href="javascript:;" class="text-danger" onclick="commentDel(<%=val['id']%>)"><i class="fa fa-times"></i>删除</a>
			</div>
		</li>
		<% }) %>
	</ul>
	<% }else{ %>
	<div class="warning">暂无讨论内容</div>
	<% } %>
</script>
<script type="text/template" id="tpl-page">
	<% if (total > 0) { %>
	<div class="text-right vcy-page"><%=multi%></div>
	<% } %>
</script>
<script type="text/javascript">
var data_json = {};

var attachs_json = {$result['attachs_json']};
var type = {$result['type']}

if(attachs_json.length > 0) {
	$('#attach_list').html('');
}
for (var i in attachs_json) {
	data_json = {
		item: attachs_json[i]
	}
	$('#attach_list').append( txTpl('tpl-attach', data_json) );
}

var audimgs_json = {$result['audimgs_json']};
for (var i in audimgs_json) {
	data_json={
		key: i,
		no: parseInt(i)+1,
		item: audimgs_json[i]
	}
	$('#audimg_list').append(txTpl('tpl-audimg', data_json));
}
if(audimgs_json.length > 0) {
	selecAudimg(0);
}


// 选择音图
function selecAudimg(key){
	$('#audimg_list i').removeClass('active');
	$('#audimg_'+key+' i').addClass('active');

	var audio_src = $("input[name='audimgs["+key+"][audio_src]'").val();
	var img_src = $("input[name='audimgs["+key+"][img_src]'").val();

	$('#audimg_audio_src').attr('src', audio_src);
	$('#audimg_img_src').attr('src', img_src);

	if(audio_src == ''){
		$('#audimg_audio_src').hide();
	}else{
		$('#audimg_audio_src').show();
	}
}

/**
 * 构造播放器
 */
var player;
if(type == 2) {
	videoPlay("{$result['video_id']}");
}

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

var limit = 10;
var aid = "{$result['id']}";
function _page(){

	$('#comments_page .pagination a').on('click', function (e) {
		e.preventDefault();
		var page = 'page';
		var result = $(this).attr('href').match(new RegExp("[\?\&]" + page + "=([^\&]+)", "i"));
		if (result == null || result.length < 1) {
			return '';
		}
		var data = {
			page: result[1],
			limit: limit,
			aid: aid
		};
		_post(data);

	});

}
function _post(data){
	$.ajax({
		url: '/Jobtrain/Apicp/Comment/list_get',
		dataType: 'json',
		data: data,
		type: 'get',
		success: function (result) {
			if(result.errcode == 0){
				$('#comments_list').html(txTpl('tpl-comments', result.result));
				$('#comments_page').html(txTpl('tpl-page', result.result));
				_page();
			}else{
				alert(result.errcode + ' : ' + result.errmsg);
			}
		}
	});
}
// 输出评论列表
_post({
	limit: limit,
	aid: aid
});
	
// 删除评论
function commentDel(id){
	if (!confirm("您确认要删除吗？")) {
        return false;
    }
	$.ajax({
		url: '/Jobtrain/Apicp/Comment/del_get',
		dataType: 'json',
		data: {
			id: id
		},
		type: 'get',
		success: function (result) {
			_post({
				limit: limit,
				aid: aid
			});
		}
	});
}
// 回复评论
function commentReply(id, username,uid){
	$('#comment_content').val('@'+username+'：');
	$('#comment_content').attr('data-to', '@'+username+'：');
	$('#comment_content').attr('data-uid', uid);
	$('#comment_content').attr('data-toid', id);
	$('#comment_content').attr('data-username', username);
	$('#myModal').modal();
}
// 发布评论
function commentPost(){
	var txt = $('#comment_content').attr('data-to');
	var content = $('#comment_content').val();
	var arr = content.split(txt);
	if(arr[0]!=undefined){
		content = arr[0];
	}
	if(arr[1]!=undefined){
		content += arr[1];
	}
	if(content==''){
		alert('请输入回复内容');
	}
	$.ajax({
		url: '/Jobtrain/Apicp/Comment/add_post',
		dataType: 'json',
		data: {
			content: content,
			aid: aid,
			to_uid: $('#comment_content').attr('data-uid'),
			to_username: $('#comment_content').attr('data-username'),
			toid: $('#comment_content').attr('data-toid')
		},
		type: 'post',
		success: function (result) {
			_post({
				limit: limit,
				aid: aid
			});
		}
	});
}
// 图片不存在
function ImgNotExist(){
	var img=event.srcElement;
	img.src='/admincp/static/images/qq.png';
	img.onerror=null;
}
// 删除
function aritcleDel(){
	if (!confirm('您确认要删除吗？')){ 
		return false; 
	}else{ 
		return true; 
	}
}

</script>


{include file="$tpl_dir_base/footer.tpl"}
