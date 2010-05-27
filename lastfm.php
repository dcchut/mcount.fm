<?php

class LastFM
{
	private $_api_key;

	public function __construct($api_key)
	{
		$this->_api_key = $api_key;
	}
	
	protected function _load($url)
	{
		$c = curl_init();
		
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
		
		return curl_exec($c);
	}
	
	protected function _is_api_error(SimpleXMLElement $xml_object)
	{
		$a = $xml_object->attributes();
		return ($a['status'] == 'failed');
	}
	
	protected function _build_url(array $parameters)
	{
		$url = 'http://ws.audioscrobbler.com/2.0/?';
		
		foreach ($parameters as $k => $v)
			$url .= '&' . urlencode($k) . '=' . urlencode($v);
		
		return $url;
	}
	
	public function request($method, array $parameters)
	{
		// build our new parameter array
		$new_parameters = array_merge(array('method'  => $method,
										    'api_key' => $this->_api_key),
									  $parameters);
									  
		$url = $this->_build_url($new_parameters);
		
		// make the request
		$result = $this->_load($url);
		
		var_dump($result);
		
		// can we interpret this as an XML object?
		$xml    = simplexml_load_string($result);
		
		// is this error'd?
		if (!is_a($xml, 'SimpleXMLElement') || $this->_is_api_error($xml))
			return FALSE;
		else
			return $xml;
	}
}