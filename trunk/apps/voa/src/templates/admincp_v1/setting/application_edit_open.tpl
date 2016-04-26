{include file='admincp/header.tpl'}
<form class="form-horizontal font12" role="form" method="post" enctype="multipart/form-data">
<input type="hidden" name="formhash" value="{$formhash}" />
<input type="hidden" name="module_id" value="{$application['module_id']}" />
<div class="panel panel-success">
	<div class="panel-heading"><strong>启用应用《{$application['source_name']|escape}》</strong></div>
	<div class="panel-body font12">
		<div class="form-group">
			<label for="plugin_name" class="col-sm-3 control-label text-right">应用名称</label>
			<div class="col-sm-7">
				<input type="text" id="plugin_name" name="plugin_name" class="form-control" value="{$application['plugin_name']|escape}" required="required" maxlength="{$name_length_limit['max']}" />
				<p class="help-block">应用名称，必须填写，限制 {$name_length_limit['min']} 到 {$name_length_limit['max']} 个字之间</p>
			</div>
			<div class="col-sm-2"><p class="help-block" id="plugin_name-tip">0/{$name_length_limit['max']}</p></div>
    	</div>
		<div class="form-group">
			<label for="plugin_description" class="col-sm-3 control-label text-right">应用描述</label>
			<div class="col-sm-7">
				<textarea class="form-control" id="plugin_description" name="plugin_description" rows="2" required="required" data-maxlength="{$description_length_limit['max']}">{$application['plugin_description']|escape}</textarea>
				<p class="help-block">应用描述，必须填写，限制 {$description_length_limit['min']} 到 {$description_length_limit['max']} 个字之间</p>
			</div>
			<div class="col-sm-2"><p class="help-block" id="plugin_description-tip">0/{$description_length_limit['max']}</p></div>
    	</div>
    	<div class="form-group">
			<label for="plugin_icon" class="col-sm-3 control-label text-right">应用图标</label>
			<div class="col-sm-7">
				<input type="hidden" name="upload_name" value="plugin_icon" />
				<input type="file" id="plugin_icon" name="plugin_icon" class="form-control" value="{$application['plugin_name']|escape}" maxlength="{$name_length_limit['max']}" />
				<p class="help-block">上传应用图标，必须填写，限制 {$icon_limit_type} 等格式图片，容量不超过 {$icon_limit_size}</p>
			</div>
			<div class="col-sm-2">
				{if $application['plugin_icon_url']}
				<p class="form-control-static"><img src="{$application['plugin_icon_url']}" class="wxqy-application-icon" alt="" /></p>
				{/if}
			</div>
    	</div>
    	<div class="form-group">
			<div class="col-sm-offset-3 col-sm-9">
				<button type="submit" class="btn btn-primary">提交启用应用</button>
				<span class="space"></span>
				<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
			</div>
			<div class="col-sm-2"></div>
    	</div>
    </div>
</div>
</form>
<script type="text/javascript">
jQuery(function(){
	reCounter('plugin_name');
	reCounter('plugin_description', false);
});
</script>
{include file='admincp/footer.tpl'}