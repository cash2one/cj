{include file="$tpl_dir_base/header.tpl"}

<div id="form-adminer-edit" class="form-horizontal font12 table-border">
	<div class="form-group">
		<label class="col-sm-2 control-label">公司名称：</label>
		<div class="col-sm-6">
			<div class="form-control-static" id="p_show"><div style="width:120px;height:20px;float:left;">{$list['ep_name']}</div></div>

		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">公司账号：</label>
		<div class="col-sm-6">
			<p class="form-control-static">{$list['ep_domain']}</p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">企业规模：</label>
		<div class="col-sm-6">
			<p class="form-control-static">{$list['ep_companysize']}</p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">企业所在行业：</label>
		<div class="col-sm-6">
			<p class="form-control-static">{$list['ep_industry']}</p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">联系人姓名：</label>
		<div class="col-sm-6">
			<p class="form-control-static">{$list['ep_contact']}</p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">手机号：</label>
		<div class="col-sm-6">
			<p class="form-control-static">{$list['ep_mobilephone']}</p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">邮箱：</label>
		<div class="col-sm-6">
			<p class="form-control-static">{$list['ep_email']}</p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">当前录入人数：</label>
		<div class="col-sm-6">
			<p class="form-control-static">{$member_count}人</p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">使用期限：</label>
		<div class="form-group">
			<div class="col-sm-6">
				{if $list['free_pay_status'] != ''}
					<p class="form-control-static">
						{$list['free_pay_status']}
					</p>
				{elseif $list['pay_status'] != ''}
					<p class="form-control-static">
						{foreach $list['pay_status'] as $k => $v}
							{if $v['pay_type'] == 1}
								{if $v['date_start'] != 0 && $v['date_end'] != 0}
									<kbd>{$v['cpg_name']}</kbd>
									{$v['date_start']} ~ {$v['date_end']}
									<br><br>
								{/if}
							{elseif $v['pay_type'] == 2}
								<kbd>{$v['cpg_name']}</kbd><br><br> 定制服务
							{elseif $v['pay_type'] == 3}
								<kbd>{$v['cpg_name']}</kbd><br><br> 私有部署
							{/if}
						{/foreach}
					</p>
				{/if}
			</div>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-2 control-label">注册时间：</label>
		<div class="col-sm-6">
			<p class="form-control-static">{$list['_ep_created']}</p>
		</div>
	</div>
</div>
<p style="color:red">
	提醒：请您在使用期限到期前续费，以免影响您的正常使用！
</p>
<div style="position:absolute;left:112px;">
	<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal">
		马上续费
	</button>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div style="margin: 0 auto;height:290px;background:#3a4a58;opacity:1;z-index:1111;border:1px solid #797979;position:relative;">
				<div style="height:40px;border-bottom:1px solid #797979;text-align:center;font:18px 微软雅黑;line-height:40px;background-color:#FFF">联系客服</div>
				<div style="width:100px;height:100px;background:#3a4a58;position:absolute;top:86px;left:85px;"><img src="/admincp/static/images/tel.png" title="400-800-6961"></div>
				<a href="http://wpa.b.qq.com/cgi/wpa.php?ln=1&key=XzkzODAyNTA0MV8yNTE0OTVfNDAwODYwNjk2MV8yXw" target="_blank" style="display:block;">
					<div style="width:100px;height:100px;background:#3a4a58;position:absolute;top:90px;right:76px;"><img src="/admincp/static/images/qq.png"></div>
					<div class="qq_qq" style="position: absolute;top: 190px;right: 57px;color: #fff;border: 1px solid #a1a1a1;width: 160px;height: 46px;font-size: 16px;line-height: 46px;text-align: center;">QQ在线咨询</div>
				</a>
				<div class="phone" style="position: absolute;top: 167px;left: 55px;color: #fff;">
					<h3>400-860-6961</h3>
					<div class="time_msg" style="font-size: 13px;margin-left: 4px;">周一至周五 9:00--18:00</div>
				</div>
			</div>
		</div>
	</div>
</div>

{include file="$tpl_dir_base/footer.tpl"}