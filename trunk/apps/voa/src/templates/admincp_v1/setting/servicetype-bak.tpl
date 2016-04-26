
<form id="form-servicetype" class="form-horizontal font12" role="form" method="post" action="{$modifyFormActionUrl}">
<input type="hidden" name="formhash" value="{$formhash}" />
<div class="panel panel-default">
	<div class="panel-heading"><strong>{$operation_list[$module][$operation][$module_plugin_id]['name']}</strong></div>
	<div class="panel-body">
{if $open_app_use}
		<div class="form-group">
			<label class="col-sm-2 control-label">服务类型</label>
			<div class="col-sm-9">
				<label class="radio-inline"><input type="radio" name="ep_wxqy" class="_ep_wxqy" value="1"{if $ep_wxqy == 1} checked="checked"{/if} /> 微信企业号应用服务</label>
				<label class="radio-inline"><input type="radio" name="ep_wxqy" class="_ep_wxqy" value="0"{if $ep_wxqy == 0} checked="checked"{/if} /> APP 及 网页版应用服务</label>
			</div>
		</div>
{/if}
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-9">
				<div class="alert alert-info" role="alert">
					<div id="ep_wxqy_1"{if $ep_wxqy != 1} style="display:none"{/if}>
						<p>基于<strong>微信企业号</strong>的服务，您可以通过微信、app移动客户端以及PC网页版本来使用本系统。</p>
						<p>使用本服务需要您已注册过 <a href="https://qy.weixin.qq.com/" target="_blank"><strong>微信企业号</strong><i class="fa fa-external-link"></i></a>，并填写下面的<a href="{$base->view_help_url('如何获取开发者凭据')}" target="_blank"><strong>开发者凭据</strong><i class="fa fa-question-circle"></i></a>。</p>
					</div>
					<div id="ep_wxqy_0"{if $ep_wxqy != 0} style="display:none"{/if}>基于APP 及 网页版的服务，您可以使用app移动客户端以及PC网页版本来使用本系统，但不能使用通过微信来使用本系统。</div>
				</div>
			</div>
		</div>
		<div id="ep_wxqy_option_1"{if $ep_wxqy != 1} style="display:none"{/if}>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-9"><h3><strong>设置开发者凭据</strong> <small><a href="{$base->view_help_url('如何获取开发者凭据')}" target="_blank">如何获取开发者凭据 <i class="fa fa-question-circle"></i></small></a></h3></div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">{$lang_corpid}</label>
				<div class="col-sm-9">
{if $is_first_run || (!$is_first_run && !$corp_id)}
<!-- 首次使用系统  或 非首次使用系统且corp_id为空 -->
					<input type="text" class="form-control" id="corpid" name="corp_id" placeholder="填写微信企业号的 {$lang_corpid}" value="{$corp_id}" required="required" />
					<span class="help-block">注：一旦确认将不可更改</span>
{else}
					<span class="form-control disabled" disabled="disabled">{$corp_id|escape}</span>
					<span class="help-block">注：{$lang_corpid} 已经确认，您不能再次更改该值</span>
{/if}
					
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">{$lang_secret}</label>
				<div class="col-sm-9">
					<input type="text" class="form-control" id="corpsecret" name="corp_secret" placeholder="填写微信企业号的 {$lang_secret}" value="{$corp_secret}" required="required" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">{$lang_qrcode_url}</label>
				<div class="col-sm-9">
					<input type="text" class="form-control" id="qrcode" name="qrcode" placeholder="填写微信企业号的二维码 URL 地址" value="{$qrcode}" required="required" />
					<span class="help-block">获取地址的方法：进入微信企业号平台，进入“<strong>设置</strong>”，找到<strong>二维码图片</strong>，点击鼠标右键选择复制图片地址，然后粘贴到这里即可。</span>
				</div>
			</div>
		</div>
		<div id="ep_wxqy_option_0"{if $ep_wxqy != 0} style="display:none"{/if}>
			<div class="form-group">
				<label class="col-sm-2 control-label"></label>
				<div class="col-sm-9">
					
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-9">
				<button type="submit" class="btn btn-primary">下一步</button>
				<span class="space"></span><span class="space"></span>
				<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
			</div>
		</div>
	</div>
</div>
</form>

<script type="text/javascript">
jQuery(function(){

	jQuery('input[name=ep_wxqy]').change(function(){
		var c = this.value;
		jQuery.each(jQuery('input[name=ep_wxqy]'), function(i, o){
			if (o.value == c) {
				jQuery('#ep_wxqy_'+o.value).show();
				jQuery('#ep_wxqy_option_'+o.value).show();
			} else {
				jQuery('#ep_wxqy_'+o.value).hide();
				jQuery('#ep_wxqy_option_'+o.value).hide();
			}
		});
	});
	
	jQuery('#form-servicetype').submit(function(){
		var jq_ep_wxqy = jQuery('input[name=ep_wxqy]');
		if (jq_ep_wxqy.length == 0 || jQuery('input[name=ep_wxqy]:checked').val()) {
			
			var jq_corpid = jQuery('#corpid');
			var jq_corpsecret = jQuery('#corpsecret');
			
			if (jq_corpid.val() == '') {
				alert('请正确填写 {$lang_corpid}');
				jq_corpid.focus();
				return false;
			}
			if (jq_corpsecret.val() == '') {
				alert('请正确填写 {$lang_secret}');
				jq_corpsecret.focus();
				return false;
			}			
		}
		
		
		return true;
	});
	
});
</script>