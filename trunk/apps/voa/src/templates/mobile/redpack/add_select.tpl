{include file='mobile/header.tpl' navtitle='红包' css_file='app_redpack.css'}

<div class="red-top"></div>
<div class="red-home-body">
    <div class="ui-btn-wrap">
        <button data-id="1" class="ui-btn-lg ui-btn-yellow btn_href">随机红包</button>
    </div>

    <div class="ui-btn-wrap">
        <button data-id="2" class="ui-btn-lg ui-btn-white btn_href">均分红包</button>
    </div>

    <div class="ui-btn-wrap">
        <button data-id="3" class="ui-btn-lg ui-btn-white btn_href">定点红包</button>
    </div>
</div>
<div class="red-bottom"></div>

{literal}
<script type="text/javascript">
require(["zepto", "underscore", "frozen"], function($, _, fz) {
    $(".btn_href").on('click', function(e) {
      location.href = '/frontend/redpack/new?ac=new&type=' + $(this).data("id");
    });

    $('.btn_href').on('mouseout', function(e) {
        $(this).removeClass('ui-btn-yellow');
        $(this).addClass('ui-btn-white');
    });

    $('.btn_href').on('mouseover', function(e) {
        $(this).removeClass('ui-btn-white');
        $(this).addClass('ui-btn-yellow');
    });
});
</script>
{/literal}

{include file='mobile/footer.tpl'}