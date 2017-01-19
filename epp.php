<?php
    include 'iepp.interface.php';
    include 'eppbase.abstract.php';

    /**
     * EPP Class
     *
     * This is the main class for EPP functions based on brazilian Registro.br registrar.
     *
     * PHP version 5.4
     *
     * @author      Diogo Tozzi <diogo@diogotozzi.com>
     * @copyright   2012 - Diogo Tozzi
     * @link        http://github.com/diogotozzi/Epp
     * @version     1.0
     */
    class Epp extends EppBase implements iEpp
    {
        // {{{ contact_info()

        /**
         * Returns information from a contact
         *
         * This function returns all informations from a contact.
         *
         * @param string $client_id Client ID to seek.
         *
         * @return array Contact's information
         *
         * @access public
         */
        public function contact_info($client_id = null)
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/contact_info.xml');
            
            $auth_info = "<contact:authInfo>
                                <contact:pw>{$this->_password}</contact:pw>
                            </contact:authInfo>";
            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';

            $xml = str_replace('$(id)$', $client_id, $xml);
            $xml = str_replace('$(auth_info)$', $auth_info, $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            if($response['epp']['response']['result_attr']['code'] != '1000')
                return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
                
            $data = array(
                'client_id' => $response['epp']['response']['resData']['contact:infData']['contact:id'],
                'client_roid' => $response['epp']['response']['resData']['contact:infData']['contact:roid'],
                'client_name' => $response['epp']['response']['resData']['contact:infData']['contact:postalInfo']['contact:name'],
                'client_address_1' => $response['epp']['response']['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:street'][0],
                'client_address_2' => $response['epp']['response']['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:street'][1],
                'client_city' => $response['epp']['response']['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:city'],
                'client_state' => $response['epp']['response']['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:sp'],
                'client_zipcode' => $response['epp']['response']['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:pc'],
                'client_country' => $response['epp']['response']['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:cc'],
                'client_phone' => $response['epp']['response']['resData']['contact:infData']['contact:voice'],
                'client_email' => $response['epp']['response']['resData']['contact:infData']['contact:email'],
                'client_create' => $response['epp']['response']['resData']['contact:infData']['contact:crDate'],
                'client_update' => (isset($response['epp']['response']['resData']['contact:infData']['contact:upDate']))? $response['epp']['response']['resData']['contact:infData']['contact:upDate'] : ''
            );
            
            return $data;
        }
        // }}}
        
        // {{{ contact_create()

        /**
         * Creates a new contact
         *
         * This function creates a new contact.
         *
         * @param string $client_name Full name.
         * @param string $client_street_1 Address.
         * @param string $client_street_2 Address 2.
         * @param string $client_city City. Eg: 'São Paulo'.
         * @param string $client_state State. Eg: 'SP'.
         * @param string $client_zipcode Zipcode. Eg: '00000-000'.
         * @param string $client_country country. Default is 'BR'.
         * @param string $client_phone Phone. Required the country code Eg: '+55.1100000000'.
         * @param string $client_email E-mail. Eg: 'test@test.com'
         *
         * @return array Returns the information of the new contact.
         *
         * @access public
         */
        public function contact_create(
            $client_name = null,
            $client_street_1 = null,
            $client_street_2 = null,
            $client_city = null,
            $client_state = null,
            $client_zipcode = null,
            $client_country = 'BR',
            $client_phone = null,
            $client_email = null
        )
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/contact_create.xml');
            
            $postal_info = "<contact:postalInfo type=\"loc\">
                                <contact:name>{$client_name}</contact:name>
                                <contact:org></contact:org>
                                <contact:addr>
                                    <contact:street>{$client_street_1}</contact:street>
                                    <contact:street>{$client_street_2}</contact:street>
                                    <contact:city>{$client_city}</contact:city>
                                    <contact:sp>{$client_state}</contact:sp>
                                    <contact:pc>{$client_zipcode}</contact:pc>
                                    <contact:cc>{$client_country}</contact:cc>
                                </contact:addr>
                            </contact:postalInfo>";
            
            $voice = "<contact:voice>{$client_phone}</contact:voice>";
            
            $auth_info = "<contact:authInfo>
                                <contact:pw>{$this->_password}</contact:pw>
                            </contact:authInfo>";
                            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';

            $xml = str_replace('<contact:id>$(id)$</contact:id>', '', $xml);
            $xml = str_replace('$(postal_info)$', $postal_info, $xml);
            $xml = str_replace('$(voice)$', $voice, $xml);
            $xml = str_replace('$(fax)$', '', $xml);
            $xml = str_replace('$(email)$', $client_email, $xml);
            $xml = str_replace('$(auth_info)$', $auth_info, $xml);
            $xml = str_replace('$(disclose)$', '', $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            if($response['epp']['response']['result_attr']['code'] != '1000')
                return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
                
            $data = array(
                'client_id' => $response['epp']['response']['resData']['contact:creData']['contact:id'],
                'client_creation' => $response['epp']['response']['resData']['contact:creData']['contact:crDate'],
                'client_cltrid' => $response['epp']['response']['trID']['clTRID']
            );
            
            return $data;
        }
        // }}}
        
        // {{{ contact_update()

        /**
         * Updates a contact
         *
         * This function updates all contact's information fields
         *
         * @param string $client_name Full name.
         * @param string $client_street_1 Address.
         * @param string $client_street_2 Address 2.
         * @param string $client_city City. Eg: 'São Paulo'.
         * @param string $client_state State. Eg: 'SP'.
         * @param string $client_zipcode Zipcode. Eg: '00000-000'.
         * @param string $client_country country. Default is 'BR'.
         * @param string $client_phone Phone. Required the country code Eg: '+55.1100000000'.
         * @param string $client_email E-mail. Eg: 'test@test.com'
         *
         * @return array Returns the contact's updated information
         *
         * @access public
         */
        public function contact_update(
            $client_id = null,
            $client_street_1 = null,
            $client_street_2 = null, 
            $client_city = null,
            $client_state = null,
            $client_zipcode = null,
            $client_country = 'BR',
            $client_phone = null, 
            $client_email = null
        )
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/contact_update.xml');
            
            $chg = "<contact:chg>
                        <contact:postalInfo type=\"loc\">
                            <contact:addr>
                                <contact:street>{$client_street_1}</contact:street>
                                <contact:street>{$client_street_2}</contact:street>
                                <contact:city>{$client_city}</contact:city>
                                <contact:sp>{$client_state}</contact:sp>
                                <contact:pc>{$client_zipcode}</contact:pc>
                                <contact:cc>{$client_country}</contact:cc>
                            </contact:addr>
                        </contact:postalInfo>
                        <contact:voice>{$client_phone}</contact:voice>
                        <contact:email>{$client_email}</contact:email>
                        <contact:authInfo>
                            <contact:pw>{$this->_password}</contact:pw>
                        </contact:authInfo>
                    </contact:chg>";
                            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';

            $xml = str_replace('$(id)$', $client_id, $xml);
            $xml = str_replace('$(add)$', '', $xml);
            $xml = str_replace('$(rem)$', '', $xml);
            $xml = str_replace('$(chg)$', $chg, $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
        }
        // }}}
        
        // {{{ org_check()

        /**
         * Checks if an organization exists.
         *
         * Checks if an organization already exists with CPF or CNPJ.
         *
         * @param string $org_id Organization's CPF or CNPJ. Eg: '246.838.523-30'.
         *
         * @return array Returns all organization's information
         *
         * @access public
         */
        public function org_check($org_id = null)
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/br_org_check.xml');
            
            $organization_list = "<brorg:cd>
                                    <brorg:id>{$org_id}</brorg:id>
                                    <brorg:organization>{$org_id}</brorg:organization>
                                </brorg:cd>";
            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';

            $xml = str_replace('<contact:id>$(id_list)$</contact:id>', '', $xml);
            $xml = str_replace('$(organization_list)$', $organization_list, $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            if($response['epp']['response']['result_attr']['code'] != '1000')
                return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
            
            $data = array(
                'org_id' => $response['epp']['response']['resData']['contact:chkData']['contact:cd']['contact:id'],
                'org_available' => $response['epp']['response']['resData']['contact:chkData']['contact:cd']['contact:id_attr']['avail'],
                'org_reason' => (isset($response['epp']['response']['resData']['contact:chkData']['contact:cd']['contact:reason'])) ? $response['epp']['response']['resData']['contact:chkData']['contact:cd']['contact:reason'] : ''
            );
            
            return $data;
        }
        // }}}
        
        // {{{ org_info()

        /**
         * Searches for an organization.
         *
         * Searches all information from an Organization
         *
         * @param string $org_id Organization's CPF or CNPJ. Eg: '246.838.523-30'.
         *
         * @return array Returns all organization's information
         *
         * @access public
         */
        public function org_info($org_id = null)
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/br_org_info.xml');
            
            $auth_info = "<contact:authInfo>
                                <contact:pw>{$this->_password}</contact:pw>
                            </contact:authInfo>";
            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';

            $xml = str_replace('<contact:id>$(id)$</contact:id>', '', $xml);
            $xml = str_replace('$(organization)$', $org_id, $xml);
            $xml = str_replace('$(auth_info)$', $auth_info, $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            if($response['epp']['response']['result_attr']['code'] != '1000')
                return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
                
            $data = array(
                'org_id' => $response['epp']['response']['resData']['contact:infData']['contact:id'],
                'org_roid' => $response['epp']['response']['resData']['contact:infData']['contact:roid'],
                'org_name' => $response['epp']['response']['resData']['contact:infData']['contact:postalInfo']['contact:name'],
                'org_status' => $response['epp']['response']['resData']['contact:infData']['contact:status'],
                'org_address_1' => $response['epp']['response']['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:street'][0],
                'client_address_2' => $response['epp']['response']['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:street'][1],
                'org_city' => $response['epp']['response']['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:city'],
                'org_state' => $response['epp']['response']['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:sp'],
                'org_zipcode' => $response['epp']['response']['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:pc'],
                'org_country' => $response['epp']['response']['resData']['contact:infData']['contact:postalInfo']['contact:addr']['contact:cc'],
                'org_phone' => $response['epp']['response']['resData']['contact:infData']['contact:voice'],
                'org_email' => $response['epp']['response']['resData']['contact:infData']['contact:email'],
                'org_client_id' => $response['epp']['response']['resData']['contact:infData']['contact:clID'],
                'org_create_id' => $response['epp']['response']['resData']['contact:infData']['contact:crID'],
                'org_create' => $response['epp']['response']['resData']['contact:infData']['contact:crDate'],
                'org_update' => (isset($response['epp']['response']['resData']['contact:infData']['contact:upDate']))? $response['epp']['response']['resData']['contact:infData']['contact:upDate'] : '',
                'org_contact' => array(
                    'org_id' => $response['epp']['response']['extension']['brorg:infData']['brorg:organization'],
                    'org_contact_id' => $response['epp']['response']['extension']['brorg:infData']['brorg:contact'],
                    'org_contact_type' => $response['epp']['response']['extension']['brorg:infData']['brorg:contact_attr']['type']
                )
            );
            
            return $data;
        }
        // }}}
        
        // {{{ org_create()

        /**
         * Creates a new organization
         *
         * Creates a new organization using a contact previously created.
         *
         * @param string $org_id Organization's CPF or CNPJ. Eg: '246.838.523-30'.
         * @param string $org_name Name.
         * @param string $org_street_1 Address.
         * @param string $org_street_2 Address 2.
         * @param string $org_city City. Eg: 'São Paulo'.
         * @param string $org_state State. Eg: 'SP'.
         * @param string $org_zipcode Zipcode. Eg: '00000-000'.
         * @param string $org_country Country. Default is 'BR'.
         * @param string $org_phone Phone. Required the country code. Eg: '+55.1100000000'.
         * @param string $org_email E-mail. Eg: 'test@test.com'.
         * @param string $contact_id ID from a contact previously created. Eg: 'JOSIL44'.
         * @param string $contact_name Contact's name.
         *
         * @return array Returns all organization's information.
         *
         * @access public
         */
        public function org_create(
            $org_id = null,
            $org_name = null,
            $org_street_1 = null,
            $org_street_2 = null,
            $org_city = null,
            $org_state = null,
            $org_zipcode = null,
            $org_country = 'BR',
            $org_phone = null,
            $org_email = null,
            $contact_id = null,
            $contact_name = null
        )
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/br_org_create.xml');
            
            $postal_info = "<contact:postalInfo type=\"loc\">
                                <contact:name>{$org_name}</contact:name>
                                <contact:addr>
                                    <contact:street>{$org_street_1}</contact:street>
                                    <contact:street>{$org_street_2}</contact:street>
                                    <contact:city>{$org_city}</contact:city>
                                    <contact:sp>{$org_state}</contact:sp>
                                    <contact:pc>{$org_zipcode}</contact:pc>
                                    <contact:cc>{$org_country}</contact:cc>
                                </contact:addr>
                            </contact:postalInfo>";
            
            $voice = "<contact:voice>{$org_phone}</contact:voice>";
            
            $auth_info = "<contact:authInfo>
                                <contact:pw>{$this->_password}</contact:pw>
                            </contact:authInfo>";
            
            $brorg_contact_list = "<brorg:contact type=\"admin\">{$contact_id}</brorg:contact>
                                    <brorg:contact type=\"tech\">{$contact_id}</brorg:contact>
                                    <brorg:contact type=\"billing\">{$contact_id}</brorg:contact>";
            
            $responsible = "<brorg:responsible>{$contact_name}</brorg:responsible>";
                            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';

            $xml = str_replace('$(id)$', $org_id, $xml);
            $xml = str_replace('$(postal_info)$', $postal_info, $xml);
            $xml = str_replace('$(voice)$', $voice, $xml);
            $xml = str_replace('$(fax)$', '', $xml);
            $xml = str_replace('$(email)$', $org_email, $xml);
            $xml = str_replace('$(auth_info)$', $auth_info, $xml);
            $xml = str_replace('$(disclose)$', '', $xml);
            $xml = str_replace('$(organization)$', $org_id, $xml);
            $xml = str_replace('$(brorg_contact_list)$', $brorg_contact_list, $xml);
            $xml = str_replace('$(responsible)$', $responsible, $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            if($response['epp']['response']['result_attr']['code'] != '1000' &&
                $response['epp']['response']['result_attr']['code'] != '1001')
                return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
                
            $data = array(
                'code_id' => $response['epp']['response']['result_attr']['code'],
                'code_message' => $response['epp']['response']['result']['msg'],
                'org_id' => $response['epp']['response']['resData']['contact:creData']['contact:id'],
                'org_creation' => $response['epp']['response']['resData']['contact:creData']['contact:crDate']
            );
            
            return $data;
        }
        // }}}
        
        // {{{ org_update()

        /**
         * Updates an organization
         *
         * Updates all organization's information.
         *
         * @param string $org_id Organization's CPF or CNPJ. Eg: '246.838.523-30'.
         * @param string $org_name Name.
         * @param string $org_street_1 Address.
         * @param string $org_street_2 Address 2.
         * @param string $org_city City. Eg: 'São Paulo'.
         * @param string $org_state State. Eg: 'SP'.
         * @param string $org_zipcode Zipcode. Eg: '00000-000'.
         * @param string $org_country Country. Default is 'BR'.
         * @param string $org_phone Phone. Required the country code. Eg: '+55.1100000000'.
         * @param string $contact_id ID from a contact previously created. Eg: 'JOSIL44'.
         * @param string $contact_name Contact's name.
         *
         * @return array Returns organization's updated information
         *
         * @access public
         */
        public function org_update(
            $org_id = null,
            $org_street_1 = null,
            $org_street_2 = null,
            $org_city = null,
            $org_state = null,
            $org_zipcode = null,
            $org_country = 'BR',
            $org_phone = null,
            $contact_id = null,
            $contact_name = null
        )
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/br_org_update.xml');
            
            $chg = "<contact:chg>
                        <contact:postalInfo type=\"loc\">
                            <contact:addr>
                                <contact:street>{$org_street_1}</contact:street>
                                <contact:street>{$org_street_2}</contact:street>
                                <contact:city>{$org_city}</contact:city>
                                <contact:sp>{$org_state}</contact:sp>
                                <contact:pc>{$org_zipcode}</contact:pc>
                                <contact:cc>{$org_country}</contact:cc>
                            </contact:addr>
                        </contact:postalInfo>
                        <contact:voice>{$org_phone}</contact:voice>
                        <contact:authInfo>
                            <contact:pw>{$this->_password}</contact:pw>
                        </contact:authInfo>
                    </contact:chg>";
                            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';

            $xml = str_replace('$(id)$', $org_id, $xml);
            $xml = str_replace('$(add)$', '', $xml);
            $xml = str_replace('$(rem)$', '', $xml);
            $xml = str_replace('$(chg)$', $chg, $xml);
            $xml = str_replace('$(organization)$', $org_id, $xml);
            $xml = str_replace('$(brorg_add)$', '', $xml);
            $xml = str_replace('$(brorg_rem)$', '', $xml);
            $xml = str_replace('$(brorg_chg)$', '', $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
        }
        // }}}
        
        // {{{ domain_check()

        /**
         * Checks if a domain is available.
         *
         * @param string $domain_name Domain to check. Eg: 'test.com.br'.
         *
         * @return array Returns information about the availability.
         *
         * @access public
         */
        public function domain_check($domain_name = null)
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/br_domain_check.xml');
            
            $domains_list = "<domain:name>{$domain_name}</domain:name>";
            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';

            $xml = str_replace('$(domains_list)$', $domains_list, $xml);
            $xml = str_replace('$(extension)$', '', $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            if($response['epp']['response']['result_attr']['code'] != '1000')
                return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
                
            $data = array(
                'domain_name' => $response['epp']['response']['resData']['domain:chkData']['domain:cd']['domain:name'],
                'domain_available' => $response['epp']['response']['resData']['domain:chkData']['domain:cd']['domain:name_attr']['avail']
            );
            
            return $data;
        }
        // }}}
        
        // {{{ domain_info()

        /**
         * Get information about a domain.
         *
         * @param int $ticket_number Ticket number if a domian has one. Eg: '6489'.
         * @param string $domain_name Domain to look for. Eg: 'test.com.br'.
         *
         * @return array Returns domain's information
         *
         * @access public
         */
        public function domain_info($ticket_number = null, $domain_name = null)
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/br_domain_info.xml');
            
            $auth_info = "<contact:authInfo>
                                <contact:pw>{$this->_password}</contact:pw>
                            </contact:authInfo>";
            
            $extention = "<extension>
                                <brdomain:info
                                xmlns:brdomain=\"urn:ietf:params:xml:ns:brdomain-1.0\"
                                xsi:schemaLocation=\"urn:ietf:params:xml:ns:brdomain-1.0
                                brdomain-1.0.xsd\">
                                    <brdomain:ticketNumber>{$ticket_number}</brdomain:ticketNumber>
                                </brdomain:info>
                            </extension>";
            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';

            $xml = str_replace('$(hosts_control)$', 'all', $xml);
            $xml = str_replace('$(name)$', $domain_name, $xml);
            $xml = str_replace('$(auth_info)$', $auth_info, $xml);
            $xml = str_replace('$(extension)$', $extention, $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            if($response['epp']['response']['result_attr']['code'] != '1000')
                return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
                
            $data = array(
                'domain_name' => $response['epp']['response']['resData']['domain:infData']['domain:name'],
                'domain_roid' => $response['epp']['response']['resData']['domain:infData']['domain:roid'],
                'domain_status' => $response['epp']['response']['resData']['domain:infData']['domain:status_attr']['s'],
                'domain_contact' => array(
                    $response['epp']['response']['resData']['domain:infData']['domain:contact']['0_attr']['type'] => $response['epp']['response']['resData']['domain:infData']['domain:contact'][0],
                    $response['epp']['response']['resData']['domain:infData']['domain:contact']['1_attr']['type'] => $response['epp']['response']['resData']['domain:infData']['domain:contact'][1],
                    $response['epp']['response']['resData']['domain:infData']['domain:contact']['2_attr']['type'] => $response['epp']['response']['resData']['domain:infData']['domain:contact'][2]
                    ),
                'domain_ticket' => $response['epp']['response']['extension']['brdomain:infData']['brdomain:ticketNumber'],
                'domain_organization' => $response['epp']['response']['extension']['brdomain:infData']['brdomain:organization'],
                'domain_publication' => (isset($response['epp']['response']['extension']['brdomain:infData']['brdomain:publicationStatus_attr']['publicationFlag']))? $response['epp']['response']['extension']['brdomain:infData']['brdomain:publicationStatus_attr']['publicationFlag'] : '',
                'domain_doc' => array(
                    'doc_message' => (isset($response['epp']['response']['extension']['brdomain:infData']['brdomain:pending']['brdomain:doc']['brdomain:description']))? $response['epp']['response']['extension']['brdomain:infData']['brdomain:pending']['brdomain:doc']['brdomain:description'] : '',
                    'doc_status' => (isset($response['epp']['response']['extension']['brdomain:infData']['brdomain:pending']['brdomain:doc_attr']['status']))? $response['epp']['response']['extension']['brdomain:infData']['brdomain:pending']['brdomain:doc_attr']['status'] : ''
                    ),
                'domain_dns' => array(
                    'dns_1' => (isset($response['epp']['response']['resData']['domain:infData']['domain:ns']['domain:hostAttr'][0]['domain:hostName']))? $response['epp']['response']['resData']['domain:infData']['domain:ns']['domain:hostAttr'][0]['domain:hostName'] : '',
                    'dns_2' => (isset($response['epp']['response']['resData']['domain:infData']['domain:ns']['domain:hostAttr'][1]['domain:hostName']))? $response['epp']['response']['resData']['domain:infData']['domain:ns']['domain:hostAttr'][1]['domain:hostName'] : '',
                    'dns_1_status' => (isset($response['epp']['response']['extension']['brdomain:infData']['brdomain:pending']['brdomain:dns']['0_attr']['status']))? $response['epp']['response']['extension']['brdomain:infData']['brdomain:pending']['brdomain:dns']['0_attr']['status'] : ''
                    ),
                'domain_dns_pending' => array(
                    'dns_1' => (isset($response['epp']['response']['extension']['brdomain:infData']['brdomain:pending']['brdomain:dns'][0]['brdomain:hostName']))? $response['epp']['response']['extension']['brdomain:infData']['brdomain:pending']['brdomain:dns'][0]['brdomain:hostName'] : '',
                    'dns_2' => (isset($response['epp']['response']['extension']['brdomain:infData']['brdomain:pending']['brdomain:dns'][1]['brdomain:hostName']))? $response['epp']['response']['extension']['brdomain:infData']['brdomain:pending']['brdomain:dns'][1]['brdomain:hostName'] : ''
                    ),
                'domain_create' => $response['epp']['response']['resData']['domain:infData']['domain:crDate'],
                'domain_expiration' => (isset($response['epp']['response']['resData']['domain:infData']['domain:exDate']))? $response['epp']['response']['resData']['domain:infData']['domain:exDate'] : '',
                'domain_autorenew' => $response['epp']['response']['extension']['brdomain:infData']['brdomain:autoRenew_attr']['active']
            );
            
            return $data;
        }
        // }}}
        
        // {{{ domain_create()

        /**
         * Creates a new domain.
         *
         * This function creates a new domain.
         *
         * @param string $domain_name Domain name. Eg: 'test.com.br'.
         * @param int $domain_period Period for creation. For default is 1 year and does not accept another value.
         * @param string $dns_1 Primary DNS in IPv4.
         * @param string $dns_2 Secondary DNS in IPv4.
         * @param string $org_id Organization ID previously created. Eg: '246.838.523-30'.
         * @param bool $auto_renew 1 for auto renew every year or 0 to expire til the end. Default is 0.
         *
         * @return array Returns domain's information
         *
         * @access public
         */
        public function domain_create(
            $domain_name = null,
            $domain_period = 1,
            $dns_1 = null,
            $dns_2 = null,
            $dns_3 = null,
            $dns_4 = null,
            $org_id = null,
            $auto_renew = 0
        )
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/br_domain_create.xml');
            
            $period = "<domain:period unit=\"y\">{$domain_period}</domain:period>";
            
            $nameservers = "<domain:ns>
                                <domain:hostAttr>
                                    <domain:hostName>{$dns_1}</domain:hostName>
                                </domain:hostAttr>
                                <domain:hostAttr>
                                    <domain:hostName>{$dns_2}</domain:hostName>
                                </domain:hostAttr>
                                <domain:hostAttr>
                                    <domain:hostName>{$dns_3}</domain:hostName>
                                </domain:hostAttr>
                                <domain:hostAttr>
                                    <domain:hostName>{$dns_4}</domain:hostName>
                                </domain:hostAttr>
                            </domain:ns>";
                            
            /**
             *  This array is only used for Registro.br homologation because the homologation
             *  requires an IPv6 message test.
             */
            /*$temp_nameservers = "<domain:ns>
                                    <domain:hostAttr>
                                        <domain:hostName>ns1.superix.com.br</domain:hostName>
                                    </domain:hostAttr>
                                    <domain:hostAttr>
                                        <domain:hostName>ns2.superix.com.br</domain:hostName>
                                        <domain:hostAddr ip=\"v4\">200.175.82.128</domain:hostAddr>
                                    </domain:hostAttr>
                                    <domain:hostAttr>
                                        <domain:hostName>ns3.superix.com.br</domain:hostName>
                                        <domain:hostAddr ip=\"v4\">200.175.82.129</domain:hostAddr>
                                        <domain:hostAddr ip=\"v6\">2001:0db8:85a3:08d3:1319:8a2e:0370:7344</domain:hostAddr>
                                    </domain:hostAttr>
                                </domain:ns>";*/
            
            $auth_info = "<contact:authInfo>
                                <contact:pw>{$this->_password}</contact:pw>
                            </contact:authInfo>";
            
            $ext_begin = "<extension>
                            <brdomain:create
                            xmlns:brdomain=\"urn:ietf:params:xml:ns:brdomain-1.0\"
                            xsi:schemaLocation=\"urn:ietf:params:xml:ns:brdomain-1.0
                            brdomain-1.0.xsd\">
                                <brdomain:organization>{$org_id}</brdomain:organization>
                                <brdomain:autoRenew active=\"{$auto_renew}\"/>
                            </brdomain:create>
                        </extension>";
            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';
            
            $xml = str_replace('$(name)$', $domain_name, $xml);
            $xml = str_replace('$(period)$', $period, $xml);
            $xml = str_replace('$(nameservers)$', $nameservers, $xml);
            $xml = str_replace('$(registrant)$', '', $xml);
            $xml = str_replace('$(other_contacts)$', '', $xml);
            $xml = str_replace('$(auth_info)$', $auth_info, $xml);
            $xml = str_replace('$(ext_begin)$', $ext_begin, $xml);
            $xml = str_replace('$(ds_ext)$', '', $xml);
            $xml = str_replace('$(br_ext)$', '', $xml);
            $xml = str_replace('$(ext_end)$', '', $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            if($response['epp']['response']['result_attr']['code'] != '1000' &&
                $response['epp']['response']['result_attr']['code'] != '1001')
                return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
        
            $data = array(
                'domain_name' => $response['epp']['response']['resData']['domain:creData']['domain:name'],
                'domain_create' => $response['epp']['response']['resData']['domain:creData']['domain:crDate'],
                'domain_ticket' => $response['epp']['response']['extension']['brdomain:creData']['brdomain:ticketNumber'],
                'domain_doc' => array(
                    'doc_message' => (isset($response['epp']['response']['extension']['brdomain:creData']['brdomain:pending']['brdomain:doc']['brdomain:description']))? $response['epp']['response']['extension']['brdomain:creData']['brdomain:pending']['brdomain:doc']['brdomain:description'] : '',
                    'doc_status' => (isset($response['epp']['response']['extension']['brdomain:creData']['brdomain:pending']['brdomain:doc_attr']['status']))? $response['epp']['response']['extension']['brdomain:creData']['brdomain:pending']['brdomain:doc_attr']['status'] : ''
                    ),
                'domain_dns' => array(
                    'dns_1' => (isset($response['epp']['response']['extension']['brdomain:creData']['brdomain:pending']['brdomain:dns'][0]['brdomain:hostName']))? $response['epp']['response']['extension']['brdomain:creData']['brdomain:pending']['brdomain:dns'][0]['brdomain:hostName'] : '',
                    'dns_2' => (isset($response['epp']['response']['extension']['brdomain:creData']['brdomain:pending']['brdomain:dns'][1]['brdomain:hostName']))? $response['epp']['response']['extension']['brdomain:creData']['brdomain:pending']['brdomain:dns'][1]['brdomain:hostName'] : ''
                    )
            );
            
            return $data;
        }
        // }}}
        
        // {{{ domain_update()

        /**
         * Updates a domain.
         *
         * This function updates a domain's information.
         *
         * @param int $ticket_number Creation ticket if the domain has one. Eg: '7889'.
         * @param string $domain_name Domain name. Eg: 'test.com.br'.
         * @param string $dns_1 Primary DNS in IPv4.
         * @param string $dns_2 Secondary DNS in IPv4.
         * @param string $client_id Contact's ID previously created. Eg: '246.838.523-30'.
         * @param string $org_id Organization ID previously created. Eg: '246.838.523-30'.
         * @param bool $auto_renew 1 for auto renew every year or 0 to expire til the end. Default is 0.
         *
         * @return array Returns domain's information
         *
         * @access public
         */
        public function domain_update(
            $ticket_number = null,
            $domain_name = null,
            $dns_1 = null,
            $dns_2 = null,
            $dns_3 = null,
            $dns_4 = null,
            $client_id = null,
            $org_id = null,
            $auto_renew = 0
        )
        {
            $domain_data = $this->domain_info($ticket_number, $domain_name);
            
            $xml = file_get_contents(dirname(__FILE__) . '/templates/br_domain_update.xml');

            /**
             *  This array is only used for Registro.br homologation because the homologation
             *  requires 3 DNS verifications.
             */
            /*$temp_chg = "<domain:rem>
                            <domain:hostAttr>
                                <domain:hostName>ns1.YOURSITE.com.br</domain:hostName>
                            </domain:hostAttr>
                            <domain:hostAttr>
                                <domain:hostName>ns2.YOURSITE.com.br</domain:hostName>
                            </domain:hostAttr>
                            <domain:hostAttr>
                                <domain:hostName>ns3.YOURSITE.com.br</domain:hostName>
                            </domain:hostAttr>
                        </domain:rem>
                        
                        <domain:rem>
                            <domain:hostAttr>
                                <domain:hostName>{$domain_data['domain_dns']['dns_1']}</domain:hostName>
                            </domain:hostAttr>
                            <domain:hostAttr>
                                <domain:hostName>{$domain_data['domain_dns']['dns_2']}</domain:hostName>
                            </domain:hostAttr>
                        </domain:rem>
                        
                        <domain:add>
                            <domain:hostAttr>
                                <domain:hostName>{$dns_1}</domain:hostName>
                            </domain:hostAttr>
                            <domain:hostAttr>
                                <domain:hostName>{$dns_2}</domain:hostName>
                            </domain:hostAttr>
                        </domain:add>";*/

            $chg = "<domain:rem>
                        <domain:hostAttr>
                            <domain:hostName>{$domain_data['domain_dns']['dns_1']}</domain:hostName>
                        </domain:hostAttr>
                        <domain:hostAttr>
                            <domain:hostName>{$domain_data['domain_dns']['dns_2']}</domain:hostName>
                        </domain:hostAttr>
                        <domain:hostAttr>
                            <domain:hostName>{$domain_data['domain_dns']['dns_3']}</domain:hostName>
                        </domain:hostAttr>
                        <domain:hostAttr>
                            <domain:hostName>{$domain_data['domain_dns']['dns_4']}</domain:hostName>
                        </domain:hostAttr>
                    </domain:rem>
                    
                    <domain:add>
                        <domain:hostAttr>
                            <domain:hostName>{$dns_1}</domain:hostName>
                        </domain:hostAttr>
                        <domain:hostAttr>
                            <domain:hostName>{$dns_2}</domain:hostName>
                        </domain:hostAttr>
                        <domain:hostAttr>
                            <domain:hostName>{$dns_3}</domain:hostName>
                        </domain:hostAttr>
                        <domain:hostAttr>
                            <domain:hostName>{$dns_4}</domain:hostName>
                        </domain:hostAttr>
                    </domain:add>";
            
            if($client_id != null)
            {
                $chg .= "<domain:rem>
                            <domain:contact type=\"admin\">{$domain_data['domain_contact']['admin']}</domain:contact>
                            <domain:contact type=\"tech\">{$domain_data['domain_contact']['tech']}</domain:contact>
                            <domain:contact type=\"billing\">{$domain_data['domain_contact']['billing']}</domain:contact>
                        </domain:rem>
                        
                        <domain:add>
                            <domain:contact type=\"admin\">{$client_id}</domain:contact>
                            <domain:contact type=\"tech\">{$client_id}</domain:contact>
                            <domain:contact type=\"billing\">{$client_id}</domain:contact>
                        </domain:add>";
            }
            
            $ext_begin = "<extension>
                            <brdomain:update
                            xmlns:brdomain=\"urn:ietf:params:xml:ns:brdomain-1.0\"
                            xsi:schemaLocation=\"urn:ietf:params:xml:ns:brdomain-1.0
                            brdomain-1.0.xsd\">
                                <brdomain:ticketNumber>{$ticket_number}</brdomain:ticketNumber>
                                <brdomain:chg>
                                    <brdomain:autoRenew active=\"{$auto_renew}\"/>
                                </brdomain:chg>
                            </brdomain:update>
                        </extension>";
            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';

            $xml = str_replace('$(name)$', $domain_name, $xml);
            $xml = str_replace('$(add)$', '', $xml);
            $xml = str_replace('$(rem)$', '', $xml);
            $xml = str_replace('$(chg)$', $chg, $xml);
            $xml = str_replace('$(ext_begin)$', $ext_begin, $xml);
            $xml = str_replace('$(ds_ext)$', '', $xml);
            $xml = str_replace('$(br_ext)$', '', $xml);
            $xml = str_replace('$(ext_end)$', '', $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
        }
        // }}}
        
        // {{{ domain_renew()

        /**
         * Updates the domain expiration date.
         *
         * When a domain is expiring, this function will update the expiration date for 1, 2 or more years.
         *
         * @param string $domain_name Domain name. Ex: 'test.com.br'.
         * @param date $domain_expiration Current expiration date. You can find out the expiration date using domain_info().
         * @param int $domain_year_renovation Number of years to renovate. Default is 1 year.
         *
         * @return array Returns domain's information.
         *
         * @access public
         */
        public function domain_renew($domain_name = null, $domain_expiration = null, $domain_year_renovation = 1)
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/domain_renew.xml');
            
            $period = "<domain:period unit=\"y\">{$domain_year_renovation}</domain:period>";
            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';
            
            $xml = str_replace('$(name)$', $domain_name, $xml);
            $xml = str_replace('$(curExpDate)$', $domain_expiration, $xml);
            $xml = str_replace('$(period)$', $period, $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            if($response['epp']['response']['result_attr']['code'] != '1000')
                return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
            
            $data = array(
                'domain_name' => $response['epp']['response']['resData']['domain:renData']['domain:name'],
                'domain_new_expiration' => $response['epp']['response']['resData']['domain:renData']['domain:exDate'],
                'domain_publication_status' => (isset($response['epp']['response']['extension']['brdomain:renData']['brdomain:publicationStatus_attr']['publicationFlag']))? $response['epp']['response']['extension']['brdomain:renData']['brdomain:publicationStatus_attr']['publicationFlag'] : ''
            );
            
            return $data;
        }
        // }}}
        
        // {{{ domain_delete()

        /**
         * Deletes a domain.
         *
         * This function deletes a domain. But there are restrictions for delete. You can only delete
         * domains created up to X days. Consult Registro.br for more details.
         *
         * @param string $domain_name Domain name. Eg: 'test.com.br'.
         *
         * @return array Returns domain's information.
         *
         * @access public
         */
        public function domain_delete($domain_name = null)
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/domain_delete.xml');
            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';
            
            $xml = str_replace('$(name)$', $domain_name, $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
        }
        // }}}
        
        // {{{ poll_request()

        /**
         * Reads the last message from the Registro.br's EPP system.
         *
         * This function reads the last message in the queue. Registro.br will get in touch with your company through messages POLL.
         * Important messages will be read with this function.
         *
         * @return array Returns the last message in the queue.
         *
         * @access public
         */
        public function poll_request()
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/poll.xml');
            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';
            
            $xml = str_replace('$(op)$', 'req', $xml);
            $xml = str_replace('$(msgID)$', '', $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            if($response['epp']['response']['result_attr']['code'] != '1000'&&
                $response['epp']['response']['result_attr']['code'] != '1301')
                return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
            
            $data = array(
                'msg_count' => $response['epp']['response']['msgQ_attr']['count'],
                'msg_id' => $response['epp']['response']['msgQ_attr']['id'],
                'msg_date' => $response['epp']['response']['msgQ']['qDate'],
                'msg_content' => (isset($response['epp']['response']['msgQ']['msg']))? $response['epp']['response']['msgQ']['msg'] : ''
            );
            
            return $data;
        }
        // }}}
        
        // {{{ poll_delete()

        /**
         * Deletes a message from the Registro.br's EPP system.
         *
         * This function deletes one message from the Registro.br's EPP system. You need the message ID, which 
         * can be retrieve from poll_request() function.
         *
         * @param int $message_id Mensagem a ser apagada. Ex: '4680'.
         *
         * @return array Returns information about the deleted mesage.
         *
         * @access public
         */
        public function poll_delete($message_id = null)
        {
            $xml = file_get_contents(dirname(__FILE__) . '/templates/poll.xml');
            
            $cltrid = '<clTRID>'.$this->generate_id().'</clTRID>';
            
            $xml = str_replace('$(op)$', 'ack', $xml);
            $xml = str_replace('$(msgID)$', "msgID=\"{$message_id}\"", $xml);
            $xml = str_replace('$(clTRID)$', $cltrid, $xml);
            
            $xml = $this->wrap($xml);
            
            $this->send_command($xml);
            
            $response = $this->xml2array($this->unwrap());
            
            return array($response['epp']['response']['result_attr']['code'] => $response['epp']['response']['result']['msg']);
        }
        // }}}
    }
?>