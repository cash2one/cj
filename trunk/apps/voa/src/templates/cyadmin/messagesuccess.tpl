{include file='cyadmin/header.tpl'}

<div class="row">
	<div class="col-md-6 col-md-offset-3">

		{if $js_url && $redirect !== null}<script type="text/javascript">delayURL('{$js_url}',2);</script>{/if}
		<div class="panel panel-success">
			<div class="panel-heading">提示信息</div>
			<div class="panel-body">
				{$message}
			</div>
			{if $url}<div class="panel-footer text-right"><a href="{$url}" class="btn btn-success btn-sm" role="button"><i class="fa fa-backward"></i> 继续操作</a></div>{/if}
		</div>

	</div>
</div>

{include file='cyadmin/footer.tpl'}