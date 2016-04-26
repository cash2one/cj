{include file='cyadmin/header.tpl'}
{include file='cyadmin/content/article/menu.tpl'}
<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<div class="right-col">
				<div class="panel tl-body form-horizontal" >
						<div class="form-group font12" style="margin-left:20px">
							<label for="dateformat" class="col-sm-1">文章标题：</label>
							<div class="col-sm-11">
								<b>{$view['title']}</b>
							</div>
						</div>
						<div class="form-group font12" style="margin-left:20px">
							<label for="dateformat" class="col-sm-1">类型：</label>
							<div class="col-sm-11">
								<b>{$view['cname']}</b>
							</div>
						</div>
						<div class="form-group font12" style="margin-left:20px">
							<label for="dateformat" class="col-sm-1 ">来源：</label>
							<div class="col-sm-11">
								<b>{$view['source']}</b>
							</div>
						</div>
						<div class="form-group font12" style="margin-left:20px">
							<label for="dateformat" class="col-sm-1">来源链接：</label>
							<div class="col-sm-11">
								<b>{$view['sourl']}</b>
							</div>
						</div>
						{if $view['logo_url']}
						<div class="form-group font12" style="margin-left:20px">
							<label for="dateformat" class="col-sm-1">来源LOGO：</label>
							<div class="col-sm-11">
								<a target="_blank" href="{$view['logo_url']}"><img src="{$view['logo_url']}" alt="" width="400"></a>
							</div>
						</div>
						{/if}
						<div class="form-group font12" style="margin-left:20px">
							<label for="dateformat" class="col-sm-1">摘要：</label>
							<div class="col-sm-11">
								{$view['description']}
							</div>
						</div>
						<div class="form-group font12" style="margin-left:20px">
							<label class="col-sm-1" for="id_author">封面图片：</label>
							<div class="col-sm-11">
								{if $view['face_url']}
								<a target="_blank" href="{$view['face_url']}"><img src="{$view['face_url']}" alt="" width="400"></a>
								{/if}
								
							</div>
							
						</div>
					<div class="form-group font12" style="margin-left:20px">
							<label class="col-sm-1" >排序：</label>
							<div class="col-sm-11">
								{$view['asort']}
							</div>
					</div>
					<div class="form-group font12" style="margin-left:20px">
							<label class="col-sm-1" >正文：</label>
							<div class="col-sm-11">
								{$view['content']}
							</div>
					</div>
					<div class="form-group font12" style="margin-left:20px">
							<label class="col-sm-1" >标签：</label>
							<div class="col-sm-11">
							{if $view['tags']}
							<ul class="train-item-ul">
								{foreach $view['tags'] as $val}
								<li class="train-item-ul__li train-item-ul__cur train-item-ul__link" >{$val}</li>
								{/foreach}
							</ul>

							{/if}
							</div>
							
					</div>
					<div class="form-group font12" style="margin-left:20px">
							<label class="col-sm-1" >阅读数：</label>
							<div class="col-sm-11">
								{$view['read_num']}
							</div>
							
					</div>
					<div class="form-group">
							<div class="col-sm-offset-2 col-sm-9">
								<div class="row">
									<div class="col-md-4"><a href="javascript:history.go(-1);" class="btn btn-default col-md-9">返回</a></div>
									<div class="col-md-4"><a href="/content/article/edit/?aid={$view['aid']}" class="btn btn-default btn-primary col-md-9">编辑</a></div>
									<div class="col-md-4"><a href="/content/article/delete/?aid={$view['aid']}" class="btn btn-default btn-primary col-md-9">删除</a></div>
								</div>
							</div>
					</div>
				
				</div>
			</div>
		</div>
	</div>
</div>
{include file='cyadmin/footer.tpl'}