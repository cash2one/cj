<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>文件评论</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
    <link rel="stylesheet" href="{$static_path}/css/ionicons.min.css">
    <link rel="stylesheet" href="{$static_path}/css/index.css">
    <script src="{$static_path}/js/main.js"></script>
    <script src="{$static_path}/js/jquery.min.js"></script>
    <script type="text/javascript" src="{$static_path}/js/jquery.more.js"></script>
    <script type="text/javascript">
        $(function(){
            $('#more').more({ 'address': '{$listurl}'})
        });
    </script>
</head>
<body>
            <div id="more" class="warp1 list-wrap">
            <ul class="single_item list-ul">
                <li><section class="list-item">
                        <a href="#">
                            <div class="list-left">
                                <img  class="member_face list-avatar" />
                            </div>
                            <div class="list-right"><div>
                                    <span class="member_username comment-name"></span>
                                    <span class="comment_created comment-timestamp b-fr"></span>
                                </div>
                                <div class="cc-wrap">
                                    <span class="comment_content comment-content"></span>
                                </div>
                            </div>
                        </a>
                    </section>
                </li>
            </ul>
                <a href="javascript:;" class="get_more">::点击加载更多内容::</a>
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

</script>