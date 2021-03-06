<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Charter_Model extends CI_Model
{
    const AGREEMENT_VERSION = 2;
    const AGREEMENT_VERSION_DATE = "6th December 2013";
    
    public $user_id;
    public $full_name;
    public $ip_address;
    public $timestamp;
    public $agreement_version;
    
    public function __construct($user_id = null, $agreement_version = self::AGREEMENT_VERSION) {
        if(!is_null($user_id)) {
            $this->get($user_id,$agreement_version);
        }
        if($this->tank_auth->is_logged_in()) {
            if(!isset($this->user_id)) $this->user_id = $this->tank_auth->get_user_data()->user_id;
        }
        if(!isset($this->ip_address)) $this->ip_address = $this->input->ip_address();
        if(!isset($this->agreement_version)) $this->agreement_version = self::AGREEMENT_VERSION;
    }
    
    public function get($user_id,$agreement_version = null) {
        $this->db->where('user_id',$user_id);
        if(!is_null($agreement_version)) $this->db->where('agreement_version',$agreement_version);
        $query = $this->db->get('charter_agreement');
        $this->_exchange_array($query->row_array());
        return $this;
    }
    
    public static function userHasSigned($user_id, $agreement_version = self::AGREEMENT_VERSION) {
        $ci =& get_instance();
        $query = $ci->db->where(array('user_id'=>$user_id,'agreement_version'=>$agreement_version))->get('charter_agreement');
        return $query->num_rows()?true:false;
    }

    public function save() {
        // If timestamp isn't set, then set it
        if(!isset($this->timestamp)) $this->timestamp = date('Y-m-d H:i:s');
        
        if(!isset($this->id)) {
            if($this->db->insert('charter_agreement',$this)) {
                $this->id = $this->db->insert_id();
                return true;
            } else {
                return false;
            }
        } else {
            $this->db->where('id',$this->id);
            if($this->db->update('charter_agreement',$this)) {
                return true;
            } else {
                return false;
            }
        }
    }
    
    public static function count($agreement_version = self::AGREEMENT_VERSION) {
        $ci =& get_instance();
        if(!is_null($agreement_version)) $ci->db->where('agreement_version',$agreement_version);
        $ci->db->select('COUNT(DISTINCT `user_id`) as count');
        $query = $ci->db->get('charter_agreement');
        $r = $query->result();
        return $r[0]->count;
    }
    
    private function _exchange_array($array) {
        $this->user_id = $array['user_id']?:NULL;
        $this->full_name = $array['full_name']?:NULL;
        $this->ip_address = $array['ip_address']?:NULL;
        $this->timestamp = $array['timestamp']?:NULL;
        $this->agreement_version = $array['agreement_version']?:NULL;
    }
}