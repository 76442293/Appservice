<?php
/**
 * StatisticsAction.class.php
 * 统计相关接口
 * DaMingGe 2018-01-02
 */
import("@.Action.BaseAction");

class StatisticsAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取得统计列表
     */
    public function listWfStatistics()
    {
        // 所属公司ID
        $ws_company = isset($_REQUEST['ws_company']) ? $_REQUEST['ws_company'] : '0';

        $_wf_statistics = M("wf_statistics", "oa_", 'DB_CONFIG_OA');

        if (!empty($ws_company)) {
            $where = " wm_company = {$ws_company}";
        } else {
            $where = " 1=1";
        }

        $ws_id = isset($_REQUEST['ws_id']) ? $_REQUEST['ws_id'] : '0';

        if ($ws_id != 0) {
            $where = " ws_id = {$ws_id}";
        }

        $list = $_wf_statistics->field("*")->where($where)->select();
        if ($list === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询错误',
                'errorSql' => $_wf_statistics->getlastsql(),
            );
        } else {
            if (empty($list)) {
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '没有数据',
                );
            } else {
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'list' => $list,
                );
            }
        }

//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;

    }

    /**
     * 根据统计ID修改统计/添加新的统计
     */
    public function editWfStatistics()
    {
        $ws_id = isset($_REQUEST['ws_id']) ? $_REQUEST['ws_id'] : '0';
        $ws_data = array();
        $ws_data['ws_name'] = $_REQUEST['ws_name'];
        $ws_data['ws_name_ch'] = $_REQUEST['ws_name_ch'];
        $ws_data['ws_company'] = $_REQUEST['ws_company'];
        $ws_data['ws_module'] = $_REQUEST['ws_module'];
        $ws_data['ws_form'] = $_REQUEST['ws_form'];
        $ws_data['ws_json'] = $_REQUEST['ws_json'];
        $ws_data['ws_abled'] = $_REQUEST['ws_abled'];

        $_wf_statistics = M("wf_statistics", "oa_", 'DB_CONFIG_OA');
        if ($ws_id == '0') {
            //新增统计
            $ws_data['ws_create_time'] = date("Y-m-d H:i:s");//创建时间
            $rs = $_wf_statistics->add($ws_data);
        } else {
            //修改统计
            $rs = $_wf_statistics->where("ws_id = {$ws_id}")->save($ws_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_statistics->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
            );
        }
//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';

        header('Access-Control-Allow-Origin:*');

        // 返回数据
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }

    /**
     * 根据统计ID删除统计
     */
    public function delWfStatisticsById()
    {
        $ws_id = $_REQUEST['ws_id'];
        $_wf_statistics = M("wf_statistics", "oa_", 'DB_CONFIG_OA');

        $rs = $_wf_statistics->where("ws_id = {$ws_id}")->delete();
        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '删除错误',
                'errorSql' => $_wf_statistics->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '删除成功',
            );
        }
//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        // 返回数据
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r);
        }
        exit;
    }

    /**
     * 根据统计ID计算统计结果 TODO 等待测试
     */
    public function doStatisticsById()
    {
        $ws_id = isset($_REQUEST['ws_id']) ? $_REQUEST['ws_id'] : '0';

        if ($ws_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'ws_id参数为空',
            );
        } else {

            $_wf_statistics = M("wf_statistics", "oa_", 'DB_CONFIG_OA');
            // 取得统计方式
            $statistics = $_wf_statistics->where("ws_id = {$ws_id}")->select();
//print_r($_wf_statistics->getLastSql());exit;
            $_wf_forms = M("wf_forms", "oa_", 'DB_CONFIG_OA');
            // 要统计的表单结构
            $wf_form = $_wf_forms->where("wff_id = {$statistics[0]['ws_form']}")->select();
            $wff_json = json_decode($wf_form[0]['wff_json'], true);
//print_r($wff_json);

            $Model = new Model(); // 实例化一个空模型
            // 先drop掉原表
            $Model->execute(" DROP TABLE IF EXISTS `temp_" . $statistics[0]['ws_name'] . "`");

            // 创建临时表
            // 临时表的名称为temp_ + 统计名称,ID为自增ID
            $sql = "CREATE TEMPORARY TABLE if not exists `temp_" . $statistics[0]['ws_name'] . "` (" .
                "`id` int(10) AUTO_INCREMENT,";
            // 根据结构json,生成临时表创建sql
            foreach ($wff_json as $key => $val) {
                // 根据属性最大长度判断字段类型,没有maxSize属性的,默认为varchar(200)
                if ($val['wfw_attr'][0]['dataType'] == 'varchar') {
                    $sql = $sql . "`" . $val['wfw_attr'][0]['name'] . "` varchar(" . $val['wfw_attr'][0]['maxSize'] . ") DEFAULT '',";
                } elseif ($val['wfw_attr'][0]['dataType'] == 'int') {
                    $sql = $sql . "`" . $val['wfw_attr'][0]['name'] . "` int(" . $val['wfw_attr'][0]['maxSize'] . ") DEFAULT 0,";
                } elseif ($val['wfw_attr'][0]['dataType'] == 'date') {
                    $sql = $sql . "`" . $val['wfw_attr'][0]['name'] . "` datetime DEFAULT NULL,";
                }
//                if (!isset($val['attr']['maxSize']) || strlen($val['attr']['maxSize']) <= 50) {
//                    $sql = $sql . "`" . $val['attr']['name'] . "` varchar(200) DEFAULT '',";
//                } else {
//                    $sql = $sql . "`" . $val['attr']['name'] . "` text DEFAULT '',";
//                }
            }
            $sql = $sql . "PRIMARY KEY (`id`) ) ENGINE=MEMORY DEFAULT CHARSET=utf8;";

//print_r($sql);

            // 生成用来统计的临时表
            $Model->execute($sql);

            // 根据指定的表单结构ID,取得套用此表单结构的所有表单数据
            $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');
            $form_datas = $_wf_form_data->field("wfd_data_json")->where("wfd_form = {$wf_form[0]['wff_id']}")->select();

//print_r($_wf_form_data->getLastSql());
//print_r($form_datas);

            // 遍历,将所有结果插入临时表
            foreach ($form_datas as $key => $val) {
                $sql = "insert into temp_" . $statistics[0]['ws_name'] . " set ";
                $form_data = json_decode($val['wfd_data_json'], true);
                $isFirst = true;
                foreach ($form_data as $kk => $vv) {
                    if (!$isFirst) {
                        $sql = $sql . ",";
                    } else {
                        $isFirst = false;
                    }
                    $sql = $sql . $vv['name'] . " = '" . $vv['value'] . "'";
                }
                $sql = $sql . ";";
//print_r($sql);
                // 执行插入语句
                $Model->execute($sql);
            }
//print_r($sql);
//print_r($Model->query("select * from  temp_" . $statistics[0]['ws_name'] . ""));
//exit;

            // 统计方式json
            $ws_json = json_decode($statistics[0]['ws_json'], true);
//print_r($ws_json);

            // 根据统计方式json,生成查询SQL
            // SELECT区域
            $showField = "";
            $isFirst = true;
            foreach ($ws_json['showField'] as $k => $field) {
                if ($isFirst) {
                    $isFirst = false;
                } else {
                    $showField = $showField . ",";
                }
                $showField = $showField . $field['field'];
            }
            $sql = "SELECT " . $showField;
            // 统计区域
            if (isset($ws_json['statisticsField']) && !empty($ws_json['statisticsField'])) {
                $sql = $sql . "," . $ws_json['statisticsType'] . "(" . $ws_json['statisticsField'] . ") AS " . $ws_json['statisticsField'];
            }

            // FROM区域
            $sql = $sql . " FROM temp_" . $statistics[0]['ws_name'] . " ";
            // WHERE区域
            $whereField = $ws_json['whereField'];
            $where = " WHERE  1=1 ";
            foreach ($whereField as $k => $v) {
                switch ($v['condition']) {
                    case "equal":
                        $condition = " = ";
                        break;
                    case "lessequal":
                        $condition = " <= ";
                        break;
                    case "less":
                        $condition = " < ";
                        break;
                    case "great":
                        $condition = " > ";
                        break;
                    case "greatequal":
                        $condition = " >= ";
                        break;
                    default:
                        $condition = "=";
                }
                $where = $where . $v['contact'] . " " . $v['field'] . $condition . "'" . $v['fieldvalue'] . "' ";
            }
            $sql = $sql . $where;
            // GROUP BY 区域
            if (isset($ws_json['groupField']) && !empty($ws_json['groupField'])) {
                $isFirst = true;
                foreach ($ws_json['groupField'] as $kg => $group) {
                    if ($isFirst) {
                        $sql = $sql . " GROUP BY ";
                        $isFirst = false;
                    } else {
                        $sql = $sql . " , ";
                    }
                    $sql = $sql . $group['field'];
                }
                //$sql = $sql . " GROUP BY " . $ws_json['groupField']['field'];
            }
            // ORDER BY 区域
            $orderField = $ws_json['orderField'];
            if (isset($orderField)) {
                $isFirst = true;
                foreach ($orderField as $k => $order) {
                    if ($isFirst) {
                        $sql = $sql . " ORDER BY ";
                        $isFirst = false;
                    } else {
                        $sql = $sql . " , ";
                    }
                    $sql = $sql . $order['field'] . " " . $order['order'];
                }
            }
//print_r($sql);

            // 查询统计结果
            $list = $Model->query($sql);
//print_r($Model ->getLastSql());exit;

            if ($list === false) {
                // 执行错误
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '统计出现错误',
                    'errorSql' => $Model->getlastsql(),
                );
            } else {

                // 设置统计结果显示方式
                $showType = $ws_json['showType'];
                $showResult = $ws_json['showResult'];
//print_r($ws_json);
                switch ($showType) {

                    // 柱状图
                    case "barChart":
                        $xName = $showResult['xName'];
                        $yName = $showResult['yName'];
                        $xAxisValues = array();
                        $yAxisValues = array();
                        $yAxisNameMin = 0;
                        $yAxisNameMax = 0;
                        $isFirst = true;
                        foreach ($list as $kk => $vv) {
                            if ($isFirst) {
                                $yAxisNameMin = $vv[$yName];
                                $yAxisNameMax = $vv[$yName];
                                $isFirst = false;
                            } else {
                                if ($vv[$yName] < $yAxisNameMin) {
                                    $yAxisNameMin = $vv[$yName];
                                }
                                if ($vv[$yName] > $yAxisNameMax) {
                                    $yAxisNameMax = $vv[$yName];
                                }
                            }
                            array_push($xAxisValues, $vv[$xName]);
                            array_push($yAxisValues, $vv[$yName]);
                        }
                        $showResult['xAxisValues'] = $xAxisValues;
                        $showResult['yAxisValues'] = $yAxisValues;
                        $showResult['yAxisNameMin'] = $yAxisNameMin;
                        $showResult['yAxisNameMax'] = $yAxisNameMax;

                        break;

                    // 饼状图
                    case "peiChart":
                        $total = 0;
                        $values = array();
                        foreach ($list as $kk => $vv) {
                            array_push($values, $vv[$showResult['name']]);
                            $total += $vv[$showResult['name']];
//print_r($vv[$showResult['name']]);
                        }
//print_r($showResult['name']);
                        $showResult['itemNum'] = count($list);
                        $showResult['values'] = $values;
                        $showResult['total'] = $total;

                        break;

                    // 漏斗图
                    case "funnelChart":
                        $values = array();
                        $texts = array();
                        foreach ($list as $kk => $vv) {
                            array_push($values, $vv[$showResult['name']]);
                            array_push($texts, $vv[$showResult['text']]);
                        }
                        $showResult['values'] = $values;
                        $showResult['texts'] = $texts;
                        break;

                    // 折线图
                    case "lineChart":
                        $xName = $showResult['xName'];
                        $yName = $showResult['yName'];
                        $xAxisValues = array();
                        $yAxisValues = array();
                        $yAxisNameMin = 0;
                        $yAxisNameMax = 0;
                        $lineName = $showResult['lineName'];
                        $lineNameTemp = "";
                        $isFirst = true;
                        $index = 0;
                        foreach ($list as $kk => $vv) {
                            if ($isFirst) {
                                $yAxisNameMin = $vv[$yName];
                                $yAxisNameMax = $vv[$yName];
                                $isFirst = false;
                            } else {
                                if ($vv[$yName] < $yAxisNameMin) {
                                    $yAxisNameMin = $vv[$yName];
                                }
                                if ($vv[$yName] > $yAxisNameMax) {
                                    $yAxisNameMax = $vv[$yName];
                                }
                            }

                            // 遍历时,此条数据的折线区别字段值和上条不一样
                            if (!empty($lineNameTemp) && $lineNameTemp != $vv[$lineName]) {
//print "\n"."true" . $index;
                                // 此条前的数据存入
                                $showResult['xAxisValues'][] = $xAxisValues;
                                $showResult['yAxisValues'][] = $yAxisValues;
                                $showResult['lineNameValues'][] = $lineNameTemp;

                                $lineNameTemp = $vv[$lineName];

                                // 临时数组清空
                                $xAxisValues = array();
                                $yAxisValues = array();
                                array_push($xAxisValues, $vv[$xName]);
                                array_push($yAxisValues, $vv[$yName]);
                            } else {
//print "\n"."false" . $index;
                                if ($index == 0) {
                                    $lineNameTemp = $vv[$lineName];
                                }
//print "A|";
//print_r($vv[$lineName]);
//print "B\n";
//print $lineNameTemp;
                                // 折线区别字段保持一致,组装入临时数组
                                array_push($xAxisValues, $vv[$xName]);
                                array_push($yAxisValues, $vv[$yName]);
                            }
                            if ($kk == count($list) - 1) {
                                // 此条前的数据存入
                                $showResult['xAxisValues'][] = $xAxisValues;
                                $showResult['yAxisValues'][] = $yAxisValues;
                                $showResult['lineNameValues'][] = $lineNameTemp;
                            }
                            $index++;
                        }
                        $showResult['yAxisNameMin'] = $yAxisNameMin;
                        $showResult['yAxisNameMax'] = $yAxisNameMax;

                        break;
                    default:

                        // 曲线图
                    case "curveChart":
                        $xName = $showResult['xName'];
                        $yName = $showResult['yName'];
                        $xAxisValues = array();
                        $yAxisValues = array();
                        $yAxisNameMin = 0;
                        $yAxisNameMax = 0;
                        $isFirst = true;
                        foreach ($list as $kk => $vv) {
                            if ($isFirst) {
                                $yAxisNameMin = $vv[$yName];
                                $yAxisNameMax = $vv[$yName];
                                $isFirst = false;
                            } else {
                                if ($vv[$yName] < $yAxisNameMin) {
                                    $yAxisNameMin = $vv[$yName];
                                }
                                if ($vv[$yName] > $yAxisNameMax) {
                                    $yAxisNameMax = $vv[$yName];
                                }
                            }

                            array_push($xAxisValues, $vv[$xName]);
                            array_push($yAxisValues, $vv[$yName]);
                        }
                        $showResult['xAxisValues'] = $xAxisValues;
                        $showResult['yAxisValues'] = $yAxisValues;
                        $showResult['yAxisNameMin'] = $yAxisNameMin;
                        $showResult['yAxisNameMax'] = $yAxisNameMax;

                        break;

                    // 总计图
                    case "totalnumberChart":

                        // 此图为特定订制图,无法使用通用sql查询,单独生成查询sql
                        // *******月度查询start*******
                        $showField = $ws_json['showField'][0]['field'];
                        $sql = "SELECT date_format(" . $showField . ", '%Y-%m') AS months ";
//                        $sql = "concat(date_format(".$showField.", '%Y'),FLOOR((date_format(".$showField.", '%m') + 2) / 3)) AS quarters" ;
                        // 统计区域
                        if (isset($ws_json['statisticsField']) && !empty($ws_json['statisticsField'])) {
                            $sql = $sql . "," . $ws_json['statisticsType'] . "(" . $ws_json['statisticsField'] . ") AS " . $ws_json['statisticsField'];
                        }

                        // FROM区域
                        $sql = $sql . " FROM temp_" . $statistics[0]['ws_name'] . " ";
                        // WHERE区域
                        $whereField = $ws_json['whereField'];
                        $where = " WHERE  1=1 ";
                        foreach ($whereField as $k => $v) {
                            switch ($v['condition']) {
                                case "equal":
                                    $condition = " = ";
                                    break;
                                case "lessequal":
                                    $condition = " <= ";
                                    break;
                                case "less":
                                    $condition = " < ";
                                    break;
                                case "great":
                                    $condition = " > ";
                                    break;
                                case "greatequal":
                                    $condition = " >= ";
                                    break;
                                default:
                                    $condition = "=";
                            }
                            $where = $where . $v['contact'] . " " . $v['field'] . $condition . "'" . $v['fieldvalue'] . "' ";
                        }
                        $sql = $sql . $where;
                        // GROUP BY 区域
                        $sql = $sql . " GROUP BY months ";
//print $sql . "\n";

                        // 查询统计结果
                        $monthList = $Model->query($sql);
//print_r($monthList);
                        $lastMonthValue = 0;
                        $thisMonthValue = 0;
                        $bestMonthValue = 0;
                        $thisMonth = date("Y-m", strtotime("now"));
                        $lastMonth = date("Y-m", strtotime("-1 month"));

//print "\n".$thisMonth."|".$lastMonth."\n";

                        $isFirst = true;
                        foreach ($monthList as $kk => $month) {
                            if ($isFirst) {
                                $bestMonthValue = $month[$ws_json['statisticsField']];
                                $isFirst = false;
                            } else {
                                if ($bestMonthValue < $month[$ws_json['statisticsField']]) {
                                    $bestMonthValue = $month[$ws_json['statisticsField']];
                                }
                            }

                            if ($thisMonth == $month['months']) {
                                $thisMonthValue = $month[$ws_json['statisticsField']];
                            }
                            if ($lastMonth == $month['months']) {
                                $lastMonthValue = $month[$ws_json['statisticsField']];
                            }
                        }
                        $showResult['month']['last'] = $lastMonthValue;
                        $showResult['month']['this'] = $thisMonthValue;
                        $showResult['month']['best'] = $bestMonthValue;
                        // *******月度查询end*******

                        // *******季度查询start*******
                        $showField = $ws_json['showField'][0]['field'];
//                        $sql = "SELECT date_format(" . $showField . ", '%Y-%m') AS months ";
                        $sql = "SELECT concat(date_format(" . $showField . ", '%Y'),FLOOR((date_format(" . $showField . ", '%m') + 2) / 3)) AS quarters";
                        // 统计区域
                        if (isset($ws_json['statisticsField']) && !empty($ws_json['statisticsField'])) {
                            $sql = $sql . "," . $ws_json['statisticsType'] . "(" . $ws_json['statisticsField'] . ") AS " . $ws_json['statisticsField'];
                        }

                        // FROM区域
                        $sql = $sql . " FROM temp_" . $statistics[0]['ws_name'] . " ";
                        // WHERE区域
                        $whereField = $ws_json['whereField'];
                        $where = " WHERE  1=1 ";
                        foreach ($whereField as $k => $v) {
                            switch ($v['condition']) {
                                case "equal":
                                    $condition = " = ";
                                    break;
                                case "lessequal":
                                    $condition = " <= ";
                                    break;
                                case "less":
                                    $condition = " < ";
                                    break;
                                case "great":
                                    $condition = " > ";
                                    break;
                                case "greatequal":
                                    $condition = " >= ";
                                    break;
                                default:
                                    $condition = "=";
                            }
                            $where = $where . $v['contact'] . " " . $v['field'] . $condition . "'" . $v['fieldvalue'] . "' ";
                        }
                        $sql = $sql . $where;
                        // GROUP BY 区域
                        $sql = $sql . " GROUP BY quarters ";
//print $sql . "\n";

                        // 查询统计结果
                        $quarterList = $Model->query($sql);
//print_r($quarterList);
                        $lastQuarterValue = 0;
                        $thisQuarterValue = 0;
                        $bestQuarterValue = 0;

                        $season = ceil((date('n')) / 3);//当月是第几季度
                        $thisQuarter = date("Y", strtotime("now")) . $season;
                        $lastQuarter = date("Y", strtotime("-3 month")) . ceil((date("n", strtotime("-3 month")) / 3));


//print "\n".$thisQuarter."|".$lastQuarter."\n";exit;

                        $isFirst = true;
                        foreach ($quarterList as $kk => $quarter) {
                            if ($isFirst) {
                                $bestQuarterValue = $quarter[$ws_json['statisticsField']];
                                $isFirst = false;
                            } else {
                                if ($bestQuarterValue < $quarter[$ws_json['statisticsField']]) {
                                    $bestQuarterValue = $quarter[$ws_json['statisticsField']];
                                }
                            }

                            if ($thisQuarter == $quarter['quarters']) {
                                $thisQuarterValue = $quarter[$ws_json['statisticsField']];
                            }
                            if ($lastQuarter == $quarter['quarters']) {
                                $lastQuarterValue = $quarter[$ws_json['statisticsField']];
                            }
                        }
                        $showResult['quarter']['last'] = $lastQuarterValue;
                        $showResult['quarter']['this'] = $thisQuarterValue;
                        $showResult['quarter']['best'] = $bestQuarterValue;
                        // *******季度查询end*******


                        // *******年度查询start*******
                        $showField = $ws_json['showField'][0]['field'];
//                        $sql = "SELECT date_format(" . $showField . ", '%Y-%m') AS months ";
                        $sql = "SELECT date_format(" . $showField . ", '%Y') AS years";
                        // 统计区域
                        if (isset($ws_json['statisticsField']) && !empty($ws_json['statisticsField'])) {
                            $sql = $sql . "," . $ws_json['statisticsType'] . "(" . $ws_json['statisticsField'] . ") AS " . $ws_json['statisticsField'];
                        }

                        // FROM区域
                        $sql = $sql . " FROM temp_" . $statistics[0]['ws_name'] . " ";
                        // WHERE区域
                        $whereField = $ws_json['whereField'];
                        $where = " WHERE  1=1 ";
                        foreach ($whereField as $k => $v) {
                            switch ($v['condition']) {
                                case "equal":
                                    $condition = " = ";
                                    break;
                                case "lessequal":
                                    $condition = " <= ";
                                    break;
                                case "less":
                                    $condition = " < ";
                                    break;
                                case "great":
                                    $condition = " > ";
                                    break;
                                case "greatequal":
                                    $condition = " >= ";
                                    break;
                                default:
                                    $condition = "=";
                            }
                            $where = $where . $v['contact'] . " " . $v['field'] . $condition . "'" . $v['fieldvalue'] . "' ";
                        }
                        $sql = $sql . $where;
                        // GROUP BY 区域
                        $sql = $sql . " GROUP BY years ";
//print $sql . "\n";

                        // 查询统计结果
                        $yearList = $Model->query($sql);
//print_r($quarterList);
                        $lastYearValue = 0;
                        $thisYearValue = 0;
                        $bestYearValue = 0;

                        $thisYear = date('Y');
                        $lastYear = date('Y') - 1;


//print "\n".$thisQuarter."|".$lastQuarter."\n";exit;

                        $isFirst = true;
                        foreach ($yearList as $kk => $year) {
                            if ($isFirst) {
                                $bestYearValue = $year[$ws_json['statisticsField']];
                                $isFirst = false;
                            } else {
                                if ($bestYearValue < $year[$ws_json['statisticsField']]) {
                                    $bestYearValue = $year[$ws_json['statisticsField']];
                                }
                            }

                            if ($thisYear == $year['years']) {
                                $thisYearValue = $year[$ws_json['statisticsField']];
                            }
                            if ($lastYear == $year['years']) {
                                $lastYearValue = $year[$ws_json['statisticsField']];
                            }
                        }
                        $showResult['year']['last'] = $lastYearValue;
                        $showResult['year']['this'] = $thisYearValue;
                        $showResult['year']['best'] = $bestYearValue;
                        // *******年度查询end*******
                        break;

                    default:
                }

                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '计算成功',
                    'list' => $list,
                    'showType' => $showType,
                    'showResult' => $showResult
                );
            }

            // 统计完成之后,drop掉临时表
            $Model->execute(" DROP TABLE IF EXISTS `temp_" . $statistics[0]['ws_name'] . "`");

            // 返回数据
            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r, JSON_UNESCAPED_UNICODE);
            }
            exit;
        }
    }

    /**
     * 根据表单结构ID,取得表单所有字段list
     */
    public function getFormFieldListByFormId()
    {
        // 表单结构ID
        $wff_id = isset($_REQUEST['wff_id']) ? $_REQUEST['wff_id'] : '0';

        if ($wff_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'wff_id参数为空',
            );
        } else {

            $_forms = M("wf_forms", "oa_", 'DB_CONFIG_OA');

            $list = $_forms->field("*")->where("wff_id = {$wff_id}")->select();
            $wff_json = json_decode($list[0]['wff_json'], true);

            // 字段list
            $fieldList = array();
            foreach ($wff_json as $key => $value) {
                $field['name'] = $value['wfw_attr'][0]['name'];
                $field['labelName'] = $value['wfw_attr'][0]['labelName'];
                array_push($fieldList, $field);
            }

            // 将表单数据表的业务字段加入
            $field['name'] = "wfd_user_id";
            $field['labelName'] = "用户ID";
            array_push($fieldList, $field);

            $field['name'] = "user_name";
            $field['labelName'] = "用户名称";
            array_push($fieldList, $field);

            if ($list === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_forms->getlastsql(),
                );
            } else {
                if (empty($list) || empty($fieldList)) {
                    $_r = array(
                        'errorCode' => '2',
                        'errorName' => '没有数据',
                    );
                } else {
                    $_r = array(
                        'errorCode' => '1',
                        'errorName' => '查询成功',
                        'list' => $fieldList,
                    );
                }
            }
        }

//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }

        exit;

    }

    /**
     * 根据表单结构ID,取得表单所有数据list
     */
    public function getFormDataListByFormId()
    {
        // 表单结构ID
        $wff_id = isset($_REQUEST['wff_id']) ? $_REQUEST['wff_id'] : '0';

        if ($wff_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'wff_id参数为空',
            );
        } else {

            $_forms = M("wf_forms", "oa_", 'DB_CONFIG_OA');

            $list = $_forms->field("*")->where("wff_id = {$wff_id}")->select();
            $wff_json = json_decode($list[0]['wff_json'], true);

            // 字段list
            $fieldList = array();
            foreach ($wff_json as $key => $value) {
                $field['name'] = $value['wfw_attr'][0]['name'];
                $field['labelName'] = $value['wfw_attr'][0]['labelName'];
                array_push($fieldList, $field);
            }

            // 取得套用此表单结构的数据
            $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');
            $form_datas = $_wf_form_data->field("wfd_data_json")->where("wfd_form = {$list[0]['wff_id']}")->select();

            // 结果list
            $dataList = array();
            // 遍历数据list
            foreach ($form_datas as $key => $form_data) {
                // 当前数据json
                $wfd_data_json = json_decode($form_data['wfd_data_json'], true);

                // 各数据字段
                foreach ($wfd_data_json as $k => $wfd_data) {
                    foreach ($fieldList as $dd => $field) {
                        if ($field['name'] == $wfd_data['name']) {
                            // 数据字段中加入labelName
                            $wfd_data_json[$k]['labelName'] = $field['labelName'];
                        }
                    }
                }
                array_push($dataList, $wfd_data_json);
            }

            if ($list === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_forms->getlastsql(),
                );
            } else {
                if (empty($list) || empty($fieldList)) {
                    $_r = array(
                        'errorCode' => '2',
                        'errorName' => '没有数据',
                    );
                } else {
                    $_r = array(
                        'errorCode' => '1',
                        'errorName' => '查询成功',
                        'list' => $dataList,
                    );
                }
            }
        }

//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }

        exit;

    }

}