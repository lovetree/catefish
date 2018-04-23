<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/14 0014
 * Time: 21:01
 */
class NewsModel extends BaseModel{
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
    public function getList($paramArr){
        //分页
        $page = 1;
        $pageSize = 20;
        if(isset($paramArr['page'])){
            $page = $paramArr['page'];
        }
        if(isset($paramArr['pageSize'])){
            $pageSize = $paramArr['pageSize'];
        }

        $sql = 'select id, title, type, publish_time from by_news where status=?';
        $valArr = ['yifabu'];
        if(isset($paramArr['type'])){
            $sql .= ' and type=?';
            array_push($valArr, $paramArr['type']);
        }

        //获取记录总数
        $countSql = preg_replace('/select .* from/', 'select count(1) as cnt from', $sql);
        $total = $this->DB()->query_fetch($countSql, $valArr);
        $pageCount = ceil($total['cnt']/$pageSize);

        //排序
        $sql .= ' order by publish_time desc';

        $sql .= ' limit ' . ($page-1) * $pageSize . ',' . $pageSize;

        $result = $this->DB()->query($sql, $valArr);

        //重构数据
        foreach ($result as $key=>$item) {
            $result[$key]['type'] = self::TYPE_ARR[$item['type']];
        }

        return ['pageCount'=>$pageCount, 'total'=>$total['cnt'], 'data'=>$result, 'result'=>0];
    }

    public function getDetail($newsId){
        $sql = <<<SQL
                select id, title, content, type, publish_time from by_news where id=?
SQL;

        $result = $this->DB()->query_fetch($sql, [$newsId]);

        //重构数据
        $result['type'] = self::TYPE_ARR[$result['type']];

        return $result;
    }
}