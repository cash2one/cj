					<table class="table table-striped table-hover font12">
						<colgroup>
							<col />
							<col class="t-col-20" />
							<col class="t-col-20" />
							<col class="t-col-20" />
						</colgroup>
						<thead>
							<tr>
								<th>文件名称</th>
								<th>文件大小</th>
								<th>上传时间</th>
								<th>浏览</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="4"></td>
							</tr>
						</tfoot>
						<tbody>
{foreach $attach_list as $id => $attach}
							<tr>
								<td>{$attach['at_filename']|escape}</td>
								<td>{$attach['_filesize']}</td>
								<td>{$attach['_created']}</td>
								<td><a href="{$attach['at_attachment']}" target="_blank">查看</a></td>
							</tr>
{foreachelse}
							<tr class="warning">
								<td colspan="4">暂无附件信息</td>
							</tr>
{/foreach}
						</tbody>
					</table>