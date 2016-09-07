<?php

  class ref_category {

    private $_country_code;
    private $_cache_id;
    private $_language_codes;
    private $_data = array();

    function __construct($country_code, $language_code=null) {

      $this->_country_code = (int)$country_code;
      $this->_cache_id = cache::cache_id('country_'.(int)$country_code);
      $this->_language_codes = array_unique(array(
        !empty($language_code) ? $language_code : language::$selected['code'],
        settings::get('default_language_code'),
        settings::get('store_language_code'),
      ));

      if ($cache = cache::get($this->_cache_id, 'file')) {
        $this->_data = $cache;
      }
    }

    public function &__get($name) {

      if (array_key_exists($name, $this->_data)) {
        return $this->_data[$name];
      }

      $this->_data[$name] = null;
      $this->load($name);

      return $this->_data[$name];
    }

    public function &__isset($name) {
      return $this->__get($name);
    }

    public function __set($name, $value) {
      trigger_error('Setting data is prohibited ('.$name.')', E_USER_WARNING);
    }

    private function load($field='') {

      switch($field) {

        case 'zones':

          $this->_data['zones'] = array();

          $query = database::query(
            "select id from ". DB_TABLE_ZONES ."
            where country_code = '". database::input($this->_country_code) ."'
            order by name;"
          );

          while ($row = database::fetch($query)) {
            foreach ($row as $key => $value) {
              $this->_data['zones'][$row['code']] = $row;
            }
          }

          break;

        default:

          $query = database::query(
            "select * from ". DB_TABLE_COUNTRIES ."
            where iso_code_2 = '". database::input($this->_country_code) ."'
            limit 1;"
          );

          $row = database::fetch($query);

          if (database::num_rows($query) == 0) return;

          foreach ($row as $key => $value) $this->_data[$key] = $value;

          break;
      }

      cache::set($this->_cache_id, 'file', $this->_data);
    }
    
    function format_address($address) {

      $address = array(
        '%company' => !empty($address['company']) ? $address['company'] : '',
        '%firstname' => !empty($address['firstname']) ? $address['firstname'] : '',
        '%lastname' => !empty($address['lastname']) ? $address['lastname'] : '',
        '%address1' => !empty($address['address1']) ? $address['address1'] : '',
        '%address2' => !empty($address['address2']) ? $address['address2'] : '',
        '%city' => !empty($address['city']) ? $address['city'] : '',
        '%postcode' => !empty($address['postcode']) ? $address['postcode'] : '',
        '%country_code' => $address['country_code'],
        '%country_name' => $this->name,
        '%zone_code' => !empty($address['zone_code']) ? $address['zone_code'] : '',
        '%zone_name' => !empty($address['zone_code']) ? $this->zones[$zone['code']]['name'] : '',
      );

      $output = strtr($this->address_format ? $this->address_format : settings::get('default_address_format'), $address);

      while (preg_match('#(\R\R)#', $output)) $output = preg_replace('#(\R\R)#', "\r\n", $output);

      return trim($output);
    }
  }

?>