<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-profile-magic-request
 *
 * @author ProfileGrid
 */
class HOARDER_request {
//put your code here
	public function sanitize_request($post,$identifier,$exclude=array()) 
	{
        $hoarder_sanitizer = new HOARDER_sanitizer;
        
       $post = $hoarder_sanitizer->remove_magic_quotes($post);

        foreach ($post as $key => $value) {
            if( !in_array($key, $exclude) ) {
                if ( !is_array($value) ) {
                    $data[$key] = $hoarder_sanitizer->get_sanitized_fields($identifier, $key, $value);
                } else {
                    $data[$key] = maybe_serialize( $this->sanitize_request_array($value, $identifier) );
                }
            }
        }
        
        if ( isset($data) ) { return $data; }
        else { return NULL; }
	}
	
	public function sanitize_request_array($post, $identifier) 
	{
	    $hoarder_sanitizer = new HOARDER_sanitizer;
	    
	    foreach ($post as $key => $value) {
                if ( is_array($value) ) {
                    $data[$key] = $this->sanitize_request_array($value, $identifier);
                } else {
                    $data[$key] = $hoarder_sanitizer->get_sanitized_fields($identifier, $key, $value);
                }
	    }
	    
	    if ( isset($data) ) 
            { 
                return $data;
            }
            else 
            { 
                return NULL; 
            }
	}
        
      
        public function hoarder_curl_get_request($url,$accesstoken,$headers)
        {
            $cURLConnection = curl_init();
            $headr[] = 'Content-type: application/json';
            $headr[] = 'Accept: */*';
            $headr[] = 'Authorization: ' . $accesstoken;

            curl_setopt($cURLConnection, CURLOPT_URL,$url);
            curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

            $phoneList = curl_exec($cURLConnection);
            curl_close($cURLConnection);

            $jsonArrayResponse = json_decode($phoneList);
            
            return $jsonArrayResponse;
        }
        
        public function hoarder_curl_request($method,$URL,$header = array(),$post_data=array())
        {
            $crl = curl_init();
            if(empty($header)):
                $header = array();
                $header[] = 'Content-type: application/json';
                $header[] = 'Accept: */*';
//              $header[] = 'Authorization: ' . $accesstoken;
            endif;
            curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, true);

            curl_setopt($crl, CURLOPT_URL, $URL);
            curl_setopt($crl, CURLOPT_HTTPHEADER, $header);
            if($method=='POST'):
                curl_setopt($crl, CURLOPT_POST, true);
                if(!empty($post_data))
                {
                    $data = json_encode($post_data);
                    curl_setopt($crl, CURLOPT_POSTFIELDS, $data);
                }
            endif;
            curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
            $ret = curl_exec($crl);
            
            if (empty($ret)) {
                // some kind of an error happened
                return curl_error($crl);
                curl_close($crl); // close cURL handler
            } 
           
    
            //return $ret;
            
