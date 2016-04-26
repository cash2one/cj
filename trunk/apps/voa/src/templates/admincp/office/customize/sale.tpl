{include file="$tpl_dir_base/header.tpl"}

<div class="stat-panel panel-padding bordered ">
	<div class="stat-row">
		<div class="stat-counters no-border">
			<div class="stat-cell col-xs-6 padding-sm no-padding-hr">
				<!-- Big text -->
				<span class="text-bg text-info">绑定微信服务号，把服务号和企业打通</span><br>
				<span class="text-xs text-muted">绑定后即可将企业号内的产品放入服务号中进行售卖，让您获更多的售卖渠道</span>
				<br><br>
				<a href="{$auth_url}"><button class="btn btn-primary btn-lg">我有微信公共账号，立即设置</button></a>
			</div>
			<div class="stat-cell col-xs-6 panel-padding">
				<span class="text-bg"><strong>温馨提示：</strong></span><br><br />
				<div class="text-xs text-muted">
					<span class="padding-xs-vr">1.一个微信服务号只能与一个企业号绑定;</span><br />
					<span class="padding-xs-vr">2.目前只支持已开通微信支付的服务号;</span><br />
					<span class="padding-xs-vr">3.授权后会更改您服务号的菜单设置;</span><br />
					<span class="padding-xs-vr">4.更多功能正在研发中, 敬请期待...</span>
				</div>
			</div>
		</div>
	</div>
</div>

{include file="$tpl_dir_base/footer.tpl"}
