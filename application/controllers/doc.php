<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//header('Content-type: application/json');
class Doc extends CI_Controller {

	/**
	 * 
	 *
	 */
	function  __construct(){
		parent::__construct();
		$this->load->database();

	
	}
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
	public function ApiDoc()
	{

		$this->load->view('doc');
// $default = array('product' => 'shoes', 'size' => 'large', 'color' => 'red');
// $arr = assoc_to_uri();
		// $arr =  $this->uri->uri_to_assoc(2);
		// var_dump($arr);die;
	}

	public function CeDoc()
	{

		$appid   = @intval($_GET['appid']);
		$cpid    = @intval($_GET['cpid']);
		$adid    = @intval($_GET['adid']);

		$sql = "select * from aso_advert as a where a.appid=".$appid." and a.cpid=".$adid;//

		$sql2 = "select * from aso_source_cpid as a where a.cpid=".$cpid;
		// echo $sql;die;
		$res = $this->db->query($sql);
		$data = $res->row_array();
		
		$list = $this->db->query($sql2)->row_array();
		if(empty($data) || empty($list)){
				echo "<script>alert('request error')</script>";
				//echo json_encode(array('code'=>99,'result'=>'接口请求错误'));
				die;
		}
		$data['cp_name'] = $list['name'];
		$data['key']     = $list['key'];
		$this->load->view('doc1',$data);
// $default = array('product' => 'shoes', 'size' => 'large', 'color' => 'red');
// $arr = assoc_to_uri();
		// $arr =  $this->uri->uri_to_assoc(2);
		// var_dump($arr);die;
	}
	public function wfck(){
		$id   = @intval($_GET['id']);
		$key    = trim($_GET['key']);
		$sql2 = "select * from aso_source_cpid as a where a.id=".$id;
		$res = $this->db->query($sql2);
		$data = $res->row_array();

		if($data['key']!=$key){
			echo json_encode(array('code'=>0));
		}else{
			echo json_encode(array('code'=>1));
		}
	}

	public function wf()
	{
		//echo base64_decode($_GET['params']);
		 $appid   = explode('&',base64_decode($_GET['params']))[0];
		$cpid   = @intval(explode('=',explode('&',base64_decode($_GET['params']))[2])[1]);
		
		$adid    = @intval(explode('=',explode('&',base64_decode($_GET['params']))[1])[1]);

		if($appid <=0 || $cpid<=0 || $adid<=0){
			echo "<script>alert('request error')</script>";
				//echo json_encode(array('code'=>99,'result'=>'接口请求错误'));
				die;
		}

		 $json = json_decode(file_get_contents("https://itunes.apple.com/lookup?id=$appid"),true);
        if($json['resultCount']==0){
            $json = json_decode(file_get_contents("https://itunes.apple.com/cn/lookup?id=$appid"),true);
        }
         //var_dump($json);die;
		$sql = "select * from aso_advert as a where a.appid=".$appid." and a.cpid=".$adid;//

		$sql2 = "select * from aso_source_cpid as a where a.cpid=".$cpid;
		// echo $sql;die;
		$res = $this->db->query($sql);
		$data = $res->row_array();
		
		$list = $this->db->query($sql2)->row_array();
		$NewBaseKey = base64_encode($_GET['params'].'&'.$list['key']);
		if($list['key']!=''){
				echo "
		<script src='http://code.jquery.com/jquery-1.8.2.min.js'></script>
		<script>
			var key = prompt('Please enter secret API key:','');
			var url  ='http://jfad.appubang.com/doc/wfck?id={$list['id']}&key='+key;
			$.get(url,{id:{$list['id']},key:key},function(msg){
				if(msg.code!=1){
					alert('key error');
				}else{
					
					window.location.href='http://jfad.appubang.com/doc/wfv?params={$NewBaseKey}';
				}
			},'json')
			
		</script>";
		}else{
		if(empty($data) || empty($list)){
				echo "<script>alert('request error')</script>";
				//echo json_encode(array('code'=>99,'result'=>'接口请求错误'));
				die;
		}
		
        $data['image']   = $json['results'][0]['artworkUrl100'];
        $data['app_name']   = $json['results'][0]['trackName'];
		$data['channel'] = $list['cpid'];
		$data['cp_name'] = $list['name'];
		$data['key']     = $list['key'];
		$this->load->view('doc2',$data);

		}
		
		
	}

