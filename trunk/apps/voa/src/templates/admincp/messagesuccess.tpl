{include file="$tpl_dir_base/header.tpl"}

<div class="row">
	<div class="col-md-6 col-md-offset-3">

		{if $jsUrl}<script type="text/javascript">delayURL('{$jsUrl}',2);</script>{/if}
		<div class="panel panel-success panel-message">
			<div class="panel-heading">提示信息</div>
			<div class="panel-body">
				{$message}
			</div>
			<div class="panel-footer text-right">{if $url}<a href="{$url}" class="btn btn-success btn-sm" role="button"><i class="fa fa-backward"></i> 继续操作</a>{/if}</div>
		</div>

	</div>
</div>

{include file="$tpl_dir_base/footer.tpl"}