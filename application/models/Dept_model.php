<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dept_model extends CI_Model
{
    /**
     * 机构模型部分
     */
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * 获取所有机构列表
     */
    function getDeptList()
    {
        $sql = "SELECT
                    d.id,
                    d.pid,
                    d.name,
                    d.aliasname,
                    d.listorder,
                    d.status
                FROM
                    sys_dept d
                ORDER BY
                    d.listorder";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /**
     * @param $Id
     * @return bool
     * 含有子节点返回真
     */
    function hasChildDept($Id){
        $sql ="SELECT * FROM `sys_dept` where pid=" . $Id;
        $query = $this->db->query($sql);
        if(empty($query->result_array())) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
}