{include file='cyadmin/header.tpl'}
<script>
	$(function(){
		$('#sandbox-container .input-daterange').datepicker({
			todayHighlight: true
			});
		});
</script>
<h5 class="col-xs-12 col-sm-4 text-left text-left-sm">{if empty($result)}添加模板{else if}编辑模板{/if}</h5>
{include file='cyadmin/data/template/menu.tpl'}



	<form action="{$add_url_base}" method="post" id="addform" >
{if !empty($result)}
<input type="hidden" name="edit" value="1">
<input type="hidden" name='ne_id' value="{$result['ne_id']}">
{/if}
<div id="form-adminer-edit" class="form-horizontal font12" style="border:1px solid #CCC">
	<div class="form-group">
		<label class="col-sm-2 control-label">模板标题：</label>
		<div class="col-sm-6">
			<p class="form-control-static">

			<input type="text" name="title" maxlength="32" value="{$result['title']}" placeholder="请输入标题" id="title"class="form-control"">

			</p>
		</div>
	</div>
		<div class="form-group">
		<label class="col-sm-2 control-label">模板摘要：</label>
		<div class="col-sm-6">
			<p class="form-control-static">
			<textarea name="summary" maxlength=120 cols=50 rows=4 id="summary"class="form-control">{$result['summary']}</textarea>
			</p>
		</div>
	</div>
			<div class="form-group">
		<label class="col-sm-2 control-label">模板图标：</label>
		<div class="col-sm-6">
			{if !empty($result)}
			<p class="form-control-static">
			<label></label><input type="radio" name="icon" value="fa-bullhorn" {if $result['icon'] == 'fa-bullhorn'}checked{/if}><i class="fa fa-bullhorn text-slg" style="padding:5px;"></i></label>
			<label></label><input type="radio" name="icon" value="fa-envelope-o" {if $result['icon'] == 'fa-envelope-o'}checked{/if}><i class="fa fa-envelope-o text-slg" style="padding:5px;"></i></label>
			</p>
			{else if}
			<p class="form-control-static">
			<label></label><input type="radio" name="icon" value="fa-bullhorn" checked><i class="fa fa-bullhorn text-slg" style="padding:5px;"></i></label>
			<label></label><input type="radio" name="icon" value="fa-envelope-o" ><i class="fa fa-envelope-o text-slg" style="padding:5px;"></i></label>
			</p>
			{/if}
		</div>
	</div>
	<div class="form-group font12" style="margin:20px 0">
		<label class="col-sm-2 control-label text-right" for="id_author">模板封面图片</label>
				<div class="col-sm-9">
					<div class="uploader_box">
						<input type="hidden" class="_input" name="atid" value="">
							<span class="btn btn-success fileinput-button">
								<i class="glyphicon glyphicon-plus"></i>
								<span>上传图片(推荐尺寸 480×230)</span>
								<input class="cycp_uploader" type="file" name="file" data-url="/attachment/upload/?file=file" data-callbackall="" data-hidedelete="1" data-showimage="1">
							</span>
							<span class="_showimage">{if !empty($result)}<img src="{$result['cover_url']}" width=52 height=32>{/if}</span>


							</div>
								<!-- <input type="file" name="coverimg" id="" class="form-control"> -->
							</div>


						</div>

	<div class="form-group">
	<label class="col-sm-2 control-label">内容：</label>
	<div class="col-sm-6">
		<p class="form-control-static">
		{$ueditor_output}
		</p>
	</div>
	</div>


	<div class="form-group">
	<label class="col-sm-2 control-label"></label>
	<div class="col-sm-6">
		{if empty($result)}
		<input type="submit" value="添加" class="btn btn-success" >
		{else if}
		<input type="submit" value="编辑" class="btn btn-success" >
		{/if}
	</div>
	</div>


</div>




</form>
<script>
	$(function(){
		$('#addform').submit(function(){
			var re = /^[\u4e00-\u9fa5a-z0-9]+$/gi;
				if($('#title').val().length ==0){
					alert('请输入标题');
					return false;
					}
				if($('#summary').val().length ==0){
					alert('请输入摘要');
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

{include file='cyadmin/footer.tpl'}