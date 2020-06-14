# 在线MYSQL测试数据生成工具

根据SQL表结构生成Mysql测试数据，根据字段名猜测类型，生成默认值

[English Readme](./README_EN.MD)

## 生成规则

普通规则见[官网](http://www.datamaker.online/)，此处讲个规则的组合使用

**自增日期（组自增） + 常量列表（组模式）**

使用场景： 一个肯德基总店下有一堆子店，希望生成[每个子店每天]的数据，一行记录包含一个店一天的汇总数据

使用方法： 在子店ID 字段上选择 `常量列表（组模式）` 用逗号分隔写上各店的ID，日期选择 `自增日期（组自增）` 即可

**Unique Key**

有 `UNIQUE KEY (user_id,platform) ` 的情况

方案一：
这种platform只有1和2的可以考虑使用 给 `platform` 选 `常量列表（组模式）` 填 `1,2`，然后user_id选自增之类保证不重就OK。

方案二：
选择重复Key选项，测试数据一般跳过就好，注意选 `REPLACE INTO` 可能出现 `AUTO_INCREMENT` + 2 的情况

## 个性化
你可以通过部署到自己的服务器上，对一些字段进行默认值配置，非常**适合公司内网**
部署也方便，代码里无任何框架依赖，能跑PHP就行

### 配置
`/conf/common.ini` 为通用配置
`/conf/local.ini` 为个性化配置，适合不宜暴露的默认值，如测试账号ID等，该文件已加入 `.gitignore` 里，如果发现不生效则跑下命令
`git update-index --assume-unchanged conf/local.ini`

配置样例：

```
[0]
key = index_day
method = INCR_DAY
value = 20180301
way = match
[1]
key = url
method = RAND_PIC_URL
value = 300,400
way = search
```
`key` : 字段名
`method` : 生成规则，如下表
`value` : 给前端的输入框值
`way` : 输入的key跟配置文件的key匹配规则，目前支持 `match` 完全相等、`search` 字符串包含，默认为`match`


| 规则英文 | 中文 |  参数 |
|----------|------|------|
| INCR_INT | 自增int  | from  : 从from开始自增，步长1
| RAND_INT | 随机int  | from,to  : 生成[from,to]闭区间整数
| RAND_FLOAT | 随机浮点  | from,to,round  : 生成[from,to]闭区间浮点，保留round位小数，这三个参数都是整数
| INCR_DAY | 日期自增     | from  : 从from开始自增，步长1天，格式20180304
| INCR\_DAY\_GROUPLY | 日期自增（组模式） | 同上，但每组值里的这个日期不变，下条SQL才变
| RAND_TIMESTAMP |  随机时间戳     | from,to  : 生成[from,to]这些天里的秒级时间戳，如 20180304,20180305
| RAND\_TIMESTAMP\_MYSQL |  随机时间（Mysql格式）     | from,to  : 生成[from,to]这些天里的秒级时间，格式：2018-04-07 18:08:34
| IGNORE |   不生成该列    |
| CONST_STR |   常量    | 常量值
| CONST\_STR\_LIST |   常量列表（组模式）    | 输入 a,b，每条SQL都输出 a,b，此时组数固定
| RAND_STR |   随机串    | length : 英文串长度
| RAND\_STR\_LIST |   随机串（指定列表）    | a,b,c : 该字段只出现a,b,c之一，可重复出现
| INCR\_STR\_SUFFIX  |   前缀+自增数字    | 输入 小王，输出 小王1，小王2，用于人名等
| RAND\_PIC\_URL |   图片地址    | width,height  : 图片的宽高

## 添加新生成规则流程

假设叫 rand_what

1. 前端 `index.php`

	`getDefaultValueByMethod()` 增加该规则的默认值  
	`getHoverContent()` 增加该规则的hover文案  
	`fillTabelWithData()` 增加对应代码  

2. 后端 `gensql.php`  
	
	`ValueGenerator` 类里新增 `randWhat()` 函数就行， 即下划线转驼峰



## License

**The MIT License**
