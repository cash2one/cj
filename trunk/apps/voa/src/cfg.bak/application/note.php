<?php

/**
 * jobtrain.php
 * 培训菜单设置
 * Create By wowxavi
 * $Author$
 * $Id$
 */
// 前台菜单配置
$conf['menu.frontend'] = array();

// 后台菜单配置
$conf['menu.admincp'] = array(
    'list' => array(
        'icon' => 'fa-list', 'name' => '课堂笔记', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 101, 'default' => 1,
    ),
    'add' => array(
        'icon' => 'fa-plus', 'name' => '添加笔记', 'display' => 1,
        'subnavdisplay' => 1, 'displayorder' => 102, 'default' => 0,
    ),
);

//微信企业号自定义菜单
$conf['menu.qywx'] = array(
    array(
        'type' => 'view', 'name' => '创建笔记',
        'url' => '{domain_url}/Note/Frontend/Index/addNote',
    ),
    array(
        'type' => 'view', 'name' => '我的笔记',
        'url' => '{domain_url}/Note/Frontend/Index/myNote',
    ),
    array(
        'type' => 'view', 'name' => '查看笔记',
        'url' => '{domain_url}/Note/Frontend/Index/viewNote',
    )
);
