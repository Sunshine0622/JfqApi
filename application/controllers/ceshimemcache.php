<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ceshimemcache extends CI_Controller {

	/**
	 * 
	 *
	 */
	var $mem;
	function  __construct(){
		parent::__construct();
		$this->load->database();
		$this->load->helper('url');
		$this->mem = new Memcache;
		$this->mem->connect('127.0.0.1',11211) or die('memcache connect failed');
		// $this->load->library('class_name');

	
	}
	public function index()
	{	
		
		// $i=0;
		// while($i<268000){
		// 	$sql="select appid,idfa from aso_submit limit $i,1000";
		// 	$res = $this->db->query($sql);
		// 	$result = $res->result_array();
			
		// 	foreach($result as $key=>$v){
		// 		 $this->mem->set($v['appid'].$v['idfa'], $v['idfa'], 0, 0);
		// 	}
		// 	$i=$i+1000;
		// }
		// $j=0;
		// while($j<2000){
		// 	$sql="select appid,idfa from aso_submit2 limit $j,1000";
		// 	$res = $this->db->query($sql);
		// 	$a = $res->result_array();
			
		// 	foreach($a as $key=>$v){
		// 		 $this->mem->set($v['appid'].$v['idfa'], $v['idfa'], 0, 0);
		// 	}
		// 	$j=$j+1000;
		// }
		// $k=120000;
		// while($k<140000){
		// 	$sql="select * from aso_source limit $k,1000";
		// 	$res = $this->db->query($sql);
		// 	$b = $res->result_array();
			
		// 	foreach($b as $key=>$v){
		// 		 $this->mem->set($v['cpid'].$v['appid'].$v['idfa'], $v, 0, 0);
		// 	}
		// 	$k=$k+1000;
		// }
		// //--------------------
		
		// 	$sql="select * from aso_advert";
		// 	$res = $this->db->query($sql);
		// 	$val = $res->result_array();
			
		// 	foreach($val as $key=>$v){
		// 		 $this->mem->set($v['appid'], $v, 0, 0);
		// 	}
		// // 	//------
		// 	$sql="select * from aso_source_cpid";
		// 	$res = $this->db->query($sql);
		// 	$list = $res->result_array();
			
		// 	foreach($list as $key=>$v){
		// 		 $this->mem->set($v['cpid'], $v, 0, 0);
		// 	}
			

		$this->mem->set('name','5178786115322760564C-96DD-42BC-A910-76112D93F338');
			$this->mem->add("myarr", array('appid'=>'454545', 'cpid'=>34, "ccc", "ddd")); //存数组
	print_r($this->mem->get("myarr")['appid']);
	$this->mem->flush();
		// $val = $this->mem->get('name2');
		// var_dump($val);
		// echo '<pre>';
		// $a = $this->mem->getStats();
		// var_dump($a);
		// echo 'Get key1 value: ' . $val .'<br>';
	}
	public function aso_source(){
		$rearr = $_GET;
		$cpid = $this->uri->segment(4,0);//此cpid为渠道商和我们之间的唯一值
		$list = $this->mem->get($rearr['appid']);//缓存查出aso_advert来value是数组
		if(empty($list)){//如果为空就查数据库
			$sql = "select * from aso_advert where appid = ".$rearr['appid'];
			$res = $this->db->query($sql);
			$list = $res->row_array();//查出来的是厂商app的信息
			if(empty($list)){
				echo  json_encode(array('code'=>'102','result'=>'appid error'));
				die;//如果appid不对返回错误信息
			}else{
				$this->mem->set($rearr['appid'], $list, 0, 0);//如果数据库有，缓存没有写入缓存
			}
		}

		$data = array();
		$data['cpid'] = $cpid;//此cpid为我们与厂商之间的唯一值
		foreach($rearr as $k=>$v){
			$data[$k] = $v;
		}

		$sour_cpid = $this->mem->get($cpid);//查出来以后value是数组存渠道的信息
		if(empty($sour_cpid)){//如果为空就查数据库
			$sql = "select * from aso_source_cpid where cpid = ".$cpid;//查找渠道信息
			$res = $this->db->query($sql);
			$sour_cpid = $res->row_array();
			if(empty($sour_cpid)){
				echo  json_encode(array('code'=>'102','result'=>'cpid error'));
				//如果渠道和我们之间cpid不对返回错误信息
				die;;
			}else{
				$this->mem->set($cpid, $sour_cpid, 0, 0);
			}
		}
		if($sour_cpid['key'] !=''){//key不为空的就是带sign,为空的就是渠道不支持sign验证
			if(md5($data['timestamp'].$sour_cpid['key']) != $data['sign']){
				echo  json_encode(array('code'=>'101','result'=>'sign error'));
				//如果我们与渠道商之间的sign错误返回信息
				return false;
			}
		}
		if(!isset($rearr['timestamp'])){
			$data['timestamp'] = time();
		}
		if($list['cpid']==1){//如果厂商的cpid为1，就是我们自己来做排重只对接渠道，不对接厂商，只做排重不回调
			// $sql = "select * from aso_source where appid = ".$rearr['appid']." and idfa='".$data['idfa']."'";
			// $sql = "select count(*) as s from aso_submit where appid = ".$rearr['appid']." and idfa='".$data['idfa']."'";
			// $res = $this->db->query($sql);
			// $result = $res->row_array();
			$result = $this->mem->get($rearr['appid'].$data['idfa']);
			
			if(empty($result)){
				$a = array('code'=>'0','result'=>'ok');
				$file_contents = json_encode($a);
				//写入缓存
				$this->mem->set($cpid.$data['appid'].$data['idfa'], $data, 0, 0);
				$id = $this->db->insert('aso_source', $data); 
				$inid = $this->db->insert_id();
				echo  $file_contents;
				mysql_close();
				die;
			}else{
				$file_contents = array('code'=>'102','result'=>'idfa repeat');
				$data['json'] =json_encode($file_contents);
				$id = $this->db->insert('aso_source_log', $data); 
				echo  json_encode($file_contents);
				mysql_close();
				//如果我们与渠道商之间的idfa是重复的就错误返回信息
				die;
			}
		}else{
			$s ='source_'.$rearr['appid'];
			$this->$s($data,$list);
			mysql_close();
			die;
		}
	}
	// //回调
	public function aso_advert(){
		$callarr = $_GET;
		if($callarr['sign'] != md5($callarr['k'].md5('callback'))){
			echo '{"resultCode":-1,"errorMsg":"sign error"} ';die;
		}
		$sql = "select * from aso_source where id=".$callarr['back_id']." and appid=".$callarr['appid']." and idfa='".$callarr['idfa']."'";//查找渠道的回调地址
		$res = $this->db->query($sql);
		$list = $res->row_array();
		if(empty($list)){
			echo '{"resultCode":-1,"errorMsg":"callback noExist"} ';die;
		}
		$callback = urldecode($list['callback']);
		$file_contents = file_get_contents($callback);
		$json = json_decode($file_contents,true );
		$result = array('id'=>$callarr['back_id'],'cpid'=>$list['cpid'],'appid'=>$callarr['appid'],'idfa'=>$callarr['idfa'],'timestamp'=>time(),'type'=>2);
		if( $json['errorMsg']='ok'){//回调值成功，修改任务表中的任务状态，完成任务
			$this->db->insert('aso_submit',$result);
			$result['json']=$file_contents;
			$this->db->insert('aso_submit_log',$result);
			echo $file_contents;
			mysql_close();
			die;
		}else{
			$result['json']=$file_contents;
			$this->db->insert('aso_submit_log',$result);
			mysql_close();
			echo  $file_contents;die;
		}
		
	}
	public function aso_IdfaRepeat(){
		$rearr = $_GET;
		$cpid = $this->uri->segment(4,0);//此cpid为渠道商和我们之间的唯一值
		$list = $this->mem->get($rearr['appid']);//从缓存读取app信息
		if(empty($list)){
			$sql = "select * from aso_advert where appid = ".$rearr['appid'];
			$res = $this->db->query($sql);
			$list = $res->row_array();//查出来的是厂商app的信息
			if(empty($list)){
				echo  json_encode(array('code'=>'102','result'=>'appid error'));
				return false;//如果appid不对返回错误信息
			}else{
				$this->mem->set($rearr['appid'], $list, 0, 0);
			}
		}
		$data = array();
		$data['cpid'] = $cpid;//此cpid为我们与应用商之间的唯一值
		$sour_cpid = $this->mem->get($cpid);
		foreach($rearr as $k=>$v){
			$data[$k] = $v;
		}
		if(empty($sour_cpid)){
			$sql = "select * from aso_source_cpid where cpid = ".$cpid;//查找渠道信息
			$res = $this->db->query($sql);
			$sour_cpid = $res->row_array();
			if(empty($sour_cpid)){
				echo  json_encode(array('code'=>'102','result'=>'cpid error'));
				//如果渠道和我们之间cpid不对返回错误信息
				return false;
			}else{
				$this->mem->set($cpid, $sour_cpid, 0, 0);
			}
		}
		if($list['cpid']==2 or $list['cpid']==3){
			$s ='IdfaRepeat_'.$list['cpid'];
			$this->$s($data,$list);
			mysql_close();die;
		}
		if($list['cpid']==1){//如果厂商的cpid为1，就是我们自己来做排重只对接渠道，不对接厂商，只做排重不回调
			$result = $this->mem->get($rearr['appid'].$data['idfa']);
			if(empty($result)){
				$a = array($rearr['idfa']=>'1');
				$file_contents = json_encode($a);
			}else{
				$file_get_contents = array($rearr['idfa']=>'0');
				$file_contents =json_encode($file_get_contents);
			}
			$log=array('cpid'=>$cpid,'appid'=>$rearr['appid'],'idfa'=>$rearr['idfa'],'json'=> $file_contents ,'date'=>time());
			$this->db->insert('aso_IdfaRepeat_log',$log);
			echo  $file_contents;
			mysql_close();die;
		}else{
			$s ='IdfaRepeat_'.$rearr['appid'];
			$this->$s($data,$list);
			mysql_close();die;
		}
		
	}
	// //做完任务上报接口
	public function aso_Submit(){
		$rearr = $_GET;
		$cpid  = $this->uri->segment(4,0);//此cpid为渠道商和我们之间的唯一值
		$list = $this->mem->get($rearr['appid']);//从缓存读取app信息
		if(empty($list)){
			$sql = "select * from aso_advert where appid = ".$rearr['appid'];
			$res = $this->db->query($sql);
			$list = $res->row_array();//查出来的是厂商app的信息
			if(empty($list)){
				echo  json_encode(array('code'=>'102','result'=>'appid error'));
				return false;//如果appid不对返回错误信息
			}else{
				$this->mem->set($rearr['appid'], $list, 0, 0);
			}
		}
		$sour_cpid = $this->mem->get($cpid);

		if(empty($sour_cpid)){
			$sql = "select * from aso_source_cpid where cpid = ".$cpid;//查找渠道信息
			$res = $this->db->query($sql);
			$sour_cpid = $res->row_array();
			if(empty($sour_cpid)){
				echo  json_encode(array('code'=>'102','result'=>'cpid error'));
				//如果渠道和我们之间cpid不对返回错误信息
				return false;
			}else{
				$this->mem->set($cpid, $sour_cpid, 0, 0);
			}
		}
		if($sour_cpid['key'] !=''){//key不为空的就是带sign,为空的就是渠道不支持sign验证
			if(md5($rearr['timestamp'].$sour_cpid['key']) != $rearr['sign']){
				echo  json_encode(array('code'=>'101','result'=>'sign error'));
				//如果我们与渠道商之间的sign错误返回信息
				return false;
			}
		}
		if($list['cpid']==2 or $list['cpid']==3){
			$s ='submit_'.$list['cpid'];
			// $s ='submit_'.$rearr['appid'];
			$rearr['cpid']=$cpid;
			$this->$s($rearr,$list);
			mysql_close();die;
		}
		// $sql = "select cpid,idfa,appid from aso_source where cpid=".$cpid." and appid = ".$rearr['appid']." and idfa='".$rearr['idfa']."'";
		// $res = $this->db->query($sql);
		// $result = $res->row_array();
		$result = $this->mem->get($cpid.$rearr['appid'].$rearr['idfa']);
		$rearr['cpid']=$cpid;
		$rearr['type']=1;
		$rearr['timestamp']=time();
		if($list['cpid']==1){//如果厂商的cpid为1，就是我们自己来做排重只对接渠道，不对接厂商，只做排重点击上报
			if(empty($result)){//等于空就是没有做任务，返回错误值
				$a = array('code'=>'103','result'=>'idfa noExist');
				$file_contents = json_encode($a);
				$rearr['json']=$file_contents;
				$this->db->insert('aso_submit_log',$rearr);
				echo  $file_contents;
				mysql_close();
				die;

			}else{
				//写入submit
				$data=array('cpid'=>$result['cpid'],'appid'=>$result['appid'],'idfa'=>$result['idfa'],'timestamp'=>time(),'type'=>1);
				//完成任务写入缓存
				$this->mem->set($data['appid'].$data['idfa'], $data, 0, 0);
				$this->db->insert('aso_submit',$data);
				echo  json_encode(array('code'=>'0','result'=>'ok'));
				mysql_close();
				die;
			}
		}else{
			$s ='submit_'.$rearr['appid'];
			$this->$s($result,$rearr);
			mysql_close();die;
		}
	}
	//巴士壹佰 点击 
	/*$data  array() 请求参数
	* $list  array() app信息
	* return json    
	*/
	function source_1008110025($data,$list){
		$key   = 'e6a47e16d1f8a11869f8142ddc8ee9da';
		$time  = time();
		$sign  = md5($time.$key);

		$value = 'appid='.$data['appid'].'&idfa='.$data['idfa'].'&ip='.$data['ip'].'&tamp='.$time.'&sign='.$sign;
		
		$file_contents   = file_get_contents($list['url'].$value);
		
		$json  = json_decode($file_contents,true );
		if($json['result']='ok'){
			$this->mem->set($data['cpid'].$data['appid'].$data['idfa'], $data, 0, 0);
			$id = $this->db->insert('aso_source', $data); 
			$inid = $this->db->insert_id();
			echo $file_contents;
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  $file_contents;
		}

	}
	// //巴士壹佰 排重 
	// /*$data  array() 请求参数
	// * $list  array() app信息
	// * return json    
	// */
	function IdfaRepeat_1008110025($data,$list){
		$file_contents = file_get_contents($list['IdfaRepeat_url']."&idfa=".$data['idfa']);
		$json = json_decode($file_contents,true );
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);

		if($json[$data['idfa']]==1){
				$a = array($data['idfa']=>'1');
				$contents = json_encode($a);
				echo  $contents;
				
			}else{
				echo  json_encode(array($data['idfa']=>'0'));
			}
		
	}
	// //巴士壹佰 上报null 
	// /*$result 未需要上报广告主，只渠道上报我们
	// * return json    
	// */
	function  submit_1008110025($result,$rearr){
		if(empty($result)){//等于空就是没有做任务，返回错误值
				$a = array('code'=>'103','result'=>'idfa noExist');
				$file_contents = json_encode($a);
				$rearr['json']=$file_contents;
				$this->db->insert('aso_submit_log',$rearr);
				echo  $file_contents;
			}else{
				$result['timestamp'] = time();
				$result['type'] = 1;
				$data=array('cpid'=>$result['cpid'],'appid'=>$result['appid'],'idfa'=>$result['idfa'],'timestamp'=>time(),'type'=>1);
				//完成任务写入缓存
				$this->mem->set($data['appid'].$data['idfa'], $data, 0, 0);
				$this->db->insert('aso_submit',$data);
				// $sql = "update aso_source set submit=1 where id =".$result['id'];
				// $this->db->query($sql);
				echo  json_encode(array('code'=>'0','result'=>'ok'));
			}

	}
	// //俄罗斯方块 点击
	function  source_1086911361($data,$list){
		$url ='http://www.p-dragon.com/weshare_wx/adReq.do?srcid=S_APYB&ENCODE_DATA=';
		$key = '390770b3f76ae639';
		if(empty($data['callback'])){
			echo'{"resultCode":-1,"errorMsg":"callback error"}'; die;  
		}
		$this->mem->set($data['cpid'].$data['appid'].$data['idfa'], $data, 0, 0);
		$id = $this->db->insert('aso_source', $data); 
		$inid = $this->db->insert_id();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.aiyingli.com/api/aso_advert/?back_id=".$inid."&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);
		$str='&reqtype=0&appid=BLOCKL&idfa='.$data['idfa'].'&device=no&os=no&isbreak=0&callback='.$callback;
		$r = Crypt::encrypt($key,$str);
		$r = Base64_Encode($r);
		$url=$url.$r;
		$file_contents=file_get_contents($url);
		$json  = json_decode($file_contents,true );
		if($json['errorMsg']='ok' ){
			echo $file_contents;die;
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  $file_contents;die;
		}
	}
	
	//闯奇分包排重
	function IdfaRepeat_2($data,$list){
		if(!isset($data['ip'])){
			echo  json_encode(array('error'=>'ip error'));die;
		}
		$file_contents = file_get_contents($list['IdfaRepeat_url']."&idfa=".$data['idfa']."&ip=".$data['ip']);
		echo $file_contents;
		$json = json_decode($file_contents,true );
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if($json[$data['idfa']]==1){
				$a = array($data['idfa']=>'0');//我们这里返回0代表失败不可做任务
				$contents = json_encode($a);
				echo  $contents;
				
			}else{
				//成功返回
				echo  json_encode(array($data['idfa']=>'1'));//这里返回1代表成功
			}
	}
	// //闯奇分包上报
	function submit_2($data,$list){
		if(!isset($data['ip'])){
			echo  json_encode(array('error'=>'ip error'));die;
		}

		$file_contents = file_get_contents($list['submit_url']."&idfa=".$data['idfa']."&ip=".$data['ip']);
		echo $file_contents;
		$json = json_decode($file_contents,true );
		if($json['success']){//上报成功
				unset($data['sign']);
				unset($data['ip']);
				$data['timestamp']=time();
				$data['type'] = 1;
				$this->mem->set($data['appid'].$data['idfa'], $data, 0, 0);
				$this->db->insert('aso_submit2',$data);
				echo  json_encode(array('code'=>'0','result'=>'ok'));
				
			}else{//失败 
				unset($data['sign']);
				unset($data['ip']);
				$data['timestamp']=time();
				$data['type'] = 1;
				$data['json'] =$file_contents;
				$this->db->insert('aso_submit_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false'));//这里返回1代表成功
			}
	}
	// //懒猫分包排重
	function IdfaRepeat_3($data,$list){
		$file_contents = file_get_contents($list['IdfaRepeat_url']."&idfa=".$data['idfa']);
		$json = json_decode($file_contents,true );
		echo $file_contents;
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		unset($data['sign']);
		//渠道返回：成功返回0  失败返回1
		if($json[$data['idfa']]==1){
				$a = array($data['idfa']=>'0');//我们这里返回0代表失败不可做任务
				$contents = json_encode($a);
				echo  $contents;
				
			}else{
				//成功返回
				echo  json_encode(array($data['idfa']=>'1'));//这里返回1代表成功
			}
	}
	// //懒猫分包上报
	function submit_3($data,$list){
		$file_contents = file_get_contents($list['submit_url']."&idfa=".$data['idfa']);
		echo $file_contents;
		$json = json_decode($file_contents,true );
		unset($data['sign']);
		if($json['status']==1){//上报成功
				unset($data['ip']);
				$data['timestamp']=time();
				$data['type'] = 1;
				$this->mem->set($data['appid'].$data['idfa'], $data, 0, 0);
				$this->db->insert('aso_submit2',$data);
				echo  json_encode(array('code'=>'0','result'=>'ok'));
				
			}else{//失败 
				unset($data['ip']);
				$data['timestamp']=time();
				$data['type'] = 1;
				$data['json'] =$file_contents;
				$this->db->insert('aso_submit_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false'));//这里返回1代表成功
			}
	} 
}
Class Crypt{
	const CIPHER = MCRYPT_RIJNDAEL_128;//算法
	const MODE = MCRYPT_MODE_CBC;//模式
	//俄罗斯方块
	/**
	* 加密 默认为 AES 128 CBC
	* @param string $key 密钥
	* @param string $plaintext 需加密的字符串
	* @param string $cipher 算法
	* @param string $mode 模式
	* @return binary
	*/
	static  public  function encrypt($key, $plaintext, $cipher = self::CIPHER, $mode = self::MODE ) {
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(self::CIPHER, self::MODE),MCRYPT_RAND);
		$padding = 16 - (strlen($plaintext) % 16);
		$plaintext .= str_repeat(chr($padding), $padding);
		$ciphertext = mcrypt_encrypt(self::CIPHER, $key, $plaintext, self::MODE, $iv);
		$ciphertext = $iv . $ciphertext;
		return $ciphertext;
	}
}
/* End of file welcome.php */
//http://cp.weile.com/api/abc/cpid/12345/?timestamp=1448777716&userid=12345&sign=cabf0c14d882dc7b810ceada73ee94ef
/* Location: ./application/controllers/welcome.php */