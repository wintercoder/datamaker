<?php

class CreateSqlParser
{

    const RAND_INT = 'rand_int';
    const RAND_FLOAT = 'rand_float';
    const INCR_INT = 'incr_int';
    const INCR_DAY = 'incr_day';
    const INCR_DAY_GROUPLY = 'incr_day_grouply';
    const RAND_TIMESTAMP = 'rand_timestamp';
    const RAND_TIMESTAMP_MYSQL = 'rand_timestamp_mysql';
    const IGNORE = 'ignore';

    const INCR_STR_PREFIX = 'incr_str_prefix';
    const RAND_STR = 'rand_str';
    const RAND_STR_LIST = 'rand_str_list';
    const CONST_STR = 'const_str';
    const CONST_STR_LIST = 'const_str_list';
    const RAND_PIC_LIST = 'rand_pic_url';


    const errorParseError = 1001;

    public function getApiReturn($error, $msg, $data)
    {
        $ret['error'] = $error;
        $ret['msg'] = $msg;
        $ret['data'] = $data;
        return $ret;
    }

    public function execute($input)
    {
        $sql = $input;
        $ret = [];

        //解析表名
        $pattern = "#CREATE TABLE `(.+)` #i";
        preg_match($pattern, $sql, $matches);
        if (empty($matches)) {
            return $this->getApiReturn(self::errorParseError, '不是建表SQL，未包含 CREATE TABLE', []);
        }
        $ret['table_name'] = $matches[1];

        //解析字段，推荐个在线正则网站 https://regexr.com/
        $pattern = "#(.+)`(.+)` ([^\s]+)(.+),#i";
        preg_match_all($pattern, $sql, $matches);
        $ret['list'] = [];
        if (empty($matches[0])) {
            return $this->getApiReturn(self::errorParseError, '未查找到SQL字段', []);
        }
//                echo json_encode($matches); exit();

        //解析后拿到
        // match[1]: 很多空格或者 UNIQUE KEY 、 KEY 这种无用字段，用于排除索引部分
        // match[2]: 字段名
        // match[3]: 类型，包含可选的长度 int(11) 、text
        // match[4]: 其他，NOT NULL AUTO_INCREMENT COMMENT '我是注释' 这种
        for ($cnt = 0; $cnt < count($matches[0]); $cnt++) {
            if( false !== stripos($matches[1][$cnt], 'KEY' ) ){ //索引 排除
                continue;
            }
            $key = $matches[2][$cnt];
            $type = $matches[3][$cnt];
            $size = 0;

            $sizeArr = explode('(',$matches[3][$cnt]);
            //如果有()说明是有数字的那种
            if(!empty($sizeArr)) {
                $type = $sizeArr[0];
                $size = explode( ")" ,$sizeArr[1]  )[0] ;
            }

            $item = $this->genDefaultAttribute($key, $type , $size, $matches[4][$cnt]);
            $item['key'] = $key;
            $ret['list'] [] = $item;
        }
        $ret['group_size'] = 5;     //组大小
        $ret['count'] = 3;          //多少条SQL

        return $this->getApiReturn(0, '', $ret);
    }

    /**
     * @param $key
     * @param $type string SQL的字段类型 varchar,int
     * @param $size  string SQL的字段类型后跟随的大小 如 varchar(10) 中的10
     * @param $others string  varchar(10) 后面的一串其他完整内容，包含 自增、非空、默认值等
     * @return array item 必备包含 'key' 字段名 'method' 生成规则 'value' 默认值
     */
    private function genDefaultAttribute($key, $type, $size, $others)
    {
        $item = [];
        $type = trim($type);
        $incrStrPre = ['老王', '射击狮', '测试店', '产品经理', '程序员', '码农', '攻城狮', 'SB'];

        switch ($type) {
            case 'varchar':
                $item = [
                    'desc' => '前缀+自增',
                    'method' => self::INCR_STR_PREFIX,
                    'value' => $incrStrPre[rand(0, count($incrStrPre) - 1)],
                ];
                break;
            case 'int':
            case 'mediumint':
            case 'integer':
                $item = [
                    'desc' => '随机整数',
                    'method' => self::RAND_INT,
                    'value' => '100,500',
                ];
                break;
            case 'bigint':
                $item = [
                    'desc' => '随机整数',
                    'method' => self::RAND_INT,
                    'value' => "1000000,99999999",
                ];
                break;
            case 'tinyint':
                $item = [
                    'desc' => '随机整数',
                    'method' => self::RAND_INT,
                    'value' => "0,{$size}",       //tinyint(4) 一般是0-4的枚举值
                ];
                break;
            case 'float':
            case 'double':
                $item = [
                    'desc' => '随机浮点',
                    'method' => self::RAND_FLOAT,
                    'value' => '1,10,5',
                ];
                break;
            case 'date':        //这个待定
            case 'datetime':
            case 'timestamp':
                $item = [
                    'method' => self::RAND_TIMESTAMP_MYSQL,
                    'value' => '20180407,20180408',
                ];
                break;
            case 'text':
            default:
                $item = [
                    'method' => self::CONST_STR,
                    'value' => '我是常量',
                ];
                break;
        }

        //自增ID
        $autoInc = stripos($others, "AUTO_INCREMENT");
        if ($autoInc) {
            $item = [
                'method' => self::IGNORE,
                'desc' => '自增ID，忽略',
            ];
        }

        //注释
        $expComment = explode("COMMENT ", $others);
        if (!empty($expComment)) {
            $item['comment'] = trim($expComment[1], "',");
        }

        //通用配置
        $commonItem = $this->parseFileForAttribute('conf/common.ini',$key);
        if(!empty($commonItem)){
            $item = $commonItem;
        }
        //个性化配置
        //针对你、你公司 常用的字段设置默认值，存放不可告人的数据秘密，配置文件在.gitignore里
        $localItem = $this->parseFileForAttribute('conf/local.ini',$key);
        if(!empty($localItem)){
            $item = $localItem;
        }
        return $item;
    }

    /**
     * 解析ini文件拿到自定义默认值，根据字段名猜测用户想要的是哪个类型，配置文件样例如下，目前只支持 模糊查找 和 精确匹配
            [0]
            key = avatar
            method = RAND_PIC_LIST
            value = 300,400
            way = search  跟key的匹配方式，search为模糊搜索，输入key包含 avatar 就走这个匹配

            [1]
            key = index_day
            method = INCR_DAY
            value = 20180301
            way = match  match为精确匹配，输入key 等于 index_day 就走这个匹配
     *
     *
     * @param $fileName string 配置文件路径
     * @param $key string 字段名
     * @return array
     */
    private function parseFileForAttribute($fileName,$key)
    {
        if(!file_exists($fileName)){
            return [];
        }
        $item = [];
        $matchArray = parse_ini_file($fileName,true);

        foreach ($matchArray as $match) {
            if(empty($match['way'])){   //默认key规则为相等
                $match['way'] = 'match';
            }
            if($match['way'] == 'search' && false !== stripos($key, $match['key'])){
                $item = $match;
                $item['method'] = strtolower($item['method']);
            }else if ($match['way'] == 'match' && $key == $match['key']) {
                $item = $match;
                $item['method'] = strtolower($item['method']);
            }
        }
        unset($item['way']);
        return $item;
    }


}
date_default_timezone_set("Asia/Shanghai");


$defaultSql = "
CREATE TABLE `test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_time` int(11) NOT NULL DEFAULT '',
  `insert_day` int NOT NULL '',
  `content` text NOT NULL '',
  `is_deleted` tinyint(2) NOT NULL,
  `up_count` int(11) NOT NULL,
  `date_ti` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

$sql = $_POST['sql'];
$parser = new CreateSqlParser();
if(empty($sql)){
    $sql = $defaultSql;
}
$ret = $parser->execute($sql);
echo json_encode($ret);exit();
