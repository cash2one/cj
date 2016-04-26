{include file='cyadmin/header.tpl'}
{include file='cyadmin/content/join/menu.tpl'}
<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<div class="right-col">
				<div class="panel tl-body form-horizontal" >
						<div class="form-group font12" style="margin-left:20px">
							<label for="dateformat" class="col-sm-1">岗位名称：</label>
							<div class="col-sm-11">
								<b>{$view['jobname']}</b>
							</div>
						</div>

					<div class="form-group font12" style="margin-left:20px">
							<label class="col-sm-1" >排序：</label>
							<div class="col-sm-11">
								{$view['jsort']}
							</div>
					</div>
					<div class="form-group font12" style="margin-left:20px">
							<label class="col-sm-1" >职位描述：</label>
							<div class="col-sm-11">
								{$view['jobdesc']}
							</div>
					</div>
					<div class="form-group">
							<div class="col-sm-offset-2 col-sm-9">
								<div class="row">
									<div class="col-md-4"><a href="javascript:history.go(-1);" class="btn btn-default col-md-9">返回</a></div>
									<div class="col-md-4"><a href="/content/join/edit/?jid={$view['jid']}" class="btn btn-default btn-primary col-md-9">编辑</a></div>
									<div class="col-md-4"><a href="/content/join/delete/?jid={$view['jid']}" class="btn btn-default btn-primary col-md-9">删除</a></div>
								</div>
							</div>
					</div>
				
				</div>
			</div>
		</div>
	</div>
</div>
{include file='cyadmin/footer.tpl'}