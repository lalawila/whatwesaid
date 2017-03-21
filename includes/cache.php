<?php
function ws_cache_get( $key, $group = '', $force = false, &$found = null ) {
	global $ws_object_cache;

	return $ws_object_cache->get( $key, $group, $force, $found );
}
function ws_cache_add( $key, $data, $group = '', $expire = 0 ) {
	global $ws_object_cache;

	return $ws_object_cache->add( $key, $data, $group, (int) $expire );
}
function ws_cache_init() {
	$GLOBALS['ws_object_cache'] = new WS_Object_Cache();
}
class WS_Object_Cache { 
    private $cache = array();
    public $cache_misses = 0;
    protected $global_groups = array();
    
    public function __construct() {

		register_shutdown_function( array( $this, '__destruct' ) );
	}
    public function __destruct() {
		return true;
	}
    public function add( $key, $data, $group = 'default', $expire = 0 ) {
		if ( ws_suspend_cache_addition() )
			return false;

		if ( empty( $group ) )
			$group = 'default';

		$id = $key;

		if ( $this->_exists( $id, $group ) )
			return false;

		return $this->set( $key, $data, $group, (int) $expire );
	}
    
    public function set( $key, $data, $group = 'default', $expire = 0 ) {
		if ( empty( $group ) )
			$group = 'default';
            
		if ( is_object( $data ) )
			$data = clone $data;

		$this->cache[$group][$key] = $data;
		return true;
	}
    
    public function flush() {
		$this->cache = array();

		return true;
	}
}