{include file='wxwall/admincp/header.tpl'}

<ul class="nav nav-tabs font-12">
	<li class="active"><a href="{$currentLink}"><strong>{$currentName}</strong></a></li>
</ul>
<br />
<form class="form-horizontal font12" role="form" method="post" action="{$formActionUrl}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label class="col-sm-3 control-label">微信墙地址</label>
		<div class="col-sm-9">
			<strong class="form-control-static"><a href="{$wxwallUrl}" target="_blank">{$wxwallUrl}</a></strong>
			<span class="help-block text-info font-12">访问该地址即可呈现微信墙页面</span>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">微信墙编号</label>
		<div class="col-sm-9">
			<p class="form-control-static">{$post_message_code}</p>
			<span class="help-block text-info font-12">回复关键字 “{$post_message_code}” 用户即可进入上墙模式</span>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">微信墙开放时间</label>
		<div class="col-sm-9">
			<p class="form-control-static"><strong>{$wxwall['_begintime']}</strong> 至 <strong>{$wxwall['_endtime']}</strong></p>
		</div>
	</div>
	<div class="form-group">
		<label for="ww_subject" class="col-sm-3 control-label">微信墙标题</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="ww_subject" name="ww_subject" placeholder="输入您的微信墙显示的标题" value="{$wxwall['ww_subject']|escape}" maxlength="80" />
		</div>
	</div>
	<div class="form-group">
		<label for="ww_isopen_{$wxwall['ww_isopen']}" class="col-sm-3 control-label">是否开放</label>
		<div class="col-sm-9">
{foreach $isopen as $_id => $_n}
			<label class="radio-inline"><input type="radio" id="ww_isopen_{$_id}" name="ww_isopen" value="{$_id}"{if $wxwall['ww_isopen'] == $_id} checked="checked"{/if} /> {$_n}</label>
{/foreach}
			<span class="help-block text-info font-12">只有设置为<label for="ww_isopen_1"><strong>开放状态</strong></label>才能够通过<abbr title="访问地址：{$wxwallUrl}" class="initialism font-12">微信墙地址</abbr>访问</span>
		</div>
	</div>
	<div class="form-group">
		<label for="ww_postverify_{$wxwall['ww_postverify']}" class="col-sm-3 control-label">墙内容审核设置</label>
		<div class="col-sm-9">
{foreach $postverify as $_id => $_n}
			<label class="radio-inline"><input type="radio" id="ww_postverify_{$_id}" name="ww_postverify" value="{$_id}"{if $wxwall['ww_postverify'] == $_id} checked="checked"{/if} /> {$_n}</label>
{/foreach}
			<span class="help-block text-info font-12">设置为<label for="ww_postverify_1"><strong>需要审核</strong></label>则所有发送到微信墙的内容均需要管理员审核通过才能呈现在微信墙上，反之则直接呈现。</span>
		</div>
	</div>
	<div class="form-group">
		<label for="ww_maxpost" class="col-sm-3 control-label">每用户最多发送内容数</label>
		<div class="col-sm-9">
			<div class="row">
				<div class="col-sm-2"><input type="number" class="form-control" id="ww_maxpost" name="ww_maxpost" placeholder="请输入正整数或者零" value="{$wxwall['ww_maxpost']}" max="255" min="0" /></div>
				<div class="col-sm-10"><span class="help-block text-info font-12">设置每个用户最多允许发表的微信墙内容条数，请输入0到255之间的正整数，0则不限制。</span></div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label for="ww_message" class="col-sm-3 control-label">微信墙说明</label>
		<div class="col-sm-9">
			<textarea class="form-control" id="ww_message" name="ww_message" cols="10" rows="2">{$wxwall['ww_message']|escape}</textarea>
			<span class="help-block text-info font-12">微信墙的介绍说明文字，会呈现在微信墙页面的上方，如果运行微信墙的分辨率不高请尽量限制少于75个字，否则超出的字符可能无法正常显示。</span>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-9">
			<button type="submit" class="btn btn-primary">保存设置</button>
		</div>
	</div>
</form>

{include file='wxwall/admincp/footer.tpl'}