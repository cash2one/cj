{include file="cyadmin/header.tpl"}
<script>
$(function() {

$('#sandbox-container .input-daterange').datepicker({
todayHighlight: true
});
});
</script>

{include file='cyadmin/content/article/menu.tpl'}
<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<div class="right-col">
				<div class="panel tl-body">
					<form class="form-horizontal font12" role="form" action="/content/article/insert" method="post" enctype="multipart/form-data">
						<div class="form-group font12" style="margin:20px 0">
							<label for="dateformat" class="col-sm-3 control-label text-right">文章标题</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="dateformat" name="title" placeholder="填写标题" maxlength="20" required="required"><p class="help-block">标题设置，最多20个字符</p>
							</div>
						</div>
						<div class="form-group font12" style="margin:20px 0">
							<label class="col-sm-3 control-label text-right" for="id_label_tc_id">文章类型</label>
							<span class="space"></span>
							<div class="col-sm-3">
								<select id="id_nca_id" name="acid" class="form-control" data-width="auto"  required="required">
									<option value="">请选择</option> 
									<option value="1">最新报道</option>
									<option value="2">热门活动</option>
									<option value="3">在线听课</option>
									<option value="4">企业微信</option>
								</select>
							</div>

						</div>
						<div class="form-group font12" style="margin:20px 0">
							<label class="col-sm-3 control-label text-right" >文章来源</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="dateformat" name="source" placeholder="请输入文章来源，默认为畅移云工作" maxlength="100" />
							</div>
						</div>
						<div class="form-group font12" style="margin:20px 0">
							<label class="col-sm-3 control-label text-right" >来源链接</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="dateformat" name="sourl" placeholder="请输入文章来源链接"  />
							</div>
						</div>
						<div class="form-group font12" id="js_source_logo" style="margin:20px 0">
							<label class="col-sm-3 control-label text-right" for="id_author">来源LOGO</label>
							<div class="col-sm-9">
								<div class="uploader_box">
									<input type="hidden" class="_input" name="logo_atid" value="">
									<span class="btn btn btn-info fileinput-button">
										<i class="glyphicon glyphicon-plus"></i>
										<span>上传图片</span>
										<input class="cycp_uploader" type="file" name="file" data-url="/attachment/upload/?file=file" data-callbackall="" data-hidedelete="1" data-showimage="1">
									</span>
									<span class="_showimage">注：图片尺寸：311像素 * 88像素</span>
								</div>
							</div>
							
						</div>
						<div class="form-group font12" style="margin:20px 0">
							<label class="col-sm-3 control-label text-right" for="id_title">摘要</label>
							<style>
								textarea.form-control {
									  height: 100px;
									}
							</style>
							<div class="col-sm-9">
								
								<textarea name="description" id="" style="resize:none" maxlength="120" class="form-control"></textarea>
							</div>
						</div>
						<div class="form-group font12" style="margin:20px 0">
							<label class="col-sm-3 control-label text-right" for="id_author">封面图片</label>
							<div class="col-sm-9">
								<div class="uploader_box">
									<input type="hidden" class="_input" name="face_atid" value="">
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
					<div class="form-group font12" style="margin:20px 0">
							<label class="col-sm-3 control-label text-right" >排序</label>
							<div class="col-sm-9">
								<input type="number" class="form-control" id="dateformat" name="asort" placeholder="请输入数字，数字越大越靠前" maxlength="100" />
							</div>
					</div>
					<div class="form-group font12" style="margin:20px 0">
							<label class="col-sm-3 control-label text-right" >标签</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="dateformat" name="tags" placeholder="请输入标签名，多个使用半角逗号分开" maxlength="100" />
							</div>
					</div>





						<div class="form-group">
							<div class="col-sm-12">{$ueditor_output}</div>
						</div>
						<div class="form-group">
							<div class="col-sm-9">
								<div class="row">
								<input type="hidden" name="is_publish" value="1">
									<div class="col-md-4">
										<button type="submit" class="btn btn-primary  col-md-9" id="draft_btn">保存草稿</button>
									</div>
									<div class="col-md-4">
										<button type="submit" class="btn btn-primary col-md-9" id="publish_btn">发布</button>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="myModal" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="myModalLabel">预览</h4>
			</div>
			<div class="modal-body padding-sm">
				<h4 id="preview_title">标题</h4>
				<p class="text-default text-sm">2014-12-12 12:11  </p>

				<hr>

				<div id="preview_content">内容</div>
			</div>
			<!-- / .modal-body -->
		</div>
		<!-- / .modal-content -->
	</div>
	<!-- / .modal-dialog -->
</div>
<!-- /.modal -->
<script>
	$(function(){
		$('#draft_btn').on('click',function(){
			$('input[name="is_publish"]').val('2');
		})
		var jqSelectElem = $("#id_nca_id"),
			jqLogo = $("#js_source_logo");
		jqSelectElem.on('change',function(){
			var $this = $(this),
				value = $this.val();
			if (value != 1) {

				jqLogo.hide();
			}else{
				jqLogo.show();
			}
		});
	})
</script>
{include file="cyadmin/footer.tpl"}