<?php

class OmiseCapabilities extends OmiseApiResource
{
    const ENDPOINT = 'capability';

    /**
     * Retrieves capabilities.
     *
     * @param  string $publickey
     * @param  string $secretkey
     *
     * @return OmiseCapabilities
     */
    public static function retrieve($publickey = null, $secretkey = null)
    {
        return parent::g_retrieve(get_class(), self::getUrl(), $publickey, $secretkey);
    }

    /**
     * Retrieves array of payment backends.
     *    
     * @return array
     */
    public function getBackends() {
        // check for filters
        if ($filters = func_get_args()) $filter = self::combineFilters($filters);
        $res = $this['payment_backends'];
        array_walk(
            $res,
            function($v, $k) use (&$res) {
                $id = array_keys($v)[0];
                $res[$k][$id]['_id'] = $id;
            }
        );
        $res = array_map(function($a) { return (object)reset($a);}, $res);
        return !empty($filter) ? array_filter($res, $filter) : $res;
    }


    public static function backendSupportsCurrency($currency) {
        return function($backend) use ($currency) { return in_array($currency, $backend->currencies); };
    }

    public static function backendTypeIs($type) {
        return function($backend) use ($type) { return $backend->type==$type; };
    }

    public static function backendSupportsAmount($amount) {
        return function($backend) use ($amount) {
            if (!empty($backend->amount['min']) && $backend->amount['min'] > $amount) return false;
            if (!empty($backend->amount['max']) && $backend->amount['max'] < $amount) return false;
            return true;
        };
    }


    /**
     * Combines boolean filters.
     *    
     * @return function
     */
    public static function combineFilters($filters) {
        return function($a) use ($filters) {
            foreach ($filters as $filter) if (!$filter($a)) return false;
            return true;
        };
    }

    /**
     * (non-PHPdoc)
     *
     * @see OmiseApiResource::g_reload()
     */
    public function reload()
    {
        parent::g_reload(self::getUrl());
    }

    /**
     * @return string
     */
    private static function getUrl()
    {
        return OMISE_API_URL.self::ENDPOINT;
    }

    /**
     * Checks if response from API was valid.
     *
     * @param  array  $array  - decoded JSON response
     * 
     * @return boolean
     */
    protected function isValidAPIResponse($array)
    {
        return count($array);
    }

    /**
     * Returns the public key.
     *
     * @return string
     */
    protected function getResourceKey()
    {
        return $this->_publickey;
    }

}