            //return 'hi';
            //print_r($result_noti);die;
            return json_decode($ret,true);
            //return $result_noti;
            
        }
        
        public function hoarder_api_verification($api_key,$url)
        {
            $header = array();
            $header[] = 'Accept: application/json';
            $apiurl = $url.'/config-service/tokenBySiteId/'.$api_key; 
            $apiurl = $url.'/config-service/token/varun/varun';
            //echo $apiurl;
            $result = $this->hoarder_curl_request('GET',$apiurl,$header);   
            return $result;
        }
        
        public function hoarder_fetch_rules($url)
        {
            $dbhandler = new HOARDER_DBhandler;
            $accesstoken = $dbhandler->get_global_option_value('hoarder_token','');
            $siteId = $dbhandler->get_global_option_value('hoarder_siteId','');
            $userId = $dbhandler->get_global_option_value('hoarder_userId','');
            echo $accesstoken;
            echo $siteId;
            $header = array();
            //$header[] = 'Content-type: application/json';
            $header[] = 'Accept: application/json';
            //$header[] = 'Accept: */*';
            $header[] = 'Authorization: Bearer ' . $accesstoken;
            $apiurl = $url.'/config-service/SiteRule/'.$siteId; 
            echo $apiurl;
            $result = $this->hoarder_curl_request('GET',$apiurl,$header);   
            return $result;
        }
        
        public function hoarder_send_response($url,$response)
        {
            $dbhandler = new HOARDER_DBhandler;
            $accesstoken = $dbhandler->get_global_option_value('hoarder_token','');
            $siteId = $dbhandler->get_global_option_value('hoarder_siteId','');
            $userId = $dbhandler->get_global_option_value('hoarder_userId','');
            
            $header = array();
            $header[] = 'Content-type: application/json';
            //$header[] = 'Accept: application/json';
            //$header[] = 'Accept: */*';
            $header[] = 'Authorization: Bearer ' . $accesstoken;
            //$apiurl = $url.'/SiteRule/'.$siteId; 
            
           //echo json_encode($response);
            $result = $this->hoarder_curl_request('POST', $url, $header, $response);
            
            echo '<pre>'.$url;
           // print_r($header);
            print_r(json_encode($response));
            var_dump($result);
            echo json_encode($result);
            echo '</pre>';
            die;
            
        }
        
        public function hoarder_check_rule_exists($rule_id)
        {
            $dbhandler = new HOARDER_DBhandler;
            $identifier = 'RULES';
            $rule = $dbhandler->get_row($identifier,$rule_id,'rule_id');
            if(isset($rule) && !empty($rule))
            {
                return $rule;
            }
            else
            {
                return false;
            }
            
        }
        
        public function hoarder_save_rules($rules)
        {
            $dbhandler = new HOARDER_DBhandler;
            $activator = new Hoarder_Activator();
            $identifier = 'RULES';
            $exclude = array('userEmailTemplateDto');
            
            foreach($rules as $rule)
            {
                $post = $this->sanitize_request($rule,$identifier,$exclude);
                if(!isset($post['status']))
                {
                    $post['status'] = 1;
                }
                if($post!=false)
                {
                        foreach($post as $key=>$value)
                        {
                            if($key=='id')
                            {
                                $data['rule_id'] = $value;
                                $rule_id = $value;
                                $arg[] = $activator->get_db_table_field_type($identifier,$key);
                            }
                            else
                            {
                                $data[$key] = $value;
                                $arg[] = $activator->get_db_table_field_type($identifier,$key);
                            }
                          
                        }
                        
                        $rule_exist = $this->hoarder_check_rule_exists($rule_id);
                        if($rule_exist)
                        {
                            $id = $rule_exist->id;
                            $dbhandler->update_row($identifier,'id',$id,$data,$arg,'%d');
                        }
                        else
                        {
                            $dbhandler->insert_row($identifier, $data,$arg);
                        }
                        
                        
                        
                        
                        
                }
                
            }
            
        }
        
        public function hoarder_check_conditions($rulesDto,$parameters)
        {
            $condition = array();
            if(!empty($rulesDto))
            {
                
                foreach($rulesDto as $dto)
                {
                    $id = $dto['id'];
                    $name = $dto['systemParameterDto']['name'];
                    $operation = $dto['operation'];
                    $min = $dto['min'];
                    $max = $dto['max'];
                    switch($name)
                    {
                        case 'previousRole':
                            $condition[$id] = $this->check_previous_role($min,$parameters,$operation);
                            break;
                        case 'UserName':
                            $condition[$id] = $this->check_user_name($min,$parameters,$operation);
                            break;
                        case 'DATETIME':
                            $condition[$id] = $this->check_date_time($min,$parameters,$operation);
                            break;
                        case 'newRole':
                            $condition[$id] = $this->check_new_role($min,$parameters,$operation);
                            break;
                        case 'roleUpdate':
                            $condition[$id] = $this->check_role_update($min,$parameters,$operation);
                            break;
                        case 'UserProfileActivity':
                            $condition[$id] = $this->check_profile_activity($min,$parameters,$operation);
                            break;
                        case 'paymentResult':
                            $condition[$id] = $this->check_payment_result($min,$parameters,$operation);
                            break;
                        case 'paymentId':
                            $condition[$id] = $this->check_payment_id($min,$parameters,$operation);
                            break;
                        case 'formName':
                            $condition[$id] = $this->check_formname($min,$parameters,$operation);
                            break;
                        case 'formResult':
                            $condition[$id] = $this->check_formresult($min,$parameters,$operation);
                            break;
                        case 'oldPrice':
                            $condition[$id] = $this->check_old_price($min,$parameters,$operation);
                            break;
                        case 'newPrice':
                            $condition[$id] = $this->check_new_price($min,$parameters,$operation);
                            break;
                        case 'productName':
                            $condition[$id] = $this->check_product_name($min,$parameters,$operation);
                            break;
                        case 'oldInventoryCount':
                            $condition[$id] = $this->check_old_inventory_count($min,$parameters,$operation);
                            break;
                        case 'newInventoryCount':
                            $condition[$id] = $this->check_new_inventory_count($min,$parameters,$operation);
                            break;
                        case 'priceUpdate':
                            $condition[$id] = $this->check_price_update($min,$parameters,$operation);
                            break;
                        case 'UserType':
                            $condition[$id] = $this->check_user_type($min,$parameters,$operation);
                            break;
                        case 'paymentAmount':
                            $condition[$id] = $this->check_payment_amount($min,$parameters,$operation);
                            break;
                        case 'revenuAmount':
                            $condition[$id] = $this->check_revenu_amount($min,$parameters,$operation);
                            break;
                        case 'orderCount':
                            $condition[$id] = $this->check_order_count($min,$parameters,$operation);
                            break;
                        
                        
                        
                        
                        
                        
                        
                        
                    }
                } 
            }
            
            
            
            return $condition;
        }
        
        public function explodeme($delimeter,$str,$conditions)
        {
            $string = '';
            $string2 = '';
            $array = explode($delimeter, $str);
            foreach($array as $ar)
            {
                if(is_numeric($ar))
                {
                    $string .=$conditions[$ar];
                    $string .= ' ';
                    $string .=$delimeter;
                    $string .= ' ';
                }
                elseif(strpos($ar,$delimeter)!==false)
                {
                
                    $string .= $this->explodeme($delimeter,$ar,$conditions);
                    
                }
                else
                {
                   if($delimeter=='&&') 
                   {
                       $newdel = '||';
                   }
                   else
                   {
                       $newdel = '&&';
                   }
                   $string2 .= $this->explodeme($newdel,$ar,$conditions);
                }
            }
            
            return $string.$string2;
                    
                    
                    
            
        }
        
        public function hoarder_final_condition_result($conditions,$ruleExpression)
        {
            //$exploded = $this->multiexplode(array(",",".","|",":"),$text);
            $string = '';
            $endpos = strpos($ruleExpression,'&&');
            $orpos = strpos($ruleExpression,'||');
            if($endpos!==false || $orpos!==false)
            {
            
                if($endpos>$orpos)
                {
                    $del = '||';
                }
                else
                {
                    $del = '&&';
                }
                $string .= $this->explodeme($del,$ruleExpression,$conditions);
                
                
                $result = substr($string, 0, -4);
                
                
            }
            else
            {
                $result =  $conditions[$ruleExpression];
            }
            
            return eval("return (".$result.");");
        }
        
        

        
        
        public function check_previous_role($role,$parameters,$operation)
        {
            if($role=='admin')
            {
                $role = 'administrator';
            }
            $old_roles = $parameters['previous_role'];
            if(in_array($role,$old_roles))
            {
                return '1';
            }
            else
            {
                return '0';
            }
            
        }
        
        public function check_profile_activity($value,$parameters,$operation)
        {
            if($parameters['UserProfileActivity']==$value)
            {
                return '1';
            }
            else
            {
                return '0';
            }
        }

        public function check_new_role($role,$parameters,$operation)
        {
            if($role=='admin')
            {
                $role = 'administrator';
            }
            $new_role = $parameters['new_role'];
            if($role==$new_role)
            {
                return '1';
            }
            else
            {
                return '0';
            }
            
        }
        
        public function check_payment_result($value,$parameters,$operation)
        {
            if($parameters['paymentResult']==$value)
            {
                return '1';
            }
            else
            {
                return '0';
            }
        }
        
}
        