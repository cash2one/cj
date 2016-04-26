<?php
/**
 * score.php
 * 积分菜单设置
 * Create By v429
 * $Author$
 * $Id$
 */

// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
    'awardexchange' => array(
        'icon' => 'fa-list', 'name' => '奖品兑换记录', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 0,
    ),
    'memberlist' => array(
        'icon' => 'fa-plus', 'name' => '成员列表', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 1,
    ),
    'setup' => array(
        'icon' => 'fa-gear', 'name' => '积分设置', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 103, 'default' => 0,
    ),
    'changelog' => array(
        'icon' => 'fa-plus', 'name' => '积分调整记录', 'display' => 0,
        'subnavdisplay' => 1, 'displayorder' => 104, 'default' => 0,
    ),
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
    array(
        'type' => 'view', 'name' => '我的积分',
        'url' => '{domain_url}/Score/Frontend/Index/scoreLogList',
    ),
    array(
        'type' => 'view', 'name' => '积分兑换',
        'url' => '{domain_url}/Score/Frontend/Index/awardList',
    )
);