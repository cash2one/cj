{include file='admincp/header.tpl'}

<form class="form-horizontal font12" role="form" method="post">
<input type="hidden" name="formhash" value="{$formhash}" />
<input type="hidden" name="module_id" value="{$application['module_id']}" />
<div class="panel panel-warning">
	<div class="panel-heading"><strong>关闭应用《{$application['source_name']|escape}》</strong></div>
	<div class="panel-body font12">
		<div class="form-group">
			<label for="plugin_name" class="col-sm-3 control-label text-right">应用名称</label>
			<div class="col-sm-7">
				<p class="form-control-static">{$application['plugin_name']|escape}{if $application['source_name'] != $application['plugin_name']} ({$application['source_name']}){/if}</p>
			</div>
			<div class="col-sm-2"></div>
    	</div>
		<div class="form-group">
			<label for="plugin_description" class="col-sm-3 control-label text-right">应用描述</label>
			<div class="col-sm-7">
				<p class="form-control-static">{$application['plugin_description']|escape}</p>
			</div>
			<div class="col-sm-2"></div>
    	</div>
{if $application['plugin_icon_url']}
    	<div class="form-group">
			<label for="plugin_icon" class="col-sm-3 control-label text-right">应用图标</label>
			<div class="col-sm-7">
				<img src="{$application['plugin_icon_url']}" alt="" class="qywx-application-icon" />
			</div>
			<div class="col-sm-2"></div>
    	</div>
{/if}
    	<div class="form-group">
    		<div class="col-sm-offset-3 col-sm-9">
    			<p class="form-control-static text-danger">
    			注：关闭应用后，应用相关数据仍可以通过后台管理进行浏览，但微信将不再具有该应用的功能。
    			</p>
    		</div>
    	</div>
    	<div class="form-group">
			<div class="col-sm-offset-3 col-sm-9">
				<button type="submit" class="btn btn-warning">确认关闭</button>
				<span class="space"></span>
				<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
			</div>
			<div class="col-sm-2"></div>
    	</div>
    </div>
</div>
</form>

{include file='admincp/footer.tpl'}