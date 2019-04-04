<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Base_model extends CI_Model
{
    /*
     * 1. 通用数据库操作方法 增删改查
     * 2. 权限/菜单模型部分
     */
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /************************
     * 数据库操作模型部分
     ***********************/
    /**
     * 获取值
     * $table: 表名
     * $select: 查找项， $select = '*' 或 $select = 'id,title'
     * $where: 条件项 $where= 'id=1' 或 $where = 'id=1 and title="blah"'
     * $order: $order = 'id desc'
     * 返回值是数组
     */
    function _get_key($table, $select = '', $where = '', $order = '')
    {
        if ($select) {
            $this->db->select($select);
        }
        if ($where) {
            $this->db->where($where);
        }
        if ($order) {
            $this->db->order_by($order);
        }

        $query = $this->db->get($table);
        return $query->result_array();
    }

    function _insert_key($table, $data)
    {
        $this->db->insert($table, $data);
        // 如果$table没有配置自增主键，则insert_id返回值为0
        return $this->db->insert_id();
    }

    function _update_key($table, $data, $where)
    {
        $this->db->where($where);
        $this->db->update($table, $data);
        return $this->db->affected_rows();
    }

    function _key_exists($table, $where)
    {
        $this->db->where($where);
        $this->db->from($table);
        return $this->db->count_all_results();
    }

    function _delete_key($table, $where)
    {
        $this->db->where($where);
        $this->db->delete($table);
        return $this->db->affected_rows();
    }


    function total($table, $field, $keyword)
    {
        $sql = "select count(*) numrows from $table where $field like '%$keyword%' ";
        $query = $this->db->query($sql);
        if (($query->row_array()) == null) {
            return 0;
        } else {
            $result = $query->result_array();
            return $result;
        }
    }
    /************************
     * 数据库操作模型部分结束
     ***********************/

    /************************
     * 权限模型部分
     ***********************/

    /** 根据perm_type 获取关联的基础表名称
     * @return array
     */
    function getBaseTable($perm_type)
    {
        $sql = "select r_table from sys_perm_type where type = '" . $perm_type . "'";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /**
     *  根据token, perm_type 获取 perm_id,perm_type,r_id
     * @return array
     */
    function getPerm($basetable, $token, $perm_type, $menuCtrl)
    {
        $hasCtrl = $menuCtrl ? "" : " WHERE basetbl.type != 2";

        $sql = "SELECT
                        t.id perm_id,
                        basetbl.*
                    FROM
                        (
                            SELECT
                                p.*
                            FROM
                                sys_user_token ut,
                                sys_user_role ur,
                                sys_role_perm rp,
                                sys_perm p,
			                    sys_role r
                            WHERE
                                ut.token = '" . $token . "'
                            AND ur.user_id = ut.user_id
                            AND rp.role_id = ur.role_id
                            AND p.id = rp.perm_id
                            AND r.id = ur.role_id
                            AND r.status=1
                            AND p.perm_type = '" . $perm_type . "'
                        ) t
                    LEFT JOIN " . $basetable . " basetbl ON t.r_id = basetbl.id" . $hasCtrl . " order by basetbl.listorder";

        $query = $this->db->query($sql);
        return $query->result_array();

    }

    function getCtrlPerm($token)
    {
        $sql = "SELECT
                        basetbl.path
                    FROM
                        (
                            SELECT
                                p.*
                            FROM
                                sys_user_token ut,
                                sys_user_role ur,
                                sys_role_perm rp,
                                sys_perm p,
			                    sys_role r
                            WHERE
                                ut.token = '" . $token . "'
                            AND ur.user_id = ut.user_id
                            AND rp.role_id = ur.role_id
                            AND p.id = rp.perm_id
                            AND r.id = ur.role_id
                            AND r.status=1
                            AND p.perm_type = 'menu'
                        ) t
                    LEFT JOIN  sys_menu basetbl ON t.r_id = basetbl.id 
                    WHERE basetbl.type = 2";

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /**
     * 判断 token 是否过期
     * // 50008:非法的token; 50012:其他客户端登录了;  50014:Token 过期了;
     */
    function TokenExpired($token)
    {
        $sql = "SELECT
                *
            FROM
                `sys_user_token`
            WHERE
                token = '$token'";
        $query = $this->db->query($sql);
        $Arr = $query->result_array();
        if (empty($Arr)) {
            return ['code' => 50008, 'message' => "非法的token"];
        }

        $now = time();
        if ($now > $Arr[0]['expire_time']) {
            return ['code' => 50014, 'message' => "Token 过期了"];
        }

        // TODO: 当token合法时，自动续期?
        $update_time = time();
        $expire_time = $update_time + 2 * 60 * 60;  // 2小时过期
        $data = [
            'expire_time' => $expire_time,
            'last_update_time' => $update_time
        ];
        $this->_update_key('sys_user_token', $data, ['token' => $token]);

        return ['code' => 20000, 'message' => "Token 合法"];
    }

    /***********************
     * 权限模型部分结束
     ***********************/

    /************************
     * 菜单模型部分
     ************************/

    /**
     * 菜单是否拥有子节点
     */
    function hasChildMenu($id)
    {
        $sql = "SELECT getChildLst(" . $id . ") children";
        $query = $this->db->query($sql);
        // var_dump($query->result_array()[0]["children"]);
        // string(14) "$,2,5,6,8,9,10"
        $array = explode(",", $query->result_array()[0]["children"]);

        if (count($array) > 2) {
            return true;
        } else {
            return false;
        }
    }

    /***********************
     * 菜单模型部分结束
     ***********************/


//    /***********************
//     * 角色模型部分
//     ************************/
//
//    /**
//     * 获取所有角色列表
//     */
//    function getRoleList()
//    {
//        $sql = "SELECT
//                    r.*
//                FROM
//                    sys_role r
//                 ORDER BY
//                    r.listorder";
//        $query = $this->db->query($sql);
//        return [
//            "items" => $query->result_array(),
//            "total" => count($query->result_array())
//            ];
//    }
//
//    /**
//     *  获取所有菜单并加入对应的权限id
//     */
//    function getAllMenus()
//    {
//        $sql = "SELECT
//                    p.id perm_id,
//                    m.*
//                FROM
//                    sys_menu m,
//                    sys_perm p
//                WHERE
//                    p.perm_type = 'menu'
//                AND p.r_id = m.id
//                ORDER BY
//                    m.listorder";
//        $query = $this->db->query($sql);
//        return $query->result_array();
//    }
//
//    /**
//     * 获取所有角色列表 带perm_id
//     */
//    function getAllRoles()
//    {
//        $sql = "SELECT
//                    r.*, p.id perm_id
//                FROM
//                    sys_role r,
//                    sys_perm p
//                WHERE
//                    r.id = p.r_id
//                AND p.perm_type = 'role'
//                ORDER BY
//                    r.listorder";
//        $query = $this->db->query($sql);
//        return [
//            "items" => $query->result_array(),
//            "total" => count($query->result_array())
//        ];
//    }
//
//
//    function getRoleMenu($RoleId)
//    {
//        $sql = "SELECT
//                    p.id perm_id,
//                    m.*
//                FROM
//                    sys_menu m,
//                    sys_perm p,
//                    sys_role_perm rp
//                WHERE
//                    rp.perm_id = p.id
//                AND p.perm_type = 'menu'
//                AND p.r_id = m.id
//                AND rp.role_id = ". $RoleId ."
//                ORDER BY
//                    m.listorder";
//        $query = $this->db->query($sql);
//        return $query->result_array();
//    }
//
//    /**
//     * 获取角色拥有的角色权限
//     * @param $RoleId
//     * @return mixed
//     */
//    function getRoleRole($RoleId)
//    {
//        $sql = "SELECT
//                    p.id perm_id,
//                    r.*
//                FROM
//                    sys_role r,
//                    sys_perm p,
//                    sys_role_perm rp
//                WHERE
//                    rp.perm_id = p.id
//                AND p.perm_type = 'role'
//                AND p.r_id = r.id
//                AND rp.role_id =". $RoleId . "
//                ORDER BY
//                    r.listorder";
//        $query = $this->db->query($sql);
//        return $query->result_array();
//    }
//
//    function getRolePerm($RoleId)
//    {
//        $sql = "SELECT
//                    role_id, perm_id
//                FROM
//                    sys_role_perm
//                WHERE
//                    role_id =" . $RoleId;
//        $query = $this->db->query($sql);
//        return $query->result_array();
//    }
//
//    /***********************
//     * 角色模型部分结束
//     ***********************/
//
//    /***********************
//     * 用户模型部分
//     ***********************/
//    /**
//     * 获取所有用户列表
//     */
//    function getUserList()
//    {
//        $sql = "SELECT
//                    u.*
//                FROM
//                    sys_user u
//                 ORDER BY
//                    u.listorder";
//        $query = $this->db->query($sql);
//        return [
//            "items" => $query->result_array(),
//            "total" => count($query->result_array())
//        ];
//    }
//    /***********************
//     * 用户模型部分结束
//     ***********************/
}