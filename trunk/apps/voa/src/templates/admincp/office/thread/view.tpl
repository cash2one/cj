{include file="$tpl_dir_base/header.tpl"}
<form class="form-inline vcy-from-search" method="post" role="form"
	action="{$deleteUrlBase}">
	<input type="hidden" name="tid" value="{$thread['tid']}" />
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title font12">
				<strong>{$project['p_subject']|escape}</strong>
			</h3>
		</div>
		<div class="panel-body">
			<table class="table table-no-border font12">
				<colgroup>
					<col class="t-col-20" />
					<col />
				</colgroup>
				<tbody>
					<tr>
						<th class="text-right">话题标题：</th>
						<td class="text-left">{$thread['subject']}</td>
					</tr>

					<tr>
						<th class="text-right">话题内容：</th>
						<td class="text-left">
							{$thread['message']}<br/><br/>
							{foreach $img_list as $imgUrl}
								<img src="{$imgUrl}" alt="" style="width: 100px; height: 100px;" />
							{/foreach}

						</td>
					</tr>

					<tr>
						<th class="text-right">发起人：</th>
						<td class="text-left"><strong
							class="label label-primary font12">{$thread['username']|escape}</strong>
							<span class="space"></span> <abbr title="发布时间"><span
								class="badge">{$thread['_created']}</span></abbr></td>
					</tr>

				</tbody>
			</table>
			<ul class="nav nav-tabs font12">
				<li class="active"><a href="#list_proc" data-toggle="tab">
						<span class="badge pull-right">{$postsCount}</span> 评论&nbsp;
				</a></li>
				<li><a href="#list_member" data-toggle="tab"> <span
						class="badge pull-right">{$likesCount}</span> 点赞&nbsp;
				</a></li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="list_proc">
					<table
						class="table table-striped table-hover table-bordered font12 table-light">
						<colgroup>
							<col class="t-col-5" />
							<col class="t-col-15" />
							<col class="t-col-15" />
							<col />
							<col class="t-col-15" />
						</colgroup>
						<thead>
							<tr>
								<th class="text-left"><label class="checkbox"><input
										type="checkbox" id="delete-all" class="px"
										onchange="javascript:checkAll(this,'delete');"
										{if !$deleteUrlBase || !$postsCount} disabled="disabled" {/if} /><span
										class="lbl">全选</span></label></th>
								<th>评论人</th>
								<th>评论时间</th>
								<th>评论内容</th>
								<th>操作</th>
							</tr>
						</thead>
						{if $postsCount > 0}
						<tfoot>
							<tr>
								<td colspan="2">{if $deleteUrlBase}
									<button type="submit" class="btn btn-danger">批量删除</button>{/if}
								</td>
								<td colspan="6" class="text-right vcy-page">{$posts_multi}</td>
							</tr>
						</tfoot>
						{/if}
						<tbody>
							{foreach $posts as $_id => $_data}
							<tr>
								<td class="text-left"><label class="px-single"><input
										type="checkbox" name="delete[{$_id}]" class="px"
										value="{$_id}" {if !$deleteUrlBase} disabled="disabled" {/if} /><span
										class="lbl"> </span></label></td>
								<td>{$_data['username']|escape}</td>
								<td>{$_data['_created']}</td>
								<td>{$_data['_message']}</td>
								<td>{$base->linkShow($deleteUrlBase, $_id, '删除',
									'fa-times', 'class="text-danger _delete"')}</td>
							</tr>
							{foreachelse}
							<tr class="warning">
								<td colspan="5">暂无评论记录</td>
							</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				<div class="tab-pane" id="list_member">
					<table
						class="table table-striped table-hover table-bordered font12 table-light">
						<colgroup>
							<col class="t-col-18" />
							<col class="t-col-20" />
							<col />
						</colgroup>
						<thead>
							<tr>
								<th>点赞人</th>
								<th>点赞时间</th>
							</tr>
						</thead>
						{if $likesCount > 0}
						<tfoot>
							<tr>
								<td colspan="4" class="text-right vcy-page">{$likes_multi}</td>
							</tr>
						</tfoot>
						{/if}
						<tbody>
							{foreach $likes as $_id => $_data}
							<tr>
								<td>{$_data['username']|escape}</td>
								<td>{$_data['_created']}</td>
							</tr>
							{foreachelse}
							<tr class="warning">
								<td colspan="4">暂无点赞记录</td>
							</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				<a href="javascript:history.go(-1);" class="btn btn-default">返回</a>

			</div>
		</div>
	</div>
</form>
{include file="$tpl_dir_base/footer.tpl"}
