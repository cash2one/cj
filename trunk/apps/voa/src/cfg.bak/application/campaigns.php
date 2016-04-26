<?php

/**
 * campaign.php
 * 活动推广菜单设置
 * Create By linshiling
 * $Author$
 * $Id$
 */
// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
    'add' => array(
        'icon' => 'fa-plus', 'name' => '新增活动', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 0,
    ),
    'list' => array(
        'icon' => 'fa-list', 'name' => '活动列表', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 1,
    ),
    'total' => array(
        'icon' => 'fa-bar-chart-o', 'name' => '数据中心', 'display' => 0,
        'subnavdisplay' => 1, 'displayorder' => 103, 'default' => 0,
    ),
    'setting' => array(
        'icon' => 'fa-gear', 'name' => '分类设置', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 104, 'default' => 0,
    )
);

// 微信企业号自定义菜单
$conf['menu.qywx'] = array(
    array(
        'type' => 'view', 'name' => '活动中心',
        'url' => '{domain_url}/Campaigns/Frontend/Index/docCenter',
    ),
    array(
        'type' => 'view', 'name' => '数据跟踪',
        'url' => '{domain_url}/Campaigns/Frontend/Index/dataTrack',
    ),
);