<?php

class SecurityController extends BaseController {

    /**
     * 加载管理员列表
     */
    public function listAction() {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        !empty($request['start_time']) && ($args['filters']['start_time'] = $request['start_time']);
        !empty($request['end_time']) && ($args['filters']['end_time'] = $request['end_time']);
        $query = $request['query'] ?? null;
        $query_type = $request['query_type'] ?? null;
        if($query && $query_type){
            switch($query_type){
                case 'admin_id':
                    $args['filters']['admin_id'] = $query;
                    break;
                case 'admin_name':
                    $args['filters']['admin_name'] = $query;
                    break;
                case 'path':
                    $args['filters']['path'] = $query;
                    break;
                case 'target_id':
                    $args['filters']['target_id'] = $query;
                    break;
            }
        }

        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new Log\SecurityModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        $navs = include APP_CFG_NAV;
//

        foreach ($list as &$item) {
            switch ($item['action']){
                case 'index/user/login':
                    $item['detail'] = '登录后台';
                    break;
                case 'index/user/logout':
                    $item['detail'] = '退出后台';
                    break;
                case 'player/user/savestock':
                    $item['detail'] = '冻结用户(id:'.json_decode($item['detail'],true)['id'].')';
                    break;
                case 'player/popularity/setpopu':
                    $item['detail'] = '修改用户(id:'.json_decode($item['detail'],true)['id'].')人气值 '.json_decode($item['detail'],true)['popularity'];
                    break;
                case 'admin/user/save':
                    $arr = json_decode($item['detail'],true);
                    if($arr['admin_id']){
                        $item['detail'] = '修改管理员用户 id :'.$arr['admin_id'].'  手机号:'.$arr['phone'].'  账户:'.$arr['username'].'  密码:'.$arr['password'].'  邮箱:'.$arr['password'];
                    }else{
                        $item['detail'] = '新增管理员用户'.$arr['username'];
                    }
                    break;
                case 'admin/user/active':
                    $arr = json_decode($item['detail'],true);
                    $item['detail'] = '修改管理员id:'.$arr['id'].' 状态'.($arr['status']==1?'启用':'禁用');
                    break;
                case 'admin/user/delete':
                    $arr = json_decode($item['detail'],true);
                    $item['detail'] = '删除管理员id:'.$arr['id'];
                    break;
                case 'admin/role/save':
                    $arr = json_decode($item['detail'],true);
                    $item['detail'] = '新增管理角色:   '.$arr['name'];
                    break;
                case 'admin/role/active':
                    $arr = json_decode($item['detail'],true);
                    $item['detail'] = '修改角色id:'.$arr['id'].' 状态'.($arr['status']==1?'启用':'禁用');
                    break;
                case 'admin/role/delete':
                    $arr = json_decode($item['detail'],true);
                    $item['detail'] = '删除角色  id:'.$arr['id'];
                    break;
                case 'admin/user/role':
                    $arr = json_decode($item['detail'],true);

                    if($arr['roles']){

                        $roles = $model->getRole($arr['roles']);
                        if($roles[0]['name']){

                            $strs = '修改管理员  id:'.$arr['admin_id'].'   角色为 '.implode(',',array_column($roles,'name'));
                        }else{

                            $strs = '无修改';
                        }

                        $item['detail'] = $strs;
                    }
                    break;
                case 'admin/role/setperms':
                    $arr = json_decode($item['detail'],true);
                    if(!$arr['perms']){
                        $item['detail'] = '修改角色id:'.$arr['id'].' 名称'.$arr['role_name'];
                    }else{
                        $arrs = explode(',',$arr['perms']);
                        $str = '';
                        foreach ($arrs as  $items){
                            $lis = explode('.',$items);
//                            echo  '<pre>';
//                            var_dump($navs[$lis[0].'.'.$lis[1]][0]);
//                            exit;
                            $str.=' '.$navs[$lis[0].'.'.$lis[1]][0].':'.($lis[2]==1?'编辑;':'查看;');
                        }
                        $item['detail'] = '修改'.$arr['role_name'].' 权限 : '.$str;
                    }
                    break;
                case 'player/user/ajaxinfo':
                    $arr = json_decode($item['detail'],true);
                    $item['detail']= '修改用户ID:('.$arr['id'].') 昵称:  '.$arr['nickname'].'手机号:'.$arr['phone'].'    VIP等级:'.$arr['user_level'];
                    break;
                case 'player/user/saveestate':
                    $arr = json_decode($item['detail'],true);
                    $item['detail']= '修改用户ID:('.$arr['id'].') 钻石:  '.($arr['credit']?$arr['credit']:0).'绿宝石:'.($arr['emerald']?$arr['emerald']:0).'金币:'.($arr['gold']?$arr['gold']:0).'  保险库金币:'.($arr['safe_gold']?$arr['safe_gold']:0);
                    break;
                case 'player/user/savepwd':
                    $arr = json_decode($item['detail'],true);
                    $item['detail']= '修改用户ID:('.$arr['id'].') 登录密码:  '.$arr['newpwd'];
                    break;
                case 'player/user/savesafe':
                    $arr = json_decode($item['detail'],true);
                    $item['detail']= '修改用户ID:('.$arr['id'].') 保险箱密码:  '.$arr['newpwd'];
                    break;
                case 'gamemt/room/setstock':
                    $arr = json_decode($item['detail'],true);

                    $item['detail']= '房间:('.$model->getRoomName($arr['id']).') 提取库存:  '.$arr['currentstock'];
                    break;
                case 'gamemt/room/setstocks':
                    $arr = json_decode($item['detail'],true);

                    $item['detail']= '房间:('.$model->getRoomName($arr['id']).') 调节池修改为:  '.$arr['stock'];
                    break;

            }

            $new = array_change_keys($item, array(
                'id' => 'id',
                'operator_name' => 'operator_name',
                'action' => 'action',
                'action_desc' => 'action_desc',
                'result_code' => 'result_code',
                'result_text' => 'result_text',
                'detail' => 'detail',
                'created_date' => 'operator_time',
                'target_id' => 'target_id',
                'ip' => 'ip'
            ));
            $ret_list[] = $new;
        }

        $data['data'] = $ret_list;

        //反馈数据
        return $this->succ($data, false);
    }

