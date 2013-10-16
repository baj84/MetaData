<?php
/*
  Copyright 2013 Ben Southall

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
   
	Original can be found at https://github.com/baj84/metadata/
   
*/

class MetaData implements Iterator {
  /**
   * Holds all the meta tags we get from the url
   *
   */
	private $_values = array();

	/**
	* Fetches a URI and parses it for meta tags, returns
	* false on error.
	*
	* @param $URI    URI to page to parse for Open Graph data
	* @return MetaData
   */
	static public function fetch($URI) {
		$curl = curl_init($URI);

		curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

        $response = curl_exec($curl);

        curl_close($curl);

		if(!empty($response)) {
			return self::_parse($response);
		}
		else {
			return false;
        }
	}

	/**
	* Parses HTML and extracts meta tags
	*
	* @param $HTML    HTML to parse
	* @return MetaData
	*/
	static private function _parse($HTML) {
		$page = new self();
		$rawTags = array();

		preg_match_all("|<meta[^>]+=\"([^\"]*)\"[^>]" . "+content=\"([^\"]*)\"[^>]+>|i", $HTML, $rawTags, PREG_PATTERN_ORDER);

		if(!empty($rawTags)) {
			$multiValueTags = array_unique(array_diff_assoc($rawTags[1], array_unique($rawTags[1])));

			for($i=0; $i < sizeof($rawTags[1]); $i++) {
				$hasMultiValues = false;
				$tag = $rawTags[1][$i];

				foreach($multiValueTags as $mTag) {
					if($tag == $mTag)
						$hasMultiValues = true;
				}
				
				if($hasMultiValues) {
					$page->_values[$tag][] = $rawTags[2][$i];
				}
				else {
					$page->_values[$tag] = $rawTags[2][$i];
				}
			}
		}

		if (empty($page->_values)) { return false; }

		return $page;
	}

	/**
	* Helper method to access attribute array directly
	*/
	public function tags() {
		return $this->_values;
	}

	/**
	* Helper method to access attributes directly
	* Example:
	* $metaData->title
	*
	* @param $key    Key to fetch from the lookup
	*/
	public function __get($key) {
		if (array_key_exists($key, $this->_values)) {
			return $this->_values[$key];
		}
	}

	/**
	* Return all the keys found on the page
	*
	* @return array
	*/
	public function keys() {
		return array_keys($this->_values);
	}

	/**
	* Helper method to check an attribute exists
	*
	* @param $key
	*/
	public function __isset($key) {
		return array_key_exists($key, $this->_values);
	}

	/**
	* Iterator code
	*/
	private $_position = 0;
	public function rewind() { reset($this->_values); $this->_position = 0; }
	public function current() { return current($this->_values); }
	public function key() { return key($this->_values); }
	public function next() { next($this->_values); ++$this->_position; }
	public function valid() { return $this->_position < sizeof($this->_values); }
}
