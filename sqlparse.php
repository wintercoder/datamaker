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
    const RAND_PIC_URL = 'rand_pic_url';


    const errorParseError = 1001;

    public function getApiReturn($error, $msg, $data)
    {
        $ret['error'] = $error;
        $ret['msg'] = $msg;
        $ret['data'] = $data;
        return $ret;
    }

    /**
     * 解析字段名没有 ` 的SQL，正则以空格为分隔符，常见软件： datagrip
     * 正则匹配规则： 若干空格 + 不含空格的字符串(字段名) + 空格 + 不含空格的字符串(类型) + 空格 + 包含换行的任意字符（额外信息） + 逗号或者）[其中右括号结尾是表里没有任何索引的情况]，支持跨行匹配
     * @param $sql
            CREATE TABLE t_supplier_product
            (
                id INT AUTO_INCREMENT
                PRIMARY KEY,
                supplier_id INT NOT NULL
                COMMENT '供应商id',
                product_detail_id INT NOT NULL
                COMMENT '单品id',
                price DOUBLE NOT NULL
                COMMENT '采购价',
                KEY (`supplier_id`)
            )
            COMMENT '供应商货品'
            ENGINE = InnoDB
            CHARSET = utf8;
     * @return array 每个元素包含如下字段
     * origin: 原SQL
     * key: 字段名
     * type: 类型，包含可选的长度 int(11) 、text
     * others:  其他，NOT NULL AUTO_INCREMENT COMMENT '我是注释' 这种
     */
    private function parseWithoutBackQuote($sql){
        $content = explode('(',$sql,2); //先拿到 create table 之后的避免 正则把 第一列 吃了
        $sql = $content[1];
        $pattern = "#( *)([^\s]+) ([^\s]+) ([\s\S]+?)[,)]#im";
        preg_match_all($pattern, $sql, $matches);
        $ret = [];
        for ($cnt = 0; $cnt < count($matches[0]); $cnt++) {
            if (false !== stripos($matches[3][$cnt], 'KEY')) { //索引 排除
                continue;
            }
            $item = [
                'origin' => $matches[0][$cnt],
                'key' => $matches[2][$cnt],
                'type' => $matches[3][$cnt],
                'others' => $matches[4][$cnt],
            ];
            $ret []= $item;
        }
        return $ret;
    }


    /**
     * 解析字段名有 ` 包含的SQL，常见软件：Navicat
     * 正则匹配规则： 任意字符若干（空格或KEY） + `列名`+ 空格 + 非空字符串（类型） + 空格 + 额外信息 + 逗号或者），其中右括号结尾是表里没有任何索引的情况，支持跨行匹配
     * 如果需要改动，需要注意： COMMENT 换行、 表无索引时最后个字段的情况，目前以 ` 来区分字段而非空格
     * @param $sql
        CREATE TABLE `im_feed` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_id` int(11) NOT NULL DEFAULT '0',
            `user_id` bigint(11) NOT NULL DEFAULT '0' COMMENT '学号或者老师工号',
            `content` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
            `is_deleted` tinyint(4) NOT NULL DEFAULT '0',
            `photos` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
            `create_time` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
     * @return array 每个元素包含如下字段
     * origin: 原SQL
     * key: 字段名
     * type: 类型，包含可选的长度 int(11) 、text
     * others:  其他，NOT NULL AUTO_INCREMENT COMMENT '我是注释' 这种
     */
    private function parseWithBackQuote($sql){
        $pattern = "#(.*)`(.+)` ([^\s]+) ([\s\S]+?)[,)]#im";
        preg_match_all($pattern, $sql, $matches);
        $ret = [];
        for ($cnt = 0; $cnt < count($matches[0]); $cnt++) {
            if (false !== stripos($matches[1][$cnt], 'KEY')) { //索引 排除
                continue;
            }

            $item = [
                'origin' => $matches[0][$cnt],
                'key' => $matches[2][$cnt],
                'type' => $matches[3][$cnt],
                'others' => $matches[4][$cnt],
            ];
            $ret []= $item;
        }
        return $ret;
    }

    public function execute($input)
    {
        $sql = $input;
        $ret = [];

        //解析表名，兼容以下两种，返回的表名带不带 `都可以成功插入
        //  CREATE TABLE `test` (
        //  CREATE TABLE t_supplier_product
        //  (
        $pattern = "#CREATE TABLE (.+?)[\s]#i";
        preg_match($pattern, $sql, $matches);
        if (empty($matches)) {
            return $this->getApiReturn(self::errorParseError, '不是建表SQL，未包含 CREATE TABLE', []);
        }
        $ret['table_name'] = $matches[1];

        //解析字段，推荐个在线正则网站 https://regexr.com/
        $matchList = $this->parseWithBackQuote($sql);     //带`的解析失败则用不带`的解析，大部分SQL是带`的
        if (empty($matchList)) {
            $matchList = $this->parseWithoutBackQuote($sql);
        }
        $ret['list'] = [];
        if (empty($matchList)) {
            return $this->getApiReturn(self::errorParseError, '未查找到SQL字段', []);
        }

//        echo json_encode($matchList); exit();

//        解析后拿到的每个item:
//              "origin":" `baoguang_pv` int(11) NOT NULL DEFAULT '0' COMMENT '昨日曝光pv',",
//              "key":"baoguang_pv",     ======= 字段名
//              "type":"bigint(20)",     ======= 类型，包含可选的长度 int(11) 、text
//              "others":"NOT NULL DEFAULT '0' COMMENT '昨日曝光pv'"  =======  其他，NOT NULL AUTO_INCREMENT COMMENT '我是注释' 这种
        foreach ($matchList as $item){
            $size = 0;
            $type = $item['type'];
            $sizeArr = explode('(',$item['type']);
            //如果有()说明是有数字的那种
            if(!empty($sizeArr)) {
                $type = $sizeArr[0];
                $size = explode( ")" ,$sizeArr[1]  )[0] ;
            }
            $entry = $this->genDefaultAttribute($item['key'], $type , $size, $item['others']);
            $entry['key'] = $item['key'];
            $ret['list'] [] = $entry;
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
        $type = strtolower(trim($type));
        $incrStrPre = ['Boss', 'Player', 'Test', 'PM', 'Programmer', 'Worker', 'Actor', 'SB'];

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
                    'value' => '1',
                ];
                break;
        }

        //自增ID
        $autoInc = stripos($others, "AUTO_INCREMENT");
        if ($autoInc !== false) {
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
            method = RAND_PIC_URL
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
CREATE TABLE `im_feed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `user_id` bigint(11) NOT NULL DEFAULT '0' COMMENT '学号或者老师工号',
  `content` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `photos` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
";

$defaultSql2 = "
CREATE TABLE t_supplier_product
(
id INT AUTO_INCREMENT
PRIMARY KEY,
supplier_id INT NOT NULL
COMMENT '供应商id',
product_detail_id INT NOT NULL
COMMENT '单品id',
price DOUBLE NOT NULL
COMMENT '采购价',
KEY (`supplier_id`)
)
COMMENT '供应商货品'
ENGINE = InnoDB
CHARSET = utf8;
";

$sql = $_POST['sql'];
$parser = new CreateSqlParser();
if(empty($sql)){
    $sql = $defaultSql;
}
$ret = $parser->execute($sql);
echo json_encode($ret);exit();
