<?php
/**
 * DataListAction.class.php
 * 数据列表管理相关接口
 * DaMingGe 2018-01-25
 */
import("@.Action.BaseAction");

class DataListAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 数据列表管理列表
     *      查询条件待指定
     *      为减少数据传输此列表中没有具体JSON数据
     */
    public function listWfDataList()
    {
        $wd_module = isset($_REQUEST['wd_module']) ? $_REQUEST['wd_module'] : '0';
        // 实例化model
        $_wf_datalist = M("wf_datalist", "oa_", 'DB_CONFIG_OA');

        // 查询条件待定
        $where = '1=1';
        if ($wd_module != 0) {
            $where = $where . " and wd_module = {$wd_module}";
        }

        // 查询表单列表
        $datalist = $_wf_datalist->field("wd_id,wd_name,wd_name_ch,wd_form,wd_company,wd_module,wd_abled,wd_create_time,wd_create_userid")->where($where)->select();
        if ($datalist === false) {
            // 执行错误
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询错误',
                'errorSql' => $_wf_datalist->getlastsql(),
            );
        } else {
            if (empty($datalist)) {
                // 数据为空
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '没有数据',
                );
            } else {
                // 查询成功
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'list' => $datalist,
                );
            }
        }
        // 返回数据
//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 根据数据列表管理ID取得指定的管理信息
     */
    public function getDataInfoByListID()
    {
        $wd_id = isset($_REQUEST['wd_id']) ? $_REQUEST['wd_id'] : '0';

        if ($wd_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'wd_id参数为空',
            );
        } else {

            // 实例化model
            $_wf_datalist = M("wf_datalist", "oa_", 'DB_CONFIG_OA');

            // 查询列表
            $list = $_wf_datalist->field("*")->where("wd_id = {$wd_id}")->find();
            if ($list === false) {
                // 执行错误
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_wf_datalist->getlastsql(),
                );
            } else {
                if (empty($list)) {
                    // 数据为空
                    $_r = array(
                        'errorCode' => '2',
                        'errorName' => '没有数据',
                    );
                } else {
                    // 查询成功
                    $_r = array(
                        'errorCode' => '1',
                        'errorName' => '查询成功',
                        'list' => $list,
                    );
                }
            }
        }

        // 返回数据
