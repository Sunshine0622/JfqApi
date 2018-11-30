<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

	/**
	 * 
	 *
	 */
	// function  __construct(){
	// 	parent::__construct();
	// 	$this->load->database();

	
	// }
	public function index()
	{
		$this->load->view('home');
	}
	public function aso_source(){
		$rearr = $_GET;
		$cpid = $this->uri->segment(4,0);//此cpid为渠道商和我们之间的唯一值
		$sql = "select * from aso_advert where appid = ".$rearr['appid'];
		$res = $this->db->query($sql);
		$list = $res->row_array();
		$data = array();
		$data['cpid'] = $list['cpid'];//此cpid为我们与应用商之间的唯一值
		
		foreach($rearr as $k=>$v){
			$data[$k] = $v;
		}
		if($cpid != 517){
			echo  json_encode(array('code'=>'102','result'=>'cpid error'));
			//如果渠道和我们之间cpid不对返回错误信息
			return false;
		}
		if(md5($data['timestamp'].md5('aiyingli')) != $data['sign']){
			echo  json_encode(array('code'=>'101','result'=>'sign error'));
			//如果我们与渠道商之间的sign错误返回信息
			return false;
		}
		$id = $this->db->insert('aso_source', $data); 
		$inid = $this->db->insert_id();
		$arr = array();
		unset($list['id']);
		$str ='';
		foreach ($list as $k=>$v){
			if($v == 1){
				$str .= "&$k=$data[$k]";
				$arr[$k]=$data[$k];
			}
		}
		$key = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.aiyingli.com/api/aso_advert/?back_id=".$inid."&k=".$data['timestamp']."&sign=".$key;
		$callback = urlencode($callback);
		// $file_contents = file_get_contents($list['url']."?appid=".$list['appid'].$str."&timestamp=".$data['timestamp']."&sign=".$data['sign']."&callback=".$callback);
		
		$a = array('code'=>'0','result'=>'ok');
		$file_contents = json_encode($a);
		$json = json_decode($file_contents,true );
		if($json['result'] =='ok'){
			$sql = "update aso_source set type=1 where id =$inid";
			$this->db->query($sql);
		}
		echo  $file_contents;
		$this->load->view('aso');
		
	}
	public function aso_advert(){
		$callarr = $_GET;
		$md5 = md5($callarr['k'].md5('callback'));
		if($md5 != $callarr['sign']){
			echo  json_encode(array('code'=>'400','result'=>'sign error'));
			return false;
		}
		$sql = "select * from aso_source where id = ".$callarr['back_id'];
		$res = $this->db->query($sql);
		$list = $res->row_array();
		$callback = urldecode($list['callback']);
		$file_contents = file_get_contents($callback);
		$json = json_decode($file_contents,true );
		if($json['result'] =='ok'){
			$sql = "update aso_source set backtype=1 where id =".$callarr['back_id'];
			$this->db->query($sql);
		}
		echo  $file_contents;
		$this->load->view('aso');
	}
}

/* End of file welcome.php */
//http://cp.weile.com/api/abc/cpid/12345/?timestamp=1448777716&userid=12345&sign=cabf0c14d882dc7b810ceada73ee94ef
/* Location: ./application/controllers/welcome.php */