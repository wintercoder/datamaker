<html>
<head>
    <title>SQL测试数据生成</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="http://cdn.static.runoob.com/libs/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="http://cdn.static.runoob.com/libs/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.12.1/bootstrap-table.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.12.1/bootstrap-table.min.js"></script>
    <!--    下拉列表 1.3有BUG，选2.0版本 -->
<!--    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/2.0.0-beta1/css/bootstrap-select.min.css" rel="stylesheet" />-->
<!--    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.0-beta/css/bootstrap-select.min.css" rel="stylesheet">-->
<!--    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.0-beta/js/bootstrap-select.min.js"></script>-->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/2.0.0-beta1/css/bootstrap-select.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/2.0.0-beta1/js/bootstrap-select.min.js"></script>
    <link rel="shortcut icon" href="./favicon.png">





</head>
<style> </style>
<!--<body style="background-image: url(http://img02.tooopen.com/images/20160601/tooopen_sy_163908772474.jpg);">-->
<body style=" background-color: #f7f8fa;">

<div class="container">
    <div class="row clearfix">
        <div class="col-md-12 column">
            <nav class="navbar navbar-default" role="navigation">
                <div class="navbar-header" >
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1"> <span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button> <a class="navbar-brand" href="#">SQL测试数据生成</a>
                </div>

                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
<!--                        <li class="active">-->
<!--                            <a href="#">Link</a>-->
<!--                        </li>-->
                    </ul>
                    <ul class="nav navbar-nav navbar-left">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">切换语言<strong class="caret"></strong></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a  id="btn_to_chinese"  href="./" >中文</a>
                                </li>
                                <li>
                                    <a id="btn_to_english"  href="./index_en.php">English</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">保存配置<strong class="caret"></strong></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a  id="btn_import"  href="javascript:void(0);" onclick="onImportBtnClick()" >导入配置</a>
                                </li>
                                <li>
                                    <a id="btn_export"  href="#">导出配置</a>
                                </li>
<!--                                <li class="divider">-->
<!--                                </li>-->
<!--                                <li>-->
<!--                                    <a href="#">Separated link</a>-->
<!--                                </li>-->
                            </ul>
                        </li>
                    </ul>

                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <a href="https://github.com/wintercoder/datamaker"  target="_blank" >Github</a>
                        </li>
                    </ul>
                </div>

            </nav>


            <form role="form" >
                <div class="form-group">
                    <label>SQL表结构</label>
<!--                    <label style="font-size:12px" class="label label-info">SQL表结构</label>-->
                    <span class="help-block"> 从 show create table tablename 获得</span>
                    <textarea id="sql_create" class="form-control" rows="3"
placeholder='CREATE TABLE `im_feed_reply` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
) ENGINE=InnoDB AUTO_INCREMENT=1;
'></textarea>
                </div>
                <button onclick="return false" id="btn_get_default" class="btn btn-default  btn-info">下一步</button>
<!--导入导出 已放右上角-->
<!--                <button onclick="return false" id="btn_export" class="btn btn-default  btn-info">导出</button>-->
<!--                <button  id="btn_import" onclick="onImportBtnClick()" class="btn btn-default  btn-info">导入</button>-->
<!--导入时用于传文件的隐藏按钮-->
                <input type="file" id="btn_import_hidden" style="display:none">

                <!--return false 禁用点击跳转，由js控制，避免点击后刷新页面-->
            </form>

            <script type="text/javascript" charset="utf-8" >
                // 导入： 点击按钮时 触发上传文件
                function onImportBtnClick() {
                    $("#btn_import_hidden").click();
                }
                // 导入
                $("#btn_import_hidden").on("change", function() {
                    //读取文件内容，转成对象，填充表格
                    let fileReader = new FileReader();
                    fileReader.onload = function(e) {
                        var fileContent = e.target.result;
                        var jsonObj = JSON.parse(fileContent);
                        console.log("上传内容：");
                        console.log(jsonObj);
                        fillTabelWithData(jsonObj);
                    };
                    fileReader.readAsText(this.files[0],'utf8');
                });
            </script>

            <!--字段 生成规则 表格-->
                <table id="select_table" hidden class="table table-striped" >
                    <thead >
                    <tr >
                        <th data-field="key">列名</th>
                        <th data-field="method">生成规则</th>
                        <th data-field="method_option">参数</th>
                    </tr>
                    </thead>
                    <tbody id ="table_tr">

                    </tbody>
                </table>

            <!-- 配置 -->
            <div id="btn_group_gen" hidden>
                <!-- 生成SQL的条数等 -->

                <div class="col-md-2 column" >
                    <button id="btn_commit_sql" type="submit" class="btn btn-info btn-default">生成SQL语句</button>
                </div>
                <div class="col-md-4 column" >
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <div class="col-sm-3">
                                <input type="number" class="form-control" id="tv_count" value="2"/>
                            </div>
                            <label class="col-3 control-label">条SQL</label>
                        </div>
                    </form>
                </div>

                <div class="col-md-4 column">
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <div class="col-sm-3 " >
                                <input type="number" class="form-control" id="tv_group_size" value="3"/>
                            </div>
                            <label class="col-2 control-label">行记录合并成一组</label>
                        </div>
                    </form>

                </div>

                <div class="col-md-2 column ">
                    <form class="form-horizontal form-inline" role="form">
                        <label class="form-label ">重复Key</label>
                        <select class="form-horizontal form-control  col-xs-12 selectpicker" id="selectpicker_insertway"  >
                            <option value="INSERT INTO " >不处理</option>
                            <option value="INSERT IGNORE "  >跳过</option>
                            <option value="REPLACE INTO "  >替换</option>
                        </select>
                    </form>
                </div>

            </div>

            <input type="text" id="tv_tablename_hidden" value="我是表名" style="display:none" >  <!--  存放表名的隐藏字段-->

            <script type="text/javascript">
                function getList(){
                    //默认隐藏各种框
                    var showTime =  400;
                    $('#select_table').show(showTime);
                    $('#btn_group_gen').show(showTime);
                    $('#tv_group_result').show(showTime);

                    // jquery ajax 请求
                    $.getJSON({
                        type:'post',
                        url :"./sqlparse.php",
                        data:{
                            sql: $('#sql_create').val() //输入的SQL表结构字符串
                        },success:function(response,status){
                            fillTabelWithData(response.data);
                        },error:function(data,statsu){
                            alert("网络获取默认值失败！");
                        }
                    })
                }
                //根据数据填充表格，用于默认值和导入，参数样例： {"list":[{"key":"id","method":"ignore","value":""},{"key":"parent_id","method":"rand_int","value":"1,100"}]}
                function fillTabelWithData(responseData) {
                    $('#tv_tablename_hidden').val(responseData.table_name);   //隐藏框 存放表名

                    $('#table_tr').html('');
                    var str = '';
                    $.each(responseData.list,function(i,val){
                        str = '';
                        str = str + '<tr id=item_' + i +'>';
                        str = str + '<td> '+ '<input type="text" class="form-control" id="name" value='+val.key +'></td>';

                        //无法直接传对象当参数 ，也不能直接json字符串，否则跟onchange的双引号乱套，需要转码
                        var jsonVal = JSON.stringify(val);
                        jsonVal = jsonVal.replace(/"/g,'&quot;');

                        var selectStr = `
                                    <select class="form-control selectpicker" id="method_selectpicker_`+i+`" onchange="selectOnChange(this,`+ jsonVal +`)" >
                                        <optgroup label="数字">
                                            <option value="incr_int" >自增</option>
                                            <option value="rand_int"  >随机整数</option>
                                            <option value="rand_float"  >随机浮点</option>
                                            <option value="incr_day" >自增日期</option>
                                            <option value="incr_day_grouply" >自增日期（组自增）</option>
                                            <option value="rand_timestamp" >随机时间戳</option>
                                            <option value="rand_timestamp_mysql" >随机时间（Mysql格式）</option>
                                            <option value="ignore" >忽略该列</option>
                                        </optgroup>
                                        <optgroup label="字符串">
                                            <option value="const_str" >常量</option>
                                            <option value="const_str_list" >常量列表（组模式）</option>
                                            <option value="rand_str">随机字符串</option>
                                            <option value="rand_str_list">随机字符串（指定列表）</option>
                                            <option value="incr_str_prefix">前缀 + 数字自增</option>
                                            <option value="rand_pic_url">图片URL</option>
                                        </optgroup>
                                        </select>
                                `;

                        str = str + '<td> '+selectStr+'  </td>';
                        str = str + '<td>' + '<input type="text"  id="tv_input_'+i+'" class="form-control" data-html="true" data-trigger="hover focus" data-toggle="tooltip" data-placement="top" data-content='+ getHoverContent(jsonVal) +'   value="" >' +'  </td>';
                        str = str + '</tr>';

                        $('#table_tr').append(str);

                        // 根据返回的method设置默认生成方法
                        $('#method_selectpicker_'+i).selectpicker();
                        $('#method_selectpicker_'+i).selectpicker('val',val.method);
                        $('#method_selectpicker_'+i).selectpicker('refresh');
                        $('#method_selectpicker_'+i).trigger('change');  //手动触发change事件

                        //设置接口返回的默认值 和 对应hover
                        $("#tv_input_"+i).val( val.value );
                        $("#tv_input_"+i).attr('data-content',getHoverContent( val.method ));
                    });

                    //开启 hover提示功能
                    $(function () { $("[data-toggle='tooltip']").popover(); });

                }

                //对生成规则的解释，鼠标hover在文本框上时显示 http://wiki.jikexueyuan.com/project/bootstrap4/components/tooltips/#section-1
                function getHoverContent(method) {
//                    jsonVal = jsonVal.replace(/\&quot;/g,'"');     var jsonObj = JSON.parse(jsonVal);    method = jsonObj.method;

                    switch (method) {
                        case 'incr_int':
                            return '输入：3 </br> 输出：3，4，5 ...';
                        case 'rand_int':
                            return "输入：1,100</br> 输出：在闭区间 [1,100] 中随机，可重复";
                        case 'rand_float':
                            return "输入：1,100,3</br> 输出：在闭区间 [1,100] 中随机浮点数，保留3位小数";
                        case 'incr_day':
                            return "输入：20180401</br> 输出：从 20180401 起日期自增，自动跨月";
                        case 'incr_day_grouply':
                            return "输入：20180401，2条SQL，3值合一组</br> 输出： <br />20180401,20180401,20180401<br />20180402,20180402,20180402<br /> 适合与常量列表组成叉积模式，生成每个子店铺每天各一个值";
                        case 'rand_timestamp':
                            return "输入：20180401,20180402</br> 输出：从 20180401 到 20180402 这两天里的随机时间戳";
                        case 'rand_timestamp_mysql':
                            return "输入：20180401,20180402</br> 输出：这两天里的随机时间  <br /> 格式： 2018-04-01 11:16:16";
                        case 'ignore':
                            return "不解释";
                        case 'const_str':
                            return "不解释";
                        case 'const_str_list':
                            return "输入：百度,阿里,腾讯；2条SQL，3值合一组</br> 输出： <br />百度,阿里,腾讯<br />百度,阿里,腾讯<br /> <br /> 按照列表中的元素生成，每个元素按顺序出现，输入元素间用英文逗号分隔。</br> <br /> 有多个常量列表则并行出现，输出SQL以 min(元素个数) 为一组";
                        case 'rand_str':
                            return '输入：长度</br> 输出：随机字符串，字符集：字母表';
                        case 'rand_str_list':
                            return '输入：摩拜,ofo</br> 输出：列表里随机选，可重复';
                        case 'incr_str_prefix':
                            return '输入：小王</br> 输出：小王1，小王2';
                        case 'rand_pic_url':
                            return '输入：300,400 </br> 输出：宽300，高400的图片地址';
                    }
                }

                //选择 生成规则 时 设置默认值，打开网站第一次获取的会被网络请求的返回值覆盖，其他情况走这个逻辑
                function selectOnChange(obj,params) {

                    var method = obj.options[obj.selectedIndex].value;
                    var parent = obj.parentNode.parentNode;
                    var brother = obj.parentNode.nextSibling;
                    var optionTxt = brother.children[0];    //参数文本框
                    var incrStrPre = ['老王','射击狮','测试店','产品经理','程序员','码农','攻城狮','SB'];
                    switch (method) {
                        case 'incr_int':
                            optionTxt.value = 1;
                            break;
                        case 'rand_int':
                        case 'rand_float':
                            optionTxt.value = '1,100';
                            break;
                        case 'incr_day':
                        case 'incr_day_grouply':
                            optionTxt.value = 20180401;
                            break;
                        case 'ignore':
                            optionTxt.value = '';
                            optionTxt.placeholder = '不生成该列，适合自增列';
                            break;
                        case 'rand_timestamp':
                        case 'rand_timestamp_mysql':
                            optionTxt.value = '20180401,20180404';
                            break;
                        case 'const_str':
                            optionTxt.value = 'Goolge';
                            break;
                        case 'const_str_list':
                            optionTxt.value = '百度,阿里,腾讯';
                            break;
                        case 'rand_str':
                            optionTxt.value = '5';
                            break;
                        case 'rand_str_list':
                            optionTxt.value = '摩拜,ofo,小蓝,悟空,7号电单车';
                            break;
                        case 'incr_str_prefix':
                            optionTxt.value = incrStrPre[ Math.floor((incrStrPre.length-1) * Math.random()) ] ;
                            break;
                        case 'rand_pic_url':
                            optionTxt.value = '300,400';
                            break;
                    }
                    //更换 生成规则 后 更新 hover
                    var currentId = obj.id.split('_')[2];   //当前点击第几行
                    $("#tv_input_"+currentId).attr('data-content',getHoverContent( method ));

                    //每次 修改 规则 时 看是否有 常量列表，有就限制组数文本框输入
                    $('#tv_group_size').attr("disabled",false);
                    $(".selectpicker").each(function () {
                        var pickerVal = $(this).val();
                        if(pickerVal === 'const_str_list'){
                            $('#tv_group_size').attr("disabled",true);
                            $('#tv_group_size').val("-1");
                        }
                    });
                }

                $(document).ready(function(){
                    //TODO 测试时使用，自动填充表结构
//                    getList();
                });
                //点击下一步后 根据sql 拉字段信息
                $('#btn_get_default').click(function(){
                    getList();
                });

                //导出配置，遍历表格拿数据，组成跟解析SQL后的json那样
                $('#btn_export').click(function() {
                    var fieldList = [];
                    $("#table_tr").find("tr").each(function(){
                        var tdArr = $(this).children();
                        var key = tdArr.eq(0).find('input').val();     //字段名
                        var method = tdArr.eq(1).find('select').val(); //method
                        var option = tdArr.eq(2).find('input').val();  //参数

                        var item = new Object();
                        item['key'] = key;
                        item['method'] = method;
                        item['value'] = option;
                        fieldList.push(item);
                    });
                    var exportData =  new Object();
                    exportData['list'] = fieldList;
                    exportData = JSON.stringify(exportData);
                    createAndDownloadFile("config_export.txt",exportData);
                });

                //提交表格，生成SQL
                $(function(){
                    $('#btn_commit_sql').click(function(){
                        var params = new Object();
                        var fieldList = [];
                        var constListSize = 999999999;  //常量列表的元素个数，用于固定一组SQL有多少value

                        //遍历tr拿表格各元素
                        $("#table_tr").find("tr").each(function(){
                            var tdArr = $(this).children();

                            var key = tdArr.eq(0).find('input').val();     //字段名
                            var method = tdArr.eq(1).find('select').val(); //method
                            var option = tdArr.eq(2).find('input').val();  //参数

                            var item = new Object();
                            item['key'] = key;
                            item['method'] = method;
                            item['value'] = option;
                            if(method == 'const_str_list'){
                                constListSize = Math.min(constListSize,option.split(',').length);
                            }
                            fieldList.push(item);
                        });
                        params['insert_way'] = $('#selectpicker_insertway').val();
                        params['list'] = fieldList;
                        params['group_size'] = $('#tv_group_size').val();
                        params['group_size'] = params['group_size'] > 500 ? 500 : params['group_size'] ;
                        if(constListSize !== 999999999){        //代表存在常量列表，将值固定为列表个数
                            params['group_size'] = constListSize;
                            $('#tv_group_size').val(constListSize);
                        }

                        params['count'] = $('#tv_count').val();
                        params['count'] = params['count'] > 5000 ?  5000 : params['count'];
                        var tablename = $('#tv_tablename_hidden').val();
                        params['table_name'] = (tablename == '') ? 'table_name' : tablename;


                        //发JSON，生成SQL
                        params = JSON.stringify(params);
                        $.post('gensql.php',params,function(data){
                            $('#sql_result').val(data);
                        });
                    });
                });


                /**
                 * 工具函数： 创建并下载文件，用于导出
                 * @param  {String} fileName 文件名
                 * @param  {String} content  文件内容
                 */
                function createAndDownloadFile(fileName, content) {
                    var aTag = document.createElement('a');
                    var blob = new Blob([content]);
                    aTag.download = fileName;
                    aTag.href = URL.createObjectURL(blob);
                    aTag.click();
                    URL.revokeObjectURL(blob);
                }
            </script>

            <br/><br/><br/><br/>
                <!-- SQL结果 -->
                <div hidden id ="tv_group_result" class="col-md-12  column">
                    <textarea  id="sql_result" class="form-control" placeholder="我是存放 SQL 结果的地方" rows="8" ></textarea>
                </div>

        </div>
    </div>
</div>




</body>
</html>

