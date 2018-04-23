<?php

class UserModel extends BaseModel {
    /**
     * 验证短信验证码
     */
    public function checkCode($phone,$code){
        $select = $this->DB()->newSelect('ms_sms',array(
            'status'=>1,
            'is_used'=>0,
            'type'=>4,
            'phone'=>$phone
        ))
            ->select('code')
            ->select('c_time')
            ->select('expire')
            ->order('id','desc');

        $check =$this->DB()->fetch($select);

        if(!$check){
            return array('result'=>500,'msg'=>'无效验证码');
        }
        $check =$check->getData();
        if($check['expire']+$check['c_time']<time()){
            return array('result'=>500,'msg'=>'验证码过期');
        }
        if($check['code']!=$code){
            return array('result'=>500,'msg'=>'验证码错误');
        }
        $up = 'update ms_sms set is_used = 1 where id = '.$check['id'];
        $this->DB()->exec($up);
        return array('result'=>200);
    }
    /**
     * 获取用户信息
     */
    public function getphone($user)
    {
        $select = $this->DB()->newSelect('ms_admin',array(
            'main_table.username'=>$user
        ))
        ->select('main_table.phone');

        $phone = $this->DB()->fetch($select)->getData('phone');
        return $phone;
    }

    /**
     * 用户登录
     * @param type $user
     * @param type $pass
     * @return bool
     */
    public function login(string $user, string $pass): bool {
        if ($this->isLogin()) {
            return true;
        }
        $select = $this->DB()->newSelect('ms_admin', array(
                    'main_table.username' => $user,
                    'main_table.password' => md5($pass),
                    'main_table.status' => array('!=' => 0),
                ))
                ->addColumn(array('main_table.username', 'main_table.status', 'ar.status as role_status'))
                ->select('group_concat(ar.name) as role', true)
                ->joinLeft('ms_admin_map_user_role as amur', 'amur.admin_id = main_table.id')
                ->joinLeft('ms_admin_role as ar', 'ar.id = amur.role_id and ar.status != 0')
                ->group('main_table.id')
                ->limit(1);
        if (!($admin = $this->DB()->fetch($select))) {
            return false;
        }
        $info = $admin->getData();
        $_SESSION[SES_LOGIN] = $info;
        return true;
    }

    /**
     * 加载用户权限数据
     */
    public function getPermissions() {
        if (isset($_SESSION[SES_PERMISSION])) {
            return $_SESSION[SES_PERMISSION];
        } else {
            $user_data = $_SESSION[SES_LOGIN];
            $user_id = $user_data['id'];
            $perm = new PermissionModel();
            $list = $perm->allByUser($user_id);
            $_SESSION[SES_PERMISSION] = $list;
            return $list;
        }
        return array();
    }

    /**
     * 检测权限
     * @return boolean
     */
    public function checkPermission(): bool {
        if ($this->isAdmin()) {
            return true;
        }
        $permissions = $this->getPermissions();
        $request = Yaf\Application::app()->getDispatcher()->getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        $key = strtolower($module . '/' . $controller . '/' . $action);
        if (in_array($key, $permissions['path'])) {
            return true;
        }
        return false;
    }

    /**
     * 获取用户的导航条
     */
    public function getNav() {
        $all = $this->isAdmin();
        $navs = include APP_CFG_NAV;
        if ($all) {
            $access_list = $navs;
        } else {
            $group = $this->getPermissions()['group'];
            $group = array_flip($group);
            $access_list = array_intersect_key($navs, $group);
        }
        $ret = [];
        foreach ($access_list as $key => $item) {
            if (!is_array($item)) {
                continue;
            }

            list($label, $url) = $item;

            //过滤权限菜单
            if($url == ''){
                continue;
            }

            $parent_key = explode('.', $key)[0];
            $ret[$parent_key]['name'] = $navs[$parent_key];
            $ret[$parent_key]['sub'][] = [
                'label' => $label,
                'url' => $url
            ];
        }
        return array_values($ret);
    }

}
