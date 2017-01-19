<?php
    /**
     * EPP Abstract Class
     *
     * This class contains the basic methods to connect and interact with Registro.br
     *
     * PHP version 5.4
     *
     * @author      Diogo Tozzi <diogo@diogotozzi.com>
     * @copyright   2012 - Diogo Tozzi
     * @link        http://github.com/diogotozzi/Epp
     * @version     1.0
     */
    abstract class EppBase
    {
        use Dic;
        // {{{ properties

        /**
         * Registro.br host address
         *
         * @var string
         * @access protected
         */
        protected $_host        = 'epp.registro.br';

        /**
         * Registro.br port
         *
         * @var int
         * @access protected
         */
        protected $_port        = 700;

        /**
         * Socket to conect via TCP
         *
         * @var resource
         * @access protected
         */
        protected $_socket      = null;

        /**
         * Cert file for SSL connection
         *
         * @var string
         * @access protected
         */
        protected $_cert        = 'client.pem';

        /**
         * Account password in Registro.br
         *
         * @var string
         * @access protected
         */
        protected $_password    = 'YOURPASSWORD';
        
        // }}}

        public function connect()
        {
	    $this->_cert = dirname(__FILE__) . '/client.pem';
            $fc = stream_context_create(array(
                'ssl' => array(
                    'cafile'  => dirname(__FILE__) . '/root.pem',
		            'local_cert' => $this->_cert
                )
			));

			if(!$this->_socket = stream_socket_client("tls://$this->_host:$this->_port", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $fc))          
                return false;
            
            return $this->unwrap();
        }
        
        protected function generate_id()
        {
            return mt_rand(00000, 99999);
        }
        
        protected function wrap($xml = null)
        {
            return pack('N', (strlen($xml) + 4)).$xml;
        }

        public function unwrap()
        {
            if(feof($this->_socket))
                print 'error';

            $packet_header = fread($this->_socket, 4);

            if(empty($packet_header))
            {
                print 'error';
            }
            else
            {
                $unpacked = unpack('N', $packet_header);
                $answer = fread($this->_socket, $unpacked[1] - 4);
            }

            try {
                $di = $this->getDic();
                $di['monolog_registrar']->addError('registrar.registrobr', array('request' => 'XML RESPONSE:', 'response' => $answer));
            } catch (Exception $e) {}
            
            return $answer;
        }

        protected function send_command($xml = null)
        {
            try {
                $di = $this->getDic();
                $di['monolog_registrar']->addError('registrar.registrobr', array('request' => 'XML REQUEST:', 'response' => $xml));
            } catch (Exception $e) {}

            return fwrite($this->_socket, $xml);
        }

        public function login($user = null, $password = null, $new_password = null, $language = 'pt')
        {
            $this->_password = $password;
            $xml = file_get_contents(dirname(__FILE__) . '/templates/login.xml');
            
            if(strlen($new_password) >= 5)
            {
				$xml = str_replace('$(newPW)$', "<newPW>$new_password</newPW>", $xml);
			}
            else
            {
				$xml = str_replace('$(newPW)$', '', $xml);
			}
            
            $xml = str_replace('$(clID)$', $user, $xml);
			$xml = str_replace('$(pw)$', $password, $xml);
			$xml = str_replace('$(lang)$', $language, $xml);
			$xml = str_replace('$(clTRID)$', '<clTRID>'.$this->generate_id().'</clTRID>', $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            return $this->unwrap();
        }

        public function logout()
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/logout.xml');
            
            $xml = str_replace('$(clTRID)$', '<clTRID>'.$this->generate_id().'</clTRID>', $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            return $this->unwrap();
        }

        public function hello()
        {
            $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>\n";
            $xml .= "<epp xmlns=\"urn:ietf:params:xml:ns:epp-1.0\">\n";
            $xml .= "<hello/>\n";
            $xml .= "</epp>";
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            return $this->unwrap();
        }
        
        public function xml2array($contents, $get_attributes = 1, $priority = 'tag')
        {
            if(!function_exists('xml_parser_create'))
                return array ();
                
            $parser = xml_parser_create('');
            
            xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
            xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
            xml_parse_into_struct($parser, trim($contents), $xml_values);
            xml_parser_free($parser);
            
            if (!$xml_values)
                return; //Hmm...
                
            $xml_array = array();
            $parents = array();
            $opened_tags = array();
            $arr = array();
            $current = &$xml_array;
            $repeated_tag_index = array ();
            
            foreach ($xml_values as $data)
            {
                unset($attributes, $value);
                
                extract($data);
                
                $result = array();
                $attributes_data = array();
                
                if(isset($value))
                {
                    if($priority == 'tag')
                        $result = $value;
                    else
                        $result['value'] = $value;
                }
                if(isset($attributes) && $get_attributes)
                {
                    foreach($attributes as $attr => $val)
                    {
                        if($priority == 'tag')
                            $attributes_data[$attr] = $val;
                        else
                            $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                    }
                }
                if ($type == 'open')
                {
                    $parent[$level -1] = &$current;
                    
                    if(!is_array($current) or (!in_array($tag, array_keys($current))))
                    {
                        $current[$tag] = $result;
                        
                        if ($attributes_data)
                            $current[$tag . '_attr'] = $attributes_data;
                            
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        $current = & $current[$tag];
                    }
                    else
                    {
                        if(isset($current[$tag][0]))
                        {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                            $repeated_tag_index[$tag . '_' . $level]++;
                        }
                        else
                        {
                            $current[$tag] = array($current[$tag],$result);
                            $repeated_tag_index[$tag . '_' . $level] = 2;
                            
                            if(isset($current[$tag . '_attr']))
                            {
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }
                        }
                        
                        $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                        $current = &$current[$tag][$last_item_index];
                    }
                }
                elseif($type == 'complete')
                {
                    if(!isset($current[$tag]))
                    {
                        $current[$tag] = $result;
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if($priority == 'tag' && $attributes_data)
                            $current[$tag . '_attr'] = $attributes_data;
                    }
                    else
                    {
                        if(isset($current[$tag][0]) && is_array($current[$tag]))
                        {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                            if ($priority == 'tag' && $get_attributes && $attributes_data)
                            {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                            $repeated_tag_index[$tag . '_' . $level]++;
                        }
                        else
                        {
                            $current[$tag] = array ($current[$tag], $result);
                            $repeated_tag_index[$tag . '_' . $level] = 1;
                            
                            if($priority == 'tag' && $get_attributes)
                            {
                                if(isset($current[$tag . '_attr']))
                                {
                                    $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                    unset($current[$tag . '_attr']);
                                }
                                if($attributes_data)
                                {
                                    $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                                }
                            }
                            $repeated_tag_index[$tag . '_' . $level]++; //0 && 1 index is already taken
                        }
                    }
                }
                elseif($type == 'close')
                {
                    $current = & $parent[$level -1];
                }
            }
            return ($xml_array);
        }
    }
?>
