{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
		<div class="text-center"><h3>{$result['csp_name']}营业数据</h3></div>
		<div class="panel-heading">
			<h3 class="panel-title font12"><strong>报表信息</strong></h3>
		</div>
		<div class="panel-body">
			<dl class="dl-horizontal font12 vcy-dl-list text-left">
				<dt class="text-left">汇报人：</dt>
				<dd>
					<strong class="label label-primary font12">{$result['username']|escape}</strong>
				</dd>
				<dt class="text-left">提交时间：</dt>
				<dd>{$result['reporttime']}</dd>
				<dt class="text-left">门店名称：</dt>
				<dd>{$result['csp_name']}</dd>
			</dl>	
{if $result['report']}	
<div style="max-width:100%">
	<div style="width:100%;overflow-x:auto">		
					<table class="table table-striped table-hover table-bordered font12 table-light">
						<colgroup>
							<col class="t-col-18" />
							<col class="t-col-18" />
							<col class="t-col-20" />
							<col />
						</colgroup>
						<thead>
							<tr>
	{foreach $result['report'] as $field1 => $report1}	
		{if $report1['type'] == 'int'}						
								<th>{$report1['fieldname']}&nbsp;{if $report1['unit'] != '' }(&nbsp;{$report1['unit']}&nbsp;){/if}</th>	
		{/if}													
	{/foreach}							
							</tr>
						</thead>						
						<tbody>
							<tr>
	{foreach $result['report'] as $field2 => $report2}	
		{if $report2['type'] == 'int'}						
								<td>{$report2['current']}</td>	
		{/if}													
	{/foreach}							
							</tr>
						</tbody>
					</table>
	</div>
</div>
{/if}	
			<dl class="dl-horizontal font12 vcy-dl-list  text-left">
	{foreach $result['report'] as $field3 => $report3}	
		{if $report3['type'] == 'text'}			
				<dt class="text-left">{$report3['fieldname']}：</dt>
				<dd style="width:80%">{$report3['current']}</dd>
		{/if}													
	{/foreach}
			</dl>
			
			<span class="space"></span>
{if $result['comments']}			
			<ul class="nav nav-tabs font12">
				<li class="active">
					<a href="#list_proc" data-toggle="tab">	评论&nbsp;</a>
				</li>	
			</ul>
		<div class="panel widget-comments">
			<div class="panel-body" id="comments">

	{foreach $result['comments'] as $comment}						
				<div class="comment">
					<img src="{$comment['avatar']}" alt=""  class="comment-avatar">
					<div class="comment-body">
						<div class="comment-by">
							{$comment['username']} &nbsp;&nbsp;发表于 &nbsp;&nbsp; {$comment['created_u']}
						</div>
						<div class="comment-text">{$comment['comment']}</div>								
					</div>
				</div>
	{/foreach}			
									
			</div>		
		</div>
{/if}		
		{if $total_page>1}<div class="text-center panel-padding"><a id="more_comments" class="btn btn-default" page="1" dr_id="{$dr_id}" total_page="{$total_page}">更多评论</a></div>{/if}
		<a href="javascript:history.go(-1);" class="btn btn-default">返回</a>
	</div>
</div>
<script type="text/javascript">
$(function(){
	$('#more_comments').bind('click',function(){
		obj = $(this);
		var page = parseInt($(this).attr('page'));
		var total_page = parseInt($(this).attr('total_page'));
		var dr_id = parseInt($(this).attr('dr_id'));
		$.get('/api/superreport/get/comments',{
			page:page+1,
			dr_id:dr_id
			},function(data){
				if (data.errmsg == 'OK'){
					var str = '';
					var list = data.result.list;
					for (i=0;i<data.result.limit;i++) {
						str +='<div class="comment">'
							  +'<img src="'+list[i].avatar+'" alt=""  class="comment-avatar">'
							  +'<div class="comment-body">'
						  	  +'<div class="comment-by">'
							  +list[i].username+'&nbsp;&nbsp;发表于 &nbsp;&nbsp;'+list[i].created_u+'</div>'
							  +'<div class="comment-text">'+list[i].comment+'</div>'
							  +'</div>'
							  +'</div>';
					}
					
					$('#comments').append(str);
					obj.attr('page',data.result.page);
					if (data.result.page >= total_page) {
						obj.remove();
					}
				}
				
				
		});
	});
});
</script>
{include file="$tpl_dir_base/footer.tpl"}