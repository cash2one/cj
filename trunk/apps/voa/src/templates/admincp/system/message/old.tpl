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


<div class="table-light" id="table">
	<div class="table-header">
		<div class="table-caption font12">
			消息列表
		</div>
	</div>


<table class="table table-striped table-hover table-bordered font12" id="table_mul">
	<colgroup>
		<col class="t-col-30" />
		<col class="t-col-25 "/>
		<col class="t-col-20" />
	</colgroup>
	<thead>
	<tr>
		<th>标题</th>
		<th>发送方</th>
		<th>发送时间</th>
	</tr>
	</thead>
	{if count($re_info[1]) > 0}
		<tfoot>
		<tr>

			<td colspan="3" class="text-right vcy-page">{$re_info[0]}</td>
		</tr>
		</tfoot>
	{/if}
	<tbody>
	{if count($re_info[1]) > 0}
		{foreach $re_info[1] as $_id => $_data}
			<tr>
				<td class="list_title"><a href="/admincp/system/message/view/?meid={$_data['meid']}&logid={$_data['logid']}&yd=1" style="color:#333333;">{$_data['title']}</a>
				</td>
				<td>{$_data['realman']}</td>
				<td>{$_data['created']|rgmdate:'Y-m-d H:i'}</td>

			</tr>
		{/foreach}
	{else}
		<tr>
			<td colspan="8" class="warning">暂无任何消息数据</td>
		</tr>
	{/if}
	</tbody>
</table>
</div>

</div>


<script type="text/javascript">
	$(function(){

			
		/*$.ajax({
			url:'{*{$CYADMIN_URL}*}cyadmin/api/message/old/',
			dataType: 'jsonp',
			jsonp: 'callback',
			data:'info = {*{$info}*}',
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
		/*$("#search_btn").click(function(e){
			e.preventDefault();
			var title = $('#id_title').val();
			if(title != ''){
				$.ajax({
					url:'{*{$CYADMIN_URL}*}cyadmin/api/message/old/',
					dataType: 'jsonp',
					jsonp: 'callback',
					data:'info = {*{$info}*}&title='+title,
					type:'get',
					success:function(result){
						if(result.errcode == 0){
						$('#table_mul').html(txTpl('tpl-list', result.result));
						_page();
						}else{
							alert(result.errcode+' : '+result.errmsg);
						}
					}
				})

			}	
		});*/

	});
</script>
{include file="$tpl_dir_base/footer.tpl"}
