{include file="$tpl_dir_base/header.tpl"}

<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="{$actionUrl}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label class="col-sm-2 control-label">当前域名</label>
		<div class="col-sm-6">
			<p class="form-control-static">{$setting['domain']}</p>
		</div>
	</div>
	<div class="form-group">
		<label for="sitename" class="col-sm-2 control-label">公司名称</label>
		<div class="col-sm-6">
			<input type="text" class="form-control" id="sitename" name="sitename" placeholder="公司名称" value="{$setting['sitename']}" />
		</div>
	</div>
	<div class="form-group">
		<label for="shortname" class="col-sm-2 control-label">公司简称</label>
		<div class="col-sm-6">
			<input type="text" class="form-control" id="shortname" name="shortname" placeholder="公司简称" value="{$setting['shortname']}" />
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-6">
			<button type="submit" class="btn btn-primary">修改</button>
			&nbsp;&nbsp;
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</form>
<script type="text/javascript" src="{$JSDIR}expand_md5.js"></script>
<script type="text/javascript">
jQuery(function() {
	jQuery('#form-adminer-edit').submit(function() {
		var sitename = jQuery('#sitename').val();
		var shortname = jQuery('#shortname').val();

		if (50 < sitename.replace(/[^\x00-\xFF]/g, '**').length) {
			alert('公司名称过长');
			return false;
		}

		if (24 < shortname.replace(/[^\x00-\xFF]/g, '**').length) {
			alert('公司简称过长');
			return false;
		}

		return true;
	});
});
</script>

{include file="$tpl_dir_base/footer.tpl"}