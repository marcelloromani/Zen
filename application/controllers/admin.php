<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
        $this->load->model('dojo_model');
		$this->load->library('tank_auth');
		$this->load->helper(array('form', 'url'));
		$this->load->model(array('charter_model'));
        
	}

	function index()
	{
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		} else {
			$data['user_id']	= $this->tank_auth->get_user_id();
			$data['username']	= $this->tank_auth->get_username();
            $data['user_data'] =  $this->tank_auth->get_user_data();
            $data['pagename'] = 'Unverified';

			$this->load->view('template/header', $data);
    		$this->load->model(array('charter_model'));

			if ($data['user_data']->role == 0){
                if(isset($_POST['verify'])){
                    foreach($_POST['verify'] as $id => $state) {
                        $this->dojo_model->verify($id, $state, $this->tank_auth->get_user_id());
                    }
                    $this->load->view('template/alert', array('type' => 'success', 'title' => 'Verified Checked Dojos', 'message' => 'Awww yeah...'));
                }
                if(isset($_POST['delete'])) {
                    foreach($_POST['delete'] as $id => $state) {
                        $this->dojo_model->delete($id, $state==="delete"?1:0, $this->tank_auth->get_user_id());
                    }
                    $this->load->view('template/alert', array('type' => 'success', 'title' => 'Deleted Dojos', 'message' => ''));
                }
                $data['dojos'] = $this->dojo_model->get_with_user(null, false, true);
				$this->load->view('admin/dojo', $data);
			} else {
				$data['type'] =  "error";
				$data["title"] = "Awkward, You're not an admin...";
				$data["message"] ="Moving swiftly on";
				$data["url"] = "/";
                $this->load->view('template/message', $data);
			}
			$this->load->view('template/footer', $data);
		}
	}
	
	function old_stats()
	{
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		} else {
			$data['user_id']	= $this->tank_auth->get_user_id();
			$data['username']	= $this->tank_auth->get_username();
            $data['user_data'] =  $this->tank_auth->get_user_data();

			$this->load->view('template/header', $data);

			if ($data['user_data']->role == 0){
			    $verified_dojos = $this->dojo_model->get(null, true);
			    $all_dojos = $this->dojo_model->get();
			    $numbers = array();
			    $totals = array();
			    foreach(
			        count_by_continent_country($verified_dojos) as
			        $continent => $countries
			    ) {
			        foreach($countries as $country => $value) {
			            $numbers[$continent][$country]['verified'] = $value;
		            }
		        }
		        foreach(
			        count_by_continent_country($all_dojos) as
			        $continent => $countries
			    ) {
			        foreach($countries as $country => $value) {
			            $numbers[$continent][$country]['total'] = $value;
		            }
		        }
		        $data['stats'] = $numbers;
		        $data['charter'] = Charter_Model::count();
                $this->load->view('admin/stats', $data);
			}
			$this->load->view('template/footer', $data);
		}
	}
    
    function stats()
    {
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		} else {
			$data['user_id']	= $this->tank_auth->get_user_id();
			$data['username']	= $this->tank_auth->get_username();
            $data['user_data'] =  $this->tank_auth->get_user_data();

			$this->load->view('template/header', $data);

			if ($data['user_data']->role == 0){
                $q = $this->db->query('
                    SELECT 
                        `continent`, `alpha2` as \'country\', `country_name`, 
                        COUNT(DISTINCT CASE WHEN (`verified` = 1 AND `stage` != 4) THEN `id` END) AS \'active_verified\',
                        COUNT(DISTINCT CASE WHEN `verified` = 1 THEN `id` END) AS \'verified\',
                        COUNT(DISTINCT `id`) as \'all\'
                    FROM `dojos` d

                    JOIN `countries` c
                    ON d.`country` = c.`alpha2`
                    
                    WHERE d.`deleted` = 0

                    GROUP BY c.`alpha2`
                    ORDER BY c.`continent` ASC, c.`alpha2` ASC
                ');
		        foreach($q->result() as $row)
                {
                    $data['stats'][real_get_continent_name($row->continent)][get_country_name($row->country)] = array(
                        'active_verified' => $row->active_verified,
                        'verified' => $row->verified,
                        'total' => $row->all
                    );
                }
		        $data['charter'] = Charter_Model::count();
                $this->load->view('admin/stats', $data);
			}
			$this->load->view('template/footer', $data);
		}
    }

	function dojos()
	{
		if (!$this->tank_auth->is_logged_in()) {
			redirect('/auth/login/');
		} else {
			$data['user_id']	= $this->tank_auth->get_user_id();
			$data['username']	= $this->tank_auth->get_username();
            $data['user_data'] =  $this->tank_auth->get_user_data();
            $data['pagename'] = 'Verified';

			$this->load->view('template/header', $data);

			if ($data['user_data']->role == 0){
                if(isset($_POST['verify'])){
                    foreach($_POST['verify'] as $id => $state) {
                        $this->dojo_model->verify($id, $state, $this->tank_auth->get_user_id());
                    }
                    $this->load->view('template/alert', array('type' => 'success', 'title' => 'Verified Checked Dojos', 'message' => 'Awww yeah...'));
                }
                if(isset($_POST['delete'])) {
                    foreach($_POST['delete'] as $id => $state) {
                        $this->dojo_model->delete($id, $state==="delete"?1:0, $this->tank_auth->get_user_id());
                    }
                    $this->load->view('template/alert', array('type' => 'success', 'title' => 'Deleted Dojos', 'message' => ''));
                }
                $data['dojos'] = $this->dojo_model->get_with_user(null, true);
				$this->load->view('admin/dojo', $data);
			} else {
				$data['type'] =  "error";
				$data["title"] = "Awkward, You're not an admin...";
				$data["message"] ="Moving swiftly on";
				$data["url"] = "/";
                $this->load->view('template/message', $data);
			}
			$this->load->view('template/footer', $data);
		}
	}
	
	function edit($id)
	{
		if(!$this->tank_auth->is_logged_in()){
			redirect("auth/login");
		} else {
		    $this->load->library(array('form_validation'));
			$data['user_id']	= $this->tank_auth->get_user_id();
			$data['username']	= $this->tank_auth->get_username();
            $data['user_data'] =  $this->tank_auth->get_user_data();
            $data['id'] = $id;
            $data['is_admin'] = true;
            
			if ($data['user_data']->role == 0){
			    //check if the has a Dojo
    			$this->load->view('template/header', $data);
                

				$data['errors'] = array();
				$data['dojo_data'] = $this->dojo_model->get($id);

				if ($this->form_validation->run('dojo')) { // validation ok

					if (!is_null($dojo_id = $this->dojo_model->update(
					$id,
					$this->form_validation->set_value('dojo_name'),
					$this->form_validation->set_value('time'),
					$this->form_validation->set_value('country'),
					$this->form_validation->set_value('location'),
					$this->form_validation->set_value('coordinates'),
					$this->form_validation->set_value('email'),
					$this->form_validation->set_value('google_group'),
					$this->form_validation->set_value('website'),
					$this->form_validation->set_value('twitter'),
					$this->form_validation->set_value('notes'),
					$this->form_validation->set_value('eb_id'),
					$this->form_validation->set_value('need_mentors'),
					$this->form_validation->set_value('stage'),
					$this->form_validation->set_value('supporter_image'),
    				$this->form_validation->set_value('private')
					))) {									// success

						redirect('/dojo/'.$dojo_id);

					} else {//failure
						$errors = $this->dojo_model->get_error_message();
						foreach ($errors as $k => $v)	$data['errors'][$k] = $v." - ";
						$this->load->view('dojo/edit', $data);
					}
				} else {
					$this->load->view('dojo/edit', $data);
				}
    			$this->load->view('template/footer', $data);
		    }
		}
	}
	public function is_country($country) {
	    $this->form_validation->set_message('is_country', 'You must select a real country...');
	    return array_key_exists($country,get_countries());
	}
	
	
}

/* End of file admin.php */
/* Location: ./application/controllers/admin.php */
