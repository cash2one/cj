<div class="mod_photo_uploader readonly">
	{foreach $attachs as $att}
	<a href="javascript:void(0);" class="photo"><img src="{voa_h_attach::attachment_url($att['at_id'], 45)}?ts={$timestamp}&sig={voa_h_attach::attach_sig_create($att['at_id'])}" data-big="{voa_h_attach::attachment_url($att['at_id'], 640)}" /></a>
	{/foreach}
</div>
