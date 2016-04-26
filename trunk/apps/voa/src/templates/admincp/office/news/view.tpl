{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title font12"><strong>公告详情</strong></h3>
		</div>
		<div class="panel-body upload">
				<div class="form-group">
					<label class="control-label col-sm-2">标题:</label>
					<div class="col-sm-9">{$news['title']|escape}</div>
				</div>
				{if $news['summary']}
				<div class="form-group">
					<label class="control-label col-sm-2">摘要:</label>
					<div class="col-sm-9">{$news['summary']|escape}</div>
				</div>
				{/if}
				<div class="form-group">
					<label class="control-label col-sm-2">消息类型:</label>
					<div class="col-sm-9">{$categories[$news['nca_id']]['name']}</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">消息保密:</label>
					<div class="col-sm-9">{if $news['is_secret']==1}开启{else}关闭{/if}</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">封面图片:</label>
					<div class="col-sm-9"><a href="{$news['cover']['url']}" target="_blank"><img src="{$news['cover']['url']}" width="150"/></a></div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-2">阅读权限:</label>
					<div class="col-sm-9">
						{if $news['is_all'] == 1}
						全公司
						{else}
							{if $default_departments != ''} 部门：{$default_departments}<br>{/if}
							{if $default_users != ''} 人员：{$default_users}{/if}
						{/if}
				</div>
				</div>
				<div class="form-group">				
					<div class="col-sm-11">{$news['content']}</div>
				</div>	
			
			<span class="space"></span>
			{if $news['comments']}			
			<ul class="nav nav-tabs font12">
				<li class="active">
					<a href="#list_proc" data-toggle="tab">评论&nbsp;</a>
				</li>	
			</ul>
		<div class="panel widget-comments">
			<div class="panel-body" id="comments">
				{foreach $news['comments'] as $comment}						
				<div class="comment clearfix" id="div_{{$comment['ncomm_id']}}">
					<img src="{$comment['avatar']}" alt=""  class="comment-avatar">
					<div class="comment-body pull-left no-margin padding-sm-hr">
						<div class="comment-by">
							{$comment['m_username']} &nbsp;&nbsp;发表于 &nbsp;&nbsp; {$comment['created']}
						</div>
						<div class="comment-text">{$comment['content']|escape}</div>
					</div>
					<button type="button" class="close"  onclick="del_comment('{$comment['ncomm_id']}')">×</button>
				</div>
				{/foreach}			
			</div>		
		</div>
		{/if}		
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-9">
				<div class="row">
					<div class="col-md-4"><a href="javascript:history.go(-1);" class="btn btn-default col-md-9">返回</a></div>
					<div class="col-md-4"><a href="{$edit_url}&multiple={$news['multiple']}" class="btn btn-default btn-primary col-md-9">编辑</a></div>
					<div class="col-md-4"><a href="javascript:void(0)" class="btn btn-default btn-danger col-md-9" id="delete_btn">删除</a></div>
				</div>
			</div>
		</div>
		
		<!--点赞列表-->
		<br />
		<ul class="nav nav-tabs font12">
			<li class="active"><a href="javascript:;" data-toggle="tab"> <span
					class="badge pull-right">{$news['num_like']}</span> 点赞&nbsp;
			</a></li>
		</ul>
		<br />
		<div class="tab-pane" id="list_member">
			<table
				class="table table-striped table-hover table-bordered font12 table-light">
				<colgroup>
					
					<col class="t-col-18" />
					<col class="t-col-20" />
					<col class="t-col-16" />
					<col class="t-col-18" />
				</colgroup>
				<thead>
					<tr>
						<th>点&nbsp;赞&nbsp;人</th>
						<th>点&nbsp;赞&nbsp;时&nbsp;间</th>
						<th>点&nbsp;赞&nbsp;记&nbsp;录</th>
						<th>i&nbsp;p</th>
					</tr>
				</thead>
				{if $like_lists > 0}
				<tfoot>
					<tr>
						<td colspan="4" class="text-right vcy-page">{$likes_multi}</td>
					</tr>
				</tfoot>
				{/if}
				<tbody>
					{foreach $likes as $_id => $_data}
					<tr>
						<td>{$_data['m_username']}</td>
						<td>{$_data['created']|rgmdate}</td>
						<td>{$_data['description']}</td>
						<td>{$_data['ip']}</td>
					</tr>
					{foreachelse}
					<tr class="warning">
						<td colspan="4">暂无点赞记录</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
		</div>

	</div>
</div>
<script type="text/javascript">
function del_comment(id) {
	if(confirm('确认删除这条评论吗？')){
			$.ajax({
				method: 'post',
				type : 'json',
				url: '/api/news/delete/comment',
				data : {
					ncomm_id: id
				},
				success: function(data){
					if(data.errcode==0){
						$('#div_'+id).remove();
					} else {
						alert(data.errmsg);
					}
				}
			});
		}
}
$(function(){
	var del_url = '{$delete_url}';
	$('#delete_btn').click(function(){
		if(confirm('确认删除？')){
			window.location.href = del_url;
		}
	});
});
</script>
{include file="$tpl_dir_base/footer.tpl"}