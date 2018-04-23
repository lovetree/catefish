<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/14 0014
 * Time: 21:01
 */
class UserModel extends BaseModel{

    const TYPE_ARR = [
        '0'=>'待支付',
        '1'=>'已完成',
        '2'=>'已退款'
    ];
    /**
     * 查询列表
     * @return array|bool
     * @throws Exception
     */
    public function login($name,$pwd){
        $sql = <<<SQL
        select 
              mu.id,
              mu.password,
              mu.nickname,
              mu.username,
              mu.email,
              mu.created_at,
              mui.last_time,
              mui.avatar,
              mus.credit,
              mus.gold
        from ms_user mu
        join ms_user_info mui
        left join ms_user_estate mus on mu.id = mus.user_id 
         WHERE mu.phone = ? AND mu.status = 1
SQL;

        $result =$this->DB1()->query_fetch($sql,[$name]);
        if(!$result){
            return array('res'=>500,'msg'=>'用户不存在');
        }

        if(md5($pwd)!=$result['password']){
            return array('res'=>500,'msg'=>'密码错误');
        }

        Yaf\Session::getInstance()->set('user_id',$result['id']);

        return array('res'=>200,'msg'=>'登录成功', 'data'=>[
            'user_id'=>$result['id'],
            'nickname'=>$result['nickname'],
            'username'=>$result['username'],
            'gold' => $result['gold'] ? $result['gold'] : 0,
            'credit' => $result['credit'] ? $result['credit'] : 0,
            'avatar' =>$result['avatar'],
            'register_time' =>$result['created_at'],
            'email' => $result['email'],
            'last_time' => $result['last_time']
        ]);
    }

    /**
     * @param $UserId
     * @return bool|mixed
     * 用户基本信息
     */
    public function baseInfo($UserId){
        $sql = <<<SQL
                select 
              mu.id,
              mu.password,
              mu.nickname,
              mu.username,
              mu.email,
              mu.created_at,
              mui.last_time,
              mui.avatar,
              mus.credit,
              mus.gold,
              mu.phone
        from ms_user mu
        join ms_user_info mui
        left join ms_user_estate mus on mu.id = mus.user_id 
         WHERE mu.id = ? AND mu.status = 1
SQL;

        $result = $this->DB1()->query_fetch($sql, [$UserId]);
        $msql =  <<<SQL
                select credit from ms_user_estate where user_id=?
SQL;
        $money = $this->DB1()->query_fetch($msql, [$UserId]);
        if($money){
            $result['money'] = $money['credit'];
        }else{
            $result['money'] = 0;
        }

        //重构数据
//        $result['type'] = self::TYPE_ARR[$result['type']];

        return $result;
    }
    public function userInfo($user_id){
        $sql = 'select * from ms_user_info WHERE user_id = ?';
        return $this->DB1()->query_fetch($sql, [$user_id]);

    }

    public function userId($username){
        $sql = 'select id from ms_user WHERE username = ?';
        return $this->DB1()->query_fetch($sql, [$username])['id'];
    }
    public function checkIsUser($user_id){
        $sql = 'select username from ms_user WHERE id = ?';
        return $this->DB1()->query_fetch($sql, [$user_id])['username'];
    }
    /**
     * 修改密码/个人信息
     */
    public function editPwd($data,$user_id){
        if ($data) {
            try {
                $this->DB1()->update('ms_user', $data, 'id = ' . $user_id);
                return true;
            } catch (Exception $ex) {
                logMessage($ex->getTraceAsString());
                return false;
            }
        }
        return false;
    }
    public function editInfo($data,$user_id){
        if ($data) {
            try {
                $this->DB1()->update('ms_user_info', $data, 'user_id = ' . $user_id);
                return true;
            } catch (Exception $ex) {
                logMessage($ex->getTraceAsString());
                return false;
            }
        }
        return false;
    }
    /**
     * 获取用户密码
     */
    public function getPwd($user_id){
        return $this->DB1()->query_fetch('select password from ms_user WHERE id = '.$user_id);

    }

    /**
     * 获取验证码
     */
    public function getOne($phone, $type, $code){
        $sql = <<<SQL
                select id,is_used,status,c_time,expire from ms_sms where phone = ? and type = ? and code = ?  order by id desc limit 1
SQL;
        return $this->DB1()->query_fetch($sql, array($phone, $type, $code));
    }

    /**
     * 查询列表
     * @return array|bool
     * @throws Exception
     */
    public function getList($paramArr,$user_id){
        //分页
        $page = 1;
        $pageSize = 20;
        if(isset($paramArr['page'])){
            $page = $paramArr['page'];
        }
        if(isset($paramArr['pageSize'])){
            $pageSize = $paramArr['pageSize'];
        }
        $start = 0;
        $end = 0;
        $type = '全部';
        $sql = 'select order_id, created_date, totalf_fee, status from ms_order where user_id= ?';
        $valArr = [$user_id];
        if(isset($paramArr['status'])){
            if ($paramArr['status']=='全部'){

            }else{
                switch ($paramArr['status']){
                    case  '待支付':
                        $type = '待支付';
                        $paramArr['status'] = 0;
                        break;
                    case  '已完成':
                        $type = '已完成';
                        $paramArr['status'] = 1;
                        break;
                    case  '已退款':
                        $type = '已退款';
                        $paramArr['status'] = 2;
                        break;
                    default :
                        $type = '全部';
                }
                $sql .= ' and status = ?';
                array_push($valArr, $paramArr['status']);
            }

        }

        if(isset($paramArr['start'])&&$paramArr['start']){
            $sql .= ' and created_date >=?';
            array_push($valArr, strtotime($paramArr['start']));
            $start = $paramArr['start'];
        }
        if(isset($paramArr['end'])&&$paramArr['end']){
            $sql .= ' and created_date <=?';
            array_push($valArr, strtotime($paramArr['end']));
            $end = $paramArr['end'];
        }


        //获取记录总数

        $countSql = preg_replace('/select .* from/', 'select count(1) as cnt from', $sql);
        $total = $this->DB1()->query_fetch($countSql, $valArr);


        $pageCount = ceil($total['cnt']/$pageSize);

        //排序
        $sql .= ' order by id desc';

        $sql .= ' limit ' . ($page-1) * $pageSize . ',' . $pageSize;

        $result = $this->DB1()->query($sql, $valArr);

        //重构数据
        foreach ($result as $key=>$item) {
            $result[$key]['status'] = self::TYPE_ARR[$item['status']];
        }

        return ['pageCount'=>$pageCount, 'total'=>$total['cnt'], 'data'=>$result, 'result'=>0,'start'=>$start,'end'=>$end,'status'=>$type];
    }

    /**
     * @return array|bool
     * 充值商品列表
     */
    public function getGoods(){
        $sql = 'select id,name,show_name,worth,total_price from ms_goods WHERE status = 1 AND aibei_waresid >0 and source = 2 order by worth asc,id asc';
        $result = $this->DB1()->query($sql);
        return $result;
    }
    public function service(){
        $sqlp = 'select value from by_siteinfo WHERE id = 1';
        $sqlq = 'select value from by_siteinfo WHERE id = 2';
        $data = array(
          'tel'=> $this->DB()->query_fetch($sqlp),
            'qq'=>$this->DB()->query_fetch($sqlq)
        );
        return $data;
    }

}