//        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 提交新列表管理信息,根据管理ID修改列表管理信息
     *      使用head实现跨域
     */
    public function editWfDataInfo()
    {
        $wd_id = isset($_REQUEST['wd_id']) ? $_REQUEST['wd_id'] : '0';
        $wd_data = array();
        $wd_data['wd_name'] = $_REQUEST['wd_name'];
        $wd_data['wd_name_ch'] = $_REQUEST['wd_name_ch'];
        $wd_data['wd_form'] = $_REQUEST['wd_form'];
        $wd_data['wd_company'] = $_REQUEST['wd_company'];
        $wd_data['wd_module'] = $_REQUEST['wd_module'];
        $wd_data['wd_abled'] = $_REQUEST['wd_abled'];
        $wd_data['wd_create_userid'] = $_REQUEST['wd_create_userid'];
//        $wd_data['wd_field_json'] = $_REQUEST['wd_field_json'];
        $wd_data['wd_style_json'] = $_REQUEST['wd_style_json'];

        $_wf_datalist = M("wf_datalist", "oa_", 'DB_CONFIG_OA');
        if ($wd_id == '0') {
            //提交新数据
            $wd_data['wd_create_time'] = date("Y-m-d H:i:s");//模块创建时间
            $rs = $_wf_datalist->add($wd_data);
        } else {
            //修改数据
            $rs = $_wf_datalist->where("wd_id = {$wd_id}")->save($wd_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_datalist->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
            );
        }

        header('Access-Control-Allow-Origin:*');

        echo json_encode($_r);
        exit;
    }

    /**
     * 删除数据管理信息
     */
    public function delWfDataInfo()
    {
        $wd_id = $_REQUEST['wd_id'];
        $_wf_datalist = M("wf_datalist", "oa_", 'DB_CONFIG_OA');

        $rs = $_wf_datalist->where("wd_id = {$wd_id}")->delete();
        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '删除失败',
                'errorSql' => $_wf_datalist->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '删除成功',
            );
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
     * 根据数据列表样式管理ID,取得符合条件的数据
     */
    public function getDataListByListId()
    {
        // 列表管理ID
        $wd_id = isset($_REQUEST['wd_id']) ? $_REQUEST['wd_id'] : '0';

        // 搜索条件
        $searchCondition = json_decode($_REQUEST['searchCondition'], true);

        // 搜索关键字
        $searchKeyWord = isset($_REQUEST['searchKeyWord']) ? $_REQUEST['searchKeyWord'] : '';

        if ($wd_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'wd_id参数为空',
            );
        } else {
            // 数据列表管理信息
            $_wf_datalist = M("wf_datalist", "oa_", 'DB_CONFIG_OA');

            $datalist = $_wf_datalist->field("*")->where("wd_id = {$wd_id}")->find();
//print_r($datalist);exit;

            $_forms = M("wf_forms", "oa_", 'DB_CONFIG_OA');

            // 表单结构
            $form = $_forms->field("*")->where("wff_id = {$datalist['wd_form']}")->find();
            // 结构JSON
            $wff_json = json_decode($form['wff_json'], true);

            // 字段list
            $fieldList = array();
            foreach ($wff_json as $key => $value) {
                $field['name'] = $value['wfw_attr'][0]['name'];
                $field['labelName'] = $value['wfw_attr'][0]['labelName'];
                array_push($fieldList, $field);
            }

            // 取得套用此表单结构的数据
            $_wf_form_data = M("wf_form_data", "oa_", 'DB_CONFIG_OA');
            $form_datas = $_wf_form_data->field("wfd_data_json,wfd_id,wfd_user_id,oa_users.user_name")->join("oa_users on user_id = wfd_user_id")->where("wfd_form = {$form['wff_id']}")->select();

//print_r($form_datas);
//exit;
            // 全部字段数据list
            $dataList = array();
            // 遍历原始表单数据list
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
                // 带有labelName的数据list
                $dataList[$key]['data'] = $wfd_data_json;
                // 表单数据表中的其它相关字段
                $dataList[$key]['wfd_id'] = $form_data['wfd_id'];
                $dataList[$key]['wfd_user_id'] = $form_data['wfd_user_id'];
                $dataList[$key]['user_name'] = $form_data['user_name'];
            }

//print_r($dataList);
//            exit;

            if ($dataList === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_forms->getlastsql(),
                );
            } else {
                if (empty($dataList)) {
                    $_r = array(
                        'errorCode' => '2',
                        'errorName' => '没有数据',
                    );
                } else {

                    $resultTemp = array();

                    // 遍历全部字段的数据List
                    foreach ($dataList as $k => $data) {
//print_r($data);exit;
                        // 临时数组,用来组装只显示需要字段的数据list
                        $dataTemp = array();
                        // 遍历本条数据
                        foreach ($data as $kk => $vv) {
                            if ($kk == 'data') {
                                foreach ($vv as $kkk => $vvv) {
                                    $dataTemp[$vvv['name']] = $vvv['value'];
                                }
                            } else {
                                $dataTemp[$kk] = $vv;
                            }

                        }

                        // 放进最终结果List
                        array_push($resultTemp, $dataTemp);
//print_r($dataTemp);exit;
                    }
//print_r($resultTemp);
//exit;
                    // 列表属性
                    $wd_style_json = json_decode($datalist['wd_style_json'], true);
                    // 接口收到的搜索条件为空时,使用配置中默认的条件
                    if (empty($searchCondition) || !isset($searchCondition)) {
                        $searchCondition = $wd_style_json['searchCondition'];
                    }

                    // 关键字搜索字段
                    $searchKeyWordField = $wd_style_json['searchKeyWordField'];
//print("\n");
//print_r($searchCondition);
//exit;

                    // 最终结果数据
                    $result = array();
                    // 筛选条件备选数据
                    $optionsArr = array();
                    // 处理结果数据,根据搜索条件筛选数据
                    foreach ($resultTemp as $kr => $vr) {
                        // 搜索条件中是否有此字段
                        $canAdd = true;

                        foreach ($searchCondition as $ks => $search) {

                            $optionTemp = array();

                            // 此筛选条件有值,且待处理数据中此字段的值符合筛选条件的值
                            if (!empty($search['valueSelected']) && $search['valueSelected'] != $vr[$search['valueField']]) {
                                $canAdd = false;

//                                print("AA\n");
//                                print_r($search);
//                                print("BB\n");
//                                print_r($vr);
                            }

                            foreach ($vr as $vrk => $vrv) {
                                // 搜索条件中有此字段
                                if ($search['nameField'] == $vrk) {
                                    // 筛选条件备选数据
                                    $optionTemp['value'] = $vr[$search['valueField']];
                                    $optionTemp['name'] = $vrv;
                                    $optionsArr[$vrk][] = $optionTemp;


                                }
                            }

                        }

//                        print("AA\n");
//                        print_r($search);
//                        print("BB\n");
//                        print_r($vr);
//                        print("CC\n");
//                        var_dump($canAdd);
//exit;


                        $searchPass = false;
                        // 模糊搜索关键字
                        if (!empty($searchKeyWord) && !empty($searchKeyWordField)) {
                            // 遍历搜索关键字字段
                            foreach ($searchKeyWordField as $key => $keyWord) {
                                // 遍历当前条数据的每个字段
                                foreach ($vr as $vrk => $vrv) {
                                    // 当前字段是关键字搜索字段
                                    if ($keyWord == $vrk) {
//print("CC\n");
//print_r($vrv);
//print("DD\n");
//print_r($searchKeyWord);
                                        // 当前条数据的本字段的值 不等于 关键字
                                        if (stripos($vrv, $searchKeyWord) !== false) {
                                            $searchPass = true;
                                        }
                                    }
                                }
                            }
                        } else {
                            $searchPass = true;
                        }


                        // 此条数据通过筛选条件的过滤
                        if ($canAdd && $searchPass) {
                            // 放进结果数组
                            array_push($result, $vr);
                        }
                    }
//print_r($result);
//print("\n");
//print_r($optionsArr);
//exit;

                    // 处理备选数据项,去重
                    foreach ($optionsArr as $ko => $options) {
                        $optionsArr[$ko] = $this->remove_duplicate($options);
                    }
//print_r($optionsArr);
//exit;
                    // 将整理好的备选数据项,放在搜索条件中
                    foreach ($optionsArr as $ko => $option) {

                        foreach ($searchCondition as $ks => $condition) {
                            if ($condition['nameField'] == $ko) {
                                $allOption = array();
                                $allOption['value'] = "";
                                $allOption['name'] = "全部";
                                array_unshift($option,$allOption);
                                $searchCondition[$ks]['options'] = $option;
                            }
                        }
                    }

                    $wd_style_json['searchCondition'] = $searchCondition;

                    $_r = array(
                        'errorCode' => '1',
                        'errorName' => '查询成功',
                        'list' => $result,
                        'wd_style_json' => $wd_style_json, // 筛选条件
                        'searchKeyWord' => $searchKeyWord,
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

    function remove_duplicate($array)
    {
        $result = array();
        foreach ($array as $key => $value) {
            $has = false;
            foreach ($result as $val) {
                if ($val['value'] == $value['value']) {
                    $has = true;
                    break;
                }
            }
            if (!$has)
                $result[] = $value;
        }
        return $result;
    }

}