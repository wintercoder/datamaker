<?php

class ValueGenerator{

    public function __call($name, $arguments){
        echo "生成规则 $name 不存在\n";exit();
    }

    /**
     * 日期自增，全局自增模式
     * @param $input array
     *      从 from值 开始自增 格式: 20180301
     * @param $groupSize int 每组多少条合并成一个数组，也是该次函数返回的数组大小
     * @param $sqlCounter int  当前是第几条SQL，用于在多条SQL之间仍然保持自增
     * @return array
     */
    public function incrDay($input,$groupSize,$sqlCounter)
    {
        $begin = $input;
        $offset = ($sqlCounter - 1) * $groupSize;
        $ret = []; $count = 0;

        $begin = date('Ymd',strtotime("{$begin} + {$offset} day"));
        while ($count < $groupSize) {
            $ret []= date('Ymd',strtotime("{$begin} + {$count} day"));
            $count++;
        }
        return $ret;
    }

    /**
     * 日期自增，组递增模式，每组的日期相同
     * @param $input array
     *      从 from值 开始自增 格式: 20180301
     * @param $groupSize int 每组多少条合并成一个数组，也是该次函数返回的数组大小
     * @param $sqlCounter int  当前是第几条SQL，用于在多条SQL之间仍然保持自增
     * @return array
     */
    public function incrDayGrouply($input,$groupSize,$sqlCounter)
    {
        $begin = $input;
        $offset = ($sqlCounter - 1) ;
        $ret = []; $count = 0;

        $begin = date('Ymd',strtotime("{$begin} + {$offset} day"));
        while ($count < $groupSize) {
            $ret []= date('Ymd',strtotime("{$begin} + 0 day"));
            $count++;
        }
        return $ret;
    }
    /**
     * 自增int
     * @param $input array
     *      从 from值 开始自增
     * @param $groupSize int 每组多少条合并成一个数组，也是该次函数返回的数组大小
     * @param $sqlCounter int  当前是第几条SQL，用于在多条SQL之间仍然保持自增
     * @return array
     */
    public function incrInt($input,$groupSize,$sqlCounter)
    {
        $begin = intval($input);
        $offset = ($sqlCounter - 1) * $groupSize;
        $ret = []; $count = 0;
        $begin += $offset;
        while ($count < $groupSize) {
            $ret []= $begin++;
            $count++;
        }
        return $ret;
    }
    /**
     * 随机int
     * @param $input string
     *      闭区间生成 [from,to]
     * @param $groupSize int 每组多少条合并成一个数组，也是该次函数返回的数组大小
     * @return array
     */
    public function randInt($input,$groupSize)
    {
        $exp = explode(',',$input);
        $from = !empty($exp[0]) ? intval($exp[0]) : 1;
        $to = !empty($exp[1]) ? intval($exp[1]) : 100;
        $ret = [];
        $count = 0;
        while ($count++ < $groupSize) {
            $ret []= mt_rand($from,$to);
        }
        return $ret;
    }
    /**
     * 随机浮点
     * @param $input string
     *      闭区间生成 [from,to] 位数为 n，默认3位
     * @param $groupSize int 每组多少条合并成一个数组，也是该次函数返回的数组大小
     * @return array
     */
    public function randFloat($input,$groupSize)
    {
        $exp = explode(',',$input);
        if(empty($exp) ){
            $from = 0; $to = 1;$wei = 3;
        }else{
            $from =  intval($exp[0]); $to = intval($exp[1]); $wei = intval($exp[2]);
            if(empty($exp[2])) $wei = 3;
        }

        $randNum = $from + mt_rand() / mt_getrandmax() * ($to - $from);
        $ret = [];
        $count = 0;
        while ($count++ < $groupSize) {
            $ret []= sprintf("%.{$wei}f",$randNum);
        }
        return $ret;
    }

    /**
     * 指定日期里的 随机时间戳
     * @param $input string
     *      [from,to] 中的时间戳
     * @param $groupSize int 每组多少条合并成一个数组，也是该次函数返回的数组大小
     * @return array
     */
    public function randTimestamp($input,$groupSize)
    {
        $exp = explode(',',$input);
        $from = !empty($exp[0]) ? intval($exp[0]) : 20180401;
        $to = !empty($exp[1]) ? intval($exp[1]) : 20380101;
        $diff = strtotime($to) - strtotime($from);
        $ret = [];
        $count = 0;
        while ($count++ < $groupSize) {
            $ret []= strtotime($from) + mt_rand(0,$diff);
        }
        return $ret;
    }

    /**
     * 指定日期里的 随机时间戳 (2018-04-08 11:16:00 格式)
     * @param $input string
     *      [from,to] 中的时间戳
     * @param $groupSize int 每组多少条合并成一个数组，也是该次函数返回的数组大小
     * @return array
     */
    public function randTimestampMysql($input,$groupSize)
    {
        $ret = $this->randTimestamp($input,$groupSize);
        foreach ($ret as &$item){
            $item =  date("Y-m-d H:i:s",$item);
        }
        return $ret;
    }

    /**
     * 常量
     * @param $input
     *          value 常量值
     * @param $groupSize int 每组多少条合并成一个数组，也是该次函数返回的数组大小
     * @return array
     */
    public function constStr($input,$groupSize){
        return explode('$#$',str_repeat($input.'$#$',$groupSize));
    }


    /**
     * 常量列表
     * @param $input
     *          value 常量值
     * @param $groupSize int 每组多少条合并成一个数组，也是该次函数返回的数组大小
     * @return array
     */
    public function constStrList($input,$groupSize){
        $input = !empty($input) ? $input : '百度$#$阿里$#$腾讯';
        return explode('$#$',$input);
    }

    /**
     * 随机字符串（字符集：大小写字母）
     * @param $input array
     *          length 长度
     * @param $groupSize int 每组多少条合并成一个数组，也是该次函数返回的数组大小
     * @return array
     */
    public function randStr($length,$groupSize)
    {
        $ret = [];
        $count = 0;
        while ($count++ < $groupSize) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $ranStr = '';
            for ($i = 0; $i < $length; $i++) {
                $ranStr .= $characters[rand(0, strlen($characters) - 1)];
            }
            $ret []= $ranStr;
        }
        return $ret;
    }
    /**
     * 在字符串列表里 随机，全局可重复
     * @param $input array
     *          length 长度
     * @param $groupSize int 每组多少条合并成一个数组，也是该次函数返回的数组大小
     * @return array
     */
    public function randStrList($input,$groupSize)
    {
        $input = !empty($input) ? $input : 'ofo,mobike';
        $value = explode(',',$input);
        $ret = [];
        $count = 0;
        while ($count++ < $groupSize) {
            $ranStr = $value[rand(0, count($value) - 1)];
            $ret []= $ranStr;
        }
        return $ret;
    }


    /**
     * 自增中文，根据 前缀+1 这种，如程序员1，程序员2
     * @param $input array
     *          pre_str 前缀
     * @param $groupSize int 每组多少条合并成一个数组，也是该次函数返回的数组大小
     * @return array
     */
    public function incrStrPrefix($input,$groupSize,$sqlCounter)
    {
        $pre = !empty($input) ? $input : '测试店';
        $inputArr['from'] = 1;
        $intArr = $this->incrInt($inputArr,$groupSize,$sqlCounter);
        $ret = [];
        foreach ($intArr as $intVal){
            $str = $pre . $intVal;
            $ret []= $str;
        }
        return $ret;
    }

    /**
     * 随机图片，目前是 http://lorempixel.com/
     * 也可以考虑用百度的 http://image.baidu.com/channel/listjson?pn=0&rn=30&tag1=%E7%BE%8E%E5%A5%B3&tag2=%E5%85%A8%E9%83%A8&ie=utf8
     * @param $input
     * @param $groupSize int 每组多少条合并成一个数组，也是该次函数返回的数组大小
     * @return array
     */
    public function randPicUrl($input,$groupSize){
        $exp = explode(',',$input);
        $width = !empty($exp[0]) ? intval($exp[0]) : 300;
        $height = !empty($exp[1]) ? intval($exp[1]) : 300;
        $url = "http://lorempixel.com/{$width}/{$height}/";

        return explode(',',str_repeat($url.',',$groupSize));
    }
}

class WorkHandler{

    // /**
    //  * 同步的数据导出，格式为.sql
    // */
    // public function syncExportStr2File($fileName, $content){
    //     header_remove();
    //     ini_set('memory_limit', '128M');
    //     set_time_limit(1800);
    //     header("Content-type:text/html;charset=utf-8");
    //     // header("Content-Transfer-Encoding: binary");
    //     // header("Content-Type: application/force-download;");
    //     header("Content-type: application/octet-stream");
    //     header("Cache-control: no-cache");
    //     header("Content-Transfer-Encoding: binary");
    //     header("Content-Disposition: attachment; filename=$fileName.sql");
    //     header("Expires: 0");
    //     header("Cache-control: private");
    //     header("Pragma: no-cache");
    //     header('Content-Length: ' . strlen($content));

    //     // $content = iconv('UTF-8', 'GBK//IGNORE', $content);
    //     echo $content.PHP_EOL;
    //     exit();
    // }

    public function checkParams($input){
        $input = json_decode($input,true);  //json转数组
        if(empty($input) || empty($input['list'])){
            echo "参数不是JSON格式";exit();
        }
        $input['count'] = intval($input['count']);
        $input['group_size'] = intval($input['group_size']);
        if( $input['count'] <= 0 || $input['group_size'] <= 0
            || $input['count'] >= 5000 || $input['group_size'] >= 5000) {
            echo "条数、组数只能是 1 到 5000 以内";exit();
        }
    }

    public function execute($input){
        $this->checkParams($input);

        $input = json_decode($input,true);  //json转数组
        $inputList = $input['list'];

        //对于自增ID等忽略的类型，删除它并重排下标，不能用 array_splice ，删除多个时会乱
        foreach ($inputList as $key => $item){
            if($item['method'] == 'ignore'){
                unset($inputList[$key]);
            }
        }
        $inputList = array_values($inputList);  //从0开始 重建下标

        $genCount = !empty($input['count']) ? $input['count'] : 5 ;
        $tableName = !empty($input['table_name']) ? $input['table_name'] : 'test';
        $groupSize = $input['group_size'];              //每个SQL有多少value组
        $insertWay = !empty($input['insert_way']) ? $input['insert_way'] : 'INSERT INTO ';

        $keyArr = array_column($inputList,'key');

        $generator = new ValueGenerator();

        $sql = '';
        //insert的SQL条数
        for($genI = 1; $genI <= $genCount; $genI++) {

            $genResult = [];    //生成结果数组： key => 生成方法+下标 防止同样的方法覆盖数据，value => 按该方法生成的数据数组
            foreach ($inputList as $itemIndex => $item){
                $genService = $this->camelize($item['method']); //方法名转驼峰
                $genResult [ $item['method'].$itemIndex ] = $generator->$genService($item['value'],$groupSize,$genI );
            }
            $valueStr = '';

            //每个SQ有多少value组
            for($cnt = 0; $cnt < $groupSize; $cnt++){

                $valueStr .= '(';
                //取每个字段的值，单引号引起来
                foreach ($genResult as $result) {
                    $valueStr .= "'{$result[$cnt]}',";
                }
                $valueStr = rtrim($valueStr,',');
                $valueStr .= '),';
            }
            $valueStr = rtrim($valueStr,',');
            $valueStr .= ';';
            $sql .= $insertWay . "{$tableName} (" . implode(",",$keyArr) .") VALUES $valueStr";
            $sql .= "\n";
            $sql .= "\n";
        }
        //一直没成功，还怀疑是jq的post方法不是超链导致，最终用前端去做了
        // $this->syncExportStr2File('datamake_'.date('YmdHis', time()).'sql' ,$sql);
        echo $sql;
    }

    /**
     * 下划线转驼峰
     * @param $str
     * @param string $separator
     * @return string
     */
    private function camelize($str,$separator='_')
    {
        $str = $separator. str_replace($separator, " ", strtolower($str));
        return ltrim(str_replace(" ", "", ucwords($str)), $separator );
    }
}


date_default_timezone_set("Asia/Shanghai");

$postData = file_get_contents('php://input');   //提交的是JSON，不能直接$post获取
if(empty($postData)){
    echo "我是空白";
    exit();
}
//echo ($postData);exit();


$handler = new WorkHandler();
$handler->execute($postData);
exit();

/*
$parser = new CreateSqlParser();
$ret = $parser->execute($sql);
if(0 == ($ret['error'])){

    $input = json_encode($ret['data']);
    $handler = new WorkHandler();
    $handler->execute($input);
} else{
    echo json_encode($ret);exit();

}

*/