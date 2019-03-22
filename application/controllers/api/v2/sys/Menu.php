<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
//To Solve File REST_Controller not found
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Menu extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Base_model');
        $this->load->model('Menu_model');
        // $this->config->load('config', true);
    }

    public function index_get()
    {
        $this->load->view('login_view');
    }

    public function testapi_get()
    {
        echo "test api ok...";

        echo APPPATH . "\n";
        echo SELF . "\n";
        echo BASEPATH . "\n";
        echo FCPATH . "\n";
        echo SYSDIR . "\n";
        var_dump($this->config->item('rest_language'));
        var_dump($this->config->item('language'));

        var_dump($this->config);

//        $message = [
//            "code" => 20000,
//            "data" => [
//                "__FUNCTION__" =>  __FUNCTION__,
//                "__CLASS__" => __CLASS__,
//                "uri" => $this->uri
//            ],
//
//        ];
//        "data": {
//            "__FUNCTION__": "router_get",
//            "__CLASS__": "User",
//            "uri": {
//                    "keyval": [],
//              "uri_string": "api/v2/user/router",
//              "segments": {
//                        "1": "api",
//                "2": "v2",
//                "3": "user",
//                "4": "router"
//              },
    }

    public function phpinfo_get()
    {
        phpinfo();
    }

    public function testdb_get()
    {
        $this->load->database();
        $query = $this->db->query("show tables");
        var_dump($query);
        var_dump($query->result());
        var_dump($query->row_array());
//         有结果表明数据库连接正常 reslut() 与 row_array 结果有时不太一样
//        一般加载到时model里面使用。
    }

    // 菜单添加操作
    function add_post()
    {
        // 根据token 判断 用户 api url 操作权限
        // var_dump($this->uri->uri_string); // string(19) "api/v2/sys/menu/add"
        // hasperm();

        // $id = $this->post('id'); // POST param
        $parms = $this->post();  // 获取表单参数，类型为数组
        //         var_dump($parms);
        //         var_dump($parms['path']);

        // 参数检验/数据预处理
        // 菜单类型为目录
        if (!$parms['type']) {
            $parms['component'] = 'Layout';
        }

        $result = $this->Base_model->_insert_key('sys_menu', $parms);
        if ($result) {
            // 超级管理员角色自动拥有该菜单功能
            $this->Base_model->_insert_key('sys_role_perm', ["role_id" => 1, "perm_id" => $result]);

            $message = [
                "code" => 20000,
                "type" => 'success',
                "message" => $parms['title'] . ' - 菜单添加成功'
            ];
            $this->set_response($message, REST_Controller::HTTP_OK);
        } else {
            $message = [
                "code" => 20000,
                "type" => 'error',
                "message" => $parms['title'] . ' - 菜单添加失败'
            ];
            $this->set_response($message, REST_Controller::HTTP_OK);
        }
    }

    // 菜单更新操作
    function edit_post()
    {
        // 根据token 判断 用户 api url 操作权限
        // var_dump($this->uri->uri_string); // string(19) "api/v2/sys/menu/add"
        // hasperm();

        // $id = $this->post('id'); // POST param
        $parms = $this->post();  // 获取表单参数，类型为数组
        // var_dump($parms['path']);

        // 参数检验/数据预处理
        if ($parms['id'] == $parms['pid']) {
            $message = [
                "code" => 20000,
                "type" => 'error',
                "message" => '父节点不能是自己'
            ];
            $this->set_response($message, REST_Controller::HTTP_OK);
        }
        // 菜单类型为目录
        if ($parms['type'] == 0) {
            $parms['component'] = 'Layout';
        }
        // 菜单类型为功能按钮时
        if ($parms['type'] == 2) {
            $parms['component'] = '';
            $parms['icon'] = '';
        }

        $id = $parms['id'];
        unset($parms['id']); // 择出索引id
        $where = ["id" => $id];

        if ($this->Base_model->_update_key('sys_menu', $parms, $where)) {
            $message = [
                "code" => 20000,
                "type" => 'success',
                "message" => $parms['title'] . ' - 菜单更新成功'
            ];
            $this->set_response($message, REST_Controller::HTTP_OK);
        } else {
            $message = [
                "code" => 20000,
                "type" => 'error',
                "message" => $parms['title'] . ' - 菜单更新错误'
            ];
            $this->set_response($message, REST_Controller::HTTP_OK);
        }
    }

    // 菜单删除操作
    function del_post()
    {
        // 根据token 判断 用户 api url 操作权限
        // var_dump($this->uri->uri_string); // string(19) "api/v2/sys/menu/add"
        // hasperm();

        $parms = $this->post();  // 获取表单参数，类型为数组
        // var_dump($parms['path']);

        // 参数检验/数据预处理
        // 存在子节点 不能删除返回
        $hasChild = $this->Menu_model->hasChild($parms['id']);

        if ($hasChild) {
            $message = [
                "code" => 20000,
                "type" => 'error',
                "message" => $parms['title'] . ' - 存在子节点不能删除'
            ];
            $this->set_response($message, REST_Controller::HTTP_OK);
        } else {
            // 删除外键关联表 sys_role_perm
            $this->Base_model->_delete_key('sys_role_perm', ['perm_id' => $parms['id']]);

            // 删除基础表 sys_menu
            if ($this->Base_model->_delete_key('sys_menu', $parms)) {
                $message = [
                    "code" => 20000,
                    "type" => 'success',
                    "message" => $parms['title'] . ' - 菜单删除成功'
                ];
                $this->set_response($message, REST_Controller::HTTP_OK);
            } else {
                $message = [
                    "code" => 20000,
                    "type" => 'error',
                    "message" => $parms['title'] . ' - 菜单删除错误'
                ];
                $this->set_response($message, REST_Controller::HTTP_OK);
            }
        }
    }

    // 根据token拉取菜单树
    function info_get()
    {
        // 根据token 判断 用户 api url 操作权限
        // var_dump($this->uri->uri_string); // string(19) "api/v2/sys/menu/add"
        // hasperm();
        $token = 'admin-token';
        $MenuTreeArr = $this->Menu_model->getMenuTree($token, true);
        $MenuTree = $this->treelib->genVueMenuTree($MenuTreeArr, 'id', 'pid', 0);
        $message = [
            "code" => 20000,
            "data" => $MenuTree,
        ];
        $this->set_response($message, REST_Controller::HTTP_OK);
    }

    // 根据token拉取 treeselect 下拉选项菜单
    function treeoptions_get()
    {
        // 根据token 判断用户 api url 操作权限
        // var_dump($this->uri->uri_string); // string(19) "api/v2/sys/menu/add"
        // hasperm();
        $token = 'admin-token';
        $MenuTreeArr = $this->Menu_model->getMenuTreeOptions($token);
        array_unshift($MenuTreeArr, ['id' => 0, 'pid' => -1, 'title' => '顶级菜单']);
        $MenuTree = $this->treelib->genVueMenuTree($MenuTreeArr, 'id', 'pid', -1);

        $message = [
            "code" => 20000,
            "data" => $MenuTree,
        ];
        $this->set_response($message, REST_Controller::HTTP_OK);
    }


    function list_get()
    {
//        $result = $this->some_model();
        $result['success'] = TRUE;

        if ($result['success']) {
            $List = array(
                array('order_no' => '201805138451313131', 'timestamp' => 'iphone 7 ', 'username' => 'iphone 7 ', 'price' => 399, 'status' => 'success'),
                array('order_no' => '300000000000000000', 'timestamp' => 'iphone 7 ', 'username' => 'iphone 7 ', 'price' => 399, 'status' => 'pending'),
                array('order_no' => '444444444444444444', 'timestamp' => 'iphone 7 ', 'username' => 'iphone 7 ', 'price' => 399, 'status' => 'success'),
                array('order_no' => '888888888888888888', 'timestamp' => 'iphone 7 ', 'username' => 'iphone 7 ', 'price' => 399, 'status' => 'pending'),
            );

            $message = [
                "code" => 20000,
                "data" => [
                    "total" => count($List),
                    "items" => $List
                ]
            ];
            $this->set_response($message, REST_Controller::HTTP_OK);
        } else {
            $message = [
                "code" => 50008,
                "message" => 'Login failed, unable to get user details.'
            ];

            $this->set_response($message, REST_Controller::HTTP_OK);
        }

    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */