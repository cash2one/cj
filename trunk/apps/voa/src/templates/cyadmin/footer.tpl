{literal}
<script type="text/javascript">
jQuery(function(){
	jQuery('._delete').click(function(){
		if (confirm('是否确认删除该数据？删除后不可恢复。')) {
			return true;
		} else {
			return false;
		}
	});
	//operationWindowSize();
	jQuery(window).resize(function(){
		//operationWindowSize();
	});
	jQuery('#myModal').on('hidden.bs.modal', function (){
		jQuery(this).removeData('bs.modal');
	});
});
</script>
{/literal}
{if !$ajax}
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="{$static_url}js/upload.js"></script>
<div id="myModal" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Event</h4>
			</div>
			<div class="modal-body">
				<p>Loading...</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="submit" class="btn btn-primary">提交</button>
			</div>
		</div>
	</div>
</div>
</body>
</html>
{/if}