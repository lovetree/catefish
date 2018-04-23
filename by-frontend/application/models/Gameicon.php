<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/14 0014
 * Time: 21:01
 */
class GameiconModel extends BaseModel{
    const TYPE_ARR = [
        '0'=>'公告',
        '1'=>'活动',
        '2'=>'攻略'
    ];

    /**
     * 查询列表
     * @return array|bool
     * @throws Exception
     */
    public function getList(){
        $sql = <<<SQL
                select id, img_url, download_url, name ,created_at from by_gameicon where is_hide=0 order by 'order'
SQL;

        $result = $this->DB()->query($sql);

        //数据重构
        foreach ($result as &$item){
            $item['created_at'] = substr($item['created_at'], 0, 10);
        }

        return $result;
    }
}