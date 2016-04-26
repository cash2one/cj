<script>
	$(function() {
		$('#sandbox-container .input-daterange').datepicker({
			todayHighlight: true
		});
	});
</script>
<script type="text/javascript">
	var init = [];
	jQuery(function() {
		jQuery('.selectpicker').selectpicker();
	});
</script>
<style>
	.bootstrap-timepicker-widget.dropdown-menu.open {
		display: block;
	}
</style>
<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<div class="right-col">
				<div class="panel tl-body">
					<!-- form start  -->
					<form action="/content/train/new" class="form-horizontal font12" method="post">
						<!-- group start  -->
						<input type="hidden" name="ac" value="{$ac}">
						{if $ac == 'update'}
						<input type="hidden" name="tid" value="{$smarty.get.tid}">
						{/if}
						<div class="form-group font12">
							<label for="" class="col-sm-3 control-label text-right">*标题</label>
							<div class="col-sm-9">
								<input type="text" required="required" class="form-control" placeholder="填写标题" maxlength="50" name="title" value="{$view['title']}">
								<p class="help-block">最多输入50个字符</p>
							</div>
						</div>
						<!-- group end  -->
						<!-- group start  -->
						<div class="form-group">
							<label for="" class="col-sm-3 control-label text-right">*来源</label>
							<div class="col-sm-9">
								<input type="text" value="{$view['source']}" class="form-control" placeholder="请输入来源名称" maxlength="50" name="source">
								<p class="help-block">最多输入50个字符</p>
							</div>
						</div>
						<!-- group end  -->
						<!-- group start  -->
						<div class="form-group">
							<label for="" class="col-sm-3 control-label text-right">*来源链接</label>
							<div class="col-sm-9">
								<input type="url" value="{$view['sourl']}" class="form-control" placeholder="请输入来源地址链接" name="sourl" >										
							</div>
						</div>
						<!-- group end  -->
						<!-- group start  -->
						<div class="form-group">
							<label for="" class="col-sm-3 control-label text-right">*摘要</label>
							<style>
								textarea.form-control {
									  height: 100px;
									}
							</style>
							<div class="col-sm-9">
								<textarea class="form-control form-group__textarea" maxlength="120" placeholder="填写摘要" name="description">{$view['description']}</textarea>				
								<p class="help-block">最多输入120个字符</p>
							</div>
						</div>
						<!-- group end  -->
						<!-- group start  -->
						<div class="form-group">
							<label for="" class="col-sm-3 control-label text-right">*封面图片</label>
							<div class="col-sm-9">
								<div class="uploader_box">
									<input type="hidden" class="_input" name="face_atid" value="{$view['face_atid']}">
									<span class="btn btn-success fileinput-button">
										<i class="glyphicon glyphicon-plus"></i>
										<span>上传图片</span>
										<input class="cycp_uploader" type="file" name="file" data-url="/attachment/upload/?file=file" data-callbackall="" data-hidedelete="1" data-showimage="1">
									</span>
									<span class="_showimage">
										{if $view['url']}
										<a href="{$view['url']}" target="_blank"><img src="{$view['url']}" border="0" style="max-width:64px;max-height:32px"></a>
										{else}
										注：图片尺寸为480*230像素
										{/if}

									</span>

								</div>
							</div>
						</div>
						<!-- group end  -->
						<!-- group start  -->
						<div class="form-group">
							<label for="" class="col-sm-3 control-label text-right">*排序</label>
							<div class="col-sm-9">
								<input type="number" value="{$view['tsort']}" class="form-control" placeholder="请输入数字,数字越大越靠前" name="tsort">
							</div>
						</div>
						<!-- grooup end  -->
						<!-- group start  -->
						<div class="form-group">
							<label for="" class="col-sm-3 control-label text-right">*标签</label>
							<div class="col-sm-9">
								<input type="text" value="{$view['tags']}" class="form-control" placeholder="请输入标签名,多个用英文逗号隔开" name="tags">
							</div>
						</div>
						<!-- grooup end  -->
						<!-- group start  -->
						<div class="form-group">
							<label class="control-label col-sm-3">*开始时间</label>
							<script>
								init.push(function () {
									var options1 = {
										todayBtn: "linked",
										orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto',
										startDate: new Date()
									};
									$('#start_data').datepicker(options1);
									$('#start_time').timepicker({
										showMeridian:false
									});
								});
							</script>
							<div class="col-sm-9">
								<div class="input-daterange input-group" style="width: 600px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
									<div style="width: 300px">
										<input value="{$view['start_time']['data']}" required="required" type="text" class="input-sm form-control" id="start_data" name="start_time[data]" placeholder="开始日期" style="width: 150px">
										<input value="{$view['start_time']['time']}" required="required" type="text" class="input-sm form-control" id="start_time" name="start_time[time]" style="width: 150px">
									</div>
								</div>
							</div>
						</div>
						<!-- grooup end  -->
						<!-- group start  -->
						<div class="form-group">
						<label class="control-label col-sm-3">*结束时间</label>
							<script>
								init.push(function () {
									var options2 = {
										todayBtn: "linked",
										orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto',
										startDate: new Date()
									};
									$('#end_data').datepicker(options2);
									$('#end_time').timepicker({
										showMeridian:false
									});
								});
							</script>
							<div class="col-sm-9">
								<div class="input-daterange input-group" style="width: 600px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
									<div style="width: 300px">
										<input value="{$view['end_time']['data']}" required="required" type="text" class="input-sm form-control" id="end_data" name="end_time[data]" placeholder="结束日期" style="width: 150px">
										<input value="{$view['end_time']['time']}" required="required" type="text" class="input-sm form-control" id="end_time" name="end_time[time]" style="width: 150px">
									</div>
								</div>
							</div>
						</div>
						<!-- grooup end  -->
						<!-- group start  -->
						<div class="form-group">
							<label for="" class="col-sm-3 control-label text-right">*嘉宾</label>
							<div class="col-sm-9">
								<textarea required="required"  class="form-control form-group__textarea" name="guests">{$view['guests']}</textarea>
							</div>
						</div>
						<!-- grooup end  -->
						<!-- group start  -->
						<div class="form-group">
							<label for="" class="col-sm-3 control-label text-right">*地点</label>
							<div class="col-sm-9">
								{$ueditor_map}
							</div>
						</div>	
						<!-- grooup end  -->											
						<!-- group start  -->
						<div class="form-group">
							<label for="" class="col-sm-3 control-label text-right">*报名信息</label>
							<div class="col-sm-9">
								<input type="hidden" name="sign_fields" value="" id="js_sign_fields">
								<ul class="train-item-ul">
								{if $ac == 'add'}
									{foreach $sign_field as $val}
									{if $val['sid'] <4}
									<li class="train-item-ul__li">
										<a data-select="false" class="train-item-ul__cur train-item-ul__link js_train_item_select" href="javascript:void(0);" data-value="{$val['sid']}">{$val['fieldname']}</a>
									</li>

									{else}
									<li class="train-item-ul__li">
										<a data-select="true" class="train-item-ul__link js_train_item_select" href="javascript:void(0);" data-value="{$val['sid']}">{$val['fieldname']}</a>
									</li>
									{/if}							

									{/foreach}	
								{/if}
								{if $ac == 'update'}
									{foreach $sign as $val}
									{if $val['sid'] <4}
									<li class="train-item-ul__li">
										<a data-select="false" class="train-item-ul__cur train-item-ul__link js_train_item_select" href="javascript:void(0);" data-value="{$val['sid']}">{$val['fieldname']}</a>
									</li>
									
									{elseif $val['selected'] == 1 and $val['sid'] >3}
									<li class="train-item-ul__li">
										<a data-select="true" class="train-item-ul__cur train-item-ul__link js_train_item_select" href="javascript:void(0);" data-value="{$val['sid']}">{$val['fieldname']}</a>
									</li>
									{else}
									<li class="train-item-ul__li">
										<a data-select="true" class="train-item-ul__link js_train_item_select" href="javascript:void(0);" data-value="{$val['sid']}">{$val['fieldname']}</a>
									</li>
									{/if}
									{/foreach}
								{/if}						
								</ul>

							</div>
						</div>	
						<!-- grooup end  -->
						<!-- group start  -->
						<div class="form-group">
							<label for="" class="col-sm-3 control-label text-right">*正文</label>
							<div class="col-sm-9">
								{$ueditor_output}
							</div>
						</div>
						<!-- grooup end  -->

						<!-- group start  -->
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
						<!-- grooup end  -->	
					</form>
					<!-- form end  -->
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	$(function(){
		$('#draft_btn').on('click',function(){
			$('input[name="is_publish"]').val('2');
		});

		$.train.SelectField();
	})
</script>
<script type="text/javascript">
	//init.push(function () {
	//})
window.PixelAdmin.start(init);
</script>