# Online Sql Test Data Generator


Generate sql test data by Mysql table structure   



Input:

```
CREATE TABLE `student` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL ,
  PRIMARY KEY (`id`),
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ;
```

Output:

```
INSERT INTO `student (user_id,name) VALUES ('10001','John1'),('10002','John2');

INSERT INTO `student (user_id,name) VALUES ('10003','John3'),('10004','John4');

```



**[Try It Now ](http://datamaker.online/index_en.php)**



## Individuation
You can deploy this project on your server to set default value for frequently used fields, good for local area network like company

Easy to deploy: It only require PHP (without any framework) and webserver 

### Config
`/conf/common.ini` is common config  
`/conf/local.ini` is personal config, good for same private value like test account.   For safe, you should run   
`git update-index --assume-unchanged conf/local.ini`   
to avoid traced to git 

Sample：

```
[0]
key = index_day 
method = INCR_DAY
value = 20180301
way = match
[1]
key = url 				
method = RAND_PIC_LIST 
value = 300,400			
way = search				
```
`key` : field name   
`method` : generate  rule in the following table  
`value` : default value  for front-end  
`way` : `match`：match this config if the input key is equals to key ; `search` match this config if the input key contains this key;  the defalut value is `match` 


| Rule Method | Description |  Parameter |
|----------|------|------|
| INCR_INT | auto increment integer  | `from`  : increase from `from`
| RAND_INT | random int  | `from,to`  : random in interval `[from,to]`
| RAND_FLOAT | random float  | `from,to,round`  : Random in interval `[from,to]` , retains `round` digits after the decimal point
| INCR_DAY | increment day    | `from`  : increase from `from` with format 20180304
| INCR\_DAY\_GROUPLY | increment day (grouply same) | same as above，but the value is same in the same group 
| RAND_TIMESTAMP |  random timestamp | `from,to`  : timestamp in day `[from,to]`, input format is 20180304,20180305
| RAND\_TIMESTAMP\_MYSQL |  random time (Mysql)     | `from,to`  : same as above，but the result format is 2018-04-07 18:08:34
| IGNORE |   ignore this column    | 
| CONST_STR |   const value    | 
| CONST\_STR\_LIST |   const (group list) | `a,b`: every group in sql will output `a,b` 
| RAND_STR |   random string    | `length` : the length of string with alphabet
| RAND\_STR\_LIST |   random string in list    | `a,b,c` : random pick one in `a,b,c` 
| INCR\_STR\_SUFFIX  |  Prefix + Number (Incr) | `test` : it will ouput test1,test2,test3 ...
| RAND\_PIC\_URL |  picture url  | `width,height`  : the width and height of url

## How to add new generator

When you add a method call `rand_what`

1. Frontend: `index.php`

	`getDefaultValueByMethod()` add the default value  
	`getHoverContent()` add the hover content  
	`fillTabelWithData()` modify it  

2. Backend`gensql.php`  
	
	Add the function call `randWhat()` In class `ValueGenerator` ( Camel-Case of `rand_what` )



## License

**The MIT License**
