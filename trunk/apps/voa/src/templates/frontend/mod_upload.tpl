{if !empty($use_wxjsapi)}
	{include file='frontend/h5mod/image_uploader.tpl' iptname='at_ids' iptvalue=$at_ids attachs=$attachs}
{else}
<div class="mod_photo_uploader" data-num-finished="{$attach_total}">
	{if !empty($attachs)}
	<label class="mod_photo_uploader readonly">
	{foreach $attachs as $att}
	<a href="javascript:void(0)" class="photo" data-result="{ldelim}&quot;id&quot;:{$att['at_id']}{rdelim}">
		<img src="{voa_h_attach::attachment_url($att['at_id'], 30)}?ts={$timestamp}&sig={voa_h_attach::attach_sig_create($att['at_id'])}" data-big="{voa_h_attach::attachment_url($att['at_id'], 640)}" />
		<i class="rm" rel="/frontend/attachment/delete/id/{$att['at_id']}">-</i>
	</a>
	{/foreach}
	</label>
	{/if}
	<!--已上传图片的id字段会放入这里-->
	<input type="hidden" id="{$iptname}" name="{$iptname}" value="{$iptvalue}" />
	<a href="javascript:void(0)" class="add" id="mpuAdd">+</a>
</div>
<script>
{if !empty($attachs)}
{literal}
require(['dialog'], function() {
	window.removeReadonlyPhotoCallback = function(link, rm) {
		MDialog.confirm(
			'删除图片',
			'您确定删除该图片吗？',
			null,
			'取消', null, null,
			'确定', function() {
				MLoading.show('稍等片刻...');
				$ajax(
					rm.getAttribute('rel'), 'POST', /** [ajax] url & method */
					{/** [ajax] params */},
					function(ajaxResult) { /** [ajax] callback */
						if (0 < ajaxResult.id) {
							var div = link.parentNode.parentNode;
							link.parentNode.removeChild(link);
							var d = parseInt($data(div, "numFinished"));
							$data(div, "numFinished", d - 1);
						}

						MLoading.hide();
					},
					true /** [ajax] use json */
				);
			}
		);
	};
});
{/literal}
{/if}

{literal}
require(['dialog', 'business', 'h5uploader', 'imageCompresser'], function() {
	configPhotoUpload({
		upload: {
			url: '/attachment/upload',
			nummax: 5 //一次最多传几张
		},
		remove: {
			url: '/frontend/attachment/delete',
			method: 'post'
		}
	});
});
{/literal}
</script>
{/if}