<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
<title>微信安全支付</title>

<style>
h1 { font-size: 60px; }
body { font-size: 40px; }
button {
	width:80%;height: 72px;margin: 24px 0; padding: 6px 12px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;
	font-size: 40px;
}
input { width: 80%; margin: 6px 0; height:60px; font-size: 40px; }
</style>
<script type="text/javascript" src="/static/mobile/lib/jquery/jquery.min.js"></script>
<script type="text/javascript">
var addressParams = {$addressParams};
{literal}
function get_addres() { 
    for (k in addressParams) {
            $('#debug').append('<br/>'+k+':'+addressParams[k]);
    }
    WeixinJSBridge.invoke(
        'editAddress', addressParams,
        function(res){
            $('#debug').append('<br/>地址接口返回:<br/>');
            for(k in res) {
                $('#debug').append(k + ':' + res[k] + '<br/>');
            }
            if(res.err_msg == 'edit_address:ok') {
                document.getElementById('name').value = res.userName;
                document.getElementById('phone').value = res.telNumber;
                document.getElementById('adr').value = res.addressCitySecondStageName +" "+res.addressCountiesThirdStageName+" "+res.addressDetailInfo;;
            }
        }
    );
}
        
//调用微信JS api 支付
function callpay() {
    //获取
    var post = {
    	'goods_id':180, 'styleid':1, 'num':1, 'phone':'13588119714', 'name':'zhuxun37', 'adr':'shanghai', 'sale_id':1
    };
    $('#pay').attr('disabled', true).html('请求支付中...');
    $.post('/api/order/post/pay', post, function (res){
        $('#pay').attr('disabled', false).html('开始支付');
        if(typeof res == 'object') {
            $('#debug').append('<br/><br/>准备发起请求<br/>');
            pay_params = res.result.pay_params;
            for(k in pay_params) {
                $('#debug').append(k + ':' + pay_params[k] + '<br/>');
            }
            WeixinJSBridge.invoke(
                'getBrandWCPayRequest',
                pay_params,
                function(res){
                    WeixinJSBridge.log(res.err_msg);
                    $('#debug').append('<br/>回調信息:<br/>');
                    if(res.err_msg == 'get_brand_wcpay_request:ok') {
                        $('#debug').append('支付成功');
                        location.href = 'order_list.php';
                    }else if(res.err_msg == 'get_brand_wcpay_request:cancel') {
                        $('#debug').append('支付已取消');
                    }else{
                        $('#debug').append(res.err_msg);
                    }
                }
            );
        }else{
            $('#debug').append('<br/>获取支付参数错误');
        }
    }, 'json');
}
{/literal}
</script>
</head>
<body>
       <h1><a href="order_list.php">返回订单列表</a></h1>
       <h1>{$goods_name}(goods_id:{$goods_id})</h1>
       <form action="javascript:;" method="post" id="form1" name="form1" style="display:block">
               <input type="hidden" name="total_fee" value="{$total_fee}"/>
               <input type="hidden" name="goods_id" value="{$goods_id}"/>
               <input type="hidden" name="goods_name" value="{$goods_name}"/>
               <input type="hidden" name="openid" value="{$token['openid']}"/>
               <input type="hidden" name="access_token" value="{$token['access_token']}"/>
               数量: <input type="text" name="num" id="num" value="1"/><br/>
               姓名: <input type="text" name="name" id="name" /><br/>
               电话: <input type="text" name="phone" id="phone" /><br/>
               地址: <input type="text" name="adr" id="adr" /><br/>
               <button type="button" onclick="get_addres()" >使用微信收货地址</button><br/>
               <button id="pay" type="button" onclick="callpay()" >开始支付</button>
       </form>
       <div id="debug">
               openid: {$token['openid']}<br/>
               url: {$url}<br/>
       </div>
</body>
</html>