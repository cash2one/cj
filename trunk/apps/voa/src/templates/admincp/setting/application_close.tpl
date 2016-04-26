{include file="$tpl_dir_base/header.tpl"}

<form id="form-servicetype" class="form-horizontal font12" role="form" method="post" action="{$application_update_submit_url}">
<input type="hidden" name="formhash" value="{$formhash}" />

<h4><strong>关闭 “<span class="text-primary">{$plugin['cp_name']|escape}</span>” 应用</strong></h4>
<div class="alert alert-warning" role="alert">
关闭该应用后将无法再发送消息，同时会在关注者的微信中消失。但应用的数据仍旧可以在本后台内浏览到，除非您手动删除处理。
<h5><strong class="font12">请登录微信企业平台（qy.weixin.qq.com）的“应用中心”，选择对应的应用“{$plugin['cp_name']|escape}”，“禁用”该应用，<a href="{$IMGDIR}help/close_01.png" target="_blank" title="点击查看"><strong><i class="fa fa-external-link"></i> 如图所示 <i class="fa fa-image"></i></strong></a>。</strong></h5>
</div>

<div class="form-group">
	<div class="col-sm-offset-3 col-sm-8">
		<button type="submit" class="btn btn-warning">确定关闭</button>
		<span class="space"></span><span class="space"></span>
		<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
	</div>
</div>
</form>

{include file="$tpl_dir_base/footer.tpl"}