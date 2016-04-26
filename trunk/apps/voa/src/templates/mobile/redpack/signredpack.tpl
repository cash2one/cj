{include file='mobile/header.tpl' navtitle='签到红包' css_file='app_redpack.css'}

<div class="red-bg-white">
    <div class="red-top"></div>
        <section class="red-avatar red-receive-one">
        <div class="ui-avatar-one red-avatar-one">
            <span style="background-image:url({$cinstance->avatar($wbs_uid)})"></span>
        </div>
        <p>{$rplog['redpack']['actname']}</p>
        <p><span>{$rplog['redpack']['wishing']}</span></p>
        <h1>{$rplog['_money']}<span>元</span></h1>
    </section>
</div>

{include file='mobile/footer.tpl'}