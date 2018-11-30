<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
header('Content-type: application/json');
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:GET');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');
//header('Content-type: application/json');
class Imoney extends CI_Controller {

	/**
	 * 
	 *
	 */
	function  __construct(){
		parent::__construct();
		$this->load->database();

	
	}

	function ceshi(){
	 	$appid       = intval($_GET['appid']);
	 	$adid        = intval($_GET['adid']);

	 	$sql = "select ac.name as channel,a.appid,s.sales_name as salesman,a.is_repeat,a.is_source,a.is_submit,a.is_advert from aso_advert a left join aso_sales as s on s.sales_id = a.salesman left join aso_source_cpid as ac on ac.cpid=a.channel where a.is_disable='0' and a.appid = $appid and a.cpid=$adid";
		//echo $sql;
		$res = $this->db->query($sql);
		$list = $res->row_array();//查出来的是厂商app的信息
		$str = '接口模式:';
		if($list['is_repeat']==1){
			$str .= '排重 ';
		}

		if($list['is_source']==1){
			$str .= '点击 ';
		}

		if($list['is_submit']==1){
			$str .= '上报 ';
		}
		if($list['is_advert']==1){
			$str .= '回调';
		}
		$str   = '('.$str.')';
		$list['channel']  = $list['channel'].$str;
		if(empty($list)){
			echo json_encode(array('code'=>99,'message'=>'adid error','data'=>'没有数据'));die;
		}else{
			$list['adid']  = $adid;
		   
			echo json_encode(array('code'=>100,'message'=>'adid normal','data'=>$list));die;
		}
	 }


	/** 
	 *  与阿福对接 判断广告id 是否可用
	 * 
	 * @param    int    AppId   AppId
	 	@param   int    Adid   广告id 
	 * @return    array    params 
	 */ 

	 function check_adid(){
	 $appid       = intval($_GET['appid']);
	 	$adid        = intval($_GET['adid']);

	 	$sql = "select ac.name as channel,a.appid,s.sales_name as salesman,a.is_repeat,a.is_source,a.is_submit,a.is_advert from aso_advert a left join aso_sales as s on s.sales_id = a.salesman left join aso_source_cpid as ac on ac.cpid=a.channel where a.is_disable='0' and a.appid = $appid and a.cpid=$adid";
		//echo $sql;
		$res = $this->db->query($sql);
		$list = $res->row_array();//查出来的是厂商app的信息
		$str = '';
		if($list['is_repeat']==1){
			$str .= '排重 ';
		}

		if($list['is_source']==1){
			$str .= '点击 ';
		}

		if($list['is_submit']==1){
			$str .= '上报 ';
		}
		if($list['is_advert']==1){
			$str .= '回调';
		}
		$str   = '('.$str.')';
		$list['channel']  = $list['channel'].$str;
		if(empty($list)){
			echo json_encode(array('code'=>99,'message'=>'adid error','data'=>'没有数据'));die;
		}else{
			$list['adid']  = $adid;
		   
			echo json_encode(array('code'=>100,'message'=>'adid normal','data'=>$list));die;
		}
	 }


	 function AdidList(){
	    $appid       = intval($_GET['appid']);
	 	$salesId     = intval($_GET['sales_id']);
	 	if($salesId==55){
	 		$sql = "select ac.name,a.app_name,a.cpid,a.appid,a.is_advert,a.is_repeat,a.is_source,a.is_submit from aso_advert as a left join aso_source_cpid  as ac on ac.cpid=a.channel  where a.appid=$appid and a.is_disable='0'";
	 	}else{
	 		$sql = "select ac.name,a.app_name,a.cpid,a.appid,a.is_advert,a.is_repeat,a.is_source,a.is_submit from aso_advert as a left join aso_source_cpid  as ac on ac.cpid=a.channel  where a.appid=$appid and a.salesman = $salesId and a.is_disable='0'";
	 	}

		//echo $sql;
		$res = $this->db->query($sql);
		$list = $res->result_array();//查出来的是厂商app的信息

		foreach($list as $k=>$val){
			$list[$k]['adid']  = $list[$k]['cpid'];

			unset($list[$k]['cpid']);
		}
		if(empty($list)){
			echo json_encode(array('code'=>99,'data'=>'没有数据'));die;
		}else{
			
			echo json_encode(array('code'=>100,'data'=>$list));die;
		}
	 }

}	