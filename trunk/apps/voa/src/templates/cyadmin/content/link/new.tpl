{include file="cyadmin/header.tpl"}
<script>
$(function() {

$('#sandbox-container .input-daterange').datepicker({
todayHighlight: true
});
});
</script>

{include file='cyadmin/content/link/menu.tpl'}
<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<div class="right-col">
				<div class="panel tl-body">
					<form class="form-horizontal font12" role="form" action="/content/link/new" method="post" enctype="multipart/form-data">
						<div class="form-group font12" style="margin:20px 0">
							<input type="hidden" name="ac" value="{$ac}">
							{if $ac == update}
								<input type="hidden" name="lid" value="{$smarty.get.lid}">
							{/if}
							<label for="dateformat" class="col-sm-3 control-label text-right">链接名称：</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="dateformat" name="linkname" placeholder="填写链接名称" maxlength="100" required="required" value="{$view['linkname']}">
							</div>
						</div>

					<div class="form-group font12" style="margin:20px 0">
							<label class="col-sm-3 control-label text-right" >排序：</label>
							<div class="col-sm-9">
								<input type="number" class="form-control" id="dateformat" name="lsort" placeholder="请输入数字，数字越大越靠前" maxlength="100" value="{$view['lsort']}" />
							</div>
					</div>

					<div class="form-group font12" style="margin:20px 0">
							<label class="col-sm-3 control-label text-right" >链接来源：</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="dateformat" name="linkurl" placeholder="请输入来源地址链接" value="{$view['linkurl']}" />
							</div>
					</div>

					<div class="form-group font12" style="margin:20px 0">
							<label class="col-sm-3 control-label text-right" for="id_label_tc_id">链接类型</label>
							<span class="space"></span>
							<div class="col-sm-3">
								<select id="id_nca_id" name="linktype" class="form-control" data-width="auto"  required="required">
									<option value="">请选择</option> 
									<option value="1" {if $view['linktype'] == 1}selected{/if}>文字链接</option>
									<option value="2" {if $view['linktype'] == 2}selected{/if}>图片链接</option>
								</select>
							</div>

						</div>
						{if $ac == 'add'}
						<div class="form-group font12" style="margin:20px 0" id="js_source_name">

							<label class="col-sm-3 control-label text-right" >企业名称：</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="dateformat" name="companyname" placeholder="请输入企业名称" maxlength="100"  />
							</div>
						</div>

						<div class="form-group font12" style="margin:20px 0" id="js_source_logo">
							<label class="col-sm-3 control-label text-right" for="id_author">企业logo</label>
							<div class="col-sm-9">
								<div class="uploader_box">
									<input type="hidden" class="_input" name="atid" >
									<span class="btn btn-success fileinput-button">
										<i class="glyphicon glyphicon-plus"></i>
										<span>上传图片</span>
										<input class="cycp_uploader" type="file" name="file" data-url="/attachment/upload/?file=file" data-callbackall="" data-hidedelete="1" data-showimage="1">
									</span>
									<span class="_showimage">注：图片尺寸为150*60像素</span>
									
								</div>								
							</div>
						</div>
						{/if}

						{if $ac == 'update'}
							{if $view['linktype'] == 1}
						<div class="form-group font12" style="margin:20px 0" id="js_source_name">

							<label class="col-sm-3 control-label text-right" >企业名称：</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="dateformat" name="companyname" placeholder="请输入企业名称" maxlength="100" value="{$view['companyname']}" />
							</div>
						</div>
						{/if}
						{if $view['linktype'] == 2}
						<div class="form-group font12" style="margin:20px 0" id="js_source_logo">
							<label class="col-sm-3 control-label text-right" for="id_author">企业logo</label>
							<div class="col-sm-9">
								<div class="uploader_box">
									<input type="hidden" class="_input" name="atid" value="{$view['atid']}">
									<span class="btn btn-success fileinput-button">
										<i class="glyphicon glyphicon-plus"></i>
										<span>上传图片</span>
										<input class="cycp_uploader" type="file" name="file" data-url="/attachment/upload/?file=file" data-callbackall="" data-hidedelete="1" data-showimage="1">
									</span>
									<span class="_showimage">
									{if $view['url']}
									<a href="{$view['url']}" target="_blank"><img src="{$view['url']}" border="0" style="max-width:64px;max-height:32px"></a>
									{else}
									注：图片尺寸为150*60像素
									{/if}
									
									</span>
									
								</div>								
							</div>
						</div>
						{/if}
						{/if}
						<div class="form-group">

							<div class="col-sm-9 col-md-offset-4">
								<div class="row">
								{if $view['is_publish'] != 1}
								<input type="hidden" name="is_publish" value="1">
									<div class="col-md-4">
										<button type="submit" class="btn btn-primary  col-md-9" id="draft_btn">保存草稿</button>
									</div>
								{/if}
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

<!-- /.modal -->
<script>
	$(function(){
		$('#draft_btn').on('click',function(){
			$('input[name="is_publish"]').val('2');
		})

		var jqSelectElem = $("#id_nca_id"),
			jqLogo = $("#js_source_logo"),
			jqName = $("#js_source_name");
		jqSelectElem.on('change',function(){
			var $this = $(this),
				value = $this.val();
				//alert(typeof value);
			switch(value){
				case '1':
					jqName.show();
					jqLogo.hide();
				break;
				case '2':
					jqLogo.show();
					jqName.hide();
				break;
				default:
					jqLogo.hide();
					jqName.hide();
					break;
			}
			
		});
	})
</script>
{include file="cyadmin/footer.tpl"}