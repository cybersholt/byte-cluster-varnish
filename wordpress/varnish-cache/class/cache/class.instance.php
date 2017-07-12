<?php
/**
 * @author Ferdy Perdaan
 * @version 1.0
 *
 * Abstract cache class
 */

abstract class XLII_Cache_Instance extends XLII_Cache_Singleton
{
	/**
	 * Returns wether the cache engine is availible on this server.
	 * 
	 * @return	bool
	 */ 
	abstract public function availible();

	/**
	 * Delete the page cache.
	 * 
	 * @param	string $key The key the cache attribute is referred by.
	 * @return	bool
	 */ 
	public function delete($key)
	{
		if($this->isValid() === false)
			return false;

		else if(!$key = (array)$key)
			return true;

		else if(function_exists('apply_filters') && !$key = apply_filters('cache_flush', $key, $this))
			return true;
			
		else 
			return $this->_delete(array_map(array($this, '_key'), $key));
	}
	
	/**
	 * Delete the page cache, inner helper method of @see delete.
	 * 
	 * @param	array $key The key the cache attribute is referred by.
	 * @return	bool
	 */ 
	abstract protected function _delete(array $key);
	
	/**
	 * Returns wether this page exists within the cache.
	 * 
	 * @param	string $key The key the cache attribute is referred by.
	 * @return	bool|null
	 */ 
	public function exists($key)
	{
		return $this->isValid() !== false ? $this->_exists($this->_key($key)) : false;
	}
	
	/**
	 * Returns wether this page exists within the cache, inner helper method of @see exists.
	 * 
	 * @param	string $key The key the cache attribute is referred by.
	 * @return	bool|null
	 */ 
	protected function _exists($key)
	{
		global $wpdb;
		
		if(!empty($wpdb) && isset($wpdb->cache_log))
			return $wpdb->get_var($wpdb->prepare('SELECT COUNT(1) FROM ' . $wpdb->cache_log . ' WHERE url = %s', $key)) > 0;
		else
			return null;
	}
	
	/**
	 * Flush the entire cache.
	 * 
	 * @return	bool
	 */ 
	public function flush()
	{
		$success = function_exists('apply_filters') ? apply_filters('cache_flush_all', null, $this) : null;
		$success = $success === null ? $this->delete(home_url('/.*')) : $success;
		
		return $success;
	}
	
	/**
	 * Return the cache object referred by the given key.
	 * 
	 * @param	string $key The key the cache attribute is referred by.
	 * @return	void|false
	 */ 
	public function get($key)
	{
		return $this->isValid() !== false ? $this->_get($this->_key($key)) : false;
	}
	
	/**
	 * Return the cache object referred by the given key, inner helper method of @see get.
	 * 
	 * @param	string $key The key the cache attribute is referred by.
	 * @return	void|false
	 */ 
	abstract protected function _get($key);
	
	/**
	 * Mutate the key to a generic key.
	 * 
	 * @param	string $key The key the cache attribute is referred by.
	 * @return	string
	 */
	protected function _key($key)
	{
		return XLII_Cache_Manager::option('general.https_indifferent', true) ? str_replace('https', 'http', $key) : $key;
	}
	
	/**
	 * Returns wether the cache connection is valid
	 * 
	 * @return	bool
	 */
	public function isValid()
	{
		return $this->availible();
	}
	
	/**
	 * Return the label the engine is referred by
	 * 
	 * @return	string
	 */ 
	abstract public function label();
	
	/**
	 * Regsiter the caching module.
	 */
	static public function register()
	{	
		$class = get_called_class();
		
		add_filter('cache_engines', array($class, '_register'));
		
		if(method_exists($class, '_configurationRender'))
		{
			add_action('cache_configuration_engine_form', array($class, '_configurationRender'));
			add_filter('cache_form_process_engine_' . $class, array($class, '_configurationProcess'));
		}
	}
	
	/**
	 * Register the cache object as a possible engine
	 * 
	 * @param	array $engines An array containing the availible cache engines
	 * @return	array
	 */
	static public function _register(array $engines)
	{
		$class = get_called_class();
		$class = new $class;
		
		$engines[] = $class;
		
		return $engines;
	}
	
	/**
	 * Store cache data under the given key.
	 * 
	 * @param	string $key The key the cache attribute is referred by.
	 * @param	void $value The value to store within the cache.
	 * @return	bool
	 */ 
	public function set($key, $value)
	{
		return $this->isValid() !== false ? $this->_set($this->_key($key), $value) : false;
	}
	
	/**
	 * Store cache data under the given key, inner helper method of @see set.
	 * 
	 * @param	string $key The key the cache attribute is referred by.
	 * @param	void $value The value to store within the cache.
	 * @return	bool
	 */ 
	abstract protected function _set($key, &$value);
}