	public function wfv()
	{
		
		$yparams = explode('&',base64_decode($_GET['params']))[0];
		$appid   = explode('&',base64_decode(explode('&',base64_decode($_GET['params']))[0]))[0];
		$cpid   = @intval(explode('=',explode('&',base64_decode(explode('&',base64_decode($_GET['params']))[0]))[2])[1]);
		
		$adid    = @intval(explode('=',explode('&',base64_decode(explode('&',base64_decode($_GET['params']))[0]))[1])[1]);

		if($appid <=0 || $cpid<=0 || $adid<=0){
			echo "<script>alert('request error')</script>";
				//echo json_encode(array('code'=>99,'result'=>'接口请求错误'));
				die;
		}
		 $json = json_decode(file_get_contents("https://itunes.apple.com/lookup?id=$appid"),true);
        if($json['resultCount']==0){
            $json = json_decode(file_get_contents("https://itunes.apple.com/cn/lookup?id=$appid"),true);
        }

		$info  ['key']  = explode('&',base64_decode($_GET['params']))[1];
		$sql = "select * from aso_advert as a where a.appid=".$appid." and a.cpid=".$adid;//

		$sql2 = "select * from aso_source_cpid as a where a.cpid=".$cpid;
		// echo $sql;die;
		$res = $this->db->query($sql);
		$data = $res->row_array();
		
		$list = $this->db->query($sql2)->row_array();
		if(isset($info['key']) && trim($info['key'])==$list['key']){
			$_SESSION['key'] = trim($info['key']);

		}
		if(!isset($_SESSION['key']) || $_SESSION['key']!=$list['key']){
			header("location:http://jfad.appubang.com/doc/wf?params=".$yparams);
		}
		if(empty($data) || empty($list)){
				echo "<script>alert('request error')</script>";
				//echo json_encode(array('code'=>99,'result'=>'接口请求错误'));
				die;
		}

       
        $data['image']   = $json['results'][0]['artworkUrl100'];
         $data['app_name']   = $json['results'][0]['trackName'];
		$data['channel'] = $list['cpid'];
		$data['cp_name'] = $list['name'];
		$data['key']     = $list['key'];
		$this->load->view('doc2',$data);
// $default = array('product' => 'shoes', 'size' => 'large', 'color' => 'red');
// $arr = assoc_to_uri();
		// $arr =  $this->uri->uri_to_assoc(2);
		// var_dump($arr);die;
	}

	public function clean_sleep(){
		define('MAX_SLEEP_TIME',600);  
   
		$hostname = "182.92.154.214";  
		$username = "root";  
		$password = "ttttottttomysql";  
		   
		@$connect = mysql_connect($hostname,$username,$password);  
		$result = mysql_query("SHOW PROCESSLIST",$connect);  
		while ($proc = mysql_fetch_assoc($result)) {  
		if ($proc["Command"] == "Sleep" && $proc["Time"] > MAX_SLEEP_TIME) {  
		@mysql_query("KILL " . $proc["Id"],$connect);  
		}  
		} //by www.jbxue.com  
		mysql_close($connect);  
	}

	public function server_check(){
		$hostname = "47.95.28.151";  
		$username = "root";  
		$password = "ttttottttomysql";  
		   
		@$connect = mysql_connect($hostname,$username,$password);
		echo $connect;
		if(!$connect){
			echo 222222;
			//echo exec('service mysqld restart');
			die;
		}else{
			echo 11111;
		}
		mysql_close($connect);

	}
}


function request_get1($url){
    	// 创建一个cURL资源
		$ch  =  curl_init ();

		// 设置URL和相应的选项
		curl_setopt ( $ch ,  CURLOPT_URL ,$url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ( $ch ,  CURLOPT_HEADER ,  0 );
		curl_setopt( $ch ,CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']); // 模拟浏览器请求
		// 抓取URL并把它传递给浏览器
		$data = curl_exec($ch);//运行curl
	    curl_close($ch);
	     return $data;

		// 关闭cURL资源，并且释放系统资源
		// curl_close ( $ch );

    }

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */