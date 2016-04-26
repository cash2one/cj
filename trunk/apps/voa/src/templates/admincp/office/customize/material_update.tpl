{include file="$tpl_dir_base/header.tpl"}
<form class="panel form-horizontal" id="soform" role="form" method="post" action="{$material_url}?act=update">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<input type="hidden" name="mtid" value="{$material['mtid']}" />
	<input type="hidden" name="refer" value="{$refer}" />
	<div class="panel-body">
		<div class="row form-group">
			<label class="col-sm-2 control-label">专题标题:</label>
			<div class="col-sm-8">
				<input type="text" id="subject" name="subject" value="{$material['subject']}" class="form-control" />
			</div>
		</div>
		<div class="row form-group">
			<label class="col-sm-2 control-label">内容:</label>
			<div class="col-sm-8">{$ueditor_output}</div>
		</div>

		<div class="col-sm-offset-2 col-sm-10">
			<input type="submit" value="提交" class="btn btn-primary"/>&nbsp;&nbsp;
			<input type="reset" value="取消" class="btn"/>

		</div>
	</div>

</form>
{include file="$tpl_dir_base/footer.tpl"}