    /**
     * 启用/禁用管理员
     * @return boolean
     */
    public function activeAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->request(), array(
                    'admin_id' => 'required',
                    'status' => 'required|inside:0:1',
                ))) {
            return false;
        }

        $user = new Admin\UserModel();
        $status = $user->setActive($input->post_get('admin_id'), $input->post_get('status') == 1 ? true : false);
        if (!$status) {
            return $this->failed('操作失败');
        }
        return $this->succ();
    }

    /**
     * 创建/修改管理员数据
     */
    public function saveAction() {
        $input = $this->input();
        //验证参数
        $request = $input->request();
        if (!$this->validation($request, [
                    'admin_id' => 'positive_number',
                    'username' => 'pattern:#1',
                    'password' => 'length:6:20',
                    'email' => 'email|length:0:50',
                    'phone' => 'phone'
                        ], [
                    '#1' => '/^[a-zA-Z]\w{3,19}$/i'
                ])) {
            return false;
        }
        $admin_id = $input->post_get('admin_id');
        $user = new Admin\UserModel();
        if (empty($admin_id)) {
            if (empty($request['username']) || empty($request['password'])) {
                return $this->failed('参数错误');
            }
            $login = new UserModel();
            $request['parent_id'] = $login->getLoginUserID();
            //新建
            $status = $user->create($request);
        } else {
            if (isset($request['password']) && $request['password'] === '******') {
                unset($request['password']);
            }
            //修改
            $status = $user->edit($admin_id, $request);
        }
        if (false === $status) {
            return $this->failed('操作失败');
        } else if (-1 === $status) {
            return $this->failed('用户名重复');
        }
        return $this->succ();
    }

    /**
     * 修改管理员角色信息
     * @return boolean
     */
    public function roleAction() {
        $input = $this->input();
        //验证参数
        $request = $input->request();
        if (!$this->validation($request, array(
                    'admin_id' => 'required|positive_number',
                    'roles' => 'required|string'
                ))) {
            return false;
        }

        $roles = explode(',', $request['roles']);

        $user = new Admin\UserModel();
        $status = $user->setRole($request['admin_id'], $roles);
        if (!$status) {
            return $this->failed('操作失败');
        }
        return $this->succ();
    }

}
