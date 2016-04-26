{include file='cyadmin/header.tpl'}
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title font12"><strong>消息详情</strong></h3>
		</div>
		<div class="panel-body upload">
			<div class="">
				<label class="control-label col-sm-2">接收者：</label>
				<div class="col-sm-9"><p>{if !empty($list['_epid'])}{$list['_epid']}{else if}所有公司{/if}</p></div>
			</div>
			
			<div class="">
				<label class="control-label col-sm-2">标题：</label>
				<div class="col-sm-9"><p>{$list['title']|escape}</p></div>
			</div>
			
			<div class="">
				<label class="col-sm-2 control-label">作者：</label>
				<div class="col-sm-9"><p>{$list['author']}</p></div>
			</div>

			<div class="">				
				<div class="col-sm-11"><p>{$list['content']}</p></div>
			</div>

			{if !empty($list['imgurl'])}
			<div class="">
				<label class="col-sm-2 control-label">配图：</label>
				<div class="col-sm-9">
					<p class="form-control-static"><img src="{$list['imgurl']}"></p>
				</div>
			</div>
			{else if}
			<div class="">
				<label class="col-sm-2 control-label">配图：</label>
				<div class="col-sm-9" style="height:26px;">
					<p class="form-control-static">无</p>
				</div>
			</div>
			{/if}
			
			<div class="y_button">
				<div class="col-sm-9" style="margin-top:39px;">
					<div class="row">
						<div class="col-md-4"><a href="javascript:history.go(-1);" class="btn btn-primary col-md-9">返回</a></div>
					</div>
				</div>
			</div>
		</div>
	</div>

{include file='cyadmin/footer.tpl'}