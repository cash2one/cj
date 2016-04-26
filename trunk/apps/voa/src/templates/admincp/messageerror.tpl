{include file="$tpl_dir_base/header.tpl"}

<div class="row">
	<div class="col-md-6 col-md-offset-3">
		<div class="panel panel-danger panel-message">
			<div class="panel-heading">{$title|default:'提示信息'}</div>
			<div class="panel-body">
				{$message}
			</div>
			<div class="panel-footer text-right"><a href="javascript:history.go(-1);" class="btn btn-danger btn-sm" role="button"><i class="fa fa-backward"></i> 返回到上一页</a></div>
		</div>
	</div>
</div>

{include file="$tpl_dir_base/footer.tpl"}