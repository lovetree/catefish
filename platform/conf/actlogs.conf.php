<?php

/**
 * 记录哪些操作需要记录日志
 */
return array(
    'index/user/login'          => ['管理员登入'],
    
    'index/user/logout'         => ['管理员登出'],
    
    'player/user/del'           => ['删除用户', 
                                    function(&$desc, $args){
                                        return $args['userid'];
                                    }],
                                            
    'player/user/freeze'        => ['冻结/解冻用户', 
                                    function(&$desc, $args){
                                        if($args['status'] == 0){
                                            $desc = '解冻用户';
                                        }else if($args['status'] == 1){
                                            $desc = '冻结用户';
                                        }
                                        return is_array($args['userid']) ? implode(',', $args['userid']) : $args['userid'];
                                    }],
                                            
    'admin/user/active'         => ['设置管理员状态'],
            
    'admin/user/save'           => ['新建/修改管理员数据'],
            
    'admin/user/role'           => ['设置管理员角色'],
            
    'admin/role/save'           => ['新建/保存角色信息'],
            
    'admin/role/setperms'       => ['设置角色权限'],
            
    'system/message/save'       => ['新建/修改系统消息'],
            
    'system/message/active'     => ['设置系统消息的状态'],
            
    'admin/role/active'         => ['设置角色状态'],
            
    'system/message/delete'     => ['删除系统消息'],
            
    'admin/role/delete'         => ['删除角色'],
            
    'admin/user/delete'         => ['删除管理员'],
            
    'player/user/save_baseinfo' => ['修改用户数据', 
                                    function(&$desc, $args){
                                        return $args['userid'];
                                    }],
            
    'system/item/save'          => ['保存道具', 
                                    function(&$desc, $args){
                                        if(!isset($args['item_id'])){
                                            $desc = '新建道具';
                                        }else {
                                            $desc = '修改道具';
                                        }
                                        return false;
                                    }],
                                            
    'system/item/active'        => ['修改道具状态', 
                                    function(&$desc, $args){
                                        if($args['status'] == 0){
                                            $desc = '禁用道具';
                                        }else if($args['status'] == 1){
                                            $desc = '启用道具';
                                        }
                                        return false;
                                    }],
                                            
    'system/item/delete'        => ['删除道具'],
                                            
    'system/goods/save'         => ['保存商品数据', 
                                    function(&$desc, $args){
                                        if(isset($args['goods_id'])){
                                            $desc = '新建商品';
                                        }else {
                                            $desc = '修改商品';
                                        }
                                        return false;
                                    }],
                                            
    'system/goods/active'       => ['修改商品状态', 
                                    function(&$desc, $args){
                                        if($args['status'] == 0){
                                            $desc = '禁用商品';
                                        }else if($args['status'] == 1){
                                            $desc = '启用商品';
                                        }
                                        return false;
                                    }],
                                            
    'system/goods/delete'       => ['删除商品'],
    'player/user/ajaxinfo'       => ['修改用户信息'],
    'gamemt/room/setstock'      =>['修改库存'],
    'gamemt/room/setstocks'      =>['修改调节池'],
    'player/user/savepwd'      =>['修改用户密码'],
    'player/user/savesafe'      =>['修改保险箱密码'],
    'player/user/savestock'      =>['冻结用户'],
    'player/user/saveestate'      =>['修改用户资金'],
    'player/popularity/setpopu'      =>['修改用户人气值'],
);
