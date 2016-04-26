{include file='uc/header.tpl'}

<div class="row">
	<div class="col-md-4 col-md-offset-4">
		<div class="panel panel-{if $msg_type == 'success'}success{else}danger{/if}">
			<div class="panel-heading">操作提示</div>
			<div class="panel-body">
				{$message}
			</div>
			<div class="panel-footer text-right">
{if $msg_type == 'success'}
				{if $url}<script type="text/javascript">delayURL('{$url}',2);</script>{/if}
				<a href="{$url}" role="button" class="btn btn-default">点击继续</a>
{else}
				<a href="{if $url}{$url}{else}javascript:history.go(-1);{/if}" role="button" class="btn btn-default">返回到上一页</a>
{/if}
			</div>
		</div>
	</div>
</div>

{include file='uc/footer.tpl'}