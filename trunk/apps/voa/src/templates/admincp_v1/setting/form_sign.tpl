{foreach $formCustomFields as $fs}
	{if $fs['id'] == 'work_days'}
			<div class="form-group font12">
				<label class="col-sm-3 control-label text-right">{$fs['title']}</label>
				<div class="col-sm-9">
		{foreach $fs['custom'] as $k => $n}
					<label class="checkbox-inline"><input type="checkbox" name="{$fs['name']}[]" value="{$k}"{if in_array($k, $fs['value'])} checked="checked"{/if} /> {$n}</label>
		{/foreach}
					<p class="help-block">{$fs['comment']}</p>
				</div>
			</div>
	{/if}
{/foreach}