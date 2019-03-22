<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Menu_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // 生成菜单树 $type = true 时，带控件按钮
    function getMenuTree($token, $type)
    {
//        $token 根据token 获取用户 id

        $hasCtrl = $type ? "" : " AND m.type != 2 ";

        $sql = "SELECT DISTINCT
                    m.id,
                    m.pid,
                    m.name,
                    m.path,
                    m.component,
                    m.type,
                    m.title,
                    m.icon,
                    m.redirect,
                    m.hidden,
                    m.listorder
                FROM
                    sys_menu m,
                    sys_user u,
                    sys_user_role ur,
                    sys_role_perm rm
                WHERE
                    m.id = rm.perm_id
                AND ur.role_id = rm.role_id
                AND ur.user_id = u.id
                AND m.`status` = 1 " . $hasCtrl .
            " AND u.id = 1
                    ORDER BY
                        m.listorder";

        $query = $this->db->query($sql);

        return $query->result_array();
    }

    // 新建编辑菜单时，下拉选项，配置父节点操作
    function getMenuTreeOptions()
    {
        $sql = "SELECT DISTINCT
                    m.id,
                    m.pid,
                    m.title
                FROM
                    sys_menu m,
                    sys_user u,
                    sys_user_role ur,
                    sys_role_perm rm
                WHERE
                    m.id = rm.perm_id
                AND ur.role_id = rm.role_id
                AND ur.user_id = u.id
                AND m.`status` = 1 
                AND m.type != 2 
                AND u.id = 1
                    ORDER BY
                        m.listorder";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    function getCtrlPerm($token)
    {
        $sql = "SELECT DISTINCT
                    m.path
                 FROM
                    sys_menu m,
                    sys_user u,
                    sys_user_role ur,
                    sys_role_perm rm
                WHERE
                    m.id = rm.perm_id
                AND ur.role_id = rm.role_id
                AND ur.user_id = u.id
                AND m.`status` = 1 
                AND m.type = 2 
                AND u.id = 1";

        $query = $this->db->query($sql);

        return $query->result_array();
    }

    function hasChild($id)
    {
        $sql = "SELECT getChildLst(" . $id . ") children";
        $query = $this->db->query($sql);
        // var_dump($query->result_array()[0]["children"]);
        // string(14) "$,2,5,6,8,9,10"
        $array = explode(",", $query->result_array()[0]["children"]);

        if (count($array) > 2){
            return true;
        } else {
            return false;
        }
    }

}