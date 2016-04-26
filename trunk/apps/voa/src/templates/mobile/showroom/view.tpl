{include file='mobile/header.tpl'}

<div class="ui-top-content">
    <h2>{$article['title']}</h2>
    <p class="other">
        <span class="time">发布时间：{$article['updated']}</span>
        <span class="time">&nbsp;&nbsp;作者：{$article['author']}</span>
    </p>
</div>

<div class="ui-main-content ui-list">{$article['content']}</div>
<div class="ui-btn-wrap">       
    <button class="ui-btn-lg ui-btn-primary" onclick="location.href='/frontend/showroom/articles/?tc_id={$article["tc_id"]}'">返回列表</button>
</div>
{include file='mobile/footer.tpl'}