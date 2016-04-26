<div class="panel panel-default font12">
	<div class="panel-body">
		<div class="profile-row">
			<div class="right-col">
				<div class="panel tl-body form-horizontal" >
					<div class="form-group font12" style="margin-left:20px">
						<label for="dateformat" class="col-sm-1">文章标题：</label>
						<div class="col-sm-11">
							<b>{$view['title']}</b>
						</div>
					</div>
					<div class="form-group font12" style="margin-left:20px">
						<label for="dateformat" class="col-sm-1">摘要：</label>
						<div class="col-sm-11">
							<b>{$view['description']}</b>
						</div>
					</div>
					<div class="form-group font12" style="margin-left:20px">
						<label class="col-sm-1" for="id_author">封面图片：</label>
						<div class="col-sm-11">
							{if $view['url']}
							<a target="_blank" href="{$view['url']}"><img src="{$view['url']}" alt="" width="400"></a>
							{/if}
						</div>

					</div>
					<div class="form-group font12" style="margin-left:20px">
						<label for="dateformat" class="col-sm-1 ">来源：</label>
						<div class="col-sm-11">
							<b>{$view['source']}</b>
						</div>
					</div>
					<div class="form-group font12" style="margin-left:20px">
						<label for="dateformat" class="col-sm-1">来源链接：</label>
						<div class="col-sm-11">
							<b>{$view['sourl']}</b>
						</div>
					</div>

					<div class="form-group font12" style="margin-left:20px">
						<label class="col-sm-1" >排序：</label>
						<div class="col-sm-11">
							{$view['tsort']}
						</div>
					</div>
					<div class="form-group font12" style="margin-left:20px">
						<label class="col-sm-1" >开始时间：</label>
						<div class="col-sm-11">
							{$view['start_time']}
						</div>
					</div>
					<div class="form-group font12" style="margin-left:20px">
						<label class="col-sm-1" >结束时间：</label>
						<div class="col-sm-11">
							{$view['end_time']}
						</div>
					</div>
					<div class="form-group font12" style="margin-left:20px">
						<label class="col-sm-1" >地址：</label>
						<div class="col-sm-11">
							{$view['address']}
						</div>
					</div>
					<div class="form-group font12" style="margin-left:20px">
						<label class="col-sm-1" >嘉宾：</label>
						<div class="col-sm-11">
							{$view['guests']}
						</div>
					</div>
					<div class="form-group font12" style="margin-left:20px">
						<label class="col-sm-1" >报名信息：</label>
						<div class="col-sm-11">
							<ul class="train-item-ul">
								{foreach $view['sign_fields_info'] as $val}
								<li class="train-item-ul__li train-item-ul__cur train-item-ul__link" >{$val}</li>
								{/foreach}
							</ul>

						</div>
					</div>
					<div class="form-group font12" style="margin-left:20px">
						<label class="col-sm-1" >正文：</label>
						<div class="col-sm-11">
							{$view['content']}
						</div>
					</div>
					<div class="form-group font12" style="margin-left:20px">
						<label class="col-sm-1" >标签：</label>
						<div class="col-sm-11">
							{if $view['tags']}
							<ul class="train-item-ul">
								{foreach $view['tags'] as $val}
								<li class="train-item-ul__li train-item-ul__cur train-item-ul__link" >{$val}</li>
								{/foreach}
							</ul>

							{/if}
						</div>

					</div>
					<div class="form-group font12" style="margin-left:20px">
						<label class="col-sm-1" >阅读数：</label>
						<div class="col-sm-11">
							{$view['read_num']}
						</div>

					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-9">
							<div class="row">
								<div class="col-md-4"><a href="javascript:history.go(-1);" class="btn btn-default col-md-9">返回</a></div>
								<div class="col-md-4"><a href="/content/train/edit/?tid={$view['tid']}" class="btn btn-default btn-primary col-md-9">编辑</a></div>
								<div class="col-md-4"><a href="/content/train/delete/?tid={$view['tid']}" class="btn btn-default btn-primary col-md-9">删除</a></div>
							</div>
						</div>
					</div>
					<div class="table-light">
						<div class="table-header">
							<div class="table-caption font12">
								<span class="btn btn-success" id="outer"><a href="#export" style="color:#fff">报名人员</a></span>
								{if $total>0}
								<span class="btn btn-success pull-right" id="btn_export">导出</span>
								{/if}
							</div>
						</div>
						<span class="space"></span>
						<table class="table table-striped table-hover table-bordered font12">
							<colgroup>
							<col class="t-col-10" />
							<col class="t-col-10" />
							<col class="t-col-18" />
							<col class="t-col-17" />
						</colgroup>
						<thead>
							<tr>
								<th>姓名</th>
								<th>手机</th>
								<th>报名时间</th>
								<th>报名IP</th>
							</tr>
						</thead>
						{if $total > 0}
						<tfoot>
							<tr>

								<td colspan="4" class="text-right vcy-page">{$multi}</td>
							</tr>
						</tfoot>
						{/if}
						<tbody>
							{if $train_sign}
							{foreach $train_sign as $_id => $_data}
							<tr>
								<td>{$_data['signname']|escape}</td>
								<td align="center">{$_data['signphone']}</td>
								<td align="center">{$_data['time']}</td>
								<td align="center">{$_data['signip']|escape}</td>
							</tr>
							{/foreach}
							{else}
							<tr>
								<td colspan="4" class="warning">暂无报名人员！</td>
							</tr>
							{/if}
						</tbody>
					</table>
				</div>

			</div>
		</div>
	</div>
</div>
</div>

<script type="text/javascript">
	$(function(){
		$('#btn_export').on('click', function(){
			var href = $('#outer a').attr('href').replace('#', '');
			window.location.href = window.location.href + '&export=' + href;

			return false;
		});
	});
</script>