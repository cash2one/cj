{include file='mobile/header.tpl' }

 <div class="kdzs-detail-title">
        <label>快递领取单</label>
        <span class="ui-icon kdzs-detail-received kdzs-detail-received-bg"></span>
    </div>
    <div class="ui-form kdzs-ui-margin-top kdzs-ui-margin-bottom">
        <div class="ui-form-item">
            <label for="#">快递代收人</label>
            <span class="ui-form-item-unit">{$express['r_username']}</span>
        </div>
        <div class="ui-form-item ui-border-t">
            <label for="#">接收时间</label>
            <span class="ui-form-item-unit">{$express['_created']}</span>
        </div>
        <div class="ui-form-item ui-border-t">
            <label for="#">收件人</label>
            <span class="ui-form-item-unit">{$express['username']}</span>
        </div>
        <div class="ui-form-item ui-border-t">
            <label for="#">领取时间</label>
            <span class="ui-form-item-unit">{$express['_get_time']}</span>
        </div>
        {if !empty($attachs)}
        <div class="ui-form-item ui-border-t">
            <label for="#">快递信息</label>
        </div>
        <div class="upload clearfix">
            <div class="ui-badge-wrap">
                  {foreach $attachs as $ach}
                 <img alt="" src="/attachment/read/{$ach.aid}">
             {/foreach}
            </div>
        </div>
        {/if}
    </div>
    <div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0 kdzs-ui-margin-bottom">
        <button id="cancle" class="ui-btn-lg">返回列表</button>
    </div>


<script>
{literal}

require(["zepto", "underscore", "frozen"], function($, _, fz) {
	$(document).ready(function() {

		//取消
		$('#cancle').on('click', function(e) {
			 var url = "/frontend/express/list";
	 	     window.location.href=url;  
		});
		
	});
});

{/literal}
</script>

{include file='mobile/footer.tpl'}