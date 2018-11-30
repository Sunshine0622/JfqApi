<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	function  __construct(){
		parent::__construct();
		$this->load->database();

	
	}
	public function index()
	{
		
		// echo phpinfo();
		// $mem = new Memcache;
		// $mem->connect('101.200.91.203',11211) or die('memcache connect failed');
		// // $mem->set('981025209CA50EADA-B75E-40CA-BA6A-6E426193', 'CA50EADA-B75E-40CA-BA6A-6E426193', 0, 360);
		// // $sql="select appid,idfa from aso_submit limit 0,1000";
		// // $res = $this->db->query($sql);
		// // $result = $res->result_array();
		// // foreach($result as $key=>$v){
		// // 	$mem->set($v['appid'].$v['idfa'], $v['idfa'], 0, 360);
		// // }
		// $val = $mem->get('9677655824271E1E6-6DCD-4F1B-BD04-B637E76467CF');
		// $a=$mem->getStats();
		// echo '<pre>';
		// var_dump($a) ;die;
		// echo 'Get key1 value: ' . $val .'<br>';


	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */