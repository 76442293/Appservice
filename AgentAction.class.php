<?php
/**
 * AgentAction.class.php
 * 代理商相关接口
 * DaMingGe 2018-03-20
 */
import("@.Action.BaseAction");

class AgentAction extends BaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据用户ID取得用户代理商以及下属公司列表
     */
    public function getAgentByUserID()
    {
        // 代理商管理员ID
        $au_id = isset($_REQUEST['au_id']) ? $_REQUEST['au_id'] : '0';

        if ($au_id == 0) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'au_id参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r, JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        $_wf_agent = M("wf_agent", "oa_", 'DB_CONFIG_OA');
        $_companys = M("companys", "oa_", 'DB_CONFIG_OA');

        $wf_agent = $_wf_agent->field("oa_wf_agent.*")->join("oa_wf_agent_user agent_user on agent_user.a_id = oa_wf_agent.a_id")
            ->where("agent_user.au_id = {$au_id} ")->find();

        if ($wf_agent === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询错误',
                'errorSql' => $wf_agent->getlastsql(),
            );
        } else if (empty($wf_agent)) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询错误',
                'errorReason' => '管理员不存在',
            );
        } else {

            $agent_company_list = $_companys->field("oa_companys.*")->join("oa_wf_agent_company company on company.company_id = oa_companys.company_id")
                ->where("company.a_id = {$wf_agent['a_id']}")->select();

            $_r = array(
                'errorCode' => '1',
                'errorName' => '查询成功',
                'agent' => $wf_agent,
                'company_list' => $agent_company_list
            );
        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 代理商管理员登录
     */
    public function agentUserLogin()
    {
        // 用户ID
        $au_login_name = $_REQUEST['au_login_name'];
        // 用户密码
        $au_psd = $_REQUEST['au_psd'];

        if (!isset($au_login_name)) {
            $_r = array(
                'errorCode' => '4',
                'errorName' => 'au_login_name参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r, JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        if (!isset($au_psd)) {
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'au_psd参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r, JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        $_wf_agent_user = M("wf_agent_user", "oa_", 'DB_CONFIG_OA');

        // MD5加密
        $au_psd = md5($au_psd);
        $agent_user = $_wf_agent_user->field("*")->where("au_login_name = '{$au_login_name}' and au_psd = '{$au_psd}'")->find();

        if ($agent_user === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '登录错误',
                'errorSql' => $_wf_agent_user->getlastsql(),
            );
        } else {
            if (empty($list)) {
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '登录错误',
                    'errorReason' => '用户登录名称或者密码错误',
                );
            } else {

                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '登录成功',
                    'agent_user' => $agent_user,
                );
            }
        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 创建/编辑 代理商管理员
     */
    public function editAgentUser()
    {
        $au_id = isset($_REQUEST['au_id']) ? $_REQUEST['au_id'] : '0';
        $au_data = array();
        $au_data['au_login_name'] = $_REQUEST['au_login_name'];
        $au_data['a_id'] = $_REQUEST['a_id'];
        $au_data['au_name'] = $_REQUEST['au_name'];
        $au_data['au_mobile'] = $_REQUEST['au_mobile'];
        $au_data['update_time'] = date("Y-m-d H:i:s");
        $au_data['update_au_id'] = $_REQUEST['update_au_id'];

        $_wf_agent_user = M("wf_agent_user", "oa_", 'DB_CONFIG_OA');
        if ($au_id == '0') {
            $au_data['au_psd'] = md5("666666");
            //新增管理员
            $rs = $_wf_agent_user->add($au_data);
        } else {
            //修改管理员
            $rs = $_wf_agent_user->where("au_id = {$au_id}")->save($au_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_agent_user->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
            );
        }
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 根据代理商ID取得管理员列表
     */
    public function listAgentUser()
    {
        $a_id = isset($_REQUEST['a_id']) ? $_REQUEST['a_id'] : '0';

        if ($a_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'a_id参数为空',
            );
        } else {

            $_wf_agent_user = M("wf_agent_user", "oa_", 'DB_CONFIG_OA');

            $list = $_wf_agent_user->field("au_id,au_login_name,a_id,au_name,au_mobile,update_time,update_au_id")->where("a_id = {$a_id}")->select();

            if ($list === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_wf_agent_user->getlastsql(),
                );
            } else {
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'list' => $list,
                );
            }
        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }


    /**
     * 删除管理员
     */
    public function deleteAgentUser()
    {
        $au_id = isset($_REQUEST['au_id']) ? $_REQUEST['au_id'] : '0';
        if ($au_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'au_id参数为空',
            );
        } else {
            $_wf_agent_user = M("wf_agent_user", "oa_", 'DB_CONFIG_OA');

            $rs = $_wf_agent_user->where("au_id = {$au_id}")->delete();
            if ($rs === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '删除错误',
                    'errorSql' => $_wf_agent_user->getlastsql(),
                );
            } else {
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '删除成功',
                );
            }
        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 创建/编辑 代理商(创建者所在代理商必须是总代或代理商  不能是公司,且创建者的<能否创建代理商>的开关是开启状态)
     */
    public function editAgent()
    {
        $a_id = isset($_REQUEST['a_id']) ? $_REQUEST['a_id'] : '0';
        $a_data = array();
        $a_data['a_name'] = $_REQUEST['a_name'];
        $a_data['a_level'] = $_REQUEST['a_level'];
        $a_data['a_parent_id'] = $_REQUEST['a_parent_id'];
        $a_data['create_time'] = date("Y-m-d H:i:s");
        $a_data['is_can_create'] = $_REQUEST['is_can_create'];

        $_wf_agent = M("wf_agent", "oa_", 'DB_CONFIG_OA');
        if ($a_id == '0') {
            //新增代理商
            $rs = $_wf_agent->add($a_data);
        } else {
            //修改代理商
            $rs = $_wf_agent->where("a_id = {$a_id}")->save($a_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_agent->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
            );
        }
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 删除代理商
     */
    public function deleteAgent()
    {
        $a_id = isset($_REQUEST['a_id']) ? $_REQUEST['a_id'] : '0';
        if ($a_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'a_id参数为空',
            );
        } else {
            $_wf_agent_company = M("wf_agent_company", "oa_", 'DB_CONFIG_OA');
            $_wf_agent_user = M("wf_agent_user", "oa_", 'DB_CONFIG_OA');
            $_wf_agent = M("wf_agent_user", "oa_", 'DB_CONFIG_OA');

            $agent_company = $_wf_agent_company->field("*")->where("a_id = {$a_id}")->select();
            $agent_user = $_wf_agent_user->field("*")->where("a_id = {$a_id}")->select();
            $child_agent = $_wf_agent->field("*")->where("a_parent_id = {$a_id}")->select();
            if (!empty($agent_company)) {
                $_r = array(
                    'errorCode' => '3',
                    'errorName' => '删除错误',
                    'errorReason' => '该代理商仍有下属公司,不能删除',
                );
            } elseif (!empty($agent_user)) {
                $_r = array(
                    'errorCode' => '4',
                    'errorName' => '删除错误',
                    'errorReason' => '该代理商仍有下属管理员,不能删除',
                );
            } elseif (!empty($child_agent)) {
                $_r = array(
                    'errorCode' => '5',
                    'errorName' => '删除错误',
                    'errorReason' => '该代理商仍有下属代理商,不能删除',
                );
            } else {

                $_wf_agent = M("wf_agent", "oa_", 'DB_CONFIG_OA');
                $rs = $_wf_agent->where("a_id = {$a_id}")->delete();

                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '删除成功',
                );
            }
        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 给指定的代理商分配公司(公司级别的代理商只能分配一个公司,同一个公司不能属于多个代理商)
     * 调用者代理商级别必须大于0
     */
    public function distributeCompany()
    {
        $a_id = isset($_REQUEST['a_id']) ? $_REQUEST['a_id'] : '0';
        $company_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : '0';


        if (!isset($a_id)) {
            $_r = array(
                'errorCode' => '6',
                'errorName' => 'a_id参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r, JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        if (!isset($company_id)) {
            $_r = array(
                'errorCode' => '5',
                'errorName' => 'company_id参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r, JSON_UNESCAPED_UNICODE);
            }
            exit;
        }


        $_wf_agent = M("wf_agent", "oa_", 'DB_CONFIG_OA');
        $_wf_agent_company = M("wf_agent_company", "oa_", 'DB_CONFIG_OA');

        $agent = $_wf_agent->field("*")->where("a_id = {$a_id}")->find();

        // 代理商不存在
        if (empty($agent)) {
            $_r = array(
                'errorCode' => '4',
                'errorName' => '执行错误',
                'errorReason' => '代理商不存在',
            );
        } else {
            if ($agent['a_level'] == 0) {
                // 代理商的全部下属公司
                $agent_company_list = $_wf_agent_company->field("*")->where("a_id = {$a_id}")->select();
                if (count($agent_company_list) >= 1) {
                    $_r = array(
                        'errorCode' => '3',
                        'errorName' => '执行错误',
                        'errorReason' => '代理商为公司级别,只能有一个下属公司',
                    );

                    if (isset($_GET['callback'])) {
                        echo $_GET['callback'] . '(' . json_encode($_r) . ')';
                    } else {
                        echo json_encode($_r, JSON_UNESCAPED_UNICODE);
                    }
                    exit;
                }
            }

            $agent_company = $_wf_agent_company->field("*")->where("company_id = {$company_id}")->select();

            // 公司是否已归属代理商
            if (empty($agent_company)) {
                $ac_data = array();
                $ac_data['a_id'] = $a_id;
                $ac_data['company_id'] = $company_id;
                $ac_data['create_time'] = date("Y-m-d H:i:s");

                $rs = $_wf_agent_company->add($ac_data);

                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '执行成功',
                );
            } else {

                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '执行错误',
                    'errorReason' => '公司已有归属代理商',
                );
            }


        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 代理商的指定下属公司解除关系
     */
    public function removeCompany()
    {
        $a_id = isset($_REQUEST['a_id']) ? $_REQUEST['a_id'] : '0';
        $company_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : '0';


        if (!isset($a_id)) {
            $_r = array(
                'errorCode' => '4',
                'errorName' => 'a_id参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r, JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        if (!isset($company_id)) {
            $_r = array(
                'errorCode' => '3',
                'errorName' => 'company_id参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r, JSON_UNESCAPED_UNICODE);
            }
            exit;
        }


        $_wf_agent = M("wf_agent", "oa_", 'DB_CONFIG_OA');
        $_wf_agent_company = M("wf_agent_company", "oa_", 'DB_CONFIG_OA');

        // 代理商信息
        $agent = $_wf_agent->field("*")->where("a_id = {$a_id}")->find();

        // 代理商不存在
        if (empty($agent)) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => '执行错误',
                'errorReason' => '代理商不存在',
            );
        } else {

            // 下属公司
            $agent_company = $_wf_agent_company->field("*")->where("company_id = {$company_id} and a_id = {$a_id}")->select();

            // 公司是否已归属代理商
            if (!empty($agent_company)) {

                $rs = $_wf_agent_company->where("company_id = {$company_id} and a_id = {$a_id}")->delete();

                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '执行成功',
                );
            } else {
                $_r = array(
                    'errorCode' => '5',
                    'errorName' => '执行错误',
                    'errorReason' => '代理商和公司不存在归属关系',
                );
            }

        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 指定A代理商为B代理商的下级(B代理商级别必须大于0,调用者为B)
     */
    public function appointChildAgent()
    {
        $parent_a_id = isset($_REQUEST['parent_a_id']) ? $_REQUEST['parent_a_id'] : '0';
        $child_a_id = isset($_REQUEST['child_a_id']) ? $_REQUEST['child_a_id'] : '0';


        if (!isset($parent_a_id)) {
            $_r = array(
                'errorCode' => '7',
                'errorName' => 'parent_a_id参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r, JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        if (!isset($child_a_id)) {
            $_r = array(
                'errorCode' => '6',
                'errorName' => 'child_a_id参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r, JSON_UNESCAPED_UNICODE);
            }
            exit;
        }


        $_wf_agent = M("wf_agent", "oa_", 'DB_CONFIG_OA');
        $_wf_agent_company = M("wf_agent_company", "oa_", 'DB_CONFIG_OA');

        // 上级代理商信息
        $parent_agent = $_wf_agent->field("*")->where("a_id = {$parent_a_id}")->find();
        // 下级代理商信息
        $child_agent = $_wf_agent->field("*")->where("a_id = {$child_a_id}")->find();

        // 代理商不存在
        if (empty($parent_agent)) {
            $_r = array(
                'errorCode' => '5',
                'errorName' => '执行错误',
                'errorReason' => '上级代理商不存在',
            );
        } else if (empty($child_agent)) {
            $_r = array(
                'errorCode' => '4',
                'errorName' => '执行错误',
                'errorReason' => '下级代理商不存在',
            );
        } else {
            if ($parent_agent['a_level'] == 0) {
                $_r = array(
                    'errorCode' => '3',
                    'errorName' => '执行错误',
                    'errorReason' => '上级代理商级别太低',
                );
            } elseif ($child_agent['a_parent_id'] != 0) {
                $_r = array(
                    'errorCode' => '2',
                    'errorName' => '执行错误',
                    'errorReason' => '下级代理商级已有上级',
                );
            } else {

                $child_agent['a_parent_id'] = $parent_a_id;

                // 更新下级代理商的父ID
                $rs = $_wf_agent->where("a_id = {$child_a_id}")->save($child_agent);

                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '执行成功',
                );
            }

        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 解除A代理商为B代理商的下级关系(调用者为B)
     */
    public function removeChildAgent()
    {
        $parent_a_id = isset($_REQUEST['parent_a_id']) ? $_REQUEST['parent_a_id'] : '0';
        $child_a_id = isset($_REQUEST['child_a_id']) ? $_REQUEST['child_a_id'] : '0';


        if (!isset($parent_a_id)) {
            $_r = array(
                'errorCode' => '5',
                'errorName' => 'parent_a_id参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r, JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        if (!isset($child_a_id)) {
            $_r = array(
                'errorCode' => '4',
                'errorName' => 'child_a_id参数缺少',
            );

            if (isset($_GET['callback'])) {
                echo $_GET['callback'] . '(' . json_encode($_r) . ')';
            } else {
                echo json_encode($_r, JSON_UNESCAPED_UNICODE);
            }
            exit;
        }


        $_wf_agent = M("wf_agent", "oa_", 'DB_CONFIG_OA');
        $_wf_agent_company = M("wf_agent_company", "oa_", 'DB_CONFIG_OA');

        // 上级代理商信息
        $parent_agent = $_wf_agent->field("*")->where("a_id = {$parent_a_id}")->find();
        // 下级代理商信息
        $child_agent = $_wf_agent->field("*")->where("a_id = {$child_a_id}")->find();

        // 代理商不存在
        if (empty($parent_agent)) {
            $_r = array(
                'errorCode' => '3',
                'errorName' => '执行错误',
                'errorReason' => '上级代理商不存在',
            );
        } else if (empty($child_agent)) {
            $_r = array(
                'errorCode' => '2',
                'errorName' => '执行错误',
                'errorReason' => '下级代理商不存在',
            );
        } else {

            $child_agent['a_parent_id'] = 0;

            // 更新下级代理商的父ID
            $rs = $_wf_agent->where("a_id = {$child_a_id}")->save($child_agent);

            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
            );

        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }


    /**
     * 根据代理商ID取得下属代理商列表
     */
    public function listChildAgent()
    {
        $a_parent_id = isset($_REQUEST['a_parent_id']) ? $_REQUEST['a_parent_id'] : '0';

        if ($a_parent_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'a_parent_id参数为空',
            );
        } else {

            $_wf_agent = M("wf_agent", "oa_", 'DB_CONFIG_OA');

            $list = $_wf_agent->field("*")->where("a_parent_id = {$a_parent_id}")->select();

            if ($list === false) {
                $_r = array(
                    'errorCode' => '0',
                    'errorName' => '查询错误',
                    'errorSql' => $_wf_agent->getlastsql(),
                );
            } else {
                $_r = array(
                    'errorCode' => '1',
                    'errorName' => '查询成功',
                    'list' => $list,
                );
            }
        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }


    /**
     * 取得所有没有代理商归属的公司列表
     */
    public function listCompanyNoAgent()
    {

        $_wf_agent = M("companys", "oa_", 'DB_CONFIG_OA');

        $list = $_wf_agent->field("oa_companys.*")->join("oa_wf_agent_company ac ON ac.company_id = oa_companys.company_id", "left")
            ->where("ac.ac_id IS NULL")->select();

        if ($list === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询错误',
                'errorSql' => $_wf_agent->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '查询成功',
                'list' => $list,
            );
        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }


    /**
     * 取得所有代理商列表
     */
    public function listAllAgent()
    {

        $_wf_agent = M("wf_agent", "oa_", 'DB_CONFIG_OA');

        $list = $_wf_agent->field("*")->select();

        if ($list === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '查询错误',
                'errorSql' => $_wf_agent->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '查询成功',
                'list' => $list,
            );
        }

        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 修改代理商管理员密码(注意权限:管理员修改自己代码)
     */
    public function editAgentUserPsd()
    {
        $au_id = isset($_REQUEST['au_id']) ? $_REQUEST['au_id'] : '0';
        $update_au_id = isset($_REQUEST['update_au_id']) ? $_REQUEST['update_au_id'] : '0';

        $_wf_agent_user = M("wf_agent_user", "oa_", 'DB_CONFIG_OA');
        if ($au_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'au_id参数为空',
            );
        } else {
            $au_data = array();
            $au_data['au_psd'] = md5($_REQUEST['au_psd']);
            $au_data['update_time'] = date("Y-m-d H:i:s");
            $au_data['update_au_id'] = $update_au_id;
            //修改管理员
            $rs = $_wf_agent_user->where("au_id = {$au_id}")->save($au_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_agent_user->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
            );
        }
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * 重置代理商管理员密码(注意权限:只能重置自己和同一个代理商的管理员密码)
     */
    public function resetAgentUserPsd()
    {
        $au_id = isset($_REQUEST['au_id']) ? $_REQUEST['au_id'] : '0';
        $update_au_id = isset($_REQUEST['update_au_id']) ? $_REQUEST['update_au_id'] : '0';

        $_wf_agent_user = M("wf_agent_user", "oa_", 'DB_CONFIG_OA');
        if ($au_id == 0) {
            // 参数为空
            $_r = array(
                'errorCode' => '2',
                'errorName' => 'au_id参数为空',
            );
        } else {
            $au_data = array();
            $au_data['au_psd'] = md5("666666");
            $au_data['update_time'] = date("Y-m-d H:i:s");
            $au_data['update_au_id'] = $update_au_id;
            //修改管理员
            $rs = $_wf_agent_user->where("au_id = {$au_id}")->save($au_data);
        }

        if ($rs === false) {
            $_r = array(
                'errorCode' => '0',
                'errorName' => '执行错误',
                'errorSql' => $_wf_agent_user->getlastsql(),
            );
        } else {
            $_r = array(
                'errorCode' => '1',
                'errorName' => '执行成功',
            );
        }
        if (isset($_GET['callback'])) {
            echo $_GET['callback'] . '(' . json_encode($_r) . ')';
        } else {
            echo json_encode($_r, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

}