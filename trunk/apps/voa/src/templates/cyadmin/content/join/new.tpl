{include file="cyadmin/header.tpl"}
<script>
$(function() {

$('#sandbox-container .input-daterange').datepicker({
todayHighlight: true
});
});
</script>

{include file='cyadmin/content/join/menu.tpl'}
<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<div class="right-col">
				<div class="panel tl-body">
					<form class="form-horizontal font12" role="form" action="/content/join/new" method="post" enctype="multipart/form-data">
						<div class="form-group font12" style="margin:20px 0">
							<input type="hidden" name="ac" value="{$ac}">
							{if $ac == update}
								<input type="hidden" name="jid" value="{$smarty.get.jid}">
							{/if}
							<label for="dateformat" class="col-sm-3 control-label text-right">岗位名称：</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="dateformat" name="jobname" placeholder="填写标题" maxlength="100" required="required" value="{$view['jobname']}"><p class="help-block">标题设置，最多100个字符</p>
							</div>
						</div>

					<div class="form-group font12" style="margin:20px 0">
							<label class="col-sm-3 control-label text-right" >排序：</label>
							<div class="col-sm-9">
								<input type="number" class="form-control" id="dateformat" name="jsort" placeholder="请输入数字，数字越大越靠前" maxlength="100" value="{$view['jsort']}" />
							</div>
					</div>

						<div class="form-group" style="margin:20px 0">
							<label class="col-sm-3 control-label text-right" >岗位描述：</label>
							<style>
								textarea.form-control {
									  height: 300px;
									}
							</style>
							<div class="col-sm-9">
								<textarea class="form-control form-group__textarea" placeholder="填写岗位描述" name="jobdesc">{$view['jobdesc']}</textarea>				
							</div>
						</div>
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
	})
</script>
{include file="cyadmin/footer.tpl"}