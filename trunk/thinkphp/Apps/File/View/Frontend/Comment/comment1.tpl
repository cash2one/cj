<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>文件评论</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
    <link rel="stylesheet" href="{$static_path}/css/ionicons.min.css">
    <link rel="stylesheet" href="{$static_path}/css/index.css">
    <script src="{$static_path}/js/main.js"></script>
    <script src="{$static_path}/js/jquery.min.js"></script>
</head>
<body>
<div class="warp1">
    <div class="list-wrap" id="gb_list">

    </div>
</div>

<div class="footer depthbg">
    <form name="add_gb" id="add_gb" action="{$acurl}" method="post">
        <div class="comment-input-wrap">
            <input type="hidden" id="file_id" name="file_id" value="{$file_id}">
            <input type="text" placeholder="输入评论内容" name="content" id="content"/>
        </div>
        <input type="submit" value="发送" name="gb_sbt" id="gb_sbt" class="btn btn-primary" style="width: 20%"/>
    </form>
</div>
</body>
</html>

<script>
    // 监听提交按钮 click 事件
    $("#gb_sbt").on('click', function(e) {

        var frm = $("#add_gb");
        // ajax 提交
        $.post(frm.attr('action'), frm.serialize(), function(data) {

            // 评论成功
            if (0 == data.errcode) {
                $("#content").val('');
                alert('评论成功');
                location.reload();
            } else { // 评论失败
                alert(data.errmsg);
            }
        });

        return false;
    });

    // 读取列表
    $.get('{$listurl}',function(data) {
        // 如果出错了
        if (0 < data.errcode) {
            alert(data.errmsg);
            return false;
        }
        var trs = '';
        var list = data['result']['data'];
        for (var k in list) {
            Date.prototype.pattern=function(fmt) {
                var o = {
                    "M+" : this.getMonth()+1, //月份
                    "d+" : this.getDate(), //日
                    "h+" : this.getHours() == 0 ? 12 : this.getHours(), //小时
                    "H+" : this.getHours(), //小时
                    "m+" : this.getMinutes(), //分
                    "s+" : this.getSeconds(), //秒
                    "q+" : Math.floor((this.getMonth()+3)/3), //季度
                    "S" : this.getMilliseconds() //毫秒
                };

                for(var k in o){
                    if(new RegExp("("+ k +")").test(fmt)){
                        fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
                    }
                }
                return fmt;
            }
            function  timetodate(tim,dat){
                return new Date(parseInt(tim)*1000).pattern(dat);   //"yyyy/MM/dd,hh,mm,ss"
            }
            var tim1 = list[k].comment_created;
            var time = timetodate(tim1,"hh:mm:ss");
            trs += '<ul class="list-ul"><li><section class="list-item"><a href="#"><div class="list-left"><img  class="list-avatar" src='+list[k].member_face+'></div><div class="list-right"><div><span class="comment-name">'+list[k].member_username+'</span><span class="comment-timestamp b-fr">'+time+'</span></div><div class="cc-wrap"><span class="comment-content">'+list[k].comment_content+'</span></div></div></a></section></li></ul>';
        }

        if ('' == trs) {
            alert('还没有任何评论');
        } else {
            $('#gb_list').append(trs);
        }

        return true;
    });
</script>