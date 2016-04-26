{include file='admincp/header.tpl'}

<form class="form-horizontal" role="form" method="post" action="{$actionSubmitUrl}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label for="cj_name" class="col-sm-2 control-label text-danger">职务名称 *</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="cj_name" name="cj_name" placeholder="职务名称" value="{$job['cj_name']|escape}" maxlength="30" required="required" />
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" class="btn btn-primary">{if $cj_id}编辑{else}添加{/if}</button>
			&nbsp;&nbsp;
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</form>

{include file='admincp/footer.tpl'}