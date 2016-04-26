<script type="text/javascript">
//最大步骤号
var max_step = 6;
// 应用ID填写检查所在的步骤号
var step_appid = 5;
</script>

<div id="step-part-1" class="step-part step-part-small">
	<strong>1</strong>
	<span>添加应用</span>
</div>

<div id="step-part-2" class="step-part step-part-small">
	<strong>2</strong>
	<span>设置可信域名</span>
</div>

<div id="step-part-3" class="step-part step-part-small">
	<strong>3</strong>
	<span>开启回调模式</span>
</div>

<div id="step-part-4" class="step-part">
	<strong>4</strong>
	<span>开启自定义菜单</span>
</div>

<div id="step-part-5" class="step-part step-part-small">
	<strong>5</strong>
	<span>填写应用 ID</span>
</div>

<div id="step-part-6" class="step-part end">
	<strong>6</strong>
	<span>设置应用权限</span>
</div>

<script type="text/template" id="step-1">
	<h5 class="alert alert-warning __top"><strong>进入微信企业号（qy.weixin.qq.com）应用中心，进入 “{$plugin['cp_name']|escape}”应用</strong></h5>
	<p class="thumbnail"><img src="{$IMGDIR}help/app_001.png" alt="" /></p>
	<h5 class="alert alert-warning"><strong>根据下面内容填写应用基本信息</strong></h5>
	<table class="table table-bordered">
		<colgroup>
			<col class="t-col-15" />
			<col />
			<col class="t-col-20" />
		</colgroup>
		<tbody>
			<tr class="text-center">
				<th class="active text-center">{$lang_app_name}</th>
				<td id="txt-appname">{$plugin['cp_name']|escape}</td>
				<td id="txt-appname-button" class="_clip">复　制</td>
			</tr>
			<tr class="text-center">
				<th class="active text-center">{$lang_app_description}</th>
				<td id="txt-appdescription">{$plugin['cp_description']}</td>
				<td id="txt-appdescription-button" class="_clip">复　制</td>
			</tr>
			<tr class="text-center">
				<th class="active text-center">{$lang_app_logo}</th>
				<td><img src="{$plugin['_icon_url']}" alt="" style="width:48px;height:48px;" /></td>
				<td><a href="{$plugin['_icon_url']}">右键点击<br />另存下载</a></td>
			</tr>
		</tbody>
	</table>
	<p class="thumbnail"><img src="{$IMGDIR}help/app_003.png" alt="" /></p>
	<h5 class="alert alert-warning"><strong>选择应用可见范围</strong></h5>
	<p class="thumbnail"><img src="{$IMGDIR}help/app_004.png" alt="" /></p>
</script>

<script type="text/template" id="step-2">
	<h5 class="alert alert-warning __top"><strong>进入可信域名设置</strong></h5>
	<p class="thumbnail"><img src="{$IMGDIR}help/app_005.png" alt="" /></p>
	<h5 class="alert alert-warning"><strong>复制下面内容，填写可信域名</strong></h5>
	<table class="table table-bordered">
		<colgroup>
			<col class="t-col-15" />
			<col />
			<col class="t-col-20" />
		</colgroup>
		<tbody>
			<tr class="text-center">
				<th class="active text-center">{$lang_app_domain}</th>
				<td id="txt-domain">{$setting['domain']|escape}</td>
				<td id="txt-domain-button" class="_clip">复　制</td>
			</tr>
		</tbody>
	</table>
	<p class="thumbnail"><img src="{$IMGDIR}help/app_007.png" alt="" /></p>
</script>

<script type="text/template" id="step-3">
	<h5 class="alert alert-warning __top"><strong>进入回调模式</strong></h5>
	<p class="thumbnail"><img src="{$IMGDIR}help/app_008.png" alt="" /></p>
	<h5 class="alert alert-warning"><strong>右上角开启</strong></h5>
	<p class="thumbnail"><img src="{$IMGDIR}help/app_009.png" alt="" /></p>
	<h5 class="alert alert-warning"><strong>根据复制下面内容填写接口配置信息</strong></h5>
	<table class="table table-bordered">
		<colgroup>
			<col class="t-col-15" />
			<col />
			<col class="t-col-20" />
		</colgroup>
		<tbody>
			<tr class="text-center">
				<th class="active text-center">{$lang_app_url}</th>
				<td id="txt-url">{$plugin_url}</td>
				<td id="txt-url-button" class="_clip">复　制</td>
			</tr>
			<tr class="text-center">
				<th class="active text-center">{$lang_token}</th>
				<td id="txt-token">{$setting['token']}</td>
				<td id="txt-token-button" class="_clip">复　制</td>
			</tr>
			<tr class="text-center">
				<th class="active text-center">{$lang_aeskey}</th>
				<td id="txt-aeskey">{$setting['aes_key']}</td>
				<td id="txt-aeskey-button" class="_clip">复　制</td>
			</tr>
		</tbody>
	</table>
	<p class="thumbnail"><img src="{$IMGDIR}help/app_011.png" alt="" /></p>
</script>

<script type="text/template" id="step-4">
	<h5 class="alert alert-warning __top"><strong>开启自定义菜单</strong></h5>
	<p class="thumbnail"><img src="{$IMGDIR}help/app_012.png" alt="" /></p>
	<p class="thumbnail"><img src="{$IMGDIR}help/app_013.png" alt="" /></p>
	<p class="thumbnail"><img src="{$IMGDIR}help/app_014.png" alt="" /></p>
</script>

<script type="text/template" id="step-5">
	<div class="form-horizontal __top">
	<table class="table table-bordered font12">
		<colgroup>
			<col class="t-col-15" />
			<col />
		</colgroup>
		<tbody>
			<tr>
				<td colspan="2" class="info">在微信企业号后台“应用中心”打开“<strong>{$plugin['cp_name']|escape}</strong>”应用，复制“{$lang_agentid}”到下方</td>
			</tr>
			<tr>
				<th class="active text-right">
					<label for="id_agent_id" class="control-label">{$lang_agentid}</label>
				</th>
				<td>
					<div class="col-sm-6">
						<input type="text" class="form-control col-sm-6" id="id_agent_id" placeholder="仅需填写数字ID" value="{$agent_id}" />
					</div>
					<div class="col-sm-3"><label class="control-label text-danger">必须填写</label></div>
				</td>
			</tr>
		</tbody>
	</table>
	</div>
	<p class="thumbnail"><img src="{$IMGDIR}help/app_020.png" alt="" /></p>
</script>

<script type="text/template" id="step-6">
	<h5 class="alert alert-warning __top"><strong>进入权限管理</strong></h5>
	<p class="thumbnail"><img src="{$IMGDIR}help/app_021.png" alt="" /></p>
	<h5 class="alert alert-warning"><strong>修改应用权限，点击“修改应用选择”，勾选“通讯录”应用，给对应的应用点选“配置应用”权限</strong></h5>
	<p class="thumbnail"><img src="{$IMGDIR}help/app_022.png" alt="" /></p>
</script>