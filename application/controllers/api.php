
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
header('Content-type: application/json');
class  Api extends CI_Controller {

	/**
	 * 
	 *
	 */
	public $db2;
	function  __construct(){
		parent::__construct();
		$this->load->database();

		$this->db2=$this->load->database('db2',TRUE);
	}

	public function index()
	{
		$this->load->view('home');
	}

	public function aso_cesi(){
		echo 333;
	}
	
	//点击接口
	public function aso_source(){
		$rearr = $_GET;
		$cpid  = $this->uri->segment(4,0);//此cpid为渠道商和我们之间的唯一值
		//判断CP 渠道是否存在
		$sql   = "select * from aso_source_cpid where cpid=$cpid";
		
		$res   = $this->db->query($sql);
		$cp_res= $res->row_array();
		if(empty($cp_res)){
			echo json_encode(array('code'=>99,'message'=>'未知渠道或CP'));die;
		}
		$rearr['cpid']  = $cpid;
		//判断广告是否存在或下线
		$adid  = intval($rearr['adid']);//获取广告id
		$appid = intval($rearr['appid']);//获取appid
		$sql   = "select * from aso_advert where is_disable='0' and appid =$appid and cpid=$adid";
		
		$res   = $this->db->query($sql);
		$list  = $res->row_array();
		if(empty($list)){
			echo json_encode(array('code'=>101,'message'=>'广告未存在或已下线'));die;
		}

		if($list['is_source']==0){
			echo json_encode(array('code'=>'-1','message'=>'Interface not open'));die;
		}

		
		//判断指定参数是否存在
		if(!isset($rearr['ip'])){
			echo json_encode(array('code'=>102,'message'=>'ip noisset'));die;
		}

		if(!isset($rearr['idfa'])){
			echo json_encode(array('code'=>103,'message'=>'idfa noisset'));die;
		}
		if(!isset($rearr['timestamp'])) $rearr['timestamp']=time();
		if(!isset($rearr['reqtype'])) $rearr['reqtype']=1;
		if(!isset($rearr['device'])){
			$rearr['device']='iphone';
		}else{ 
			$rearr['device']=str_replace(' ','',$rearr['device']);
		}//将设备空格去掉;
		if(!isset($rearr['os'])) $rearr['os']='未知';
		if(!isset($rearr['isbreak'])) $rearr['isbreak']=0;
		if(!isset($rearr['keywords'])) $rearr['keywords']='未知';
		if(!isset($rearr['sign'])) $rearr['sign']='';

		//判断签名
		if($cp_res['key'] !=''){//key不为空的就是带sign,为空的就是渠道不支持sign验证
			if(md5($rearr['timestamp'].$cp_res['key']) != $rearr['sign']){
				echo  json_encode(array('code'=>'104','result'=>'sign error'));die;
			}
		}

		if($rearr['adid']==1){
			$sql = "select count(*) as s from aso_submit where appid = ".$rearr['appid']." and idfa='".$rearr['idfa']."'";
			$res = $this->db2->query($sql);
			$result = $res->row_array();

			if(empty($result['s'])){
				$a = array('code'=>'0','result'=>'ok');
				$file_contents = json_encode($a);
				$id = $this->db->insert('aso_source', $rearr); 
				$inid = $this->db->insert_id();
				echo  $file_contents;
				mysql_close();
				die;
			}else{
				$file_contents = array('code'=>'102','result'=>'idfa repeat');
				$data['json'] =json_encode($file_contents);
				$id = $this->db->insert('aso_source_log', $rearr); 
				echo  json_encode($file_contents);
				mysql_close();
				//如果我们与渠道商之间的idfa是重复的就错误返回信息
				die;
			}
		}else{
			if($list['api_cat']==1){
				$s ='source_'.$rearr['adid'];
			
				$this->$s($rearr,$list);
				mysql_close();die;
			}else{
				//获取接口请求方式
				$request_method  = explode('%',$list['source_value'])[2];
				//获取接口响应key值
				$key_value       = explode('%',$list['source_value'])[0];
				//获取接口请求成功响应值
				$TrueValue       = explode('%',$list['source_value'])[1];
				//获取关键词加密方式
				$ktype           = explode('%',$list['source_value'])[3];
				//取出CP点击接口
				$source_url      = $list['source_url'];
				//回调接口
				
			    $callback        = urlencode("http://asoapi.appubang.com/api/aso_advert/?k=".$rearr['timestamp']."&idfa=".$rearr['idfa']."&appid=".$rearr['appid']."&sign=".md5($rearr['timestamp'].md5('callback')));
			    $source_url      = str_replace('{1}',$rearr['appid'],$source_url);
				$source_url      = str_replace('{2}',$rearr['idfa'],$source_url);
				$source_url      = str_replace('{3}',$rearr['ip'],$source_url);
				$source_url      = str_replace('{4}',$rearr['device'],$source_url);
				$source_url      = str_replace('{5}',$ktype==0?$rearr['keywords']:urlencode($rearr['keywords']),$source_url);
				$source_url      = str_replace('{6}',$rearr['os'],$source_url);
				$source_url      = str_replace('{7}',$callback,$source_url);
				$source_url      = str_replace('{8}',md5($rearr['timestamp'].$list['key']),$source_url);
				$source_url      = str_replace('{9}',$rearr['timestamp'],$source_url);
				$keys            = str_replace('idfa',$rearr['idfa'],$key_value);
				$keys            = explode('.',$keys);
				$KCount          = count(explode('.',$key_value));


				if($request_method==1){
					$file_contents = $this->request_get($source_url,$rearr);

			   		 $json          = json_decode($file_contents,true);
			   
				}else{
					
					 $sourceUrl    = explode('?',$source_url)[0];
					 $sourceParams = explode('&',explode('?',$source_url)[1]);

					 foreach($sourceParams as $k=>$val){
					 	$data[explode('=',$val)[0]] = explode('=',$val)[1];
					 }
					if($list['post_type']==1){
					 	$file_contents  = $this->request_post2($sourceUrl,json_encode($data));
					 }else{
					 	$file_contents  = $this->request_post($sourceUrl,$data);
					 }
					
					$json          = json_decode($file_contents,true);
					
				}
				$error=  $this->db->get_where('aso_error',array('appid'=>$rearr['appid'],'adid'=>$rearr['adid']))->row_array();
				if($error){
					$NowReCo   = $error['source_request_count'];
					$this->db->update('aso_error',array('source_request_count'=>$NowReCo+1),array('id'=>$error['id']));
				}else{
					$this->db->insert('aso_error',array('appid'=>$rearr['appid'],'adid'=>$rearr['adid'],'source_request_count'=>1));
					$error=  $this->db->get_where('aso_error',array('appid'=>$rearr['appid'],'adid'=>$rearr['adid']))->row_array();
				}
				
				if($TrueValue=='auto'){
					$this->db->insert('aso_source', $rearr); 
					echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
				}else{
					if($KCount==1){
				    	if($json[$keys[0]]==$TrueValue){
				    		$this->db->insert('aso_source', $rearr); 
							echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
				    		
				    	}else{

				    		$rearr['json'] =$file_contents;
				    		
							$this->db->insert('aso_source_log', $rearr);
							$this->db->update('aso_error',array('source_error_count'=>$error['source_error_count']+1),array('id'=>$error['id']));
							echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
				    	}
				    }else if($KCount==2){
				    	if($json[$keys[0]][$keys[1]]==$TrueValue){
				    		$this->db->insert('aso_source', $rearr); 
							echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
				    	}else{
				    		$rearr['json'] =$file_contents;
				    		$this->db->update('aso_error',array('source_error_count'=>$error['source_error_count']+1),array('id'=>$error['id']));
							$this->db->insert('aso_source_log', $rearr); 
							echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
				    	}
				    }else{
				    	if($json[$keys[0]][$keys[1]][$keys[2]]==$TrueValue){
				    		$this->db->insert('aso_source', $rearr); 
							echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
				    	}else{
				    		$rearr['json'] =$file_contents;
				    		$this->db->update('aso_error',array('source_error_count'=>$error['source_error_count']+1),array('id'=>$error['id']));
							$this->db->insert('aso_source_log', $rearr); 
							echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
				    	}
				    }
				}
				
			}
			
		}
	}
	//回调
	public function aso_advert(){
		
		$callarr = $_GET;
		

		//$sql = "select * from aso_source where id=".$callarr['back_id']." and appid=".$callarr['appid']." and idfa='".$callarr['idfa']."'";//查找渠道的回调地址
		$sql = "select id,adid,cpid,appid,idfa,callback from aso_source where appid=".$callarr['appid']." and idfa='".$callarr['idfa']."'" . " ORDER BY id DESC LIMIT 1";//
		// echo $sql;die;
		$res = $this->db->query($sql);
		$list = $res->row_array();
		if(empty($list)){
			echo '{"resultCode":-1,"errorMsg":"callback noExist"} ';die;
		}

		$data_al['appid']        = $callarr['appid'];
		$data_al['idfa']         = $callarr['idfa'];
		$data_al['timestamp']    = time();
		$data_al['cpid']         = $list['cpid'];
		if(isset($callarr['sign'])){
			if($callarr['sign'] != md5($callarr['k'].md5('callback'))){
				$data_al['error']  = '{"resultCode":-1,"errorMsg":"sign error"} ';
				$this->db->insert('aso_advert_log', $data_al);
				echo '{"resultCode":-1,"errorMsg":"sign error"} ';die;
			}else{
				$this->db->insert('aso_advert_log', $data_al);
			}
		}
		


		$url = $list['callback'];
		$pos = strrpos($list['callback'], "imoney.one");
		if($pos !== 0) {
			$url = str_replace("imoney.one", "eimoney.com", $list['callback']);
		}
		
		$file_contents = $this->request_get($url);
		
		$json = json_decode($file_contents,true );
		$result = array('adid'=>$list['adid'],'cpid'=>$list['cpid'],'appid'=>$callarr['appid'],'idfa'=>$callarr['idfa'],'timestamp'=>time(),'type'=>2);

		/*记录到本地激活库*/
		
		$this->db2->insert('aso_submit2',$result);

		/*回调值处理*/
		if($list['cpid']==217){
			if(!$json['c']){//回调值成功，完成任务
				$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
				
				echo  json_encode(array('success'=>true,'message'=>'ok'));
				mysql_close();
				die;
			}else{
				$result['json']=$file_contents;
				$this->db->insert('aso_submit_log',$result);
				mysql_close();
				echo  json_encode(array('success'=>false,'message'=>'error'));die;
			}
		}
		if($list['cpid']==512){
			if($json['msg'] == "success"){//回调值成功，完成任务
				$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
				
				echo  json_encode(array('success'=>true,'message'=>'ok'));
				mysql_close();
				die;
			}else{
				$result['json']=$file_contents;
				$this->db->insert('aso_submit_log',$result);
				mysql_close();
				echo  json_encode(array('success'=>false,'message'=>'error'));die;
			}
		}
		if($list['cpid']==909){
				$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
			    
				echo  json_encode(array('success'=>true,'message'=>'ok'));
				mysql_close();
				die;
		}
		if($list['cpid']==7166){
			$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
			
				echo  json_encode(array('success'=>true,'message'=>'ok'));
				mysql_close();
				die;
		}
		if($list['cpid']==406 && !$json['c']){
			$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
			
			echo  json_encode(array('success'=>true,'message'=>'ok'));
			mysql_close();
			die;
		}
		if($list['cpid']==465 && $file_contents == "ok"){
			$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
			
			echo  json_encode(array('success'=>true,'message'=>'ok'));
			mysql_close();
			die;
		}
		if($list['cpid']==963 && $json['statusCode'] == 200){
			$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
			
			echo  json_encode(array('success'=>true,'message'=>'ok'));
			mysql_close();
			die;
		}
		if($list['cpid']==517 && $json['code'] == 0){
			$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
			
			echo  json_encode(array('success'=>true,'message'=>'ok'));
			mysql_close();
			die;
		}
		if($list['cpid']==1356 && $json['status'] == 1){
			$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
			
			echo  json_encode(array('success'=>true,'message'=>'ok'));
			mysql_close();
			die;
		}

		if($list['cpid']==10013 && $json['status'] == "success"){
			$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
			
			echo  json_encode(array('success'=>true,'message'=>'ok'));
			mysql_close();
			die;
		}

		if($list['cpid']==10014 && $json['code'] == 0){
			$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
			
			echo  json_encode(array('success'=>true,'message'=>'ok'));
			mysql_close();
			die;
		}

		if($list['cpid']==10018 && $json['code'] == 200){
			$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
			echo  json_encode(array('success'=>true,'message'=>'ok'));
			mysql_close();
			die;
		}

		if($list['cpid']==333 && $json['code'] == 1){
			$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
			
			echo  json_encode(array('success'=>true,'message'=>'ok'));
			mysql_close();
			die;
		}

		if($list['cpid']==10016 && $json['statusCode'] == 200){
			$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
			
			echo  json_encode(array('success'=>true,'message'=>'ok'));
			mysql_close();
			die;
		}
		$this->db->update('aso_source',array('type'=>2,'activetime'=>time()),array('id'=>$list['id']));
		if(isset($json['success']) && $json['success']){//回调值成功，完成任务
			
			
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
		$cpid  = $this->uri->segment(4,0);//此cpid为渠道商和我们之间的唯一值
		//判断CP 渠道是否存在
		$sql   = "select * from aso_source_cpid where cpid=$cpid";
		
		$res   = $this->db->query($sql);
		$cp_res= $res->row_array();
		if(empty($cp_res)){
			echo json_encode(array('code'=>99,'message'=>'未知渠道或CP'));die;
		}
		//判断广告是否存在或下线
		$adid  = intval($rearr['adid']);//获取广告id
		$appid = intval($rearr['appid']);//获取appid
		$sql   = "select * from aso_advert where is_disable='0' and appid =$appid and cpid=$adid";
		
		$res   = $this->db->query($sql);
		$list  = $res->row_array();
		if(empty($list)){
			echo json_encode(array('code'=>101,'message'=>'广告未存在或已下线'));die;
		}

		if($list['is_repeat']==0){
			echo json_encode(array('code'=>'-1','message'=>'Interface not open'));die;
		}

		
		$rearr['cpid'] = $cpid;
		//判断指定参数是否存在
		if(!isset($rearr['ip'])){
			$rearr['ip']='';
		}
		if(!isset($rearr['sign'])) $rearr['sign']='';

		if(!isset($rearr['idfa'])){
			echo json_encode(array('code'=>103,'message'=>'idfa noisset'));die;
		}
		if(!isset($rearr['timestamp'])) $rearr['timestamp']=time();
		if($rearr['adid']==1){
			
			$sql = "select count(*) as s from aso_submit where appid = ".$rearr['appid']." and idfa='".$rearr['idfa']."'";
		
			
			$res = $this->db2->query($sql);
			$result = $res->row_array();
			if(empty($result['s'])){
				// $a = array('code'=>'0','result'=>'ok');
				// $file_contents = json_encode($a);
				$a = array($rearr['idfa']=>'1');
				$file_contents = json_encode($a);
				$log=array('cpid'=>$rearr['cpid'],'appid'=>$rearr['appid'],'adid'=>$rearr['adid'],'idfa'=>$rearr['idfa'],'json'=>$file_contents ,'date'=>time());
				
				
			}else{
				$file_get_contents = array($rearr['idfa']=>'0');
				$file_contents =json_encode($file_get_contents);
				$log=array('cpid'=>$rearr['cpid'],'appid'=>$rearr['appid'],'adid'=>$rearr['adid'],'idfa'=>$rearr['idfa'],'json'=>$file_contents ,'date'=>time());
				//$this->db->insert('aso_IdfaRepeat_log',$log);
				// echo  $file_contents;
				//如果我们与渠道商之间的idfa是重复的就错误返回信息
				
			}
			
			 $this->db->insert('aso_IdfaRepeat_log',$log);
			echo  $file_contents;
			mysql_close();die;
		}else{

			/*判断是否在本地激活库里*/
			 $ActiveData=  $this->db2->get_where('aso_submit2',array('appid'=>$rearr['appid'],'idfa'=>$rearr['idfa']))->row_array();
			 if($ActiveData){
			 	echo json_encode(array($rearr['idfa']=>'0'));die;
			 }


			if($list['api_cat']==1){
				$s ='IdfaRepeat_'.$rearr['adid'];
			
				$this->$s($rearr,$list);
				mysql_close();die;
			}else{
				//获取接口请求方式
				$request_method  = explode('%',$list['repeat_value'])[2];
				//获取接口响应key值
				$key_value       = explode('%',$list['repeat_value'])[0];
				//获取接口请求成功响应值
				$TrueValue       = explode('%',$list['repeat_value'])[1];
				//获取关键词加密方式
				$ktype           = explode('%',$list['repeat_value'])[3];

				$nTrueValue       = @explode('%',$list['repeat_value'])[4];
				//取出CP点击接口
				$IdfaRepeat_url    = $list['IdfaRepeat_url'];
				$IdfaRepeat_url    = str_replace('{1}',$rearr['appid'],$IdfaRepeat_url);
				$IdfaRepeat_url    = str_replace('{2}',$rearr['idfa'],$IdfaRepeat_url);
				$IdfaRepeat_url    = str_replace('{3}',$rearr['ip'],$IdfaRepeat_url);
				$IdfaRepeat_url      = str_replace('{9}',$rearr['timestamp'],$IdfaRepeat_url);
				$IdfaRepeat_url    = str_replace('{8}',md5($rearr['timestamp'].$list['key']),$IdfaRepeat_url);
				
				$keys = $key_value;
				// if($rearr['adid']!=10077 && $rearr['adid']!=10991){
				// 	$keys              = str_replace('idfa',$rearr['idfa'],$key_value);
				// }
				
			    $keys              = explode('.',$keys);
			    if(in_array('idfa',$keys)){
			    	$ikey = array_search('idfa',$keys);
			    	$keys[$ikey] = $rearr['idfa'];
			    }

			    $KCount            = count(explode('.',$key_value));
			   
				if($request_method==1){
					if($rearr['appid']==984804729 && $rearr['adid']==10093){
						$file_contents = $this->request_get($IdfaRepeat_url,'',array("Authorization: Basic ZGluZ2RhbmdfdXNlcjpkZEBqazE4OTkyISYkIyE="));
					}else{
						$file_contents = $this->request_get($IdfaRepeat_url,$rearr);
					}
					

				    $json          = json_decode($file_contents,true);
				    
				}else{
					
					 $IdfaRepeatUrl    = explode('?',$IdfaRepeat_url)[0];
					 $IdfaRepeatParams = explode('&',explode('?',$IdfaRepeat_url)[1]);

					 foreach($IdfaRepeatParams as $k=>$val){
					 	$data[explode('=',$val)[0]] = explode('=',$val)[1];
					 }
					 if($list['post_type']==1){
					 	$file_contents  = $this->request_post2($IdfaRepeatUrl,json_encode($data));
					 }else{
					 	$file_contents  = $this->request_post($IdfaRepeatUrl,$data);
					 }
					
					

					$json          = json_decode($file_contents,true);
				}

				$error=  $this->db->get_where('aso_error',array('appid'=>$rearr['appid'],'adid'=>$rearr['adid']))->row_array();
				if($error){
					$NowReCo   = $error['repeat_request_count'];
					$this->db->update('aso_error',array('repeat_request_count'=>$NowReCo+1),array('id'=>$error['id']));
				}else{
					$this->db->insert('aso_error',array('appid'=>$rearr['appid'],'adid'=>$rearr['adid'],'repeat_request_count'=>1));
					$error=  $this->db->get_where('aso_error',array('appid'=>$rearr['appid'],'adid'=>$rearr['adid']))->row_array();
				}

				
				$log=array('cpid'=>$rearr['cpid'],'appid'=>$rearr['appid'],'adid'=>$rearr['adid'],'idfa'=>$rearr['idfa'],'json'=>$file_contents ,'date'=>time());
				$this->db->insert('aso_IdfaRepeat_log',$log);
			 	if($KCount==1){
			 		if($nTrueValue==null){
			 			if($json[$keys[0]]==$TrueValue){
			 				 /*判断是否有锁定任务*/
			     			$locking_sql       = "select * from aso_task_locking where appid={$rearr['appid']} and idfa='{$rearr['idfa']}' order by id desc limit 1";
			   
			     			$locking_data = $this->db2->query($locking_sql)->row_array();

			    			 if(!empty($locking_data) && $locking_data['cpid']!=$rearr['cpid'] && time()-$locking_data['date']<1800){
			     				echo json_encode(array('code'=>101,'reult'=>'tasking is locked'));die;
			   				 }else{
			   			 		$this->db2->insert('aso_task_locking',$log);
			   				 }
			     			/*判断是否有锁定任务结束*/
			 				
			    			echo json_encode(array($rearr['idfa']=>'1'));die;
			    		}else{
			    			$this->db->update('aso_error',array('repeat_error_count'=>$error['repeat_error_count']+1),array('id'=>$error['id']));
			    			echo json_encode(array($rearr['idfa']=>'0','ErrorInfo'=>$file_contents));die;
			    		}
			 		}else{
			 			if(isset($json[$keys[0]]) && $json[$keys[0]]==$TrueValue){
			 				/*判断是否有锁定任务*/
			     			$locking_sql       = "select * from aso_task_locking where appid={$rearr['appid']} and idfa='{$rearr['idfa']}' order by id desc limit 1";
			   
			     			$locking_data = $this->db2->query($locking_sql)->row_array();

			    			 if(!empty($locking_data) && $locking_data['cpid']!=$rearr['cpid'] && time()-$locking_data['date']<1800){
			     				echo json_encode(array('code'=>101,'reult'=>'tasking is locked'));die;
			   				 }else{
			   			 		$this->db2->insert('aso_task_locking',$log);
			   				 }
			     			/*判断是否有锁定任务结束*/
			    			echo json_encode(array($rearr['idfa']=>'1'));die;
				    	}else if(isset($json[$keys[0]]) && $json[$keys[0]]==$nTrueValue){
				    		$this->db->update('aso_error',array('repeat_error_count'=>$error['repeat_error_count']+1),array('id'=>$error['id']));
				    		echo json_encode(array($rearr['idfa']=>'0','ErrorInfo'=>$file_contents));die;
				    	}else{
				    		$this->db->update('aso_error',array('repeat_error_count'=>$error['repeat_error_count']+1),array('id'=>$error['id']));
				    		echo json_encode(array($rearr['idfa']=>'-1','ErrorInfo'=>$file_contents));die;
				    	}
			 		}
			    	
			    }else if($KCount==2){
			    	if($nTrueValue==null){
			    		if($json[$keys[0]][$keys[1]]==$TrueValue){
			    			/*判断是否有锁定任务*/
			     			$locking_sql       = "select * from aso_task_locking where appid={$rearr['appid']} and idfa='{$rearr['idfa']}' order by id desc limit 1";
			   
			     			$locking_data = $this->db2->query($locking_sql)->row_array();

			    			 if(!empty($locking_data) && $locking_data['cpid']!=$rearr['cpid'] && time()-$locking_data['date']<1800){
			     				echo json_encode(array('code'=>101,'reult'=>'tasking is locked'));die;
			   				 }else{
			   			 		$this->db2->insert('aso_task_locking',$log);
			   				 }
			     			/*判断是否有锁定任务结束*/
			    			echo json_encode(array($rearr['idfa']=>'1'));die;
			    		}else{
				    		$this->db->update('aso_error',array('repeat_error_count'=>$error['repeat_error_count']+1),array('id'=>$error['id']));

			    			echo json_encode(array($rearr['idfa']=>'0'));die;
			    		}
			    	}else{
			    		if(isset($json[$keys[0]][$keys[1]]) && $json[$keys[0]][$keys[1]]==$TrueValue){
			    			$this->db2->insert('aso_task_locking',$log);
			    			echo json_encode(array($rearr['idfa']=>'1'));die;
				    	}else if(isset($json[$keys[0]][$keys[1]]) && $json[$keys[0]][$keys[1]]==$nTrueValue){
				    		$this->db->update('aso_error',array('repeat_error_count'=>$error['repeat_error_count']+1),array('id'=>$error['id']));

				    		echo json_encode(array($rearr['idfa']=>'0','ErrorInfo'=>$file_contents));die;
				    	}else{
				    		$this->db->update('aso_error',array('repeat_error_count'=>$error['repeat_error_count']+1),array('id'=>$error['id']));

				    		echo json_encode(array($rearr['idfa']=>'-1','ErrorInfo'=>$file_contents));die;
				    	}
			    	}
			    	
			    }else if($KCount==3){
			    	if($nTrueValue==null){
			    		if($json[$keys[0]][$keys[1]][$keys[2]]==$TrueValue){
			    			/*判断是否有锁定任务*/
			     			$locking_sql       = "select * from aso_task_locking where appid={$rearr['appid']} and idfa='{$rearr['idfa']}' order by id desc limit 1";
			   
			     			$locking_data = $this->db2->query($locking_sql)->row_array();

			    			 if(!empty($locking_data) && $locking_data['cpid']!=$rearr['cpid'] && time()-$locking_data['date']<1800){
			     				echo json_encode(array('code'=>101,'reult'=>'tasking is locked'));die;
			   				 }else{
			   			 		$this->db2->insert('aso_task_locking',$log);
			   				 }
			     			/*判断是否有锁定任务结束*/
			    			echo json_encode(array($rearr['idfa']=>'1'));die;
			    		}else{
				    		$this->db->update('aso_error',array('repeat_error_count'=>$error['repeat_error_count']+1),array('id'=>$error['id']));

			    			echo json_encode(array($rearr['idfa']=>'0'));die;
			    		}
			    	}else{
			    		if(isset($json[$keys[0]][$keys[1]][$keys[2]]) && $json[$keys[0]][$keys[1]][$keys[2]]==$TrueValue){
			    			$this->db2->insert('aso_task_locking',$log);
			    			echo json_encode(array($rearr['idfa']=>'1'));die;
			    		}else if(isset($json[$keys[0]][$keys[1]][$keys[2]]) && $json[$keys[0]][$keys[1]][$keys[2]]==$nTrueValue){
				    		$this->db->update('aso_error',array('repeat_error_count'=>$error['repeat_error_count']+1),array('id'=>$error['id']));

			    			echo json_encode(array($rearr['idfa']=>'0','ErrorInfo'=>$file_contents));die;
			    		}else{
				    		$this->db->update('aso_error',array('repeat_error_count'=>$error['repeat_error_count']+1),array('id'=>$error['id']));

			    			echo json_encode(array($rearr['idfa']=>'-1','ErrorInfo'=>$file_contents));die;
			    		}
			    	}
			    	
			    }else{
			    	if($nTrueValue==null){
			    		if($json[$keys[0]][$keys[1]][$keys[2]][$keys[3]]==$TrueValue){
			    			/*判断是否有锁定任务*/
			     			$locking_sql       = "select * from aso_task_locking where appid={$rearr['appid']} and idfa='{$rearr['idfa']}' order by id desc limit 1";
			   
			     			$locking_data = $this->db2->query($locking_sql)->row_array();

			    			 if(!empty($locking_data) && $locking_data['cpid']!=$rearr['cpid'] && time()-$locking_data['date']<1800){
			     				echo json_encode(array('code'=>101,'reult'=>'tasking is locked'));die;
			   				 }else{
			   			 		$this->db2->insert('aso_task_locking',$log);
			   				 }
			     			/*判断是否有锁定任务结束*/
			    			echo json_encode(array($rearr['idfa']=>'1'));die;
			    		}else{
				    		$this->db->update('aso_error',array('repeat_error_count'=>$error['repeat_error_count']+1),array('id'=>$error['id']));

			    			echo json_encode(array($rearr['idfa']=>'0'));die;
			    		}
			    	}else{
			    		if(isset($json[$keys[0]][$keys[1]][$keys[2]][$keys[3]]) && $json[$keys[0]][$keys[1]][$keys[2]][$keys[3]]==$TrueValue){
			    			/*判断是否有锁定任务*/
			     			$locking_sql       = "select * from aso_task_locking where appid={$rearr['appid']} and idfa='{$rearr['idfa']}' order by id desc limit 1";
			   
			     			$locking_data = $this->db2->query($locking_sql)->row_array();

			    			 if(!empty($locking_data) && $locking_data['cpid']!=$rearr['cpid'] && time()-$locking_data['date']<1800){
			     				echo json_encode(array('code'=>101,'reult'=>'tasking is locked'));die;
			   				 }else{
			   			 		$this->db2->insert('aso_task_locking',$log);
			   				 }
			     			/*判断是否有锁定任务结束*/
			    			echo json_encode(array($rearr['idfa']=>'1'));die;
			    		}else if(isset($json[$keys[0]][$keys[1]][$keys[2]][$keys[3]]) && $json[$keys[0]][$keys[1]][$keys[2]][$keys[3]]==$nTrueValue){
				    		$this->db->update('aso_error',array('repeat_error_count'=>$error['repeat_error_count']+1),array('id'=>$error['id']));

			    			echo json_encode(array($rearr['idfa']=>'0','ErrorInfo'=>$file_contents));die;
			    		}else{
				    		$this->db->update('aso_error',array('repeat_error_count'=>$error['repeat_error_count']+1),array('id'=>$error['id']));

			    			echo json_encode(array($rearr['idfa']=>'-1','ErrorInfo'=>$file_contents));die;
			    		}
			    	}
			    	
			    }
			}
			
		}
	}
	//做完任务上报接口
	public function aso_Submit(){
		$rearr = $_GET;
		$cpid  = $this->uri->segment(4,0);//此cpid为渠道商和我们之间的唯一值
		//判断CP 渠道是否存在
		$sql   = "select * from aso_source_cpid where cpid=$cpid";
		
		$res   = $this->db->query($sql);
		$cp_res= $res->row_array();
		if(empty($cp_res)){
			echo json_encode(array('code'=>99,'message'=>'未知渠道或CP'));die;
		}
		//判断广告是否存在或下线
		$adid  = intval($rearr['adid']);//获取广告id
		$appid = intval($rearr['appid']);//获取appid
		$sql   = "select * from aso_advert where is_disable='0' and appid =$appid and cpid=$adid";
		
		$res   = $this->db->query($sql);
		$list  = $res->row_array();
		if(empty($list)){
			echo json_encode(array('code'=>101,'message'=>'广告未存在或已下线'));die;
		}

		if($list['is_submit']==0){
			echo json_encode(array('code'=>'-1','message'=>'Interface not open'));die;
		}

		
		$rearr['cpid']  = $cpid;
		//判断指定参数是否存在

		if(!isset($rearr['idfa'])){
			echo json_encode(array('code'=>103,'message'=>'idfa noisset'));die;
		}

		if(!isset($rearr['timestamp'])) $rearr['timestamp']=time();
		if(!isset($rearr['reqtype'])) $rearr['reqtype']=1;
		if(!isset($rearr['device'])) $rearr['device']='iphone';
		if(!isset($rearr['os'])) $rearr['os']='未知';
		if(!isset($rearr['isbreak'])) $rearr['isbreak']=0;
		if(!isset($rearr['keywords'])) $rearr['keywords']='';
		if(!isset($rearr['sign'])) $rearr['sign']='';

		//判断签名
		if($cp_res['key'] !=''){//key不为空的就是带sign,为空的就是渠道不支持sign验证
			if(md5($rearr['timestamp'].$cp_res['key']) != $rearr['sign']){
				echo  json_encode(array('code'=>'104','result'=>'sign error'));die;
			}
		}
		if($rearr['adid']==1){
			$sql = "select id,cpid,idfa,appid from aso_source where cpid=".$cpid." and appid = ".$rearr['appid']." and idfa='".$rearr['idfa']."'";
			$res = $this->db->query($sql);
			$Soresult = $res->row_array();

			$sql = "select count(*) as s from aso_submit where appid = ".$rearr['appid']." and idfa='".$rearr['idfa']."'";
			$r = $this->db2->query($sql);
			$Suresult = $r->row_array();
			unset($rearr['sign'],$rearr['ip'],$rearr['reqtype'],$rearr['isbreak'],$rearr['device'],$rearr['os'],$rearr['callback']);
			if(!empty($Suresult['s'])){//已经做过任务或者已经提交

				$a = array('code'=>'103','result'=>'idfa Exist');
				$file_contents = json_encode($a);
				$rearr['json']=$file_contents;
				$this->db->insert('aso_submit_log',$rearr);
				echo  $file_contents;
				mysql_close();
				die;

			}
		//如果厂商的cpid为1，就是我们自己来做排重只对接渠道，不对接厂商，只做排重点击上报
			if(empty($Soresult)){//等于空就是没有做任务，返回错误值
				$a = array('code'=>'103','result'=>'idfa noExist');
				$file_contents = json_encode($a);
				$rearr['json']=$file_contents;
				$this->db->insert('aso_submit_log',$rearr);
				echo  $file_contents;
				mysql_close();
				die;

			}else{
				//写入submit
				$rearr['timestamp'] = time();
				$rearr['type'] = 1;
				
				$this->db2->insert('aso_submit',$rearr);
				
				$this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$Soresult['id']));
				// $sql = "update aso_source set submit=1 where id =".$result['id'];
				// $this->db->query($sql);
				echo  json_encode(array('code'=>'0','result'=>'ok'));
				mysql_close();
				die;
				}
			
		}else{

			if($list['submit_type']==2){
	    		$rearr['timestamp']=time();
				$rearr['type'] = 1;

				$activeExists=  $this->db2->get_where('aso_submit2',array('appid'=>$rearr['appid'],'idfa'=>$rearr['idfa']))->row_array();

				if(!empty($activeExists)){

					echo  json_encode(array('code'=>'103','result'=>'Idfa Exists'));die;
				}else{
					$TaskRecord  = $this->db->get_where('aso_source',array('appid'=>$rearr['appid'],'cpid'=>$rearr['cpid'],'adid'=>$rearr['adid'],'idfa'=>$rearr['idfa'],'type'=>0))->row_array();

					if($TaskRecord){
						$this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$TaskRecord['id']));
					}else{
						$this->db->insert('aso_source',array('appid'=>$rearr['appid'],'adid'=>$rearr['adid'],'idfa'=>$rearr['idfa'],'cpid'=>$rearr['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$rearr['keywords']));
					}
					$active_data['appid'] = $rearr['appid'];
					$active_data['adid']  = $rearr['adid'];
					$active_data['idfa']  = $rearr['idfa'];
					$active_data['cpid']  = $rearr['cpid'];
					$active_data['type']  = 1;
					$active_data['keywords']  = $rearr['keywords'];
					$active_data['timestamp']  = time();
					$this->db2->insert('aso_submit2',$active_data);
					echo  json_encode(array('code'=>'0','result'=>'ok'));die;
				}
				
				
			    		
			  }
			if($list['api_cat']==1){
				$s ='submit_'.$rearr['adid'];
				
				$this->$s($rearr,$list);
				mysql_close();die;
			}else{
				//获取接口请求方式
				$request_method  = explode('%',$list['submit_value'])[2];
				//获取接口响应key值
				$key_value       = explode('%',$list['submit_value'])[0];
				//获取接口请求成功响应值
				$TrueValue       = explode('%',$list['submit_value'])[1];
				//获取关键词加密方式
				$ktype           = explode('%',$list['submit_value'])[3];
				//取出CP上报接口
				$submit_url    = $list['submit_url'];
				$submit_url    = str_replace('{1}',$rearr['appid'],$submit_url);
				$submit_url    = str_replace('{2}',$rearr['idfa'],$submit_url);
				$submit_url    = str_replace('{3}',$rearr['ip'],$submit_url);
				$submit_url    = str_replace('{4}',$rearr['device'],$submit_url);
				$submit_url    = str_replace('{5}',$ktype==0?$rearr['keywords']:urlencode($rearr['keywords']),$submit_url);
				$submit_url    = str_replace('{6}',$rearr['os'],$submit_url);
				$keys          = str_replace('idfa',$rearr['idfa'],$key_value);
				$keys          = explode('.',$keys);
				$KCount        = count(explode('.',$key_value));
				if($request_method==1){
					$file_contents = $this->request_get($submit_url,$rearr);

				    $json          = json_decode($file_contents,true);
				}else{
					
					 $submitUrl    = explode('?',$submit_url)[0];
					 $submitParams = explode('&',explode('?',$submit_url)[1]);

					 foreach($submitParams as $k=>$val){
					 	$data[explode('=',$val)[0]] = explode('=',$val)[1];
					 }
					if($list['post_type']==1){
					 	$file_contents  = $this->request_post2($submitUrl,json_encode($data));
					 }else{
					 	$file_contents  = $this->request_post($submitUrl,$data);
					 }
					
					$json          = json_decode($file_contents,true);
				}

				$error=  $this->db->get_where('aso_error',array('appid'=>$rearr['appid'],'adid'=>$rearr['adid']))->row_array();
				if($error){
					$NowReCo   = $error['submit_request_count'];
					$this->db->update('aso_error',array('submit_request_count'=>$NowReCo+1),array('id'=>$error['id']));
				}else{
					$this->db->insert('aso_error',array('appid'=>$rearr['appid'],'adid'=>$rearr['adid'],'submit_request_count'=>1));
					$error=  $this->db->get_where('aso_error',array('appid'=>$rearr['appid'],'adid'=>$rearr['adid']))->row_array();
				}
				unset($rearr['sign'],$rearr['ip'],$rearr['reqtype'],$rearr['isbreak'],$rearr['device'],$rearr['os'],$rearr['callback']);
					
				if($TrueValue=='auto'){
					$rearr['timestamp']=time();
					$rearr['type'] = 1;

					$SourceExists=  $this->db->get_where('aso_source',array('appid'=>$rearr['appid'],'cpid'=>$rearr['cpid'],'adid'=>$rearr['adid'],'idfa'=>$rearr['idfa']))->row_array();

					if(!empty($SourceExists)){

						$this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));

					}else{
						$this->db->insert('aso_source',array('appid'=>$rearr['appid'],'adid'=>$rearr['adid'],'idfa'=>$rearr['idfa'],'cpid'=>$rearr['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$rearr['keywords']));
					}
					
					echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
				}else{
					if($KCount==1){
			    	if($json[$keys[0]]==$TrueValue){
			    		$rearr['timestamp']=time();
						$rearr['type'] = 1;

						$SourceExists=  $this->db->get_where('aso_source',array('appid'=>$rearr['appid'],'cpid'=>$rearr['cpid'],'adid'=>$rearr['adid'],'idfa'=>$rearr['idfa']))->row_array();

						if(!empty($SourceExists)){

							$this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));

						}else{
							$this->db->insert('aso_source',array('appid'=>$rearr['appid'],'adid'=>$rearr['adid'],'idfa'=>$rearr['idfa'],'cpid'=>$rearr['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$rearr['keywords']));
						}

						$active_data['appid'] = $rearr['appid'];
						$active_data['adid']  = $rearr['adid'];
						$active_data['idfa']  = $rearr['idfa'];
						$active_data['cpid']  = $rearr['cpid'];
						$active_data['type']  = 1;
						$active_data['keywords']  = $rearr['keywords'];
						$active_data['timestamp']  = time();
						$this->db2->insert('aso_submit2',$active_data);
						
						echo  json_encode(array('code'=>'0','result'=>'ok'));die;
			    		
			    	}else{
			    		$rearr['timestamp']=time();
						$rearr['type'] = 1;
						$rearr['json'] =$file_contents;
						$this->db->insert('aso_submit_log',$rearr);
						$this->db->update('aso_error',array('submit_error_count'=>$error['submit_error_count']+1),array('id'=>$error['id']));
			    		echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
			    	}
			    }else if($KCount==2){
			    	if($json[$keys[0]][$keys[1]]==$TrueValue){
			    		$rearr['timestamp']=time();
						$rearr['type'] = 1;
						$SourceExists=  $this->db->get_where('aso_source',array('appid'=>$rearr['appid'],'cpid'=>$rearr['cpid'],'adid'=>$rearr['adid'],'idfa'=>$rearr['idfa']));
						if($SourceExists){
							$this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
						}else{
							$this->db->insert('aso_source',array('appid'=>$rearr['appid'],'adid'=>$rearr['adid'],'idfa'=>$rearr['idfa'],'cpid'=>$rearr['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$rearr['keywords']));
						}

						$active_data['appid'] = $rearr['appid'];
						$active_data['adid']  = $rearr['adid'];
						$active_data['idfa']  = $rearr['idfa'];
						$active_data['cpid']  = $rearr['cpid'];
						$active_data['type']  = 1;
						$active_data['keywords']  = $rearr['keywords'];
						$active_data['timestamp']  = time();
						$this->db2->insert('aso_submit2',$active_data);
						
						echo  json_encode(array('code'=>'0','result'=>'ok'));die;
			    		
			    	}else{
			    		$rearr['timestamp']=time();
						$rearr['type'] = 1;
						$rearr['json'] =$file_contents;
						$this->db->insert('aso_submit_log',$rearr);
						$this->db->update('aso_error',array('submit_error_count'=>$error['submit_error_count']+1),array('id'=>$error['id']));
			    		echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
			    	}
			    }else{
			    	if($json[$keys[0]][$keys[1]][$keys[2]]==$TrueValue){
			    		$rearr['timestamp']=time();
						$rearr['type'] = 1;
						$SourceExists=  $this->db->get_where('aso_source',array('appid'=>$rearr['appid'],'cpid'=>$rearr['cpid'],'adid'=>$rearr['adid'],'idfa'=>$rearr['idfa']));
						if($SourceExists){
							$this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
						}else{
							$this->db->insert('aso_source',array('appid'=>$rearr['appid'],'adid'=>$rearr['adid'],'idfa'=>$rearr['idfa'],'cpid'=>$rearr['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$rearr['keywords']));
						}
						$active_data['appid'] = $rearr['appid'];
						$active_data['adid']  = $rearr['adid'];
						$active_data['idfa']  = $rearr['idfa'];
						$active_data['cpid']  = $rearr['cpid'];
						$active_data['type']  = 1;
						$active_data['keywords']  = $rearr['keywords'];
						$active_data['timestamp']  = time();
						$this->db2->insert('aso_submit2',$active_data);
						echo  json_encode(array('code'=>'0','result'=>'ok'));die;
			    	}else{
			    		$rearr['timestamp']=time();
						$rearr['type'] = 1;
						$rearr['json'] =$file_contents;
						$this->db->update('aso_error',array('submit_error_count'=>$error['submit_error_count']+1),array('id'=>$error['id']));
						$this->db->insert('aso_submit_log',$rearr);
			    		echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
			    	}
			    }
				}
				
			}
			
		}
	}
	/**************************************** 接口开始**********************************************/
	//钱咖渠道点击接口
	public function aso_qkClick(){

		if($_SERVER['REQUEST_METHOD']=='GET'){
			echo json_encode(array('code'=>99,'message'=>'request method error'));die;
		}
		if(!isset($_POST['idfa']) || empty($_POST['idfa'])){
			echo json_encode(array('code'=>99,'message'=>'idfa error'));die;
		}

		$data   = $_POST;
		$sql    = "select adid from aso_wfqk where appid = {$data['appid']} and is_del = '0'";
		$res    = $this->db->query($sql);
		$cp_res = $res->row_array();
		if(empty($cp_res)){
			echo json_encode(array('code'=>99,'message'=>'adid error'));die;
		}
		$adid   = $cp_res['adid'];
		
		$url    = 'http://asoapi.appubang.com/api/aso_source/cpid/622/?appid='.$data['appid'].'&idfa='.$data['idfa'].'&ip='.$data['ip'].'&adid='.$adid;
		
		
		$info   = $this->request_get($url);
		echo $info;
		
		
	}
	//钱咖渠道排重接口
	public function aso_qkIdfaRepeat(){

		if($_SERVER['REQUEST_METHOD']=='GET'){
			echo json_encode(array('code'=>99,'message'=>'request method error'));die;
		}
		if(!isset($_POST['idfa']) || empty($_POST['idfa'])){
			echo json_encode(array('code'=>99,'message'=>'idfa error'));die;
		}
		
		$data          = $_POST;
		$sql    = "select adid from aso_wfqk where appid = {$data['appid']} and is_del = '0'";
		$res    = $this->db->query($sql);
		$cp_res = $res->row_array();
		if(empty($cp_res)){
			echo json_encode(array('code'=>99,'message'=>'adid error'));die;
		}
		$adid   = $cp_res['adid'];
		
		$idfas         = explode(',',$data['idfa']);
		$ReturnIdfa    = array();
		foreach($idfas as $k=>$val){
			

			$info   = $this->request_get("http://asoapi.appubang.com/api/aso_IdfaRepeat/cpid/622?adid=".$adid."&appid=".$data['appid'].'&idfa='.$val);
		
			$json   = json_decode($info,true);
			
			if($json[$val]=='1'){
				//echo  json_encode(array($data['idfa']=>'0','ErrorInfo'=>$file_contents));
				$ReturnIdfa[$val]=0;
			}else{
				$ReturnIdfa[$val]=1;
			}

		}
		
		echo json_encode($ReturnIdfa);
		
		
	
	}
	
	//懒猫分包排重
	function IdfaRepeat_3($data,$list){
		$file_contents = file_get_contents($list['IdfaRepeat_url']."&idfa=".$data['idfa']);
		$json = json_decode($file_contents,true );
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		unset($data['sign']);
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && $json[$data['idfa']]==1){
				$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents);//我们这里返回0代表失败不可做任务
				$contents = json_encode($a);
				echo  $contents;
				
			}else{
				//成功返回
				echo  json_encode(array($data['idfa']=>'1'));//这里返回1代表成功
			}
	}
	//懒猫分包上报
	function submit_3($data,$list){
		$file_contents = file_get_contents($list['submit_url']."&idfa=".$data['idfa']."&ip=".$data['ip']);
		$json = json_decode($file_contents,true );
		
		// unset($data['sign']);
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(isset($json['status']) && $json['status']==1){//上报成功
				// unset($data['ip']);
				$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
				echo  json_encode(array('code'=>'0','result'=>'ok'));
				
			}else{//失败 
				// unset($data['ip']);
				$data['timestamp']=time();
				$data['type'] = 1;
				$data['json'] =$file_contents;
				$this->db->insert('aso_submit_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
			}
	} 
	//懒猫分包点击
	function source_3($data,$list){
		$file_contents = file_get_contents($list['source_url']."&idfa=".$data['idfa']."&ip=".$data['ip']."&os=".$data['os']);
		$json = json_decode($file_contents,true );
		if(isset($json['status']) && $json['status']==1){//上报成功
				$this->db->insert('aso_source',$data);
				echo  json_encode(array('code'=>'0','result'=>'ok'));
				
			}else{//失败
				$data['json'] =  $file_contents;
				$this->db->insert('aso_source_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
			}	
	}
	//应用雷达排重
	function IdfaRepeat_4($data,$list){
		$url = $list['IdfaRepeat_url'];
        $post_data['appid']       = $data['appid'];
        $post_data['idfa']      = $data['idfa'];
        
        $file_contents = $this->request_post($url, $post_data);  
       	$json = json_decode($file_contents,true );
		// echo $file_contents;die;
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		unset($data['sign']);
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && $json[$data['idfa']]==0){
			//成功返回
			echo  json_encode(array($data['idfa']=>'1'));//这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents);//我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;
		}
	}

	//小鱼赚钱 排重
	function IdfaRepeat_7($data,$list){
		$url = $list['IdfaRepeat_url'].'?appid='.$data['appid'].'&idfa='.$data['idfa'].'&sign='.md5($data['appid'].'c4^@@L%Uxubj');
        $file_contents = $this->request_get($url);  
       	$json = json_decode($file_contents,true );
		// echo $file_contents;die;
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && $json[$data['idfa']]==0){
			//成功返回
			echo  json_encode(array($data['idfa']=>'1'));//这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents);//我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;
		}
	}

	//应用雷达NOW直播注册回调
	function source_57($data,$list){
		$this->source_4($data,$list);
	}
	//应用雷达NOW直播注册回调排重
	function IdfaRepeat_57($data,$list){
		$this->IdfaRepeat_4($data,$list);
	}
	//应用雷达点击请求
	function source_4($data,$list){
		$id = $this->db->insert('aso_source', $data); 
		$inid = $this->db->insert_id();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=".$inid."&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);
		// echo $callback;die;
		$url = $list['source_url'] ."udid=".$data['idfa'].'&appid='.$data['appid'].'&returnFormat=null&multipleurl='.$callback;
		// $file_contents = file_get_contents("http://integralwall.ann9.com/Interface/ServiceiMoney.ashx?"."&udid=".$data['idfa'].'&appid='.$data['appid'].'&returnFormat=null&multipleurl='.$callback);
		$file_contents = $this->request_get($url);
		$json = json_decode($file_contents,true );
		if($data['appid']==550926736){
			if(isset($json['code'])){//点击成功
				$data['json']=$file_contents;
				$this->db->insert('aso_source_log',$data);
				echo  json_encode(array('code'=>'0','result'=>'ok'));die;
				
			}else{//失败 
				$data['json']=$file_contents;
				$this->db->insert('aso_source_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
				die;
			}
		}
		if($data['appid']==395096736){
			if($json['status']){//点击成功

				echo  json_encode(array('code'=>'0','result'=>'ok'));die;
				
			}else{//失败 
				$data['json']=$file_contents;
				$this->db->insert('aso_source_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
				die;
			}
		}
		if(isset($json['success']) && $json['success']){//点击成功

				echo  json_encode(array('code'=>'0','result'=>'ok'));
				
			}else{//失败 
				$data['json']=$file_contents;
				$this->db->insert('aso_source_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
			}	
	}
	
	//boss直聘
	function IdfaRepeat_10054($data,$list){
		$str='';
		$surl ='';
		$app_id ='';
		$uniqid ='';
		$secretKey='';
		if($data['appid']==887314963){
			$app_id=4050;
			$uniqid='9080app';
			$secretKey='b4c64402a3e858c6d725f05c5a1ca097';
		}else{
			$app_id=4133;
			$uniqid='ayl';
			$secretKey='83b9f264a6e63459c945b4ad0c38716b';
		}
		list($t1, $t2) = explode(' ', microtime());
		$time= $t2 .  ceil( ($t1 * 1000) );
		$arr = array('app_id'=> $app_id,'req_time'=>$time,'uniqid'=>$uniqid,'idfa'=>$data['idfa'],'v'=>'2.0');
		foreach ($arr as $key => $value) {
			$surl .=$key.'='.$value.'&';
		}
		ksort($arr);
		foreach ($arr as $key => $value) {
			$str.=$key.'='.$value;
		}
		$sig='V2.0' . MD5('/api/integralWall/idfaExist'.$str.$secretKey);
		$url='https://boss-api2.weizhipin.com/api/integralWall/idfaExist?'.$surl.'sig='.$sig;
		
		$file_contents = $this->request_get($url);
		
		$json = json_decode($file_contents,true );
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		if(isset($json[$data['idfa']]) && $json[$data['idfa']]==1){
				$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents);//我们这里返回0代表失败不可做任务
				$contents = json_encode($a);
				echo  $contents;
				
		}elseif(isset($json[$data['idfa']]) && $json[$data['idfa']]==1){

		}else{
			//成功返回
			echo  json_encode(array($data['idfa']=>'1'));//这里返回1代表成功
		}

	}
	function source_10054($data,$list){
		
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$str='';

		$app_id ='';
		$uniqid ='';
		$secretKey='';
		$source='';
		if($data['appid']==887314963){
			$app_id=4050;
			$uniqid='9080app';
			$secretKey='b4c64402a3e858c6d725f05c5a1ca097';
			$source ='aiyingli';
		}else{
			$app_id=4133;
			$uniqid='ayl';
			$secretKey='83b9f264a6e63459c945b4ad0c38716b';
			$source ='ayl';
		}
		list($t1, $t2) = explode(' ', microtime());
		$time= $t2 .  ceil( ($t1 * 1000) );
		$arr = array('app_id'=> $app_id,'req_time'=>$time,'uniqid'=>$uniqid,'idfa'=>$data['idfa'],'v'=>'2.0','ip'=>$data['ip'],'source'=>$source,'callback'=>$callback,'mac'=>'none','openUdid'=>'none');
		ksort($arr);
		foreach ($arr as $key => $value) {
			$str.=$key.'='.$value;
		}
		$sig='V2.0' . MD5('/api/integralWall/save8090'.$str.$secretKey);
		$arr2 = array('app_id'=>  $app_id,'req_time'=>$time,'uniqid'=>$uniqid,'idfa'=>$data['idfa'],'v'=>'2.0','ip'=>$data['ip'],'source'=>$source,'callback'=>$callback,'mac'=>'none','openUdid'=>'none','sig'=>$sig);
		$url='https://boss-api2.weizhipin.com/api/integralWall/save8090';
		$file_contents = $this->request_post($url,$arr2); 
		$json = json_decode($file_contents,true );
		if(isset($json['status']) && $json['status']==1){//点击成功
				echo  json_encode(array('code'=>'0','result'=>'ok'));die;
				$this->db->insert('aso_source', $data);
			}else{//失败 
				$data['json']=$file_contents;
				$this->db->insert('aso_source_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
				die;
			}
	}



	//看准 排重
	function IdfaRepeat_10055($data,$list){
		
	
		//$file_contents = $this->request_post($list['IdfaRepeat_url'], $req);
		$sig               = strtoupper(md5('idfa='.$data['idfa'].'&key=asSJU&*%asdOID!@!4123$@#1LJdj'));

		$url               = $list['IdfaRepeat_url']."?sig=".$sig."&idfa=".$data['idfa'];
	
		$file_contents = $this->NoUserAgent_get($url);
		
		$json = json_decode($file_contents,true);
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json['code']) && $json['code']==1004){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}

	//看准 点击
	function source_10055($data,$list){
		//ASCII
		 $sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		 $callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		
		 $callback = urlencode($callback);
		 $sigData['callback'] = $callback;
		 $sigData['mac']      = '02:00:00:00:00:00';
		 $sigData['ip']      = $data['ip'];
		 $sigData['source']      = 'imoney';
		 $sigData['idfa']      = $data['idfa'];
		
		 $sig               = strtoupper(md5($this->ASCII($sigData).'&key=asSJU&*%asdOID!@!4123$@#1LJdj'));
		//$url = $list['source_url']."?idfa=".$data['idfa']."&ip=".$data['ip']."&callback=".$callback.'&mac=02:00:00:00:00:00&source=imoney&ua=&openUdid=&sig='.$sig;
		// echo $url;die;
		$postData['callback'] = $callback;
		$postData['mac'] ='02:00:00:00:00:00';
		$postData['ip'] =  $data['ip'];
		$postData['callback'] = $callback;
		$postData['source'] = 'imoney';
		$postData['idfa'] = $data['idfa'];
		$postData['ua'] = '';
		$postData['openUdid'] = '';
		$postData['sig'] =  $sig;
		$file_contents   = $this->request_post($list['source_url'],$postData);

		$json  = json_decode($file_contents,true );

		if(isset($json["code"]) && $json["code"] == 1){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}


	
	
	//今日赚排重
	function IdfaRepeat_9($data,$list){
		if(!isset($data['ip'])){
			echo  json_encode(array('error'=>'ip error'));die;
		}
		// $url = "http://idfa.jinrizhuanqian.com:8080/jinrizhuancooper/downstream_check_idfa?source=aiyingli&check=1"."&idfa=".$data['idfa']."&appleid=".$data['appid']."&ip=".$data['ip'];
		$url = $list['IdfaRepeat_url']."&idfas=".$data['idfa']."&ip=".$data['ip'];
		//echo $url;
		$file_contents = $this->request_get($url);
		$json = json_decode($file_contents,true );
		//var_dump($json);
		// echo $file_contents;die;
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json['code'])&&isset($json['data'][$data['idfa']]) && $json['data'][$data['idfa']]==0  && $json['code']==0){
				
				//成功返回
				echo  json_encode(array($data['idfa']=>'1'));//这里返回1代表成功
				
		}else{
				$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents);//我们这里返回0代表失败不可做任务
				$contents = json_encode($a);
				echo  $contents;
		}
	}
	//今日赚点击回调
	
	function source_9($data,$list){
		// if(empty($data['callback'])){
		// 	echo'{"resultCode":-1,"errorMsg":"callback empty"}'; die;  
		// }
		$id = $this->db->insert('aso_source', $data); 
		$inid = $this->db->insert_id();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=".$inid."&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);
		//$HeadUrl  = $list['source_url']!=''?$list['source_url']:'http://click.jinrizhuanqian.com:8080/jinrizhuancooper/downstream_click_notice?source=aiyingli&mac=02:00:00:00:00:00';
		$url=$list['source_url'].'&idfa='.$data['idfa'].'&mac=02:00:00:00:00:00&os='.$data['os'].'&ip='.$data['ip'].'&callbackurl='.$callback;
		//echo $url;
		$file_contents = $this->request_get($url);
		// echo $file_contents;die;
		$json = json_decode($file_contents,true );
		if(isset($json['code']) && $json['code']==0){//点击成功
				echo  json_encode(array('code'=>'0','result'=>'ok'));die;
				
			}else{//失败 
				$data['json']=$file_contents;
				$this->db->insert('aso_source_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
				die;
			}
		
		// echo $file_contents;die;

	}

	//今日赚上报
	function submit_9($data,$list){
		$url = $list['submit_url'] ."&ip=" . $data['ip'] . "&idfa=" . $data['idfa'];
		//echo $url;
		$file_contents = $this->request_get($url);
		$json = json_decode($file_contents,true);
	//var_dump($json);
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if($json['code']==0 && isset($json['code'])){//上报成功
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
		}
	}
	//铜板墙 排重
	function IdfaRepeat_10($data,$list){

		
		$data_al['channelid']   = '4e9fbabb-f18d-4436-b837-b43de3f2c409';
		$data_al['idfa']        = $data['idfa']; 
		$data_al['appid']       = $data['appid'];
		$data_al['ip']          = $data['ip'];

		$url = $list['IdfaRepeat_url'];
		//var_dump($data_al);
		//echo $url;
		$file_contents = $this->request_post($url,$data_al);
		
		// $file_contents = $this->request_get("http://121.40.57.89:8004/vendor/ioscheck?channelid=61B73D05-57E0-4F13-B7C3-54FB6B0D994F&appid=880988864"."&idfa=".$data['idfa']."&ip=".$data['ip']);
		$json = json_decode($file_contents,true );
		
		// echo $file_contents;
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json['resState']) && $json['resState']==200){
				//成功返回
				echo  json_encode(array($data['idfa']=>'1'));//这里返回1代表成功
				
			}else{
				

				$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents);//我们这里返回0代表失败不可做任务
				$contents = json_encode($a);
				echo  $contents;
			}
	}
	//铜板墙 点击
	function source_10($data,$list){
		
		$data_al['channelid']   = '4e9fbabb-f18d-4436-b837-b43de3f2c409';
		$data_al['idfa']        = $data['idfa']; 
		$data_al['appid']       = $data['appid'];
		$data_al['ip']          = $data['ip'];
		
		$data_al['model']       = $data['device'];
		$data_al['os']          = $data['os'];
		$data_al['mac']         = '02:00:00:00:00:00';
	    $data_al['keyword']     = $data['keywords'];
		$url = $list['source_url'];
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);
		
		// $url="http://121.40.57.89:8004/vendor/iosclick?channelid=61B73D05-57E0-4F13-B7C3-54FB6B0D994F".'&idfa='.$data['idfa'].'&appleid=880988864&ip='.$data['ip'].'&callbackurl='.$callback;
		// echo $url;die;
		
		$file_contents = $this->request_post($url,$data_al);
		// echo $url;
		$json = json_decode($file_contents,true );
		
		if(isset($json['resState']) && $json['resState']==200){//点击成功
				$this->db->insert('aso_source', $data);
				echo  json_encode(array('code'=>'0','result'=>'ok'));die;
				
			}else{//失败 
				$data['json']=$file_contents;
				$this->db->insert('aso_source_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
				die;
			}
		
		// echo $file_contents;die;

	}

	//铜板墙上报
	function submit_10($data,$list){
		
		$data_al['channelid']   = '4e9fbabb-f18d-4436-b837-b43de3f2c409';
		$data_al['idfa']        = $data['idfa']; 
		$data_al['appid']       = $data['appid'];
		$data_al['ip']          = $data['ip'];
		
		$data_al['model']       = 'iphone';
		$data_al['os']          = '9.3.2';
		$data_al['mac']         = '02:00:00:00:00:00';
		$data_al['keyword']     = $data['keywords'];
		$url = $list['submit_url'];
		$file_contents = $this->request_post($url,$data_al);
		$json = json_decode($file_contents,true );
		
		// echo $url;
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);

		if(isset($json['resState']) && $json['resState']==200){//上报成功
				$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
				echo  json_encode(array('code'=>'0','result'=>'ok'));
				
			}else{//失败 
				$data['timestamp']=time();
				$data['type'] = 1;
				$data['json'] =$file_contents;
				$this->db->insert('aso_submit_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
			}
	}


	//秒赚大钱快 排重 
	function IdfaRepeat_21($data,$list){
		$arr = $this->convertUrlQuery($list['IdfaRepeat_url']);
		$adid = $arr['adid'];
		$channel= 34108;
		$key = "9b4506365436fdb0237e69af9979064d";
		$sign = md5($adid."|".$channel."|".$key);
		$url = $list['IdfaRepeat_url'] . "&idfa=" . $data['idfa'] . "&channel=" . $channel . "&sign=" . $sign;
		

		$file_contents = $this->request_get($url);

		$json = json_decode($file_contents,true);
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);

		if(isset($json[$data['idfa']]) && !$json[$data['idfa']]){ // 1代表成功
				$a = array($data['idfa']=>'1');
				$contents = json_encode($a);
				echo  $contents;
		}else{ // 0代表失败
			echo  json_encode(array($data['idfa']=>'0','ErrorInfo'=>$file_contents));
		}
	}

	//秒赚大钱快 点击 
	function source_21($data,$list){
		$arr = $this->convertUrlQuery($list['source_url']);
		$adid = $arr['adid'];
		$asign   = $sign = md5($data['timestamp'].md5('callback'));
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$asign;
		$callback = urlencode($callback);
		$channel= 34108;
		$key = "9b4506365436fdb0237e69af9979064d";
		$sign = md5($adid."|".$channel."|".$key);

		$value = '&idfa='. $data['idfa'] .'&keywords='.urlencode($data['keywords'])."&channel=" . $channel . "&ip=" . $data['ip'] . "&sign=" . $sign.'&callbackurl='.$callback;
		

		$file_contents   = $this->request_get($list['source_url'] . $value);
		$json  = json_decode($file_contents,true);

		if(!$json['code'] && $json['result'] == "ok"){
			$this->db->insert('aso_source', $data); 
			echo $file_contents;
		}else{
			
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));
		}
	}

	//秒赚大钱快 上报 
	function submit_21($data,$list){
		$arr = $this->convertUrlQuery($list['submit_url']);
		$adid = $arr['adid'];
		$channel= 34108;
		$key = "9b4506365436fdb0237e69af9979064d";
		$sign = md5($adid."|".$channel."|".$key);

		$file_contents = $this->request_get($list['submit_url'] . "&idfa=" . $data['idfa'] . "&channel=" . $channel . "&sign=" . $sign);
		$json = json_decode($file_contents,true );
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(!$json['code'] && $json['result'][$data['idfa']]){//上报成功
				$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
				echo  json_encode(array('code'=>'0','result'=>'ok'));
				
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
		}
	}

	//一伴婚恋 排重
	
	function IdfaRepeat_20($data,$list){
		
			list($t1, $t2) = explode(' ', microtime()); 
			
			$timestamp= (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);  
			$key_title='相亲';
			$idfa_channel="aiyingli";
			$channel='2';
            $platform=2;
			$str="channel".$channel."idfa".$data['idfa']."idfachannel".$idfa_channel.'keytitle'.$key_title.'platformtype'.$platform."timestamp".$timestamp;
			$str1=preg_replace("/[^a-zA-Z0-9]+/","", $str);
			$s1 = MD5("fidfas".$str1."9id8jo90");
			$signstr=MD5("3i45".$s1."bffffak");

			$url = $list['IdfaRepeat_url']."?idfa=".$data['idfa']."&sign=".$signstr."&timestamp=".$timestamp."&key_title=相亲"."&idfa_channel=aiyingli&channel=".$channel."&platform_type=".$platform;
			
			$file_contents = $this->request_get($url);
			$json = json_decode($file_contents,true);
			
			$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
			$this->db->insert('aso_IdfaRepeat_log',$log);

			if(isset($json['state']) && $json['state']==0){
				echo  json_encode(array($data['idfa']=>'1')); //这里返回1代表成功
				die;
			}else if(isset($json['state']) && $json['state']==1){
				$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
				$contents = json_encode($a);
				echo  $contents;
				die;
			}else{
				echo $file_contents;
			}
			
			//echo $signstr;
		
		
	}
	//一伴婚恋 排重
	function submit_20($data,$list){
		if($data['appid']==1335846722){
			list($t1, $t2) = explode(' ', microtime()); 
			
			$timestamp= (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);  
			$key_title='相亲';
			$idfa_channel="ayl";
			$channel='2';
            $platform=2;
			$str="channel".$channel."idfa".$data['idfa']."idfachannel".$idfa_channel.'keytitle'.$key_title.'platform'.$platform."timestamp".$timestamp;
			$str1=preg_replace("/[^a-zA-Z0-9]+/","", $str);
			
			$s1 = MD5("dgfdg43spi".$str1."y712efggr");
			$signstr=MD5("fdf3ng".$s1."ojjky");

			$url = $list['submit_url']."?idfa=".$data['idfa']."&sign=".$signstr."&timestamp=".$timestamp."&key_title=相亲"."&idfa_channel=ayl&channel=".$channel."&platform=".$platform;
			$file_contents = $this->request_get($url);
			$json = json_decode($file_contents,true);
			
			unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
			
			if(isset($json['code']) && $json['code'] == 1){//上报成功
				$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
				echo  json_encode(array('code'=>'0','result'=>'ok'));
			}else{//失败 
				unset($data['ip']);
				$data['timestamp']=time();
				$data['type'] = 1;
				$data['json'] =$file_contents;
				$this->db->insert('aso_submit_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
			//echo $signstr;
			}	
		}else{
			$time = time();
			if(explode('&',$list['submit_url'])[1]=='idfa_channel=ayl'){
			
				$key = 'idfachannelaylkeytitletimestamp';
			}else{
				$key = 'idfachannelkuchuankeytitletimestamp';
			}
			
			$joint = "idfa" . str_replace("-","",$data['idfa']) . $key . $time;
		    $a = md5("fidfas" . $joint ."9id8jo90");
			$sign = md5("3i45" . $a . "bffffak");
			$url = $list['submit_url'] .'&idfa='.$data['idfa']."&timestamp=" . $time."&sign=".$sign;
			;
			$file_contents = $this->request_get($url);
			$json = json_decode($file_contents,true);
			unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
			
			if(isset($json['state']) && $json['state'] == 1){//上报成功
				$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
				echo  json_encode(array('code'=>'0','result'=>'ok'));
			}else{//失败 
				unset($data['ip']);
				$data['timestamp']=time();
				$data['type'] = 1;
				$data['json'] =$file_contents;
				$this->db->insert('aso_submit_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
			}
		}
	}
	//一伴婚恋 点击
	function source_20($data,$list){
		list($t1, $t2) = explode(' ', microtime()); 
			
			$timestamp= (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);  
			$key_title='相亲';
			$idfa_channel="aiyingli";
			$channel='2';
            $platform=2;
			$str="channel".$channel."idfa".$data['idfa']."idfachannel".$idfa_channel.'keytitle'.$key_title.'platformtype'.$platform."timestamp".$timestamp;
			$str1=preg_replace("/[^a-zA-Z0-9]+/","", $str);
			$s1 = MD5("fidfas".$str1."9id8jo90");
			$signstr=MD5("3i45".$s1."bffffak");

			$url = $list['source_url']."?idfa=".$data['idfa']."&sign=".$signstr."&timestamp=".$timestamp."&key_title=相亲"."&idfa_channel=aiyingli&channel=".$channel."&platform_type=".$platform;
			
			$file_contents = $this->request_get($url);
			$json = json_decode($file_contents,true);
			
			$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
			$this->db->insert('aso_IdfaRepeat_log',$log);

		if(isset($json[$data['idfa']]) && $json[$data['idfa']]==0){//点击成功
			$this->db->insert('aso_source',$data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['json']=$file_contents;
			$this->db->insert('aso_source_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
			die;
		}
			
	}
	
	
	//应用猎人 排重
	function IdfaRepeat_24($data,$list){
		
	
		//$file_contents = $this->request_post($list['IdfaRepeat_url'], $req);
		
		$url               = $list['IdfaRepeat_url']."&appid=".$data['appid']."&idfa=".$data['idfa'];
		
		$file_contents = $this->request_get($url);

		$json = json_decode($file_contents,true);
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if($json[$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}

	//应用猎人 点击
	function source_24($data,$list){
		// if(empty($data['callback'])){
		//  	echo'{"resultCode":-1,"errorMsg":"callback empty"}'; die;  
		//  }
		 $id = $this->db->insert('aso_source', $data); 
		 $inid = $this->db->insert_id();
		 $sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		 $callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=".$inid."&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		
		 $callback = urlencode($callback);
		$url = $list['source_url']."&idfa=".$data['idfa']."&appid=".$data['appid']."&ip=".$data['ip']."&callback=".$callback;

	
		$file_contents   = $this->request_get($url);

		$json  = json_decode($file_contents,true );

		if(isset($json["status"]) && $json["status"] == 1){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}


	//应用猎人 完成上报
		function submit_24($data,$list){
		$url = $list['submit_url'] ."&appid=".$data['appid'].'&idfa=' . $data['idfa'] ."&ip=".$data['ip'];
		$file_contents = $this->request_get($url);

		$json = json_decode($file_contents,true);
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		
		if(isset($json["status"]) && $json["status"] == 1){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;//这里返回1代表成功
		}
	}
	
	//熊猫排重
	function IdfaRepeat_25($data,$list){
		$file_contents = $this->request_get($list['IdfaRepeat_url']."&appid=".$data['appid']."&idfa=".$data['idfa']."&ip=".$data['ip']);
		 $list['IdfaRepeat_url']."&appid=".$data['appid']."&idfa=".$data['idfa']."&ip=".$data['ip'];
		$json = json_decode($file_contents,true);

		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && !$json[$data['idfa']]){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}
	//熊猫上报
	function submit_25($data,$list){
		$url = $list['submit_url'] ."&appid=".$data['appid'].'&idfa=' . $data['idfa'] ."&ip=".$data['ip'];
		$file_contents = $this->request_get($url);

		$json = json_decode($file_contents,true);
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(isset($json["status"]) && $json["status"] == 1){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;//这里返回1代表成功
		}
	}


	//熊猫回调
	function source_25($data,$list){
		// if(empty($data['callback'])){
		//  	echo'{"resultCode":-1,"errorMsg":"callback empty"}'; die;  
		//  }
		 $id = $this->db->insert('aso_source', $data); 
		 $inid = $this->db->insert_id();
		 $sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		 $callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=".$inid."&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;

		 $callback = urlencode($callback);
		$url = $list['source_url']."&idfa=".$data['idfa']."&appid=".$data['appid']."&ip=".$data['ip']."&callBackUrl=".$callback.'&os='.$data['os'];

	
		$file_contents   = $this->request_get($url);

		$json  = json_decode($file_contents,true );

		if(isset($json["status"]) && $json["status"] == 1){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}
	
	
	

	//CN 排重
	function IdfaRepeat_51($data,$list){
		$this->IdfaRepeat_53($data,$list);
	}

	
	//CN 点击
	function source_51($data,$list){
		$this->source_53($data,$list);
	}


	//试用宝 排重
	function IdfaRepeat_73($data,$list){
		$file_contents = $this->request_get($list['IdfaRepeat_url']."&idfa=".$data['idfa'].'&app_id='.$data['appid'].'&ip='.$data['ip']);
		$json = json_decode($file_contents,true);
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json['status']) && $json['status']==0){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}

	//试用宝 点击
	function source_73($data,$list){
		
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		
		$callback = urlencode($callback);

		 $url = $list['source_url'] ."&idfa=".$data['idfa'].'&app_id='.$data['appid'].'&ip='.$data['ip']."&callback_url=".$callback;
		$file_contents   = $this->request_get($url);
		
		$json  = json_decode($file_contents,true);
		
		if(isset($json['status']) && $json['status']==0){
			 $this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}
	//试用宝 上报
	function submit_73($data,$list){
		$url = $list['submit_url']."&app_id=".$data['appid']."&idfa=".$data['idfa']."&ip=".$data['ip'];
		
		$file_contents = $this->request_get($url);

		$json = json_decode($file_contents,true);
		//var_dump($json);
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(isset($json['status']) && $json['status']==0){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}

	//新CN排重对接接口
	function IdfaRepeat_53($data,$list){
		$file_contents = $this->request_get($list['IdfaRepeat_url']."?appid=".$data['appid']."&idfa=".$data['idfa'].'&ip='.$data['ip']);
		$json = json_decode($file_contents,true);

		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && !$json[$data['idfa']]){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}

	//新CN点击对接接口
	function source_53($data,$list){
		// if(empty($data['callback'])){
		// 	echo'{"resultCode":-1,"errorMsg":"callback empty"}'; die;  
		// }
		$id = $this->db->insert('aso_source', $data); 
		$inid = $this->db->insert_id();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=".$inid."&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		
		$callback = urlencode($callback);

		 $url = $list['source_url'] ."&appid=".$data['appid']."&idfa=".$data['idfa']."&ip=".$data['ip']."&callback=".$callback;
		$file_contents   = $this->request_get($url);
		
		$json  = json_decode($file_contents,true);
		
		if($json["code"]==1){
			// $this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}
	//新CN激活对接接口 激活
	function submit_53($data,$list){
		$url = $list['submit_url']."&idfa=".$data['idfa']."&ip=".$data['ip'];
		
		$file_contents = $this->request_get($url);

		$json = json_decode($file_contents,true);
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(isset($json['code']) && $json['code'] == 1){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}
	//行者天下 京东金融 appid=jingdongjinrong
	function IdfaRepeat_105($data,$list){
		$this -> IdfaRepeat_359($data, $list);
	}

	function source_105($data,$list){
		$this -> source_359($data, $list);
	}

	//新CN
	function IdfaRepeat_969($data,$list){
		$this -> IdfaRepeat_53($data, $list);
	}

	function source_969($data,$list){
		$this -> source_53($data, $list);
	}

	function submit_969($data,$list){
		$this -> submit_53($data, $list);
	}

	//猎豆点击
	function source_54($data,$list){
		$nsign          = md5("appBaseId=".$data['appid']."&channelId=20015&idfa=".$data['idfa']."slims267(envied");
		$cparam		   = "{'source':'liedou','appBaseId':{$data['appid']},'sign':{$nsign}}";
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		
		$callback = urlencode($callback);

		 $url = $list['source_url'] ."&appid=".$data['appid']."&idfa=".$data['idfa']."&ip=".$data['ip']."&userid=waifang&osver=".$data['os']."&cparam=".$cparam."&ctype=1&mac=02:00:00:00:00:00&callback=".$callback;
		$file_contents   = $this->request_get($url);
		
		$json  = json_decode($file_contents,true);
		
		if(isset($json['code']) && $json['code']==0){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}


	//猎豆排重
	function IdfaRepeat_54($data,$list){
		$sign          = md5("appBaseId=".$data['appid']."&channelId=20015&idfa=".$data['idfa']."slims267(envied");
		$cparam		   = "{'source':'liedou','appBaseId':{$data['appid']},'sign':{$sign}}";
		$file_contents = $this->request_get($list['IdfaRepeat_url']."&appBaseId=".$data['appid']."&idfa=".$data['idfa'].'&sign='.$sign.'&cparam='.$cparam);
		
		$json = json_decode($file_contents,true);
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		

		
		//渠道返回：成功返回0  失败返回1
		if($json['code']==0 && $json['data']['result']==1){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}

	//猎豆 激活
	function submit_54($data,$list){
		
		$sign          = md5("appBaseId=".$data['appid']."&channelId=20015&idfa=".$data['idfa']."slims267(envied");

		$cparam		   = "{'source':'liedou','appBaseId':{$data['appid']},'sign':{$sign}}";	
		$file_contents = $this->request_get($list['submit_url']."&appBaseId=".$data['appid']."&idfa=".$data['idfa']."&ip=".$data['ip']."&sign=".$sign.'&cparam='.$cparam.'&mac=02:00:00:00:00:00');

		//echo $list['submit_url']."&appBaseId=".$data['appid']."&idfa=".$data['idfa']."&ip=".$data['ip']."&sign=".$sign.'&cparam='.$cparam.'&mac=02:00:00:00:00:00';
	    $json = json_decode($file_contents,true);
	
		
		//echo $list['submit_url']."?uuid=".$data['idfa']."&appid=".$data['appid']."&deviceName=ipone4&deviceVersion=4.0.0&idfa=".$data['idfa']."&ip=".$data['ip']."&deviceMac=02:00:00:00:00:00&network=wifi&secretKey=NQiMpTaKdMp6cFSdWHoqvY7tPzU0t58f";
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);

		if($json['message']== 'success' && $json['code']==0){//上报成功
				$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
				echo  json_encode(array('code'=>'0','result'=>'ok'));exit();
				
			}else{//失败 
				
				$data['timestamp']=time();
				$data['type'] = 1;
				$data['json'] =$file_contents;
				$this->db->insert('aso_submit_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));exit();//这里返回1代表成功
			}
	}

	//巨掌排重 
	function IdfaRepeat_246($data,$list){
		
		$file_contents = $this->request_get($list['IdfaRepeat_url']."&idfa=".$data['idfa'].'&ip='.$data['ip'].'&mac=02:00:00:00:00:00');
		
		$json = json_decode($file_contents,true);
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		

		
		//渠道返回：成功返回0  失败返回1
		if($json[$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}
	//巨掌点击 
	function source_246($data,$list){
		// if(empty($data['callback'])){
		// 	echo'{"resultCode":-1,"errorMsg":"callback empty"}'; die;  
		// }
		
		if($data['device']=='iPhone 5s'){
			$data['device']=str_replace(' ','',$data['device']);
		}
		$url = $list['source_url'] ."&idfa=".$data['idfa']."&ip=".$data['ip'].'&devicemodel='.$data['device'].'&systemversion='.$data['os'];
		$file_contents   = $this->request_get($url);
		
		$json  = json_decode($file_contents,true);
		
		if($json["State"]==100){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}
	//巨掌上报 
	function submit_246($data,$list){
		$url = $list['submit_url']."&idfa=".$data['idfa']."&ip=".$data['ip'];
		
		$file_contents = $this->request_get($url);
		
		$json = json_decode($file_contents,true);
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(isset($json['State']) && $json['State'] == 100){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}
	
	
	
	//36氪 排重
	function IdfaRepeat_10764($data,$list){
		
		$file_contents = $this->request_get($list['IdfaRepeat_url']."?idfa=".$data['idfa'].'&appid='.$data['appid']);
		
		$json = json_decode($file_contents,true);
		//var_dump($json);
		//var_dump($json);
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		

		
		//渠道返回：成功返回0  失败返回1
		if($json[$data['idfa']]==1){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}
	//36氪 点击回调
	function source_10764($data,$list){
		// if(empty($data['callback'])){
		// 	echo'{"resultCode":-1,"errorMsg":"callback empty"}'; die;  
		// }
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		
		$callback = urlencode($callback);

		$url = $list['source_url'] ."?idfa=".$data['idfa']."&callback=".$callback.'&appid='.$data['appid'].'&ip='.$data['ip'].'&timestamp='.$data['timestamp'].'&sign='.md5($data['timestamp'].'21e6022915a46283741cdb686693d83ab40d9692');
		
		$file_contents   = $this->request_get($url);
		
		$json  = json_decode($file_contents,true);
		
		if(isset($json['code']) && $json['code']==0){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}

	

	//拍拍贷 排重
	function IdfaRepeat_37($data,$list){
		
		date_default_timezone_set('Asia/Shanghai');
		$appid                  = explode('=',explode('&',$list['source_url'])[0])[1];
		//echo $appid;
		$res['AppId']           = explode('=',explode('&',$list['source_url'])[1])[1];
		//echo $res['AppId'];
		$res['Idfas'][0]['Idfa'] = $data['idfa'];
		$res['Source']          = explode('=',explode('&',$list['source_url'])[2])[1];
		$json_string            = json_encode($res);
		
        $url       = 'https://openapi.ppdai.com/marketing/AdvertiseService/CheckAdvertise';
        
		$file_contents = $this->SendRequest($url,$json_string,$appid,$data['appid']);
		

		$json = json_decode($file_contents,true);
		//var_dump($json);
		//print_r($json);die;
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json['Result']) && $json['Result']==0 && $json['Content']['CheckIdfaResults'][0]['IsActive']==1){
			echo  json_encode(array($data['idfa']=>'1')); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;
		}
	}
	
	//拍拍贷 点击
	function source_37($data,$list){
	    date_default_timezone_set('Asia/Shanghai');
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		
		//$callback = urlencode($callback);
		$appid                  = explode('=',explode('&',$list['source_url'])[0])[1];
		$res['AppId']           = explode('=',explode('&',$list['source_url'])[1])[1];
		$res['Idfa']            = $data['idfa'];
		$res['CallBackUrl']     = $callback;
		$res['DeviceId']        = '';
		$res['Source']          = explode('=',explode('&',$list['source_url'])[2])[1];
		$res['Mac']             = '';
		$json_string            = json_encode($res);
		//echo $json_string;
		$url                    = 'https://openapi.ppdai.com/marketing/AdvertiseService/SaveAdvertise';
		
		$file_contents = $this->SendRequest($url,$json_string,$appid,$data['appid']);
		
		//$file_contents   = $this->request_get($url);
		
		$json  = json_decode($file_contents,true);
		//var_dump($json);
		if(isset($json['Result']) && $json['Result']==0 && $json['Content']['IsActive']==1){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}

	
	//钱大师 点击
	function source_100($data,$list){
		// if(empty($data['callback'])){
		// 	echo'{"resultCode":-1,"errorMsg":"callback empty"}'; die;  
		// }
		
		$url = $list['source_url']."&idfa=".$data['idfa'].'&ip='.$data['ip'].'&mac=02:00:00:00:00:00';
		//echo $url;
		$file_contents   = $this->request_get($url);

		$json  = json_decode($file_contents,true );
		
		
		//print_r($json);exit;
		if($json['data']==1 && $json['status']==1){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}
	
	
	//钱大师 排重
	function IdfaRepeat_100($data,$list){
		$url = $list['IdfaRepeat_url'].'&idfa='.$data['idfa'];
		
		$file_contents = $this->request_get($url);
		$json = json_decode($file_contents,true);
		//var_dump($json);
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && !$json[$data['idfa']]){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}
	
	function submit_100($data,$list){
		$url = $list['submit_url']."&idfa=".$data['idfa'].'&ip='.$data['ip'];
		
		$file_contents = $this->request_get($url);

		$json = json_decode($file_contents,true);
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(isset($json['status']) && $json['status'] == 1){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}


	// 友邦金储宝理财 点击
	function source_103($data,$list){
		// if(empty($data['callback'])){
		// 	echo'{"resultCode":-1,"errorMsg":"callback empty"}'; die;
		// }
		// $id = $this->db->insert('aso_source', $data);
		// $inid = $this->db->insert_id();
		if(!isset($data['os'])){
			$data['os']='';
		}
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);
		//$url="http://106.15.36.23:8080/YouBang/SyncServer?type=ClickSync&idfa=".$data['idfa']."&ip=".$data['ip']."&callback=".$callback."&adid=".$addid."&channel=".$channelid."&clickKeyword=".$clickKeyword;
		//$file_contents = file_get_contents($url);
		$url=$list['source_url']."&device=".$data['device']."&idfa=".$data['idfa']."&ip=".$data['ip']."&callback=".$callback."&os=".$data['os'].'&keyword='.$data['keywords'];
		$file_contents   = $this->request_get($url);
		$json  = json_decode($file_contents,true );
		//print_r($url);die;
		//print_r($json);exit;
		//print_r($file_contents);exit;
		//var_dump($json);
		if(isset($json['resultCode']) && $json['resultCode']==1000){
			$this->db->insert('aso_source', $data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}

	// 友邦 排重
	function IdfaRepeat_103($data,$list){
		$url=$list['IdfaRepeat_url']."&idfa=".$data['idfa']."&ip=".$data['ip']."&os=&channel=10010";
		$file_contents = $this->request_get($url);
		// print_r($file_contents);exit;
		$json = json_decode($file_contents,true);
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());

		$this->db->insert('aso_IdfaRepeat_log',$log);
		// print_r($file_contents);die;
		//渠道返回：成功返回0  失败返回1
		//idfa 已存在标识为 1，不存在标识为 0。

		if(isset($json['data']) && $json['data'] == 1) {
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}elseif(isset($json["msg"]) && $json["msg"] == 0) {
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}elseif(isset($json["success"]) && $json["success"] && $json["exist"] == 0){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}elseif(isset($json["data"]) && !$json["error_code"] && !$json["data"][$data['idfa']]){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}elseif(isset($json[$data['idfa']]) && !$json[$data['idfa']]){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}
	
	//友邦 上报
	function submit_103($data,$list){
		$url = $list['submit_url'] . "&idfa=" . $data['idfa'] . "&ip=" . $data['ip'].'&keyword='.$data['keywords'];
		$file_contents = $this->request_get($url);
		$json = json_decode($file_contents,true);
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(isset($json['resultCode']) && $json['resultCode']==1000){//上报成功
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));
		}else{//失败
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
		}
	}


	//聚点 排重
	function IdfaRepeat_158($data,$list){

		$file_contents = $this->request_get($list['IdfaRepeat_url']."?adid=217&idfa=".$data['idfa']);
		// print_r($file_contents);exit;
		$json = json_decode($file_contents,true);
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());

		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && !$json[$data['idfa']]){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}

	//聚点 点击
	function source_158($data,$list){
		// if(empty($data['callback'])){
		// 	echo'{"resultCode":-1,"errorMsg":"callback empty"}'; die;
		// }

		$id = $this->db->insert('aso_source', $data);
		$inid = $this->db->insert_id();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=".$inid."&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);

		$file_contents   = $this->request_get($list['source_url']."&idfa=".$data['idfa']."&mac=02:00:00:00:00:00&ip=".$data['ip']."&callback=".$callback);
		// print_r($file_contents);exit;
		$json  = json_decode($file_contents,true );

		if(isset($json['status']) && $json['status'] == 1){
			// $this->db->insert('aso_source', $data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}


	//聚点 上报
	function submit_158($data,$list){
		$file_contents = $this->request_get($list['submit_url']."&idfa=".$data['idfa']."&ip=".$data['ip']."&mac=02:00:00:00:00:00");

		$json = json_decode($file_contents,true);
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);

		if(isset($json['status']) && $json['status'] == 1){//上报成功
				$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
				echo  json_encode(array('code'=>'0','result'=>'ok'));exit();
				
			}else{//失败 
				unset($data['ip']);
				$data['timestamp']=time();
				$data['type'] = 1;
				$data['json'] =$file_contents;
				$this->db->insert('aso_submit_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));exit();//这里返回1代表成功
			}
	}


	//优森 排重
	function IdfaRepeat_10783($data,$list){
		
		$file_contents = $this->request_get($list['IdfaRepeat_url']."?appid=".$data['appid']."&IDFA=".$data['idfa']);
		
		$json = json_decode($file_contents,true);
		
		//写入logecho 
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		
		
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && $json[$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}
	//优森 激活上报
	function submit_10783($data,$list){
		$sign   = strtoupper(MD5('1018'.$data['appid'].$data['idfa'].'Usen1036324'));

		$url = $list['submit_url']."?appid=".$data['appid']."&IDFA=".$data['idfa']."&Ip=".$data['ip'].'&Channel=1018&sign='.$sign;
		
		$file_contents = $this->request_get($url);
		
		$json = json_decode($file_contents,true);
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		
		if(isset($json['success']) && $json['success']==1){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}


	//来赚 排重
	function IdfaRepeat_191($data,$list){
		$sign          = md5($data['idfa'].time().'agikpcevmr');

		$file_contents = $this->request_get($list['IdfaRepeat_url']."&idfa=".$data['idfa']."&ip=".$data["ip"].'&time='.time().'&sign='.$sign);
		// print_r($file_contents);exit;
		$json = json_decode($file_contents,true);
		//echo $list['IdfaRepeat_url']."&idfa=".$data['idfa']."&ip=".$data["ip"].'&time='.time().'&sign='.$sign;
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());

		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json['data'][$data['idfa']]) && $json['data'][$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}

	//来赚 点击
	function source_191($data,$list){
		// if(empty($data['callback'])){
		// 	echo'{"resultCode":-1,"errorMsg":"callback empty"}'; die;
		// }

		$id = $this->db->insert('aso_source', $data);
		$inid = $this->db->insert_id();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=".$inid."&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);

		$file_contents   = $this->request_get($list['source_url']."&idfa=".$data['idfa']."&ip=".$data['ip']."&callback=".$callback);
		// print_r($list['source_url']."&idfa=".$data['idfa']."&ip=".$data['ip']."&callback=".$callback);exit;
		$json  = json_decode($file_contents,true );

		if(isset($json['success']) && $json['success'] == 1){
			// $this->db->insert('aso_source', $data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}

	//来赚 上报
	function submit_191($data,$list){
		$sign          = md5($data['idfa'].time().'agikpcevmr');

		$file_contents = $this->request_get($list['submit_url']."&idfa=".$data['idfa']."&ip=".$data["ip"].'&time='.time().'&sign='.$sign.'&keyword='.$data['keywords']);
		// print_r($file_contents);exit;
		$json = json_decode($file_contents,true);
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		
		if(isset($json['code']) && $json['code']==200){//上报成功
				$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
				echo  json_encode(array('code'=>'0','result'=>'ok'));exit();
				
			}else{//失败 
				unset($data['ip']);
				$data['timestamp']=time();
				$data['type'] = 1;
				$data['json'] =$file_contents;
				$this->db->insert('aso_submit_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));exit();//这里返回1代表成功
			}
	}
	

	//猎豆 九秀直播 排重
	 function IdfaRepeat_10065($data,$list){
		$file_contents = $this->request_get($list['IdfaRepeat_url']."&idfa=".$data['idfa']."&appid=".$data['appid']."&cparam=%7B%22source%22%3A%22liedou%22%2C%22appid%22%3A%22717804271%22%2C%22sign%22%3A%22sign%22%7D%20");
		
		//echo $file_contents;die;
		//写入log
		$json   = json_decode($file_contents,true);
		
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if($json['uniq']==1 && $json['msg']=='success'){
				
				//成功返回
				echo  json_encode(array($data['idfa']=>'1'));//这里返回1代表成功
			}else{
				$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents);//我们这里返回0代表失败不可做任务
				$contents = json_encode($a);
				echo  $contents;
			}
	}
	//猎豆 九秀直播 激活
	function submit_10065($data,$list){
		
		$sign          = md5("appBaseId=".$data['appid']."&channelId=20015&idfa=".$data['idfa']."slims267(envied");
		$file_contents = $this->request_get("http://m.liedou.com/batman/external/v2/activeClick?channelId=20015&cparam=%7B%22source%22%3A%22liedou%22%2C%22appid%22%3A%22717804271%22%2C%22sign%22%3A%22sign%22%7D&cpCid=30011&mac=02:00:00:00:00:00&appBaseId=".$data['appid']."&idfa=".$data['idfa']."&ip=".$data['ip']."&sign=".$sign);
	    $json = json_decode($file_contents,true);
	
		
		//echo $list['submit_url']."?uuid=".$data['idfa']."&appid=".$data['appid']."&deviceName=ipone4&deviceVersion=4.0.0&idfa=".$data['idfa']."&ip=".$data['ip']."&deviceMac=02:00:00:00:00:00&network=wifi&secretKey=NQiMpTaKdMp6cFSdWHoqvY7tPzU0t58f";
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);

		if($json['message']== 'success' && $json['code']==0){//上报成功
				$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
				echo  json_encode(array('code'=>'0','result'=>'ok'));exit();
				
			}else{//失败 
				
				$data['timestamp']=time();
				$data['type'] = 1;
				$data['json'] =$file_contents;
				$this->db->insert('aso_submit_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));exit();//这里返回1代表成功
			}
	}
	

	/*行者天下  点击*/
	function source_359($data,$list){
		$id = $this->db->insert('aso_source', $data);
		$inid = $this->db->insert_id();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=".$inid."&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
	
		$callback = urlencode($callback);

		$file_contents   = $this->request_get($list['source_url']."&idfa=".$data['idfa']."&client_ip=".$data['ip']."&callback=".$callback);
			//echo $list['source_url']."&idfa=".$data['idfa']."&client_ip=".$data['ip']."&callback=".$callback;
		$json     = json_decode($file_contents,true);
		
		if($json['success']){
			// $this->db->insert('aso_source', $data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}


	/*行者天下  排重*/
	function IdfaRepeat_359($data,$list){
		$file_contents = $this->request_get($list['IdfaRepeat_url']."&idfa=".$data['idfa']);
		
		$json = json_decode($file_contents,true);
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && !$json[$data['idfa']]){
			//成功返回
			echo  json_encode(array($data['idfa']=>'1'));//这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents);//我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;
		}
	}

	/*行者天下  上报*/
	function submit_359($data,$list){
		$file_contents = $this->request_get($list['submit_url']."&idfa=".$data['idfa']."&client_ip=".$data['ip']);

	    $json = json_decode($file_contents,true);
	
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);

		if($json['result']== 1){//上报成功
				$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
				echo  json_encode(array('code'=>'0','result'=>'ok'));exit();
				
			}else{//失败 
				unset($data['ip']);
				$data['timestamp']=time();
				$data['type'] = 1;
				$data['json'] =$file_contents;
				$this->db->insert('aso_submit_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));exit();//这里返回1代表成功
			}
	}

	

	//有为互动 排重
	function IdfaRepeat_66($data,$list){
		
		$file_contents = $this->request_get($list['IdfaRepeat_url']."&idfa=".$data['idfa'].'&appid='.$data['appid']);
		
		$json = json_decode($file_contents,true);
		//var_dump($json);
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		

		
		//渠道返回：成功返回0  失败返回1
		if(isset($json['status']) && $json['status']==20000 && $json['result']['allowed']){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}
	//有为互动 点击
	function source_66($data,$list){
		
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		
		$callback = urlencode($callback);

		 $url = $list['source_url']."&appid=".$data['appid']."&model=".$data['device']."&idfa=".$data['idfa']."&ip=".$data['ip']."&sysVer=".$data['os']."&word=".urlencode($data['keywords'])."&callbackUrl=".$callback;
		
		$file_contents   = $this->request_get($url);
		
		$json  = json_decode($file_contents,true);
		
		if(isset($json['status']) && $json['status']==20000 && $json['result']['success']){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}
	//有为互动 上报
	function submit_66($data,$list){
		$url = $list['submit_url']."&idfa=".$data['idfa']."&appid=".$data['appid'].'&mac=02:00:00:00:00:00&ip='.$data['ip'].'&word='.urlencode($data['keywords']);
		//echo $url;
		$file_contents = $this->request_get($url);

		$json = json_decode($file_contents,true);
		//var_dump($json);
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(isset($json['status']) && $json['status']==20000 && $json['result']['success']){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}
	

	

	/*掌贝网易新闻  排重*/
	function IdfaRepeat_597($data,$list){
		$file_contents = file_get_contents($list['IdfaRepeat_url'].'&idfa='.$data['idfa']."&ip=".$data['ip']);
		// print_r($file_contents);exit;
		// $json = json_decode($file_contents,true);
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		//var_dump($json);
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
 		$pos = strpos($file_contents, "0");
        if ($pos !== false){
			//成功返回
			echo  json_encode(array($data['idfa']=>'1'));//这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents);//我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;
		}
	}

	//掌贝网易新闻 点击
	function source_597($data,$list){
		$data['timestamp'] = time();
		$id = $this->db->insert('aso_source', $data);
		$inid = $this->db->insert_id();
		
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=".$inid."&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;

		$callback = urlencode($callback);

		$url=$list['source_url'].'&clientip='.$data['ip'].'&IDFA='.$data['idfa'].'&callback_url='.$callback;
		
        $file_contents = file_get_contents($url);
        // $file_contents = $this->StatusCode($url);
		// $json = json_decode($file_contents,true);

 		$pos = strpos($file_contents, "1");
        if ($pos !== false){
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
			// $data['json']=$file_contents;
			// $this->db->insert('aso_source_log',$data);
		}else{//失败 
			$data['json']=$file_contents;
			$this->db->insert('aso_source_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
			die;
		}
	}

	//掌贝网易新闻 激活上报
	function submit_597($data,$list){
		$url = $list['submit_url']."&idfa=".$data['idfa']."&ip=".$data['ip'];
		
		$file_contents = $this->request_get($url);
		
		$json =explode('|',$file_contents);
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if($json[0]=='1'){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}


	function IdfaRepeat_10343($data,$list){

		$data_al['time']   = time();
		$data_al['ip']     = isset($data['ip'])?$data['ip']:'127.0.0.1';
		$data_al['app_id'] = $data['appid'];
		$data_al['idfa']   = $data['idfa'];
		$data_al['channel_id'] =3002;

		$data_al['sign']   = strtoupper(md5('app_id='.$data_al['app_id'].'&channel_id=3002&idfa='.$data_al['idfa'].'&ip='.$data_al['ip'].'&time='.$data_al['time'].'&token=001B46D4ACF3BAC3D23548899A17FBBB'));
		// $data['sign']   = strtoupper(md5('app_id=123456789&channel_id=3000&idfa=63F784F7-3517-45B0-A6FF-DC4660AEDEFE&ip=58.214.177.114&time=1494406823&token=B9D2A457703473F3F9ABEE2BFE50A4D4'));
		$file_contents = $this->request_post($list['IdfaRepeat_url'],$data_al);
 
	
		// print_r($file_contents);exit;
		$json = json_decode($file_contents,true);
		
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json['success']) && $json['success']==1){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}

	function source_10343($data,$list){
		
		$data_al['time']   = time();
		$data_al['ip']     = isset($data['ip'])?$data['ip']:'127.0.0.1';
		$data_al['app_id'] = $data['appid'];
		$data_al['idfa']   = $data['idfa'];
		$data_al['platform_id']  = 0;
		$data_al['channel_id'] =3002;
		$sign = md5($data['timestamp'].md5('callback'));
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;

		$data_al['callback'] = urlencode($callback);
		$data_al['sign']   = strtoupper(md5('app_id='.$data_al['app_id'].'&channel_id=3002&idfa='.$data_al['idfa'].'&ip='.$data_al['ip'].'&platform_id=0&time='.$data_al['time'].'&token=001B46D4ACF3BAC3D23548899A17FBBB'));
		// $data['sign']   = strtoupper(md5('app_id=123456789&channel_id=3000&idfa=63F784F7-3517-45B0-A6FF-DC4660AEDEFE&ip=58.214.177.114&time=1494406823&token=B9D2A457703473F3F9ABEE2BFE50A4D4'));
		$file_contents = $this->request_post($list['source_url'],$data_al);

	
		// print_r($file_contents);exit;
		$json = json_decode($file_contents,true);
		
		
		if(isset($json['code']) && $json['code']==200){
			$this->db->insert('aso_source', $data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}

	function submit_10343($data,$list){
		
		$data_al['time']   = time();
		$data_al['ctime']   = $data_al['time'];
		$data_al['ip']     = isset($data['ip'])?$data['ip']:'127.0.0.1';
		$data_al['app_id'] = $data['appid'];
		$data_al['idfa']   = $data['idfa'];
		$data_al['platform_id']  = 0;
		$data_al['channel_id'] =3002;
		$data_al['sign']   = strtoupper(md5('app_id='.$data_al['app_id'].'&channel_id=3002&ctime='.$data_al['time'].'&idfa='.$data_al['idfa'].'&ip='.$data_al['ip'].'&platform_id=0&time='.$data_al['time'].'&token=001B46D4ACF3BAC3D23548899A17FBBB'));
		
		$url = $list['submit_url']."&idfa=".$data['idfa']."&ip=".$data_al['ip'];
		
		$file_contents = $this->request_post($list['submit_url'],$data_al);
		
		$json = json_decode($file_contents,true);
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(isset($json['success']) && $json['success']==1){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}




	
	/*聚鹏  排重*/
	function IdfaRepeat_9656($data,$list){
		$url = $list['IdfaRepeat_url']."&idfa=".$data['idfa'];
		$file_contents = $this->request_get($url);

		$json = json_decode($file_contents,true);
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		//var_dump($json);
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1

        if (isset($json[$data['idfa']]) && $json[$data['idfa']] == 0){
			//成功返回
			echo  json_encode(array($data['idfa']=>'1'));//这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents);//我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;
		}
	}

	//聚鹏 点击
	function source_9656($data,$list){
		$data['timestamp'] = time();
		
		
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;

		$callback = urlencode($callback);

		$url = $list['source_url']."&clientip=".$data['ip']."&idfa=".$data['idfa'].'&keywords='.urlencode($data['keywords']);

        $file_contents = $this->request_get($url);
// print_r($file_contents);exit;
        // $file_contents = $this->StatusCode($url);
		$json = json_decode($file_contents,true);

        if (isset($json['success']) && $json['success']){
        	$this->db->insert('aso_source', $data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
			// $data['json']=$file_contents;
			// $this->db->insert('aso_source_log',$data);
		}else{//失败 
			$data['json']=$file_contents;
			$this->db->insert('aso_source_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
			die;
		}
	}

	/*聚鹏  上报*/
	function submit_9656($data,$list){
		$file_contents = $this->request_get($list['submit_url']."&idfa=".$data['idfa']."&clientIp=".$data['ip'].'&keywords='.urlencode($data['keywords']));

	    $json = json_decode($file_contents,true);
	
		
		//echo $list['submit_url']."?uuid=".$data['idfa']."&appid=".$data['appid']."&deviceName=ipone4&deviceVersion=4.0.0&idfa=".$data['idfa']."&ip=".$data['ip']."&deviceMac=02:00:00:00:00:00&network=wifi&secretKey=NQiMpTaKdMp6cFSdWHoqvY7tPzU0t58f";
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);

		if(isset($json['success']) &&  $json['success']){//上报成功
				$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
				echo  json_encode(array('code'=>'0','result'=>'ok'));exit();
				
			}else{//失败 
				unset($data['ip']);
				$data['timestamp']=time();
				$data['type'] = 1;
				$data['json'] =$file_contents;
				$this->db->insert('aso_submit_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));exit();//这里返回1代表成功
			}
	}


	//塔防 排重
	function IdfaRepeat_10018($data,$list){
		if($data['appid']==569387346){
			echo  json_encode(array('code'=>'101','message'=>'Repeat error'));exit();
		}
		$url           = $list['IdfaRepeat_url']==''?'http://111.230.78.86:8080/interface/distinct':$list['IdfaRepeat_url'];
		
		$file_contents = $this->request_get($url."?idfa=".$data['idfa'].'&appid='.$data['appid']);
	
		// print_r($file_contents);exit;
		$json = json_decode($file_contents,true);
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && $json[$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else if(isset($json['result'][$data['idfa']]) && $json['result'][$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}
	//钱小咖点击
	function source_10018($data,$list){
		
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);
		if(!isset($data['ip'])){
			$data['ip']='';
		}
		$file_contents   = $this->request_get($list['source_url']."&muid=".$data['idfa']."&sign=".md5($data['idfa'].'727c0248e68c597c')."&callback=".$callback.'&ip='.$data['ip']);
		
		// print_r($file_contents);exit;
		$json  = json_decode($file_contents,true );
	
		
		if(isset($json['status']) && $json['status']==0){
			$this->db->insert('aso_source', $data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}
	//塔防 上报
	function submit_10018($data,$list){
		if($data['appid']==569387346){
			echo  json_encode(array('code'=>'101','message'=>'Active error'));exit();
		}
		$data_al['appid']  = $data['appid'];
		$data_al['idfa']   = $data['idfa'];
		$data_al['source'] = 'aipuyoubang';
		$data_al['ip']     = $data['ip'];
		$data_al['sign']   = md5('aipuyoubang|'.$data['appid'].'|'.$data['idfa'].'|'.'m5f5nohv4mk5gz2pa7h9hr5qrd9mv14b');
		if($list['submit_url'] && $data_al['appid']=='1408418200'){

			$urlDate = explode('&',explode('?',$list['submit_url'])[1]);
			$data_al['source'] = explode('=',$urlDate[0])[1];
			$data_al['sign']   = md5($data_al['source'].'|'.$data['appid'].'|'.$data['idfa'].'|'.'72ef5a527f5fb9088ef7818993b03efc');
			$url           = $list['submit_url']==''?'http://111.230.78.86:8080/interface/active':explode('?',$list['submit_url'])[0];


		}else{
			$url           = $list['submit_url']==''?'http://111.230.78.86:8080/interface/active':$list['submit_url'];
		}
		//$url           = $list['submit_url']==''?'http://111.230.78.86:8080/interface/active':$list['submit_url'];
		$json_data         = json_encode($data_al);
		$file_contents = $this->request_post2($url,$json_data);

	    $json = json_decode($file_contents,true);
	
		
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);

		if(isset($json['err_code']) &&  $json['err_code']==0){//上报成功
				$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
				echo  json_encode(array('code'=>'0','result'=>'ok'));exit();
				
			}else{//失败 
				unset($data['ip']);
				$data['timestamp']=time();
				$data['type'] = 1;
				$data['json'] =$file_contents;
				$this->db->insert('aso_submit_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));exit();//这里返回1代表成功
			}
	}


	//人人贷借款  排重
	function IdfaRepeat_10027($data,$list){

		$file_contents = $this->request_get($list['IdfaRepeat_url']."&apple_id=".$data['appid']."&idfas=".$data['idfa']);
	
		// print_r($file_contents);exit;
		$json = json_decode($file_contents,true);
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json['data']['idfas']) && $json['data']['idfas'][$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}
	
	//人人贷借款  点击
	function source_10027($data,$list){
		
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);

		$file_contents   = $this->request_get($list['source_url']."&ip=".$data['ip'].'&idfa='.$data['idfa']."&click_time=".$data['timestamp'].'&key='.$data['keywords'].'&notify_url='.$callback);
		
		// print_r($file_contents);exit;
		$json  = json_decode($file_contents,true );
	
		
		if(isset($json['errno']) && $json['errno']==0){
			$this->db->insert('aso_source', $data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}


	// 来福 上报激活
	function submit_10027($data,$list){
		$url = $list['submit_url']."&idfa=".$data['idfa']."&notify_time=".time();
		
		$file_contents = $this->request_get($url);
		
		$json = json_decode($file_contents,true);
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(isset($json['errno']) && $json['errno']==0){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}

	// 招财猫  排重
	function IdfaRepeat_10030($data,$list){


		$file_contents = $this->request_get($list['IdfaRepeat_url']."&uuid=".$data['idfa']);
	
		// print_r($file_contents);exit;
		$json = json_decode($file_contents,true);
		
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if( isset($json[$data['idfa']]) && $json[$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}

	
	//58新接口
	function source_10030($data,$list){
		
		// $id = $this->db->insert('aso_source', $data); 
		// $inid = $this->db->insert_id();
		if($data['appid']==1016820238){
				$callback = urlencode("http://asoapi.appubang.com/api/aso_advert/?back_id=&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".md5($data['timestamp'].md5('callback')));
				$sign     = md5('OMHCNBGCOGNZGYLH&2&'.$data['appid'].'&'.$data['timestamp']);

				$file_contents = $this->request_get($list['source_url'].'&ost=2&appid='.$data['appid'].'&uuid='.$data['idfa'].'&ip='.$data['ip'].'&timestamp='.$data['timestamp'].'&sign='.$sign.'&callback='.$callback);

				$json               = json_decode($file_contents,true);
			
			
			if($json['success']==1 && isset($json['success'])){
				$this->db->insert('aso_source', $data); 
				echo  json_encode(array('code'=>'0','result'=>'ok'));die;
			}else{
				$data['json'] =$file_contents;
				$this->db->insert('aso_source_log', $data); 
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; //这里返回1代表成功
			}
		}else{
			$callback = "http://asoapi.appubang.com/api/aso_advert/?back_id=&k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".md5($data['timestamp'].md5('callback'));
			$res= "[{'ost':3,'uuid':'{$data['idfa']}','osn':'{$data['device']}','osv':'{$data['os']}','ip':'{$data['ip']}','callback':'{$callback}'}]";
			
		   list($t1, $t2) = explode(' ', microtime());
	       $datetime      = explode('.',$t2 . '.' .  ceil( ($t1 * 1000) ))[0].explode('.',$t2 . '.' .  ceil( ($t1 * 1000) ))[1];

			$data_al['params']  = urlencode($res);
			//var_dump(json_encode($res));
	       //$data_al['params']  = json_encode($res);
			if($data['appid']==1147166510){
				$data_al['app']     = '58citynet_aipuyoubang';
			}else if($data['appid']==1169404447){
				$data_al['app']     = 'citynet_aipuyoubang';
			}else{
				$data_al['app']     = 'aipuyoubang';
			}
			
			$data_al['ts']		= $datetime;
			//模拟get
			
			$file_contents      = $this->request_post('http://appces.58.com/notice',$data_al);

			//var_dump($data_al);
			// echo $data_al['ts'];
			// var_dump($res);
			$json               = json_decode($file_contents,true);
			
			
			if($json['code']==0 && !empty($json['data']) && $json['data'][0]['status']==0){
				$this->db->insert('aso_source', $data); 
				echo  json_encode(array('code'=>'0','result'=>'ok'));die;
			}else{
				$data['json'] =$file_contents;
				$this->db->insert('aso_source_log', $data); 
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; //这里返回1代表成功
			}
		}
	}

	
	//尚邻 排重
	function IdfaRepeat_10033($data,$list){
		$data_al['adid']   = explode('=',$list['IdfaRepeat_url'])[1];
		$data_al['appid']  = $data['appid'];
		$data_al['idfas']  = $data['idfa'];
		$data_al['sign']   = md5('adid='.explode('=',$list['IdfaRepeat_url'])[1].'&appid='.$data['appid'].'&idfas='.$data['idfa'].'&d41d8cd98f00b204e9800998ecf8427e');
		

		$file_contents = $this->request_post(explode('?',$list['IdfaRepeat_url'])[0],$data_al);
	
		// print_r($file_contents);exit;
		$json = json_decode($file_contents,true);
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if($json['code']==0 && $json['data'][$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}
	//尚邻 点击
	function source_10033($data,$list){
		$data['timestamp']  = time();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);

		$data_al['adid']       = explode('=',$list['IdfaRepeat_url'])[1];
		$data_al['appid']      = $data['appid'];
		$data_al['idfa']      = $data['idfa'];
		$data_al['ip']         = $data['ip'];
		$data_al['notify_url'] = $callback;
		$data_al['sign']       = md5('adid='.explode('=',$list['source_url'])[1].'&appid='.$data['appid'].'&idfa='.$data['idfa'].'&ip='.$data['ip'].'&notify_url='.$callback.'&d41d8cd98f00b204e9800998ecf8427e');

		

		
		$file_contents   = $this->request_post(explode('?',$list['source_url'])[0],$data_al);
		
		// print_r($file_contents);exit;
		$json  = json_decode($file_contents,true );
	
		
		if(isset($json['code']) && $json['code']==0){
			$this->db->insert('aso_source', $data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}


	//赚客 排重
	function IdfaRepeat_10034($data,$list){


		$file_contents = $this->request_get($list['IdfaRepeat_url']."&idfa=".$data['idfa']);
	
		// print_r($file_contents);exit;
		$json = json_decode($file_contents,true);
		
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json['repeat']) && $json['repeat']==1){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else if(isset($json[$data['idfa']]) && $json[$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}

	//赚客 激活上报
	function submit_10034($data,$list){
		$url = $list['submit_url']."&idfa=".$data['idfa']."&taskip=".$data['ip'];
		
		$file_contents = $this->request_get($url);
		
		$json = json_decode($file_contents,true);
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(isset($json[$data['idfa']]) && $json[$data['idfa']]==0){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}

	

	//雪球 点击 
	function source_10037($data,$list){
		
		$data['timestamp']  = time();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);
		
		$file_contents   = $this->request_get($list['source_url'].'?idfa='.$data['idfa'].'&channel=xianhou&callback='.$callback);
		
		$json            = json_decode($file_contents,true);
		
		
		
		if(isset($json['success']) && $json['success']){
			$this->db->insert('aso_source', $data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}
	//雪球   排重
	function IdfaRepeat_10037($data,$list){

		$data_al['idfa']    = $data['idfa'];
		$data_al['appid']   = $data['appid'];

		$file_contents = $this->request_post($list['IdfaRepeat_url'],$data_al);
	
		// print_r($file_contents);exit;
		$json = json_decode($file_contents,true);
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && $json[$data['idfa']]=='0'){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}


	//瑞盈互动 排重 
	function IdfaRepeat_10209($data,$list){

		$data_al['appid']   = $data['appid'];
		$data_al['idfa']    = $data['idfa'];
		$data_al['ip']      = $data['ip'];
		$data_al['scode']   = 'CHN8HUSKWUAH787JHG';
		
		$file_contents = $this->request_post2($list['IdfaRepeat_url'],json_encode($data_al));
		
		$json = json_decode($file_contents,true);
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
	
		
		//渠道返回：成功返回0  失败返回1
		if($json['data'][$data['idfa']]==1 && $json['status']==1){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}elseif($json['data'][$data['idfa']]==0 && $json['status']==1){
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}else{
			echo $file_contents;die;
		}
	}
	//瑞盈互动 点击 
	function source_10209($data,$list){
		$data['timestamp']  = time();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);
		$data_al['appid']   = $data['appid'];
		$data_al['idfa']    = $data['idfa'];
		$data_al['scode']   = 'CHN8HUSKWUAH787JHG';
		$data_al['ip']      = $data['ip'];
		$data_al['keyword'] = $data['keywords'];
		if($list['is_advert']==1){
			$data_al['callbackUrl']  = $callback;
		}
		$file_contents   = $this->request_post2($list['source_url'],json_encode($data_al));
		
		$json  = json_decode($file_contents,true);
		
		if($json["status"]==1 && isset($json['status'])){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}
	//瑞盈互动 上报 
	function submit_10209($data,$list){
		$data_al['appid']   = $data['appid'];
		$data_al['idfa']    = $data['idfa'];
		$data_al['scode']   = 'CHN8HUSKWUAH787JHG';
		$data_al['ip']      = $data['ip'];
		$data_al['keyword'] = $data['keywords'];
		$data_al['isComp']  = 1;
		
		$file_contents   = $this->request_post2($list['submit_url'],json_encode($data_al));
		
		$json  = json_decode($file_contents,true);
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if($json["status"]==1 && isset($json['status'])){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}


	
	//谢总 排重

	function IdfaRepeat_10039($data,$list){
		if($data['appid']==463150061){
			$params['idfa']        = $data['idfa'];
			$params['app_id']      = $data['appid'];
			$params['time_stamp']  = time();
			$params['app_secret']  = 'xt3Z4rHLOyjqp2';
			$params['source']      = 'fenmei';
			ksort($params);

			$data_al  = implode('',$params);
			$sign     = md5($data_al);

			$file_contents = $this->request_get($list['IdfaRepeat_url']."&idfa=".$data['idfa'].'&app_id='.$data['appid'].'&source=fenmei&time_stamp='.time().'&sign='.$sign);
			
			// print_r($file_contents);exit;
			$json = json_decode($file_contents,true);
			
			
			//写入log
			$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
			
			$this->db->insert('aso_IdfaRepeat_log',$log);
			//渠道返回：成功返回0  失败返回1
			if(isset($json['body'][$data['idfa']]) && $json['body'][$data['idfa']]==1){
				echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
			}else{
				$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
				$contents = json_encode($a);
				echo  $contents;exit();
			}

		 }else{

		 	$params['idfa']        = $data['idfa'];
			$params['appid']       = $data['appid'];
			$params['time']        = time();
			
			$params['sign']        = md5(explode('?',$list['IdfaRepeat_url'])[1].time());
			$params['source']      = explode('?',$list['IdfaRepeat_url'])[1];

			$file_contents = $this->request_post($list['IdfaRepeat_url'],$params);
			
			// print_r($file_contents);exit;
			$json = json_decode($file_contents,true);
			
			
			//写入log
			$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
			
			$this->db->insert('aso_IdfaRepeat_log',$log);
			//渠道返回：成功返回0  失败返回1
			if(isset($json[$data['idfa']]) && $json[$data['idfa']]==0){
				echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
			}else{
				$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
				$contents = json_encode($a);
				echo  $contents;exit();
			}


		 }
	}
	//谢总 点击
	function source_10039($data,$list){
		if($data['appid']==1308869647){
			$data['timestamp']  = time();
			$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
			$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
			$callback = urlencode($callback);
			
			$file_contents   = $this->request_get($list['source_url'].'&idfa='.$data['idfa'].'&app='.$data['appid'].'&callback='.$callback);
			
			$json            = json_decode($file_contents,true);
			
			
			
			if(isset($json['success']) && $json['success']){
				$this->db->insert('aso_source', $data);
				echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
			}else{
				$data['json'] =$file_contents;
				$this->db->insert('aso_source_log', $data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
			}

		 }
	}
	//掌阅 激活上报
	function submit_10039($data,$list){
		$params['idfa']        = $data['idfa'];
		$params['app_id']      = $data['appid'];
		$params['time_stamp']  = time();
		$params['app_secret']  = 'xt3Z4rHLOyjqp2';
		$params['source']      = 'fenmei';
		ksort($params);

		$data_al  = implode('',$params);
		$sign     = md5($data_al);
		$file_contents = $this->request_get($list['submit_url']."&idfa=".$data['idfa']."&app_id=".$data['appid'].'&source=fenmei&time_stamp='.time().'&sign='.$sign);

	    $json = json_decode($file_contents,true);
	
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		
		if(isset($json['body']['success']) && $json['body']['success']==1){//上报成功
				$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
				echo  json_encode(array('code'=>'0','result'=>'ok'));exit();
				
			}else{//失败 
				unset($data['ip']);
				$data['timestamp']=time();
				$data['type'] = 1;
				$data['json'] =$file_contents;
				$this->db->insert('aso_submit_log',$data);
				echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));exit();//这里返回1代表成功
			}
	}

	//66钱庄 排重
	function IdfaRepeat_10087($data,$list){
		
		//date_default_timezone_set('Asia/Shanghai');
		// var_dump($list);
		$ParamsData             = explode('?',$list['IdfaRepeat_url'])[1];
		//echo $ParamsData;
		$appid                  = explode('=',explode('&',$ParamsData)[0])[1];
		//echo $appid;
		$res['appId']           = explode('=',explode('&',$ParamsData)[0])[1];

		
		//echo $res['AppId'];
		$res['idfas'][0]        = $data['idfa'];
		$res['channelId']       = explode('=',explode('&',$list['IdfaRepeat_url'])[1])[1];
		$json_string            = json_encode($res);
		
		$ChAppId                = explode('=',explode('&',$ParamsData)[2])[1];
		
        $url                    = explode('?',$list['IdfaRepeat_url'])[0];
      
		$file_contents          = $this->QzSendRequest($url,$json_string,$ChAppId,$data['appid']);
		
		//echo $file_contents;
		$json = json_decode($file_contents,true);
		
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json['data']) && $json['code']==1 && $json['data']['checkIdfaResults'][0]['isActive']==1){
			echo  json_encode(array($data['idfa']=>'1')); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;
		}
	}
	
	//66钱庄 点击
	function source_10087($data,$list){
	  //date_default_timezone_set('Asia/Shanghai');
		// var_dump($list);
		$data['timestamp']      = time();
		$sign                   = md5($data['timestamp'].md5('callback'));
		$ParamsData             = explode('?',$list['source_url'])[1];
		//echo $ParamsData;
		$appid                  = explode('=',explode('&',$ParamsData)[0])[1];
		//echo $appid;
		$res['appId']           = explode('=',explode('&',$ParamsData)[0])[1];

		$res['callbackUrl']     = urlencode("http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign);
		//echo $res['AppId'];
		$res['idfa']            = $data['idfa'];
		$res['deviceId']        = $data['device'];
		$res['channelId']       = explode('=',explode('&',$list['source_url'])[1])[1];
		$json_string            = json_encode($res);
		
		$ChAppId                = explode('=',explode('&',$ParamsData)[2])[1];
		
        $url                    = explode('?',$list['source_url'])[0];
      
		$file_contents          = $this->QzSendRequest($url,$json_string,$ChAppId,$data['appid']);
		
		$json = json_decode($file_contents,true);
		
		
		//写入log
		if(isset($json['data']) && $json['code']==1 && $json['data']['isActive']==1){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}

	//66钱庄 排重
	function IdfaRepeat_10089($data,$list){
		
		//date_default_timezone_set('Asia/Shanghai');
		// var_dump($list);
		$ParamsData             = explode('?',$list['IdfaRepeat_url'])[1];
		//echo $ParamsData;
		$appid                  = explode('=',explode('&',$ParamsData)[0])[1];
		//echo $appid;
		$res['appId']           = explode('=',explode('&',$ParamsData)[0])[1];

		
		//echo $res['AppId'];
		$res['idfas'][0]        = $data['idfa'];
		$res['channelId']       = explode('=',explode('&',$list['IdfaRepeat_url'])[1])[1];
		$json_string            = json_encode($res);
		
		$ChAppId                = explode('=',explode('&',$ParamsData)[2])[1];
		
        $url                    = explode('?',$list['IdfaRepeat_url'])[0];
      
		$file_contents          = $this->QzSendRequest($url,$json_string,$ChAppId,$data['appid']);
		
		//echo $file_contents;
		$json = json_decode($file_contents,true);
		
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json['data']) && $json['code']==1 && $json['data']['checkIdfaResults'][0]['isActive']==1){
			echo  json_encode(array($data['idfa']=>'1')); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;
		}
	}
	
	//66钱庄 点击
	function source_10089($data,$list){
	  //date_default_timezone_set('Asia/Shanghai');
		// var_dump($list);
		$data['timestamp']      = time();
		$sign                   = md5($data['timestamp'].md5('callback'));
		$ParamsData             = explode('?',$list['source_url'])[1];
		//echo $ParamsData;
		$appid                  = explode('=',explode('&',$ParamsData)[0])[1];
		//echo $appid;
		$res['appId']           = explode('=',explode('&',$ParamsData)[0])[1];

		$res['callbackUrl']     = urlencode("http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign);
		//echo $res['AppId'];
		$res['idfa']            = $data['idfa'];
		$res['deviceId']        = $data['device'];
		$res['channelId']       = explode('=',explode('&',$list['source_url'])[1])[1];
		$json_string            = json_encode($res);
		
		$ChAppId                = explode('=',explode('&',$ParamsData)[2])[1];
		
        $url                    = explode('?',$list['source_url'])[0];
      
		$file_contents          = $this->QzSendRequest($url,$json_string,$ChAppId,$data['appid']);
		
		$json = json_decode($file_contents,true);
		
		
		//写入log
		if(isset($json['data']) && $json['code']==1 && $json['data']['isActive']==1){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}


	//博睿排重 
	function IdfaRepeat_10101($data,$list){
		
		$file_contents = $this->request_get($list['IdfaRepeat_url']."&idfa=".$data['idfa']);
		
		$json = json_decode($file_contents,true);
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		

		
		//渠道返回：成功返回0  失败返回1
		if($json['result'][$data['idfa']]==0 && isset($json['result'][$data['idfa']])){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}
	//博睿点击 
	function source_10101($data,$list){
		
		$url = $list['source_url'] ."&idfa=".$data['idfa']."&ip=".$data['ip'].'&device='.$data['device'].'&osv='.$data['os'];
		$file_contents   = $this->request_get($url);
		
		$json  = json_decode($file_contents,true);
		
		if($json["code"]==200 && isset($json["code"])){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}
	//博睿上报 
	function submit_10101($data,$list){
		$url         = $list['submit_url']."&idfa=".$data['idfa']."&client_ip=".$data['ip'].'&mac=';

		$SubmitData  = $this->db->get_where('aso_submit2',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();

		$file_contents='';
		if(empty($SubmitData)){
			$file_contents = $this->request_get($url);
		}else{
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}
		//$file_contents = $this->request_get($url);
		
		$json = json_decode($file_contents,true);
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(isset($json['code']) && $json['code'] == 200){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
		        $data['timestamp']=time();
		        $data['type']=1;
			 $this->db->insert('aso_submit2',$data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}

	



	//直客 袁艳萍 排重 
	function IdfaRepeat_10042($data,$list){
		
		if($data['appid']==1076275012){
			$file_contents = $this->request_get($list['IdfaRepeat_url']."?idfa=".$data['idfa'].'&appid='.$data['appid']);
		}else{

			$file_contents = $this->request_get($list['IdfaRepeat_url']."&idfas=".$data['idfa'].'&appid='.$data['appid'].'&client_ip='.$data['ip']);
		}
		// print_r($file_contents);exit;
		$json = json_decode($file_contents,true);
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json['code']) && $json['code']==0){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else if(isset($json[$data['idfa']]) && $json[$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}
	//直客 袁艳萍 点击 
	function source_10042($data,$list){
		// if(empty($data['callback'])){
		// 	echo'{"resultCode":-1,"errorMsg":"callback empty"}'; die;  
		// }
		$data['timestamp']  = time();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);

		if($data['appid']==1076275012){
			$url = $list['source_url'] ."&idfa=".$data['idfa']."&appid=".$data['appid']."&ip=".$data['ip']."&timestamp=".$data['timestamp'].'&sign='.md5($data['timestamp'].'522aa4e0eff61267d3921a8881a29e92').'&callback='.$callback;
		}else{

			$url = $list['source_url'] ."&idfa=".$data['idfa']."&appid=".$data['appid']."&client_ip=".$data['ip'].'&callback='.$callback;
		}
		
		$file_contents   = $this->request_get($url);
		
		$json  = json_decode($file_contents,true);
		
		if(isset($json['code']) && $json["code"]==1){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}
	//直客 袁艳萍 激活上报
	function submit_10042($data,$list){
		$idfa          = $data['idfa'];

		$Tlast         = substr($idfa,strlen($idfa)- 1,1);

		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);

		if($Tlast==1 || $Tlast==2){
			$data['timestamp']=time();
			$data['type'] = 0;
			$this->db->insert('aso_gnh_submit',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));
		}else{
			$data['timestamp']=time();
			$data['type'] = 1;
			$this->db->insert('aso_gnh_submit',$data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));
		}
		
	}

	
	//果推 排重
	function IdfaRepeat_10044($data,$list){
		
		$file_contents = $this->request_get($list['IdfaRepeat_url']."?idfa=".$data['idfa']."&appid=".$data['appid']);
		
		$json = json_decode($file_contents,true);
		
		//写入logecho 
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		
		
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && $json[$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}
	///果推 点击 
	function source_10044($data,$list){
		
		$data['timestamp']  = time();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		
		$url = $list['source_url'] ."&idfa=".$data['idfa']."&appid=".$data['appid']."&ip=".$data['ip'].'&k='.$data['timestamp'].'&callback='.$sign;
		
		$file_contents   = $this->request_get($url);
		
		// $json  = json_decode($file_contents,true);
		
		if(is_numeric($file_contents) && $file_contents==1){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}

	//蚂蚁小咖 排重
	function IdfaRepeat_10046($data,$list){
		
		$file_contents = $this->request_get($list['IdfaRepeat_url']."&appid=".$data['appid']."&idfa=".$data['idfa']."&ip=".$data['ip']);
		
		$json = json_decode($file_contents,true);
		
		//写入logecho 
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		
		
		//渠道返回：成功返回0  失败返回1
		if(isset($json['status']) && $json['status']==1){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}
	//蚂蚁小咖 激活上报
	function submit_10046($data,$list){
		$url = $list['submit_url']."&appid=".$data['appid']."&idfa=".$data['idfa']."&ip=".$data['ip'].'&os='.'&keyword='.urlencode($data['keywords']);
		
		$file_contents = $this->request_get($url);
		
		$json = json_decode($file_contents,true);
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		
		if(isset($json['status']) && $json['status']){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}

	// 蜜蜂试玩 点击
	function source_10264($data,$list){
		$ArData             = parse_url($list['IdfaRepeat_url'])['query'];
		parse_str($ArData);
		$data['timestamp']  = time();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);
		$data['sign']   = md5($source.'|'.$adid.'|'.$data['idfa'].'|'.'m5f5nohv4mk5gz2pa7h9hr5qrd9mv14b'.'|'.$data['timestamp']);
		if($list['is_advert']==1){
			$url = $list['source_url'] ."&idfa=".$data['idfa']."&ip=".$data['ip']."&keyword=".$data['keywords']."&timestamp=".$data['timestamp']."&device_type=".$data['device'].'&os_version='.$data['os'].'&sign='.$data['sign'].'&callback='.$callback;
		}else{
			$url = $list['source_url'] ."&idfa=".$data['idfa']."&ip=".$data['ip']."&keyword=".$data['keywords']."&timestamp=".$data['timestamp']."&device_type=".$data['device'].'&os_version='.$data['os'].'&sign='.$data['sign'];
		}
		
		$file_contents   = $this->request_get($url);
		
		 $json  = json_decode($file_contents,true);
		
		if(isset($json['err_code']) && $json['err_code']==0){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}

	//蜜蜂试玩 排重
	function IdfaRepeat_10264($data,$list){
		$ArData             = parse_url($list['IdfaRepeat_url'])['query'];
		parse_str($ArData);
		
		$data['timestamp']  = time();

		$data['sign']   = md5($source.'|'.$adid.'|'.$data['idfa'].'|'.'m5f5nohv4mk5gz2pa7h9hr5qrd9mv14b'.'|'.$data['timestamp']);
// echo $source.'|'.$adid.'|'.$data['idfa'].'|'.'m5f5nohv4mk5gz2pa7h9hr5qrd9mv14b'.'|'.$data['timestamp'];
 		$url = $list['IdfaRepeat_url'] ."&idfa=".$data['idfa']."&ip=".$data['ip']."&keyword=测试&timestamp=".$data['timestamp']."&device_type=iphone&os_version=9.1&sign=".$data['sign'];
 		
		$file_contents   = $this->request_get($url);
		
		 $json  = json_decode($file_contents,true);
		
		//写入logecho 
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		
		
		//渠道返回：成功返回0  失败返回1
		if(isset($json['result'][$data['idfa']]) && $json['result'][$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}

	//蜜蜂试玩上报 
	function submit_10264($data,$list){
		$ArData             = parse_url($list['IdfaRepeat_url'])['query'];
		parse_str($ArData);
		
		$data['timestamp']  = time();

		$data['sign']   = md5($source.'|'.$adid.'|'.$data['idfa'].'|'.'m5f5nohv4mk5gz2pa7h9hr5qrd9mv14b'.'|'.$data['timestamp']);

		$url = $list['submit_url'] ."&idfa=".$data['idfa']."&ip=".$data['ip']."&keyword=测试&timestamp=".$data['timestamp']."&device_type=iphone&os_version=9.1&sign=".$data['sign'];
		
		$file_contents   = $this->request_get($url);
		
		 $json  = json_decode($file_contents,true);
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(isset($json['err_code']) && $json['err_code']==0){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}
	//啪啪彩票 排重
	function IdfaRepeat_10048($data,$list){
		$data_al['idfa']   = $data['idfa'];
		$file_contents     = $this->request_post($list['IdfaRepeat_url'],$data_al);
		
		$json = json_decode($file_contents,true);
		
		//写入logecho 
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		
		
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && !$json[$data['idfa']]){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}

	//U盟  排重
	function IdfaRepeat_10073($data,$list){
		
		$file_contents     = $this->request_get($list['IdfaRepeat_url'].'?appid='.$data['appid'].'&idfa='.$data['idfa']);
		
		$json = json_decode($file_contents,true);
		
		//写入logecho 
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		
		
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && !$json[$data['idfa']]){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}

	function source_10073($data,$list){
		$data['timestamp']  = time();
		$sign     = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		

		$nsign    = md5('appid='.$data['appid'].'&ch=abby.sy@AIYILI&idfa='.$data['idfa'].'&pburl='.$callback.'&time='.$data['timestamp'].'344e95cdff');
		//echo 'appid='.$data['appid'].'&ch=abby.sy@AIYILI&idfa='.$data['idfa'].'&phurl='.$callback.'&time='.$data['timestamp'].'344e95cdff';

		$callback = urlencode($callback);
		$url      = $list['source_url'] ."?ch=abby.sy@AIYILI&idfa=".$data['idfa']."&sign=".$nsign."&time=".$data['timestamp']."&appid=".$data['appid'].'&pburl='.$callback;

		
		//echo $nsign;
		$file_contents   = $this->request_get($url);
		
		 $json  = json_decode($file_contents,true);
		
		if(isset($json['status']) && $json['status']==200){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}



	//小白 排重
	function IdfaRepeat_10100($data,$list){
		
		$file_contents     = $this->request_get($list['IdfaRepeat_url'].'?appid='.$data['appid'].'&idfa='.$data['idfa'].'&timestamp='.$data['timestamp']);
		
		$json = json_decode($file_contents,true);
		
		//写入logecho 
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		
		
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && !$json[$data['idfa']]){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}
	//小白  点击
	function source_10100($data,$list){
		$data['timestamp']  = time();
		$sign     = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		

		$nsign    = md5('appid='.$data['appid'].'&callback='.$callback.'&idfa='.$data['idfa'].'&source=AYLKJ&timestamp='.$data['timestamp'].'5AEFC74B3336952B84FB82BD');
		//echo 'appid='.$data['appid'].'&ch=abby.sy@AIYILI&idfa='.$data['idfa'].'&phurl='.$callback.'&time='.$data['timestamp'].'344e95cdff';

		$callback = urlencode($callback);
		$url      = $list['source_url'] .'?appid='.$data['appid'].'&idfa='.$data['idfa']."&sign=".$nsign."&timestamp=".$data['timestamp'].'&source=AYLKJ'.'&callback='.$callback;

		 
		//echo $nsign;
		$file_contents   = $this->request_get($url);
		
		 $json  = json_decode($file_contents,true);
		
		if(isset($json['code']) && $json['code']==0){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}

	// 定当  国美排重 
	function IdfaRepeat_10093($data,$list){
		$data_al['sId']       = 38;
		$data_al['sign']      = '8babda9df54c8639d1ba50049a68a713';
		
		$data_al['idfa']      = array($data['idfa']);
		$file_contents        = $this->request_post2($list['IdfaRepeat_url'],json_encode($data_al));
		
		$json = json_decode($file_contents,true);
		
		//写入logecho 
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		
		
		//渠道返回：成功返回0  失败返回1
		if($json['isSuccess']=='N' && empty($json['failReason'])){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0'); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}
	// 定当  国美点击 
	function source_10093($data,$list){
		$data['timestamp']  = time();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		
		$callback = urlencode($callback);
		$url      = $list['source_url'] .'?appid='.$data['appid']."&idfa=".$data['idfa']."&ip=".$data['ip']."&ua=1&callback=".$callback;

		
		//echo $nsign;
		$file_contents   = $this->request_get($url);
		
		 $json  = json_decode($file_contents,true);
		
		if(isset($json['status']) && $json['status']==0){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false'));die; 
		}
	}
	//爱盈利-1  排重
	function IdfaRepeat_10999($data,$list){
		$key = MD5($data['appid'].$data['idfa'].'106639931e8e5f56c3572956f014882ba');
		$file_contents     = $this->request_get($list['IdfaRepeat_url'].'?appid='.$data['appid'].'&idfa='.$data['idfa'].'&pipeId=1&key='.$key);
		//echo $list['IdfaRepeat_url'].'?appid='.$data['appid'].'&idfa='.$data['idfa'].'&key='.$key;
		
		$json = json_decode($file_contents,true);
		
		//写入logecho 
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		
		//var_dump($json);
		//渠道返回：成功返回0  失败返回1
		if(isset($json[$data['idfa']]) && $json[$data['idfa']]==1){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}
	//爱盈利-1点击
	function source_10999($data,$list){
		$data['timestamp']  = time();
		$sign     = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		

		//$nsign    = md5('appid='.$data['appid'].'&callback='.$callback.'&idfa='.$data['idfa'].'&source=AYLKJ&timestamp='.$data['timestamp'].'5AEFC74B3336952B84FB82BD');
		//echo 'appid='.$data['appid'].'&ch=abby.sy@AIYILI&idfa='.$data['idfa'].'&phurl='.$callback.'&time='.$data['timestamp'].'344e95cdff';
		$key = MD5($data['appid'].$callback.$data['idfa'].$data['ip'].'1'.$data['timestamp'].'06639931e8e5f56c3572956f014882ba');
		$callback = urlencode($callback);
		$url= $list['source_url'] .'?appid='.$data['appid'].'&idfa='.$data['idfa']."&ip=".$data['ip']."&timestamp=".$data['timestamp'].'&callback='.$callback.'&pipeId=1&key='.$key;

		 //echo $url."<br/>";
		//echo $nsign;
		$file_contents   = $this->request_get($url);
		
		 $json  = json_decode($file_contents,true);
		//var_dump($json);
		if(isset($json['code']) && $json['code']==0){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}
   //爱盈利-1上报
	function submit_10999($data,$list){
		$url = $list['submit_url'] ."?appid=" . $data['appid'] . "&idfa=" . $data['idfa']."&pipeId=1&key=".md5($data['appid'].$data['idfa'].'106639931e8e5f56c3572956f014882ba');
		//echo $url;
		$file_contents = $this->request_get($url);
		//echo $url."<br/>";
		$json = json_decode($file_contents,true);
	//var_dump($json);
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		//var_dump($json);
		if($json['code']==0 && isset($json['code'])){//上报成功
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
		}
	}

	/*百福 排重接口*/
	function IdfaRepeat_10265($data,$list){

		$body='{"requestHeader":{"requestSequence":"11111111111111111111111111111111","channelCode":"iMoney","requestDate":"'.date('Ymd',time()).'","requestTime":"'.time().'"},"requestBody":"{\"deviceType\":\"iOS\",\"storeAppId\":\"'.$data['appid'].'\",\"idfa\":\"'.$data['idfa'].'\"}"}';
		 $body_data     = json_encode(array('deviceType'=>'iOS','storeAppId'=>$data['appid'],'idfa'=>$data['idfa']));

		 $file_contents = $this->request_post2($list['IdfaRepeat_url'], $body);
		 
		$json = json_decode($file_contents,true);
	
		//var_dump($json);
		//写入logecho 
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
		
		
		//渠道返回：成功返回0  失败返回1
		if(isset($json['responseBody']) && $json['responseBody']=='{"isRepeat":"N"}'){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else if(isset($json['responseBody']) && $json['responseBody']=='{"isRepeat":"Y"}'){
			echo  json_encode(array($data['idfa']=>'0','ErrorInfo'=>$file_contents));die; //这里返回1代表成功
		}else{
			 echo $file_contents;
		}
	}
	//*百福 点击接口*/
	function source_10265($data,$list){
		// if(empty($data['callback'])){
		// 	echo'{"resultCode":-1,"errorMsg":"callback empty"}'; die;  
		// }
		$data['timestamp']  = time();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);
		$body='{"requestHeader":{"requestSequence":"11111111111111111111111111111111","channelCode":"iMoney","requestDate":"'.date('Ymd',time()).'","requestTime":"'.time().'"},"requestBody":"{\"deviceType\":\"iOS\",\"storeAppId\":\"'.$data['appid'].'\",\"idfa\":\"'.$data['idfa'].'\",\"ip\":\"'.$data['ip'].'\",\"timestamp\":\"'.time().'\",\"callbackUrl\":\"'.$callback.'\"}"}';
			
		$file_contents   = $this->request_post2($list['source_url'],$body);
		
		$json  = json_decode($file_contents,true);
		
		
		if(isset($json['responseHeader']['code']) && $json['responseHeader']['code']==0000){
			$this->db->insert('aso_source', $data); 
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data); 
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die; 
		}
	}
	//*百福 上报接口*/
	function submit_10265($data,$list){
		$data['timestamp']  = time();
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);
		$body='{"requestHeader":{"requestSequence":"11111111111111111111111111111111","channelCode":"iMoney","requestDate":"'.date('Ymd',time()).'","requestTime":"'.time().'"},"requestBody":"{\"deviceType\":\"iOS\",\"storeAppId\":\"'.$data['appid'].'\",\"idfa\":\"'.$data['idfa'].'\",\"ip\":\"'.$data['ip'].'\",\"timestamp\":\"'.time().'\",\"callbackUrl\":\"'.$callback.'\"}"}';
			
		$file_contents   = $this->request_post2($list['submit_url'],$body);
		
		$json  = json_decode($file_contents,true);
		$respone = json_decode($json['responseBody'],true);
		
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		if(isset($json['responseHeader']['code']) && $json['responseHeader']['code']==0000){
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));die;
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}

	//汇泰在线 排重
	function IdfaRepeat_10268($data,$list){
		
		
		$file_contents = $this->request_get($list['IdfaRepeat_url']."?idfa=".$data['idfa'].'&appid='.$data['appid'].'&source=aiyingli');
	
		// print_r($file_contents);exit;
		$json = json_decode($file_contents,true);
		
		//写入log
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		
		$this->db->insert('aso_IdfaRepeat_log',$log);
		//渠道返回：成功返回0  失败返回1
		if(isset($json['result'][$data['idfa']]) && $json['result'][$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));exit(); //这里返回1代表成功
		}else if(isset($json['result'][$data['idfa']]) && $json['result'][$data['idfa']]==1){
			echo  json_encode(array($data['idfa']=>'0','ErrorInfo'=>$file_contents));exit(); //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;exit();
		}
	}
	//汇泰在线点击
	function source_10268($data,$list){
		
		$sign = md5($data['timestamp'].md5('callback'));//回调地址的sign;
		$callback = "http://asoapi.appubang.com/api/aso_advert/?k=".$data['timestamp']."&idfa=".$data['idfa']."&appid=".$data['appid']."&sign=".$sign;
		$callback = urlencode($callback);
		if(!isset($data['ip'])){
			$data['ip']='';
		}
		$file_contents   = $this->request_get($list['source_url']."?appid=".$data['appid']."&sign=".md5('aiyingli|'.$data['appid'].'|'.$data['idfa'].'|aK40nyshtxZ2WkCRX')."&callbackurl=".$callback.'&ip='.$data['ip'].'&idfa='.$data['idfa'].'&source=aiyingli');
		// echo $file_contents;
		// echo $list['source_url']."?appid=".$data['appid']."&sign=".md5('aiyingli|'.$data['appid'].'|'.$data['idfa'].'|aK40nyshtxZ2WkCRX')."&callbackurl=".$callback.'&ip='.$data['ip'].'&idfa='.$data['idfa'].'&source=aiyingli';
		// print_r($file_contents);exit;
		$json  = json_decode($file_contents,true );
	
		
		if(isset($json['err_code']) && $json['err_code']==0){
			$this->db->insert('aso_source', $data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}


	function IdfaRepeat_10272($data,$list){
		
		$data['sign']   = md5(date('Y').date('m').date('d').'W^X*2018^09^07-59F9ED5D');
// echo $source.'|'.$adid.'|'.$data['idfa'].'|'.'m5f5nohv4mk5gz2pa7h9hr5qrd9mv14b'.'|'.$data['timestamp'];
 		$url = $list['IdfaRepeat_url'] ."&idfa=".$data['idfa']."&sign=".$data['sign'];
 		
		$file_contents   = $this->request_get($url);
		
		 $json  = json_decode($file_contents,true);
		
		//写入logecho 
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
	
		
		//渠道返回：成功返回0  失败返回1
		if(isset($json['data'][$data['idfa']]) && $json['data'][$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}


	function submit_10272($data,$list){
		$data['sign']   = md5(date('Y').date('m').date('d').'W^X*2018^09^07-59F9ED5D');
// echo $source.'|'.$adid.'|'.$data['idfa'].'|'.'m5f5nohv4mk5gz2pa7h9hr5qrd9mv14b'.'|'.$data['timestamp'];
 		$url = $list['submit_url'] ."&idfa=".$data['idfa']."&sign=".$data['sign'].'&ip='.$data['ip'];
 		
		$file_contents   = $this->request_get($url);
		
		 $json  = json_decode($file_contents,true);
		
	//var_dump($json);
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		//var_dump($json);
		if($json['code']==1 && isset($json['code'])){//上报成功
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
		}
	}


	function IdfaRepeat_10292($data,$list){
		$key1         = explode('?',$list['IdfaRepeat_url'])[1];
		$sign         = strtolower(md5($key1.'&apple_id='.$data['appid'].'&idfa='.$data['idfa'].'&ip='.$data['ip'].'&mac=&model=&os=&tag=&sign_key=sags4a8749c375db12a16s09sf8aylsw'));

		$data_al['sign'] =$sign;
		$data_al['apple_id'] =$data['appid'];
		$data_al['idfa']  =$data['idfa'];
		$data_al['ip']   = $data['ip'];
		$data_al['mac']  = '';
		$data_al['model'] ='';
		$data_al['os']  ='';
		$data_al['tag'] ='';
		$data_al['adid'] = explode('=',explode('&',explode('?',$list['IdfaRepeat_url'])[1])[0])[1];
		$data_al['app_id'] = explode('=',explode('&',explode('?',$list['IdfaRepeat_url'])[1])[1])[1];
		
	
		$file_contents   = $this->request_post2(explode('?',$list['IdfaRepeat_url'])[0],json_encode($data_al));
		
		 $json  = json_decode($file_contents,true);
		//echo $file_contents;die;
		//写入logecho 
		$log=array('cpid'=>$data['cpid'],'appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'json'=>$file_contents ,'date'=>time());
		$this->db->insert('aso_IdfaRepeat_log',$log);
	
		
		//渠道返回：成功返回0  失败返回1
		if(isset($json['data'][$data['idfa']]) && $json['data'][$data['idfa']]==0){
			echo  json_encode(array($data['idfa']=>'1'));die; //这里返回1代表成功
		}else{
			$a = array($data['idfa']=>'0','ErrorInfo'=>$file_contents); //我们这里返回0代表失败不可做任务
			$contents = json_encode($a);
			echo  $contents;die;
		}
	}

	function source_10292($data,$list){
		
		$key1         = explode('?',$list['source_url'])[1];
		$sign         = strtolower(md5($key1.'&apple_id='.$data['appid'].'&idfa='.$data['idfa'].'&ip='.$data['ip'].'&mac=&model='.$data['device'].'&os='.$data['os'].'&tag='.$data['keywords'].'&sign_key=sags4a8749c375db12a16s09sf8aylsw'));

		$data_al['sign'] =$sign;
		$data_al['apple_id'] =$data['appid'];
		$data_al['idfa']  =$data['idfa'];
		$data_al['ip']   = $data['ip'];
		$data_al['mac']  = '';
		$data_al['model'] =$data['device'];
		$data_al['os']  =$data['os'];
		$data_al['tag'] =$data['keywords'];
		$data_al['adid'] = explode('=',explode('&',explode('?',$list['source_url'])[1])[0])[1];
		$data_al['app_id'] = explode('=',explode('&',explode('?',$list['source_url'])[1])[1])[1];
		
		
		$file_contents   = $this->request_post2(explode('?',$list['source_url'])[0],json_encode($data_al));
		
		 $json  = json_decode($file_contents,true);
	
		
		if(isset($json['data'][$data['idfa']]) && $json['data'][$data['idfa']]==1){
			$this->db->insert('aso_source', $data);
			echo  json_encode(array('code'=>'0','result'=>'ok'));die; //这里返回0代表成功
		}else{
			$data['json'] =$file_contents;
			$this->db->insert('aso_source_log', $data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));die;
		}
	}


	function submit_10292($data,$list){
		$data['os']   = '';
		$data['device']   = '';
		$key1         = explode('?',$list['submit_url'])[1];
		$sign         = strtolower(md5($key1.'&apple_id='.$data['appid'].'&idfa='.$data['idfa'].'&ip='.$data['ip'].'&mac=&model='.$data['device'].'&os='.$data['os'].'&tag='.$data['keywords'].'&sign_key=sags4a8749c375db12a16s09sf8aylsw'));

		$data_al['sign'] =$sign;
		$data_al['apple_id'] =$data['appid'];
		$data_al['idfa']  =$data['idfa'];
		$data_al['ip']   = $data['ip'];
		$data_al['mac']  = '';
		$data_al['model'] =$data['device'];
		$data_al['os']  =$data['os'];
		$data_al['tag'] =$data['keywords'];
		$data_al['adid'] = explode('=',explode('&',explode('?',$list['submit_url'])[1])[0])[1];
		$data_al['app_id'] = explode('=',explode('&',explode('?',$list['submit_url'])[1])[1])[1];
		
	
		$file_contents   = $this->request_post2(explode('?',$list['submit_url'])[0],json_encode($data_al));
		
		 $json  = json_decode($file_contents,true);
		
	//var_dump($json);
		unset($data['sign'],$data['ip'],$data['reqtype'],$data['isbreak'],$data['device'],$data['os'],$data['callback']);
		//var_dump($json);
		if(isset($json['data'][$data['idfa']]) && $json['data'][$data['idfa']]==1){//上报成功
			$data['timestamp']=time();
			$data['type'] = 1;

		        $SourceExists=  $this->db->get_where('aso_source',array('appid'=>$data['appid'],'cpid'=>$data['cpid'],'adid'=>$data['adid'],'idfa'=>$data['idfa']))->row_array();
						 
		       if(!empty($SourceExists)){
				     $this->db->update('aso_source',array('type'=>1,'activetime'=>time()),array('id'=>$SourceExists['id']));
		        }else{
				      $this->db->insert('aso_source',array('appid'=>$data['appid'],'adid'=>$data['adid'],'idfa'=>$data['idfa'],'cpid'=>$data['cpid'],'type'=>1,'activetime'=>time(),'keywords'=>$data['keywords']));
		        }
			    
			echo  json_encode(array('code'=>'0','result'=>'ok'));
		}else{//失败 
			$data['timestamp']=time();
			$data['type'] = 1;
			$data['json'] =$file_contents;
			$this->db->insert('aso_submit_log',$data);
			echo  json_encode(array('code'=>'103','result'=>'false','ErrorInfo'=>$file_contents));//这里返回1代表成功
		}
	}
	
	
	

	/**************************************** 接口结束**********************************************/
	/********************************** 自定义函数开始*********************************************/
	/**
     * 赶集构成加密串
     * @param  array $params 参数
     * @return string
     */
    static function buildParamStr( $params ) {
        ksort( $params );
        $paramStr = '';
        foreach ( $params as $key => $value ) {
            if ( $key == 'signature' ) {
                continue;
            }
            $paramStr .= sprintf( '%s=%s&', $key, $value );
        }

        return rtrim( $paramStr, '&' );
	}

	//自定义ascii排序
	function ASCII($params = array()){
    if(!empty($params)){
       $p =  ksort($params);
       if($p){
           $str = '';
           foreach ($params as $k=>$val){
               $str .= $k .'=' . $val . '&';
           }
           $strs = rtrim($str, '&');
           return $strs;
       }
    }
    return '参数错误';
}



	/**
     * 模拟post进行url请求
     * @param string $url
     * @param array $post_data
     */
    function request_post($url = '', $post_data = array()) {
        if (empty($url) || empty($post_data)) {
            return false;
        }
        
        $o = "";
        foreach ( $post_data as $k => $v ) 
        { 
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);

        $postUrl = $url;
        $curlPost = $post_data;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        return $data;
    }

     /**
     * 模拟post进行url请求
     * @param string $url
     * @param 以json流传递
     */
    function request_post2($url = '', $post_data = '') {
       
        $ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json;charset=utf-8',
		    'Content-Length: ' . strlen($post_data))
		);
 
		$result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    function request_get($url,$ada=array(),$arr_header=array()){

    	// 创建一个cURL资源
		$ch  =  curl_init ();
		//$arr_header[] = "Authorization: Basic ZGluZ2RhbmdfdXNlcjpkZEBqazE4OTkyISYkIyE=";
		// 设置URL和相应的选项
		curl_setopt ( $ch ,  CURLOPT_URL ,$url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ( $ch ,  CURLOPT_HEADER ,  0 );
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS,5000);
		if(!empty($arr_header)){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $arr_header);
		}
		
		if(!isset($_SERVER['HTTP_USER_AGENT'])){
			$UsAg= 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1';
		}else{
			if(strstr($_SERVER['HTTP_USER_AGENT'],'Java')){
					$UsAg= 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1';
			}else{
					$UsAg =$_SERVER['HTTP_USER_AGENT'];
			}
			
		}
		//curl_setopt( $ch ,CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']); // 模拟浏览器请求
		
		@curl_setopt( $ch ,CURLOPT_USERAGENT,$UsAg);
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		//curl_setopt($ch, CURLOPT_TIMEOUT,1); 
		
		// 抓取URL并把它传递给浏览器
		$data = curl_exec($ch);//运行curl

		$curl_errno = curl_errno($ch);  
        $curl_error = curl_error($ch);


	    curl_close($ch);
	 
	   //判断是否请求超时 允许最大请求时间2秒
	    if($curl_errno >0){  

	    		if($url!='' && !empty($ada)){
    					$info['url']      = $url;
	    		        $info['message']  = $curl_error;
	    		        $info['date']     = time();
	    		        $info['appid']    = $ada['appid'];
	    		        $info['adid']     = $ada['adid'];
    					$this->db->insert('aso_timeout_log',$info);
    			}
	    		
	    		
               echo $data = json_encode(array("code"=>99,"ErrorInfo"=>$curl_error)); die;
        }
	     return $data;
		// 关闭cURL资源，并且释放系统资源
		// curl_close ( $ch );

    }
    //不传递UserAgent get请求

    function NoUserAgent_get($url,$ada=array(),$arr_header=array()){

    	// 创建一个cURL资源
		$ch  =  curl_init ();
		//$arr_header[] = "Authorization: Basic ZGluZ2RhbmdfdXNlcjpkZEBqazE4OTkyISYkIyE=";
		// 设置URL和相应的选项
		curl_setopt ( $ch ,  CURLOPT_URL ,$url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ( $ch ,  CURLOPT_HEADER ,  0 );
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS,5000);
		if(!empty($arr_header)){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $arr_header);
		}
		
		
		
		
		// 抓取URL并把它传递给浏览器
		$data = curl_exec($ch);//运行curl

		$curl_errno = curl_errno($ch);  
        $curl_error = curl_error($ch);


	    curl_close($ch);
	 
	   //判断是否请求超时 允许最大请求时间2秒
	    if($curl_errno >0){  

	    		if($url!='' && !empty($ada)){
    					$info['url']      = $url;
	    		        $info['message']  = $curl_error;
	    		        $info['date']     = time();
	    		        $info['appid']    = $ada['appid'];
	    		        $info['adid']     = $ada['adid'];
    					$this->db->insert('aso_timeout_log',$info);
    			}
	    		
	    		
               echo $data = json_encode(array("code"=>99,"ErrorInfo"=>$curl_error)); die;
        }
	     return $data;
		// 关闭cURL资源，并且释放系统资源
		// curl_close ( $ch );

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

    function request_get2($url, $headers){
    	// 创建一个cURL资源
		$ch  =  curl_init ();
		$headerArr = array(); 
		foreach( $headers as $n => $v ) { 
		    $headerArr[] = $n .':' . $v;  
		}
		// 设置URL和相应的选项
		curl_setopt ( $ch ,  CURLOPT_URL ,$url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);
		// 抓取URL并把它传递给浏览器
		$data = curl_exec($ch);//运行curl
	    curl_close($ch);
	     return $data;

		// 关闭cURL资源，并且释放系统资源
		// curl_close ( $ch );

    }

    	/**
	 * RSA私钥签名
	 * 
	 * @param $signdata: 待签名字符串      	     	
	 */
	function sign($signdata,$AppStoreId){
	if($AppStoreId==1176078944){
	  $appPrivateKey="-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAOJmBoc5nidFw2rH
Gxljo5bCsKMA5yksj2lb9pykxwXgy7Jgeow8yIKF9ZO7TvDWdvYWYYqejZRPmFcD
siYtC+aSGmDuXjimIEYAuaXRpg/eySqVkB7e/bCGwRqXI5jen2vsXSZIQsfOgIYw
A2IIbLNEluMlicse1ClbcDaiq30zAgMBAAECgYBIad24ruM5KIVCyACQ9F/Evu0E
litZ7hjI2FNe8w19gdNlcJqB9IclyHcuE4FCYzaVq77zOZeLUpIlctcugsYFFb63
17/KbUrqKIdI3P++0b4kzeOGu8B7F4/hpS/9Y43aXFQzHpn+/7XBHRCScs5mpmAQ
CLjQn6AAdkJE5gCfUQJBAPMQoHyBGMyyUyl77/CPhs0w1mVTk/Lr5FaIKTHzFmcT
Rm5887GSLu16IiwQQFTUQEhIXtRjmw3qUZH02gRfXN8CQQDuclhjT5QZjbU/Usv7
BSWdoxf6409bpzhqamnfGZE/8M20mw6G/sl21vJtsKb/R1cCYT7240uWfKlTxKXE
sxYtAkBczzx4TdLqVizq6ifz8tnF/5/dkMwtNWU6pUMVj3w+X13FUnC6nNbOVpQ1
vv7RZTomX3vWHTJXXeFHmfalNMSBAkEA5Z65nVE59m2vd7587ktjkO1JH3Kcrk9X
FatKLu0JIgD7pwuWrstXKRkPNjBicPy7PnB1WP1DgjSkPyXk2In5NQJBANIuQKAX
N+P8I2P9usBgQzwGubaxcZFNuXoi0xWTp20/2FT35g8U9ziCIcmw2tq08/B3dmZT
RiZIVKcD0VZknZc=
-----END PRIVATE KEY-----";
	}else if($AppStoreId==1104239394){
		  $appPrivateKey="-----BEGIN PRIVATE KEY----- 
MIICdQIBADANBgkqhkiG9w0BAQEFAASCAl8wggJbAgEAAoGBAIeBa1z6EeL3dkTC6Y
81VaiNK59Rqq7vJQLAKlzVBRKNWNX0OsP4fnU9Qukg9LNdfSejfz6iSIFbsRJfs3HO
Rds153+/fQmoDelaCkjbj0jiwdyFQE03tfUWXZMIJToSBBr8U5uzMD/s1Ef3gdUL9Y
EPUHo+k6kf2+qB9pyMTbRjAgMBAAECgYBTohQiuZFKlVNgkzBWHCP3ONJArcX73Evq
i7JZw3wy/BxlSSzwATIDqEDg5F9DSSNS0L1bagv4EyCR55E4X4iLIO0APfceVCGtLP
V2PfCHTflgXN7JXEYWEYhhrT9FyEL2D4frBu1thezCPQ1kGspFiwPXcgjvs0lJO0Tk
YOfbMQJBANKrX6eDNCfciQ/CCZ/2I25sjXJi5CBpuYb9do2zEaxnpShf6uuDpwSo01
VN65JI6pYqHzHrDSeI4Xm5AuI4ZrsCQQCkqa2160nGV89YwKQ4sDhbGGp1IJjmSjLq
yB64mlpSm2A/2R44pkjTCro/O56j+xpMGq0CnlUsQoatbpUQhBJ5AkB7pj6UkXvRUa
3Y4+jGTK/rJie3VbfUFnngc3BcJxhees8DbZjy9ujW4Uh5LyzvRYD69mos4GtuIvdE
fITmxnf1AkBSC9Xpcm6VLMW9FGf/cxbxlQ3ehLqK7OfIAqUEGKzuwkrIJZggZAKfXZ
YF0eAvFvw4dYZFar1Hy3It0o7l5tkJAkADcbrKca2QyJlUq/q7oIgzM1bR/dZvOfj/
P2r71Gzx9fMr06xK92FhKR5mQe511C7IezP5IUoB4b0y0xQmOZQ/
-----END PRIVATE KEY-----";
	}else if($AppStoreId==983488107){
		  $appPrivateKey="-----BEGIN PRIVATE KEY----- 
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAM7denwTRPdolkuCZL/g
4zo6lcdzCsDxc9zgNSh7yJapkxMrAiqkw25ij0KJvJDBXCTMui11xqTkJpga2I078NqY
IDMXe3p2X+dEXEmQaPD35c2CLjss5UygG0CNGNfRyg4uIACPqXuhaeTvrjBAM8N7E7rg
9oYE5LZxaAyVaSZDAgMBAAECgYAKJeVPVuaoOHI/DAuDOjYLcjpMyYD6jB3B9SHGdaQW
eAUmCJMXonOP47fhbL5aX5H0oDJ17nQrPKIEDjUXYJxlFjSGX9GabXMcgtEx7C07gFg+
SnRfjqM794EzjKXSVxe9BTEoohH1NoMrO3QM22AanpIAxDBfEFX7uEAkgbyTCQJBAPcu
0Q1HUFxXJky6uHi97jnrydYvXs58gyegrY+c8NMRbgN0Rp6Pg8faR1PwZCHIWmuAtn2t
cdQBi30npW+hLpcCQQDWPn7B++2pKQF4fZignRoG2v0qoPrdbjutGUQXknAKGgAK9xEx
ynpmeE4rw4F5hAP19uwBu1K4pbEG5SuESqc1AkAHS1xj9ezLLM82iHQVLBWxo+Gq7m7v
zQDZ1IYKrOj2cZc7htzmpPmQlkJwmbF6xbzVW1EHWGz5gqopIVhiePE1AkA/6c7o0d45
i7kbl+RTbeqYxvWlpPaR3lPBNPtiSNZRvSXsH36qquvO6+7uEVnrxV1lIC+R6K8p1Iw2
MWHFCnxNAkEA1ZTD/bBq0Ri+SSLFkviKmaRdsR4iiuWzvXAhhaoupeUeJnXPzW1M1K3/
3hviRzZeLMZnr+Mio+cw8prXhpPs5w==
-----END PRIVATE KEY-----";
	}
	  // $aaaa=preg_replace("/\s/", ' ', $appPrivateKey);
	    if(openssl_sign($signdata,$sign,$appPrivateKey));
	        $sign = base64_encode($sign);
	        //echo $appPrivateKey;
	    return $sign;
	}


	/**
	 * RSA私钥签名
	 * 
	 * @param $signdata: 待签名字符串      	     	
	 */
	function Qzsign($signdata){
	
	  $appPrivateKey="-----BEGIN PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAN9Dt/ZMublbHesnEY
QJgk36rRFPCj/aFy0C6WHx6tqvS6+dl5XUTnANua8wcs3WbY9OHSSArpfsbxwE4Qdu
ADaojkgbGMEan2zxUeB3MtYXUakTfVRWF9MMaW9md8WuDS4BYymVUCeACu7s2QOkcw
kQhs3f9r9/VzulCEnE/HiJAgMBAAECgYA1LbIRrnDGX5eevS2E36bz3/N9HfB5CP9g
L0Nbmv2vnPVQHR9QBsOwGPTTb5zIvnxWF+sFGMl9UkmcyOS4mlDeQfiLhnEq4R062t
9/hPtFWzDsOs2TTcUA72QvnxSLG6Y9Tft351mf2NNMHQZFR24qBvMDOp5L0Cyk81qt
WIVxAQJBAPvYQwR0Fd2/jrpag2S9bh+kWfFqqNjb/3ztBSE/ijdO8QKeNI6fyjDOuL
SwHNKX9SximqE87UKZM/kcDZZ+CnECQQDi8r1wLKqs8tOeaPl8KI0nj0Y3VIeNPBws
DWew9pjwcJ54vOxSIq8SPgAYTh/DunN7waKBxpsgXP2aJs2xe2uZAkBT8CFuE47SKK
1WeSJ/6g8RJsL/jrAWD0UZCxqBmV7kzj/PwpD71FAcclnnhyckHZeOopKtGNRvNQa4
iVwSA5JRAkBNAg0h6SYo9WS1Ve2CIchz6fvrfnVYiVMN56aNt7+BptU/JuwRms9JI1
yo4qmIotXY4oWf/6JXwvYSvqQBW13RAkEAtxFGmW9r2Mq2DYIMNoBJE/FTxF6Piy81
pDKlQVBWh0mwPEgDmX3XjaYndW/nw4/zgxtsmLGXIiRYLO3y/rG1Ug==
-----END PRIVATE KEY-----";
	
	  // $aaaa=preg_replace("/\s/", ' ', $appPrivateKey);
	    if(openssl_sign($signdata,$sign,$appPrivateKey));
	        $sign = base64_encode($sign);
	        //echo $appPrivateKey;
	    return $sign;
	}
		/**
	 * 排序Request至待签名字符串
	 *
	 * @param $request: json格式Request
	 */
	function sortToSign($request){
	    $obj = json_decode($request);
	    $arr = array();
	    foreach ($obj as $key=>$value){
	        if(is_array($value)){
	            continue;
	        }else{
	            $arr[$key] = $value;
	        }
	    }
	    ksort($arr);
	    $str = "";
	    foreach ($arr as $key => $value){
	        $str = $str.strtolower($key).$value;
	    }
	    //$str = strtolower($str);
	    return $str;
	}

	// 包装好的发送请求函数
	function QzSendRequest($url,$request,$appId,$ChAppID){
		$curl = curl_init ($url);
		
		$timestamp = time(); // UTC format
		
		$timestap_sign = $this->Qzsign($appId.$timestamp);
		$requestSignStr = $this->sortToSign($request);
		$request_sign = $this->Qzsign($requestSignStr);
		//echo $requestSignStr;
		$header = array ();
		$header [] = 'Content-Type:application/json;charset=UTF-8';
		$header [] = 'X-Pluosi-Timestamp:' . $timestamp;
		$header [] = 'X-Pluosi-App-Id:' . $appId;
		$header [] = 'X-Pluosi-Timestamp-Sign:' . $timestap_sign;
		$header [] = 'X-Pluosi-Signature:' . $request_sign;
		
			
		curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header );
		curl_setopt ( $curl, CURLOPT_POST, 1 );
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, $request );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		$result = curl_exec ( $curl );
		curl_close ( $curl );
	//         $j = json_decode ( $result, true );
		//var_dump($result);
		return $result;
	}

	function log( $logthis ){
		file_put_contents('/www/web/default/log/advert.log', date("Y-m-d H:i:s"). " " . $logthis. "\r\n", FILE_APPEND | LOCK_EX);
	}

	
	// 包装好的发送请求函数
	function SendRequest($url,$request,$appId,$AppStoreId){
		$curl = curl_init ($url);
		
		$timestamp = gmdate ( "Y-m-d H:i:s", time ()); // UTC format
		$timestap_sign = $this->sign($appId. $timestamp,$AppStoreId);
		$requestSignStr = $this->sortToSign($request);
		$request_sign = $this->sign($requestSignStr,$AppStoreId);
		//echo $requestSignStr;
		$header = array ();
		$header [] = 'Content-Type:application/json;charset=UTF-8';
		$header [] = 'X-PPD-TIMESTAMP:' . $timestamp;
		$header [] = 'X-PPD-TIMESTAMP-SIGN:' . $timestap_sign;
		$header [] = 'X-PPD-APPID:' . $appId;
		$header [] = 'X-PPD-SIGN:' . $request_sign;
		
		curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header );
		curl_setopt ( $curl, CURLOPT_POST, 1 );
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, $request );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		$result = curl_exec ( $curl );
		curl_close ( $curl );
	//         $j = json_decode ( $result, true );
		//var_dump($result);
		return $result;
	}

	function getSignature($str, $key) {  
	    $signature = "";  
	    if (function_exists('hash_hmac')) {  
	        $signature = base64_encode(hash_hmac("sha1", $str, $key, true));  
	    } else {  
	        $blocksize = 64;  
	        $hashfunc = 'sha1';  
	        if (strlen($key) > $blocksize) {  
	            $key = pack('H*', $hashfunc($key));  
	        }  
	        $key = str_pad($key, $blocksize, chr(0x00));  
	        $ipad = str_repeat(chr(0x36), $blocksize);  
	        $opad = str_repeat(chr(0x5c), $blocksize);  
	        $hmac = pack(  
	                'H*', $hashfunc(  
	                        ($key ^ $opad) . pack(  
	                                'H*', $hashfunc(  
	                                        ($key ^ $ipad) . $str  
	                                )  
	                        )  
	                )  
	        );  
	        $signature = base64_encode($hmac);  
	    }  
	    return $signature;  
   }
   
    function GetHttpStatusCode($url){   
        $curl = curl_init();  
        curl_setopt($curl,CURLOPT_URL,$url);//获取内容url  
        curl_setopt($curl,CURLOPT_HEADER,1);//获取http头信息  
        curl_setopt($curl,CURLOPT_NOBODY,1);//不返回html的body信息  
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);//返回数据流，不直接输出  
        curl_setopt($curl,CURLOPT_TIMEOUT,30); //超时时长，单位秒 
        curl_setopt($curl,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.134 Safari/537.36"); 
        curl_exec($curl);  
        $rtn= curl_getinfo($curl,CURLINFO_HTTP_CODE);  
        curl_close($curl);  
        return  $rtn;  
    }
    function StatusCode($url){   
        $curl = curl_init();  
        curl_setopt($curl,CURLOPT_URL,$url);//获取内容url  
        curl_setopt($curl,CURLOPT_HEADER,1);//获取http头信息 
        // curl_setopt($curl,CURLOPT_FOLLOWLOCATION,1); 
      
        curl_setopt($curl,CURLOPT_NOBODY,1);//不返回html的body信息  
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);//返回数据流，不直接输出  
        curl_setopt($curl,CURLOPT_TIMEOUT,30); //超时时长，单位秒  
        curl_setopt($curl,CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.134 Safari/537.36");
        // curl_setopt($curl,CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)");
        // curl_setopt($curl,CURLOPT_FOLLOWLOCATION,1); 
        $a=curl_exec($curl); 
        echo $a;die; 
        // $rtn= curl_getinfo($curl,CURLINFO_HTTP_CODE);  //获取http状态码
        $rtn= curl_getinfo($curl,CURLINFO_EFFECTIVE_URL);  //获取http状态码
        curl_close($curl);  
        return  $rtn;  
    }
    //获取当前请求url
    function curPageURL() 
	{
	    $pageURL = 'http';

	    // if ($_SERVER["HTTPS"] == "on") 
	    // {
	    //     $pageURL .= "s";
	    // }
	    $pageURL .= "://";

	    if ($_SERVER["SERVER_PORT"] != "80") 
	    {
	        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	    } 
	    else 
	    {
	        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	    }
	    return $pageURL;
	}
	

   

	/** 
	 *  将URL中的参数取出来放到数组里
	 * 
	 * @param    string    query 
	 * @return    array    params 
	 */ 
	function convertUrlQuery($url)
	{ 
		$arr = parse_url($url);
		$query = $arr['query'];
	    $queryParts = explode('&', $query); 
	    
	    $params = array(); 
	    foreach ($queryParts as $param) 
		{ 
	        $item = explode('=', $param); 
	        $params[$item[0]] = $item[1]; 
	    } 
	    
	    return $params; 
	}

	

}


/********************************** 自定义函数结束*********************************************/
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
