{include file='cyadmin/header.tpl'}

<div class="panel panel-default">

<style type="text/css">
	.pagination{
		margin:0 0;
	}
</style>

<div class="panel-heading">提醒列表</div>

<div class="panel-body">

<form>
<table class="table table-striped table-hover table-bordered font12" id="table">
	<colgroup>
		<col class="t-col-2" />
		<col class="t-col-15" />
		<col class="t-col-20" />
		<col class="t-col-15" />
		<col class="t-col-15" />
		<col class="t-col-10" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-center"><input type="checkbox" class="px" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$total} disabled="disabled"{/if} />
					<span class="lbl">全选</span></th>
			<th class="text-center">公司名</th>
			<th class="text-center">套件</th>
			<th class="text-center">到期类型</th>
			<th class="text-center">提醒时间</th>
			<th class="text-center">操作</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan = '1' class= "text-center"><button name="submit" value="1" type="button"
	class="btn btn-danger  input-sm" id="goread">批量已读</button></td>
			<td colspan="8" class="text-right">{$multi}</td>
		</tr>
	</tfoot>
	<tbody>
		{foreach $data as $k=>$val}
		<tr>
			<td class="px text-center"><input type="checkbox" class="px" name="delete[{$val['ovid']}]" value="{$val['ovid']}" /></td>
			<td class="px text-center">{if !empty($val['_epid'])}{$val['_epid']}{else if}暂无数据{/if}</td>
			<td class="px text-center">{$val['suid']}</td>
			<td class="px text-center">{$val['overdue_status']|escape}</td>
			<td class="px text-center">{$val['_created']}</td>

			<td class="px text-center">
				<a href="javascript:;" data-id="{$val['ovid']}" class="overread">已读</a>
				
			</td>
		</tr>
		{foreachelse}
			<tr>
				<td colspan="9" class="warning">暂无任何提醒数据</td>
			</tr>
		{/foreach}
	</tbody>
</table>
<div class="control-label col-sm-1">

</form>
</div>
</div>
</div>
{literal}
<script type="text/javascript">
	// 点击批量已读 批量设置已读的消息 
	$(function(){
		var uid = {/literal}{$uid}{literal}; // 当前登录者的caid
		$("#table").on('click', '#goread', function(){

			var all_read_ids = [];
			$('#table input[name^=delete]:checked').each(function(i,val){
				all_read_ids.push($(this).val());
			});
			//console.log(all_read_ids);
			if(all_read_ids.length > 0){
				_read(all_read_ids, uid);
			}else{
				alert('您当前还没有任何勾选!');
				return;
			}
			// window.location.reload(true);
			//re_relaod();
		});

		$("#table").on('click', ".overread", function(){
			var ovid = $(this).attr('data-id');
			_read(ovid, uid);
			// window.location.reload(true);
		});


		// 添加已读记录
		function _read(logid, uid){
			$.ajax({
				url:'/enterprise/overdue/onread/',
				dataType: 'json',
				data:{read:logid,uid:uid},
				type:'post',
				success:function(rs){
					alert(rs[1]);
					window.location.reload(true);
				}
			});
		}
	})
	


</script>
{/literal}
{include file='cyadmin/footer.tpl'}
