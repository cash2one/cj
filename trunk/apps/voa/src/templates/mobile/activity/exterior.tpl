{include file='mobile/header.tpl' css_file='app_activity.css' navtitle='外部人员'}


<ul class="ui-tab-nav ui-border-b">
    <li id="interior">内部人员</li>
    <li class="current">外部人员</li>
</ul>
<ul class="ui-tab-content">
    <li>
        <ul class="ui-list ui-list-text">
            {if $data != false}
            {foreach $data as $k => $v}
            <li id="{$k}" class="ui-border-t ui-form-item-link activity-item-link tag">
                <div class="ui-avatar-s">
                    <span style="background-image:url()"></span>
                </div>
                <div class="ui-list-info">
                    <h4 >{$v['outname']}</h4>
                </div>
                <div class="ui-list-right activity-people-list">
                    <h4 class="ui-nowrap" style="margin-top: 0;">{$v['created']}</h4>
                </div>
            </li>
            <li id="remark{$k}" style="display: none;height: auto;" class="ui-list-info">
                <div class="activity-outsidepeople-list">
                    <div class="ui-form-item" style="height: auto;">
                        <label for="#">手机号</label>
                        <p>{$v['outphone']}</p>
                    </div>
                    <div class="ui-form-item" style="height: auto;">
                        <label for="#">备注</label>
                        <p>{$v['remark']}</p>
                    </div>
                    {if $v['other'] != ''}
                        {foreach $v['other'] as $key => $val}
                            <div class="ui-form-item" style="height: auto;">
                                <label for="#">{$key}</label>
                                <p>{$val}</p>
                            </div>
                        {/foreach}
                    {/if}
                </div>
            </li>
            {/foreach}
            {else}
                <li class="ui-border-t ui-form-item-link activity-item-link">
                    <div class="ui-avatar-s">
                        <span style="background-image:url()"></span>
                    </div>
                    <div class="ui-list-info">
                        <h4 >暂时无人</h4>
                    </div>
                    <div class="ui-list-right activity-people-list">
                        <h4 class="ui-nowrap"></h4>
                    </div>
                </li>
            {/if}
        </ul>
    </li>
</ul>
<input id="acid" type="hidden" value="{$acid}">

{literal}
    <script type="text/javascript">
        require(["zepto"], function($) {
            //显示详情
            $('.tag').on('click', function () {
                $(this).toggleClass('active');
                var id = $(this).attr('id');
                $('#remark'+id).toggle();
            });
            $('#interior').on('click', function () {
                window.location.href = '/frontend/activity/view?ac=interior&acid=' + $('#acid').val();
            });
        });
    </script>
{/literal}

{include file='mobile/footer.tpl'}