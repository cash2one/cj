{include file="$tpl_dir_base/header.tpl"}


{*<div class="panel panel-default font12">
	<div class="panel-heading"><strong>消息搜索</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="">
			<div class="form-row">
				<div class="form-group">
					
					<label class="message" for="message_title">　消息标题：</label>
					<input type="text" class="form-control form-small" id="id_title" name="title" placeholder="输入关键词" value="{$search_conds['title']|escape}" maxlength="30" />
					<span class="space"></span>
					<button id="search_btn" type="buuton" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					<span class="space"></span>
					<a href="{$list_url}" role="button" class="btn btn-default form-small form-small-btn">全部记录</a>
				</div>
			</div>
		</form>
	</div>
</div>*}


{*<div class="table-light" id="table">
	<div class="table-header">
		<div class="table-caption font12">
			消息列表
		</div>
	</div>


<table class="table table-striped table-hover table-bordered font12" id="table_mul">

</table>
</div>*}

<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			消息列表
		</div>
	</div>

	<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}?delete">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<table class="table table-striped table-hover table-bordered font12" id="table">
			<colgroup>
				<col class="t-col-5" />
				<col class="t-col-30" />
				<col class="t-col-25 "/>
				<col class="t-col-20" />
			</colgroup>
			<thead>
			<tr>
				<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" /><span class="lbl">全选</span></label></th>
				<th>标题</th>
				<th>发送方</th>
				<th>发送时间</th>
			</tr>
			</thead>
			{if count($re_info[1]) > 0}
				<tfoot>
				<tr>
					<td colspan="2" style="overflow:hidden;">
						<button type="button" class="btn btn-danger" style="float:left;margin-left:20px;" id="re_read">批量已读</button>
					</td>
					<td colspan="2" class="text-right vcy-page">{$re_info[0]}</td>
				</tr>
				</tfoot>
			{/if}
			<tbody>
			{if count($re_info[1]) > 0}
				{foreach $re_info[1] as $_id => $_data}
					<tr>

						<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete['logid']" value="{$_data['logid']}" /><span class="lbl"> </span></label></td>

						<td class="list_title"><a href="/admincp/system/message/view/?meid={$_data['meid']}&logid={$_data['logid']}" logid="{$_data['logid']}" style="color:#333333;">{$_data['title']}</a>
						</td>

						<td>{$_data['realman']}</td>

						<td>{$_data['created']|rgmdate:'Y-m-d H:i'}</td>

					</tr>
				{/foreach}
			{else}
				<tr>
					<td colspan="8" class="warning">{if $issearch}未搜索到指定条件的公告数据{else}暂无任何消息数据{/if}</td>
				</tr>
			{/if}
			</tbody>
		</table>
	</form>
</div>



