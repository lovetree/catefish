<?php
/**
 * 系统配置
 * User: jim
 * Date: 2017/3/10 0010
 * Time: 16:53
 */

class ConfigModel extends BaseModel{

    /**
     * @param $code
     * @return int
     * @throws Exception
     */
    public function getConfigByCode($code){
        if(!$code){
            return '';
        }

        $select = $this->DB()->newSelect('ms_config');
        $select->select('value');
        $select->select('remark');
        $select->where('code', $code);

        return $this->DB()->fetch($select);
    }

    public function getAllConfig(){
        $select = $this->DB()->newSelect('ms_config');

        return $this->DB()->search($select->toString());
    }
}