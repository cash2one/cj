{include file='cyadmin/header.tpl'}
<script>
	$(function(){
		$('#sandbox-container .input-daterange').datepicker({
			todayHighlight: true
			});
		});
</script>

{include file='cyadmin/enterprise/news/menu.tpl'}
	<h4>
		新增消息:
	</h4>
	<form action="{$add_url_base}" method="post" id="addform">



<div id="form-adminer-edit" class="form-horizontal font12" style="border:1px solid #CCC">
	<div class="form-group">
		<label class="col-sm-2 control-label">标题：</label>
		<div class="col-sm-6">
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
	<div class="col-sm-6">
		<p class="form-control-static">
		{$ueditor_output}
		</p>
	</div>
	</div>
	

	<div class="form-group">
	<label class="col-sm-2 control-label"></label>
	<div class="col-sm-6">
		<input type="submit" value="发送" class="btn btn-success" >
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