<script type="text/javascript">
	$(function(){
		function _page() {
			$("#table_mul tr .list_title a").click(function(e){
				var logid = $(this).attr('logid');
				_read(logid);
			});

			$('#table_mul .pagination a').on('click', function(){
				var page = 'page';
				var result =$(this).attr('href').match(new RegExp("[\?\&]" + page+ "=([^\&]+)", "i"));
				if (result == null || result.length < 1) {
					return '';
				}
				_post(result[1]);
				return false;
			});
		}
		function _post(page){
			var title = $('#id_title').val();
			$.ajax({
				url:'{$CYADMIN_URL}cyadmin/api/message/list/',
				dataType: 'jsonp',
				jsonp: 'callback',
				data:'info = {$info}&title='+title+'&page='+page,
				type:'get',
				success:function(result){
					$('#table_mul').html(txTpl('tpl-list', result.result));
					_page();
					}
			});
		}

		// 保留 添加已读记录
		function rpc_mark(logid) {

			$.ajax({

				async: false,
				url: '{$list_url}',
				dataType: 'json',
				data:'logid=' + logid,
				type: 'get',
				success: function(rs) {
					// alert('ok');
				}
			});
		}

		// 添加已读记录
		/*function _read(logid){
			$.ajax({
				url:'{$CYADMIN_URL}cyadmin/api/message/list/',
				dataType: 'jsonp',
				jsonp: 'yzp',
				data:'info = {$info}&logid='+logid,
				type:'get',
				success:function(rs){
					//alert(rs.message);
				}
			});
		}*/

		var re_relaod = function(){
			$.ajax({
				url:'{$CYADMIN_URL}cyadmin/api/message/list/',
				dataType: 'jsonp',
				jsonp: 'callback',
				data:'info = {$info}',
				type:'get',
				success:function(result){
					//console.log(result.result.list);
					if(result.errcode == 0){
					$('#table_mul').html(txTpl('tpl-list', result.result));
					_page();
					}else{
						alert(result.errcode+' : '+result.errmsg);
					}
				}
			})	
		}

		// re_relaod();

		/*$.ajax({
			url:'{$CYADMIN_URL}cyadmin/api/message/list/',
			dataType: 'jsonp',
			jsonp: 'callback',
			data:'info = {$info}',
			type:'get',
			success:function(result){
				//console.log(result.result.list);
				if(result.errcode == 0){
				$('#table_mul').html(txTpl('tpl-list', result.result));
				_page();
				}else{
					alert(result.errcode+' : '+result.errmsg);
				}
			}
		})*/

		// 搜索的功能
		$("#search_btn").click(function(e){
			e.preventDefault();
			var title = $('#id_title').val();
			if(title != ''){
				$.ajax({
					url:'{$CYADMIN_URL}cyadmin/api/message/list/',
					dataType: 'jsonp',
					jsonp: 'callback',
					data:'info = {$info}&title='+title,
					type:'get',
					success:function(result){
						//console.log(result.result.list);
						//
						if(result.errcode == 0){
						$('#table_mul').html(txTpl('tpl-list', result.result));
						_page();
						}else{
							alert(result.errcode+' : '+result.errmsg);
						}
					}
				})

			}	
		});

		// 保留
		$('#table').on('change', '#delete-all', function(){
			checkAll($(this),'delete');
		});

		// 点击批量已读 批量设置已读的消息  保留

		$("#table").on('click', '#re_read', function(){

			var all_read_ids = [];
			$('#table input[name^=delete]:checked').each(function(i, val){
				all_read_ids.push($(this).val());
			});

			if(all_read_ids.length > 0){
				rpc_mark(all_read_ids);
				// _read(all_read_ids);
			}else{
				alert('您当前还没有任何勾选!');
				return;
			}
			window.location.reload();
			//re_relaod();
		});


	});
</script>
{literal}
<script type="text/template" id="tpl-list">
		<colgroup>
				<col class="t-col-5" />
				<col class="t-col-30" />
				<col class="t-col-25 "/>
				<col class="t-col-20" />		
		</colgroup>
		<thead>
			<tr>
				<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" /><span class="lbl">全选</span></label></th>
				<th>标题</th>
				<th>发送方</th>
				<th>发送时间</th>
			</tr>
		</thead>
		<tbody>
		<% if (!jQuery.isEmptyObject(list)) { %>
			<% $.each(list,function(n,val){ %>

			<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete[<%=n%>]" value="<%=n%>" /><span class="lbl"> </span></label></td>
			
			<td class="list_title"><a href="/admincp/system/message/view/?meid=<%=val['meid']%>&logid=<%=val['logid']%>" logid="<%=val['logid'] %>" style="color:#333333;"><%=val['title']%></a>
			</td>

			<td><%=val['realman'] %></td>

			<td><%=val['created'] %></td>
			</tr>



			<% }) %>
		<% }else{ %>
			<tr>
				<td colspan="8" class="warning">暂无任何消息数据</td>
			</tr>
		<% } %>
		</tbody>
		<tfoot>

			<tr>
				<% if (!jQuery.isEmptyObject(list)) { %>
				<td colspan="2" style="overflow:hidden;">
					<button type="button" class="btn btn-danger" style="float:left;margin-left:20px;" id="re_read">批量已读</button>		
				</td>
				<td colspan="2" class="text-right vcy-page"><%=multi%></td>
				<% } %>

				
			</tr>
		</tfoot>
	</tr>


</script>
{/literal}
{include file="$tpl_dir_base/footer.tpl"}
