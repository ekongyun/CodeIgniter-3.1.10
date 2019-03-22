<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Base_model extends CI_Model
{
    /*
     * 通用数据库操作方法 增删改查
     */
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    function _get_key($key)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
            ->get(config_item('rest_keys_table'))
            ->row();
    }

    function _insert_key($table, $data)
    {
        $this->db->insert($table, $data);
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

    function select($table, $select = '', $where = '', $order = '')
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
        return $query->result();

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

}