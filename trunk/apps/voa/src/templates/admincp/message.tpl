{include file="$tpl_dir_base/header.tpl"}

<div class="row">
	<div class="col-md-6 col-md-offset-3">
	{if $type == 'success'}
	<script type="text/javascript">delayURL('{$jsUrl}',2);</script>
	<div class="panel panel-success">
		<div class="panel-heading">提示信息</div>
		<div class="panel-body">
			{$message}
		</div>
		<div class="panel-footer text-right"><a href="{$url}" class="btn btn-success btn-sm" role="button"><i class="fa fa-backward"></i> 继续操作</a></div>
	</div>

	{else}

	<div class="panel panel-danger">
		<div class="panel-heading">提示信息</div>
		<div class="panel-body">
			{$message}
		</div>
		<div class="panel-footer text-right"><a href="javascript:history.go(-1);" class="btn btn-danger btn-sm" role="button"><i class="fa fa-backward"></i> 返回到上一页</a></div>
	</div>

	{/if}
	</div>
</div>

{include file="$tpl_dir_base/footer.tpl"}