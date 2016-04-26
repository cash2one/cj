{include file='cyadmin/header.tpl'}
{include file='cyadmin/enterprise/news/menu.tpl'}
<div class="panel panel-default">

<style type="text/css">
	.pagination{
		margin:0 0;
	}
	#myModal{
		z-index: 1000;
	}
	.modal-backdrop{
		z-index:999;
	}
</style>

<div class="panel-heading">消息列表
	
</div>

<div class="panel-body">

<form action="{$form_url}" method="post">
<table class="table table-striped table-hover table-bordered font12">
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
			<th class="text-center"><input type="checkbox" class="px" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$delete_url_base || !$total} disabled="disabled"{/if} />
					<span class="lbl">全选</span></th>
			<th class="text-center">接收者</th>
			<th class="text-center">消息标题</th>
			<th class="text-center">作者 </th>
			<th class="text-center">创建时间</th>
			<th class="text-center">操作</th>

		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan = '1' class= "text-center"><button name="submit" value="1" type="submit"
	class="btn btn-primary  input-sm">批量删除</button></td>
			<td colspan="8" class="text-right">{$multi}</td>
		</tr>
	</tfoot>
	<tbody>
		{foreach $data as $k=>$val}
		<tr>
			<td class="px text-center"><input type="checkbox" class="px" name="delete[{$val['meid']}]" value="{$val['meid']}" /></td>
			<td class="px text-center">{if !empty($val['_epid'])}{$val['_epid']}{else if}所有公司{/if}</td>
			<td class="px text-center"><a href="{$view_url_base}{$k}">{$val['title']|escape}</a></td>
			<td class="px text-center">{if !empty($val['author'])}{$val['author']|escape}{else if}暂无作者{/if}</td>
			<td class="px text-center">{$val['_created']}</td>

			<td class="px text-center">
				{$base->show_link($view_url_base, $val['meid'], '详情', 'fa-eye')} |
				{$base->show_link($delete_url_base, $val['meid'], '删除', 'fa-times')}
			</td>
		</tr>
		{foreachelse}
			<tr>
				<td colspan="9" class="warning">{if $issearch}未搜索到指定条件的{$module_plugin['cp_name']|escape}数据{else}暂无任何{$module_plugin['cp_name']|escape}数据{/if}</td>
			</tr>
		{/foreach}
	</tbody>
</table>
<div class="control-label col-sm-1">

	</form>
</div>
</div>
</div>

<!--模态框-->
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title" id="myModalLabel">新增消息</h4>
	      </div>
	      <div class="modal-body">
	      	<!--主体信息-->
	      	<form action="/enterprise/news/add" method="post" id="addform">
	      	<div id="form-adminer-edit" class="form-horizontal font12" style="border:1px solid #CCC">
	      		<div class="form-group">
	      			<label class="col-sm-2 control-label">标题：</label>
	      			<div class="col-sm-9">
	      				<p class="form-control-static">
	      		
	      				<input type="text" name="title" maxlength="50"  placeholder="请输入标题" id="title"class="form-control"">

	      				</p>
	      			</div>
	      		</div>
	      		<div class="form-group font12" style="margin:20px 0">
	      			<label class="col-sm-2 control-label text-right" for="id_author">上传图片</label>
	      					<div class="col-sm-9">
	      						<div class="uploader_box">
	      							<input type="hidden" class="_input" name="atid" value="">
	      								<span class="btn btn-success fileinput-button">
	      									<i class="glyphicon glyphicon-plus"></i>
	      									<span>上传图片</span>
	      									<input class="cycp_uploader" type="file" name="file" data-url="/attachment/upload/?file=file" data-callbackall="" data-hidedelete="1" data-showimage="1">
	      								</span>
	      								<span class="_showimage"></span>

	      						
	      								</div>
	      									<!-- <input type="file" name="coverimg" id="" class="form-control"> -->
	      								</div>
	      							</div>
	      		<div class="form-group">
	      		<label class="col-sm-2 control-label">内容：</label>
	      		<div class="col-sm-9">
	      			<p class="form-control-static">
	      			{$ueditor_output}
	      			</p>
	      		</div>
	      		</div>

	      		<div class="form-group">
	      		<label class="col-sm-2 control-label"></label>
	      		</div>
	      		

	      	</div>



	      	</form>
	      	<script>
	      		$(function(){
	      			$('.go').on('click', function(){
	      				$('#addform').submit();
	      			})

	      			$('#addform').submit(function(){
	      				var re = /^[\u4e00-\u9fa5a-z0-9]+$/gi;
	      					if($('#title').val().length ==0){
	      						alert('请输入标题');
	      						return false;
	      						}
	      				

	      					if($('#title').val().length >50){
	      						alert('长度过长');
	      						return false;
	      					}
	      					//只能输入汉字数字和英文字母
	      				
	      				/*	if($('#title').val() !=''){
	      					if (!re.test($('#title').val())) {
	      						alert('输入标题含非法字符');
	      						return false;

	      					}
	      					}
	      				*/
	      				});
	      			});
	      	</script>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
	        <button type="submit" class="btn btn-primary go">保存</button>
	      </div>
	    </div>
	  </div>
	</div>

{include file='cyadmin/footer.tpl'}
