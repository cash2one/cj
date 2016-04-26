{include file="$tpl_dir_base/header.tpl"}	
		
				<p style="margin:30px;font-size:16px"><span style="margin-right:100px">标题：{$news['title']}</span> <span style="margin:0 10px 0 10px"></span><span>发布时间：{$news['updated']}</span></p>
		
			

			<ul class="nav nav-tabs font12">
			<li {if $type==1}class="active"{/if}><a href="{$read_url}">已读 ({$num})</a></li>
			<li {if $type==2}class="active"{/if}><a href="{$unread_url}">未读 ({$un_num})</a></li>
			{if $type==2}{if $news['is_publish'] == 1}<a  href="javascript:void(0)" class="btn btn-info pull-right" id="dialog">未读提醒</a>{/if}{/if}
			</ul>
			{if $type ==1}
			<div class="tab-content" >
				<div class="tab-pane active" id="list_proc">
					<table class="table table-striped table-hover table-bordered font12 table-light">
						<colgroup>
							<col class="t-col-20" />
							<col class="t-col-22" />
							<col class="t-col-22" />
							<col class="t-col-16" />
							<col class="t-col-20" />
						</colgroup>
						<thead>
							<tr>
								<th>姓名</th>
								<th>部门</th>
								<th>职位</th>
								<th>手机号</th>
								<th>阅读时间</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td><a href="javascript:history.go(-1);" class="btn btn-default">返回</a></td>
								<td colspan="4" class="text-right">{$multi}</td>
							</tr>
						</tfoot>
						<tbody>
{foreach $users as $_id => $_user}
							<tr>
								<td>{$_user['m_username']}</td>
								<td>{$_user['department']}</td>
								<td>{$_user['job']}</td>
								<td>{$_user['m_mobilephone']}</td>
								<td>{$_user['read_time']}</td>
							</tr>
{foreachelse}
							<tr class="warning">
								<td colspan="5">暂无阅读记录</td>
							</tr>
{/foreach}
						</tbody>
					</table>
				</div>
			</div>
<!--未读人员-->
{else}
			<div class="tab-content" id="list_unread">
				<div class="tab-pane active" >
					<table class="table table-striped table-hover table-bordered font12 table-light">
						<colgroup>
							<col class="t-col-20" />
							<col class="t-col-22" />
							<col class="t-col-22" />
							<col class="t-col-16" />
						</colgroup>
						<thead>
							<tr>
								<th>姓名</th>
								<th>部门</th>
								<th>职位</th>
								<th>手机号</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td><a href="javascript:history.go(-1);" class="btn btn-default">返回</a></td>
								<td colspan="3" class="text-right">{$un_multi}</td>
							</tr>
						</tfoot>
						<tbody>
{foreach $un_users as $_id => $_user}
							<tr>
								<td>{$_user['m_username']}</td>
								<td>{$_user['department']}</td>
								<td>{$_user['job']}</td>
								<td>{$_user['m_mobilephone']}</td>
							</tr>
{foreachelse}
							<tr class="warning">
								<td colspan="4">暂无未读记录</td>
							</tr>
{/foreach}
						</tbody>
					</table>
				</div>
			</div>
			{/if}
		</div>
<!--弹框-->
<script>
		$('#dialog').on('click', function () {
		bootbox.confirm({
			message: "确定再次发送提醒？",
			callback: function(result) {
				var neid = {$smarty.get.ne_id};
			     if (result) {
			        $.ajax({
			        	type:'POST',
			        	url:'/api/news/post/sendmsg',
			        	dataType:'json',
			        	data:"ne_id="+neid,
			        	success:function(s){
			        		if(s.errcode==0){
			        			alert('消息已发送！');
                                $.ajax('/api/common/post/sendmsg');
			        		}else{
			        			alert(s.errmsg);
			        		}
			        	}
			        	});
			     }
			     return true;
			},
		    buttons: {  
	            confirm: {  
	                label: '确认',  
	                className: 'btn-myStyle'  
	            },  
	            cancel: {  
	                label: '取消',  
	                className: 'btn-default'  
	            }  
            },
            className: "bootbox-sm"
		});
		return false;
	});
</script>
{include file="$tpl_dir_base/footer.tpl"}