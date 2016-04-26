{include file='frontend/header.tpl'}
<style>
    .sure {
        padding: 90px 0px;
    }

    .sure .o_suc {
        height: 60px;
        line-height: 60px;
        text-align: center;
        font-size: 30px;
        color: #2a98da;
    }

    .sure .o_pro {
        height: 50px;
        line-height: 50px;
        text-align: center;
        font-size: 20px;
        color: #2a98da;
    }

    .sure a.combtn {
        line-height: 30px;
        padding: 5px 15px;
        border-radius: 20px;
        color: #fff !important;
        font-family: "微软雅黑" !important;
    }

    .sure a.combtn_blue {
        background: #01b6e5;
    }

    .sure a.combtn_yellow {
        background: #fe932b;
    }

    .sure .o_pro a {
        margin-left: 20px;
        color: inherit;
        font-family: georgia;
    }

</style>
<div class="sure">
    <div class="o_suc">是否确认登录</div>
    <div class="o_pro">
        <input type="hidden" id="code" name="code" value="{$code}">
        <a class="combtn combtn_blue" href="/frontend/xdf/qrcodelogin?sure=1&scode={$code}">确认</a>
        <a class="combtn combtn_yellow" onclick="javascript:doClose()">取消</a>
    </div>
    <div class="o_ser"></div>
    <div class="tips"></div>
</div>
<script>
    function doClose() {
        var userAgent = navigator.userAgent;
        if (userAgent.indexOf("Firefox") != -1 || userAgent.indexOf("Presto") != -1) {
            window.location.replace("about:blank");
        } else {
            window.open('about:blank', '_self');
            window.close();
        }
    }
</script>
{include file='frontend/footer.tpl'}