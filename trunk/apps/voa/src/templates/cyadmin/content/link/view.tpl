{include file='cyadmin/header.tpl'}
{include file='cyadmin/content/link/menu.tpl'}
<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<div class="right-col">
				<div class="panel tl-body form-horizontal" >
						<div class="form-group font12" style="margin-left:20px">
							<label for="dateformat" class="col-sm-1">链接名称：</label>
							<div class="col-sm-11">
								<b>{$view['linkname']}</b>
							</div>
						</div>

					<div class="form-group font12" style="margin-left:20px">
							<label class="col-sm-1" >排序：</label>
							<div class="col-sm-11">
								{$view['lsort']}
							</div>
					</div>

					<div class="form-group font12" style="margin-left:20px">
							<label class="col-sm-1" >链接来源：</label>
							<div class="col-sm-11">
								{$view['linkurl']}
							</div>
					</div>

					<div class="form-group font12" style="margin-left:20px">
							<label class="col-sm-1" >链接类型：</label>
							<div class="col-sm-11">
								{$view['type']}
							</div>
					</div>
					{if $view['linktype'] == 1}
					<div class="form-group font12" style="margin-left:20px">
							<label class="col-sm-1" >企业名称：</label>
							<div class="col-sm-11">
								{$view['companyname']}
							</div>
					</div>
					{else}
						<div class="form-group font12" style="margin-left:20px">
							<label class="col-sm-1" >链接LOGO：</label>
							<div class="col-sm-11">
								<img src="{$view['url']}" alt="" width="150" height="60">
							</div>
						</div>
					{/if}
					
					<div class="form-group">
							<div class="col-sm-offset-2 col-sm-9">
								<div class="row">
									<div class="col-md-4"><a href="javascript:history.go(-1);" class="btn btn-default col-md-9">返回</a></div>
									<div class="col-md-4"><a href="/content/link/edit/?lid={$view['lid']}" class="btn btn-default btn-primary col-md-9">编辑</a></div>
									<div class="col-md-4"><a href="/content/link/delete/?lid={$view['lid']}" class="btn btn-default btn-primary col-md-9">删除</a></div>
								</div>
							</div>
					</div>
				
				</div>
			</div>
		</div>
	</div>
</div>
{include file='cyadmin/footer.tpl'}