
<table class="table table-striped table-bordered table-hover font12">
	<colgroup>
		<col class="t-col-25" />
		<col class="t-col-75" />
	</colgroup>
	<thead>
		<tr>
			<th>日报类型</th>
			<th>是否启用</th>
		</tr>
	</thead>
	<tbody>
{foreach $dailyType as $_k=>$_opt name=option}
	<tr>
		<td>
			<input type="hidden" name="daily_type[{$_k}][0]" value="{$_opt[0]}" class="form-control" />
			{$_opt[0]}
		</td>
		<td>
			<label class="radio-inline"><input type="radio" name="daily_type[{$_k}][1]" value="1" {if $_opt[1] == 1}checked="checked"{/if}> 开启</label>
			<label class="radio-inline"><input type="radio" name="daily_type[{$_k}][1]" value="0" {if $_opt[1] == 0}checked="checked"{/if}> 关闭</label>
		</td>
	</tr>
{/foreach}
	</tbody>
</table>

<!-- 
<ul>
{foreach $dailyType as $_k=>$_opt name=option}
<li>
   <input type="text" name="daily_type[{$_k}][0]" value="{$_opt[0]}" />
   <input type="radio" name="daily_type[{$_k}][1]" value="1" {if $_opt[1] == 1}checked="checked"{/if}>开启
   <input type="radio" name="daily_type[{$_k}][1]" value="0" {if $_opt[1] == 0}checked="checked"{/if}>关闭
</li>
{/foreach}
</ul> -->
