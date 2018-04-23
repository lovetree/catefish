<?php
/**
 * 系统设置
 * User: Administrator
 * Date: 2017/3/18 0018
 * Time: 9:33
 */

class SysconfigController extends BaseController{
    /**
     * 获取配置
     * @return bool
     */
    public function getConfigAction(){
        $request = $this->input()->request();

        //查询数据
        $config = new ConfigModel();
        if(isset($request['code'])){
            $result = $config->getConfigByCode($request['code']);
        }else{
            $result = $config->getAllConfig();
        }

        //数据重构
        $dataArr = [];
        foreach ($result as $item){
            $dataArr[$item['code']] = [
                'name' => $item['name'],
                'value' => $item['value'],
                'remark' => $item['remark']
            ];
        }

        //返回
        $this->succ($dataArr);
    }

    /**
     * 保存配置
     * @return bool
     */
    public function saveAction(){
        $request = $this->input()->request();

        if(isset($request['remark'])){
            $remark = $request['remark'];
            //删除参数remark以免影响下面的入库循环
            unset($request['remark']);
        }

        foreach ($request as $key=>$val){
            //忽略路径参数
            if(stripos($key, '/') !== false) continue;
            //构造更新数组
            $setData = [
                'value'=>$val
            ];
            if(isset($remark)){
                $setData['remark'] = $remark;
            }

            //更新数据
            $select = $this->DB()->newSelect("ms_config");
            $select->where('code', $key);
            $select->setData($setData);
            $this->DB()->exec($select->updateSql());
            //更新redis
            RedisFreshModel::refreshSysconfig($key, $val);
        }

        return $this->succ();
    }
}