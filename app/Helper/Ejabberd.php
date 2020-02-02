<?php 


// use Illuminate\Support\Facades\Log;
namespace App\Http;
use App\Models\ErrorLogs;
  

    class Ejabberd{
        /**
         * @var string
         */
        protected $server;
        /**
         * @var string
         */
        protected $host;
        /**
         * @var bool
         */
        protected $debug;
        /**
         * @var int
         */
        protected $timeout;
        /**
         * @var string
         */
        protected $username;
        /**
         * @var string
         */
        protected $password;
        /**
         * @var string
         */
        protected $userAgent;
  
        public function  __construct(array $options){
            
            if (!isset($options['server'])) {
                throw new \InvalidArgumentException("Parameter 'server' is not specified");
            }
    
            if (!isset($options['host'])) {
                throw new \InvalidArgumentException("Parameter 'host' is not specified");
            }
    
            $this->server = $options['server'];
            $this->host = $options['host'];
            $this->username = isset($options['username']) ? $options['username'] : '';
            $this->password = isset($options['password']) ? $options['password'] : '';
            $this->debug = isset($options['debug']) ? (bool)$options['debug'] : false;
            $this->timeout = isset($options['timeout']) ? (int)$options['timeout'] : 5;
            $this->userAgent = isset($options['userAgent']) ? $options['userAgent'] : 'Utkarsh';
    
            if ($this->username && !$this->password) {
                throw new \InvalidArgumentException("Password cannot be empty if username was defined");
            }
            if (!$this->username && $this->password) {
                throw new \InvalidArgumentException("Username cannot be empty if password was defined");
            }
        }       
        public function setUserAgent($userAgent)
        {
            $this->userAgent = $userAgent;

            return $this;
        } 
        protected function sendRequest($url,$body = "",$method = "POST")
        {
            
            $base_url  = $this->server;
            $url = $base_url.$url;
            $header = ['Accept: application/json', 'Content-Type: application/json'];
            // Log::info('sending request ejabberd '.$url." PARAMS ".json_encode($body));
            ErrorLogs::addErrorLog(529,'ejjaberd logs request',"URL - ".$url." PARAMS - ".json_encode($body));
            $body = json_encode($body);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response = [
                'code' => $httpCode,
                'response' => $result
            ];
            // Log::info('receiving request ejabberd '.$url." PARAMS ".json_encode($response));
            ErrorLogs::addErrorLog(529,'ejjaberd logs response',"URL - ".$url." response - ".json_encode($response));
            return json_encode($response);
           
        }
        /////////////////////////////////V CARD ///////////////////////////////////////
        public function setVcard($user, $name, $value){
            $url = "/api/set_vcard";
            if (strstr($name, ' ')) {
                $url = "/api/set_vcard2";
                list($name, $subname) = explode(' ', $name);
                
                $params = [
                    'host' => $this->host,
                    'user' => $user,
                    'name' => $name,
                    'subname' => $subname,
                    'content' => $value,
                ];
                
            } else {
                
                $params = [
                    'host' => $this->host,
                    'user' => $user,
                    'name' => $name,
                    'content' => $value,
                ];
               
            }
            // Log::info($url);
            // Log::info(json_encode($params));
            $response = $this->sendRequest($url,$params,"POST");
            $response = json_decode($response);
            return $this->renderResponse($response);
        }
        ///////////////////////////////END V CARD ////////////////////////////////////
        //////////////////////////////// ROSTER ITEMS ////////////////////////////////
        public function addRosteritem($username,$form_xmpp_name,$user_name,$group,$subs) {
            $params = [
                "localuser" => $username,
                "localserver" => $this->host,
                "user" => $form_xmpp_name,
                "server" => $this->host,
                "nick" => $user_name,
                "group" => $group,
                "subs" => $subs
            ];
            $url = "/api/add_rosteritem";
            $response = $this->sendRequest($url,$params,"POST");
            $response = json_decode($response);
            return $this->renderResponse($response);
        }
        public function deleteRosterItem($localUser,$user){
            $url = "/api/delete_rosteritem";
            $params = [
                'localuser'   => $localUser,
                'localserver' => $this->host,
                'user'        => $user,
                'server'      => $this->host,
            ];
            $response = $this->sendRequest($url,$params,"POST");
            $response = json_decode($response);
            return $this->renderResponse($response);
        }
        ///////////////////////////////////// ROSTER ITEMS END ////////////////////////
        ////////////////////////////////////// ROOM OPTIONS //////////////////////////////
        public function createRoom($room_name){
            $url = "/api/create_room";
            $params = [
                'name'    => $room_name,
                'service' => 'conference.' . $this->host,
                'host'    => $this->host,
            ];
            
            $response = $this->sendRequest($url,$params,"POST");
            $response = json_decode($response);
            return $this->renderResponse($response);
        }
        public function deleteRoom($room_name){
            $params = [
                'name'    => $room_name,
                'service' => 'conference.' . $this->host,
                
            ];
            
            $url = "/api/destroy_room";
            $response = $this->sendRequest($url,$params,"POST");
            
            $response = json_decode($response);
            return $this->renderResponse($response);
        }
        public function setRoomAffiliation($name,$jid,$affiliations){
            $params = [
                'name'        => $name, // room name
                'service'     => 'conference.' . $this->host,    
                'jid'         => $jid,
                'affiliation' => $affiliations,               
            ];
            $url = "/api/set_room_affiliation";
            $response = $this->sendRequest($url,$params,"POST");
            $response = json_decode($response);
            return $this->renderResponse($response);
        }
        public function setRoomOption($name, $option, $value){
            $params = [
                'name'    => $name,
                'service' => 'conference.' . $this->host,
                'option'  => $option,
                'value'   => (string) $value,            
            ];
            $url = "/api/change_room_option";
            $response = $this->sendRequest($url,$params,"POST");
            $response = json_decode($response);
            return $this->renderResponse($response);
        }
        public function inviteToRoom($name, $password, $reason, array $users){
            $params = [
                'name'     => $name,
                'service'  => 'conference.'. $this->host,
                'password' => $password,
                'reason'   => $reason,
                'users'    => join(':', $users),
            ];
            $url = "/api/send_direct_invitation";
            $response = $this->sendRequest($url,$params,"POST");
            $response = json_decode($response);
            return $this->renderResponse($response);
        }
        ////////////////////////////////////// END ROOM OPTIONS ///////////////////////////
        public function createUser($username,$password){
            $params = [
                'host' => $this->host,
                'user' => $username,
                'password' => $password
            ];
            $url = "/api/register";
            
            $response = $this->sendRequest($url,$params,"POST");
            $response = json_decode($response);
            return $this->renderResponse($response);
        }
        public function renderResponse($response){
            $code = $response->code;
            $data = $response->response;
            $response = [];
            $response['status'] = 200;
            $response['message'] = "";
            
            switch($code){
                case 0:
                    if($data){
                        $response['status'] = 200;
                        $response['message'] = "Api Responded";
                    }else{
                        throw new \InvalidArgumentException($data);
                    }
                    break;
                case 400:
                    //missing parameters bad request
                    throw new \InvalidArgumentException($data);
                    break;
                case 409:
                    throw new \InvalidArgumentException("Cannot create a user with same name");
                    break;
            }
            return json_encode($response);
            
        }
    }

 ?>