{include file='cyadmin/header.tpl'}
<h5 class="col-xs-12 col-sm-4 text-left text-left-sm">模板详情</h5>
{include file='cyadmin/data/template/menu.tpl'}

<div id="form-adminer-edit" class="form-horizontal font12" style="border:1px solid #CCC">
	<div class="form-group">
		<label class="col-sm-2 control-label">模板标题：</label>
		<div class="col-sm-6">
			<p class="form-control-static">{$data['title']}</p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">模板摘要：</label>
		<div class="col-sm-6" style="word-wrap:break-word;word-break:break-all;">
			<p class="form-control-static">{$data['summary']}</p>
		</div>
	</div>
	<div class="form-group">
	<label class="col-sm-2 control-label">模板图标：</label>
	<div class="col-sm-6" style="word-wrap:break-word;word-break:break-all;">
		<p class="form-control-static" ><i class="fa {$data['icon']} text-slg" style="padding:5px;"></i></p>
	</div>
	</div>

	<div class="form-group">
		<label class="col-sm-2 control-label">封面图片：</label>
		<div class="col-sm-6">
			<p class="form-control-static"><img src="{$data['cover_url']}" width=480 height=230 ></p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">模板内容：</label>
		<div class="col-sm-6" style="word-wrap:break-word;word-break:break-all;">
			<p class="form-control-static">{$data['content']}</p>
		</div>
	</div>
</div>
<br>

 <div style="clear:both">
 <a href="javascript:history.go(-1);" class="btn btn-default">返回</a>
 </div>

{include file='cyadmin/footer.tpl'}