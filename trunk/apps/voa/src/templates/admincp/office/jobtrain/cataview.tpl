{include file="$tpl_dir_base/header.tpl"}
<div class="panel panel-default font12">
	<div class="panel-body">
			
		<div class="form-group">
			<label class="control-label col-sm-2 text-right">上级分类：</label>
			<div class="col-sm-10"><p class="form-control-static">{if $parent['title']}{$parent['title']}{else}无{/if}</p></div>
		</div>

		<div class="form-group">
			<label class="control-label col-sm-2 text-right">标题：</label>
			<div class="col-sm-10">
				<p class="form-control-static">{$result.title}</p>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-sm-2 text-right">排序号：</label>
			<div class="col-sm-10">
				<p class="form-control-static">{$result.orderid}</p>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-sm-2 text-right">适用范围：</label>
			<div class="col-sm-10">
				{if $result['is_all'] == 1}
				<p class="form-control-static">全公司</p>
				{else}
					{if $result['departments']}
					<pre style="font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;">{foreach $result['departments'] as $_k => $_v}{$_v['cd_name']}&nbsp;&nbsp;{/foreach}</pre>
					{/if}
					{if $result['members']}
					<pre style="margin-top: 10px; font-size: 12px; letter-spacing: 1px; background-color: #FAFAFA;">{foreach $result['members'] as $_k => $_v}{$_v['m_username']}&nbsp;&nbsp;{/foreach}</pre>
					{/if}
				{/if}

			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-sm-2 text-right">启用分类：</label>

			<div class="col-sm-10">
				<p class="form-control-static">{if $result['is_open'] == 1}是{else}否{/if}</p>
			</div>

		</div>

		<div class="form-group" id="btn-box">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="button" onclick="javascript:history.go(-1);" class="btn btn-default">返回</button>
			</div>
		</div>

	</div>
</div>
{include file="$tpl_dir_base/footer.tpl"}