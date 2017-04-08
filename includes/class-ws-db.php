<?php
define('OBJECT', 'OBJECT');
define('object', 'OBJECT');

define('OBJECT_K', 'OBJECT_K');
define('ARRAY_A', 'ARRAY_A');
define('ARRAY_N', 'ARRAY_N');
/**
 * object   rows[n] = class{ a = 1, b = 2 }
 * object_k rows[a] = class{ a = 1, b = 2 }
 * array_a  rows[n] = [ a => 1, b => 2 ]
 * array_n  rows[n] = [ 1, 2 ]
 *
 */


class WS_DB {
    protected $dbuser;
    protected $dbpassword;
    protected $dbname;
    protected $dbhost;
    protected $dbh;

    private $checking_collation = false;
    private $ready = false;
    protected $check_current_query = true;

    private $last_result;
    private $last_error;
    protected $result;
    
    protected $num_rows;
    protected $rows_affected;
    protected $insert_id;

    protected $reconnect_retries = 5;
    protected $col_meta = array();

    public $charset;
    public $collate; 
    public function __construct($dbuser, $dbpassword, $dbname, $dbhost) {
        register_shutdown_function(array($this, '__destruct'));

        $this->dbuser = $dbuser;
        $this->dbpassword = $dbpassword;
        $this->dbname = $dbname;
        $this->dbhost = $dbhost;

        $this->db_connect();
    }

    public function __destruct() {
        return true;
    }
    public function __get( $name ) {
        return $this->$name;
    }

    public function db_connect() {
        $this->dbh = mysqli_init();

        $port = null;
        $socket = null;
        $host = $this->dbhost;
        $port_or_socket = strstr($host, ':');
        if (!empty($port_or_socket)) {
            $host = substr($host, 0, strpos($host, ':'));
            $port_or_socket = substr($port_or_socket, 1);
            if (0 !== strpos($port_or_socket, '/')) {
                $port = intval($port_or_socket);
                $maybe_socket = strstr($port_or_socket, ':');
                if (!empty($maybe_socket)) {
                    $socket = substr($maybe_socket, 1);
                }
            } else {
                $socket = $port_or_socket;
            }
        }
        @mysqli_real_connect($this->dbh, $host, $this->dbuser, $this->dbpassword, null, $port, $socket, 0);
        if ($this->dbh->connect_errno) {
            $this->dbh = null;
        }

        if ($this->dbh) {

            $this->init_charset();

            $this->has_connected = true;

            $this->set_charset($this->dbh);

            $this->ready = true;
            $this->set_sql_mode();
            $this->select($this->dbname, $this->dbh);
            return true;
        }

        return false;
    }

    public function set_charset($dbh, $charset = null, $collate = null) {
        if (!isset($charset))
            $charset = $this->charset;
        if (!isset($collate))
            $collate = $this->collate;
        if (!empty($charset)) {
            $set_charset_succeeded = true;
            $set_charset_succeeded = mysqli_set_charset($dbh, $charset);
            if ($set_charset_succeeded) {
                $query = $this->prepare('SET NAMES %s', $charset);
                if (!empty($collate))
                    $query .= $this->prepare(' COLLATE %s', $collate);
                mysqli_query($dbh, $query);
            }
        }
    }

   	public function prepare( $query, $args ) {
		if ( is_null( $query ) )
			return;

		$args = func_get_args();
		array_shift( $args );
		// If args were passed as an array (as in vsprintf), move them up
		if ( isset( $args[0] ) && is_array($args[0]) )
			$args = $args[0];
		$query = str_replace( "'%s'", '%s', $query ); // in case someone mistakenly already singlequoted it
		$query = str_replace( '"%s"', '%s', $query ); // doublequote unquoting
		$query = preg_replace( '|(?<!%)%f|' , '%F', $query ); // Force floats to be locale unaware
		$query = preg_replace( '|(?<!%)%s|', "'%s'", $query ); // quote the strings, avoiding escaped strings like %%s
		array_walk( $args, array( $this, 'escape_by_ref' ) );
		return @vsprintf( $query, $args );
    }

    public function escape_by_ref( &$string ) {
		if ( ! is_float( $string ) )
			$string = $this->_real_escape( $string );
    }

    public function _real_escape( $string ) {
		if ( $this->dbh ) {
				return mysqli_real_escape_string( $this->dbh, $string );
		}
		return addslashes( $string );
    
    }

    public function select($db, $dbh = null) {
        if (is_null($dbh))
            $dbh = $this->dbh;
        $success = mysqli_select_db($dbh, $db);

        if(!$success) 
            $this->ready = false;
    }

    public function get_results($query = null, $output = object) {

        if ($this->check_current_query && $this->check_safe_collation($query)) {
            $this->check_current_query = false;
        }

        if ($query) {
            $this->query($query);
        } else {
            return null;
        }

        $new_array = array();
        if ($output == object) {
            // Return an integer-keyed array of row objects
            return $this->last_result;
        } elseif ($output == OBJECT_K) {
            // Return an array of row objects with keys from column 1
            // (Duplicates are discarded)
            foreach ($this->last_result as $row) {
                $var_by_ref = get_object_vars($row);
                $key = array_shift($var_by_ref);
                if (!isset($new_array[$key]))
                    $new_array[$key] = $row;
            }
            return $new_array;
        } elseif ($output == ARRAY_A || $output == ARRAY_N) {
            // Return an integer-keyed array of...
            if ($this->last_result) {
                foreach ((array )$this->last_result as $row) {
                    if ($output == ARRAY_N) {
                        // ...integer-keyed row arrays
                        $new_array[] = array_values(get_object_vars($row));
                    } else {
                        // ...column name-keyed row arrays
                        $new_array[] = get_object_vars($row);
                    }
                }
            }
            return $new_array;
        } elseif (strtoupper($output) === object) {
            // Back compat for OBJECT being previously case insensitive.
            return $this->last_result;
        }
        return null;
    }


    public function query($query) {
        if (!$this->ready) {
            $this->check_current_query = true;
            return false;
        }
        $this->flush();
        // If we're writing to the database, make sure the query will write safely.
        if ($this->check_current_query && !$this->check_ascii($query)) {
            $stripped_query = $this->strip_invalid_text_from_query($query);
            // strip_invalid_text_from_query() can perform queries, so we need
            // to flush again, just to make sure everything is clear.
            $this->flush();
            if ($stripped_query !== $query) {
                $this->insert_id = 0;
                return false;
            }
        }
        $this->check_current_query = true;

        $this->_do_query($query);

        // MySQL server has gone away, try to reconnect.
        $mysql_errno = 0;
        if (!empty($this->dbh)) {
            if ($this->dbh instanceof mysqli) {
                $mysql_errno = mysqli_errno($this->dbh);
            } else {
                // $dbh is defined, but isn't a real connection.
                // Something has gone horribly wrong, let's try a reconnect.
                $mysql_errno = 2006;
            }
        }

        if (empty($this->dbh) || 2006 == $mysql_errno) {
            if( $this->check_connection()) {
                $this->_do_query( $query );
            
            } else{
                $this->insert_id = 0;
                return false;
            }
        }

        // If there is an error then take note of it.
        if ($this->dbh instanceof mysqli) {
            $this->last_error = mysqli_error($this->dbh);
        } 

        if ($this->last_error) {
            // Clear insert_id on a subsequent failed insert.
            if ($this->insert_id && preg_match('/^\s*(insert|replace)\s/i', $query))
                $this->insert_id = 0;

            return false;
        }

        if (preg_match('/^\s*(create|alter|truncate|drop)\s/i', $query)) {
            $return_val = $this->result;
        } elseif (preg_match('/^\s*(insert|delete|update|replace)\s/i', $query)) {
            $this->rows_affected = mysqli_affected_rows($this->dbh);
            // Take note of the insert_id
            if (preg_match('/^\s*(insert|replace)\s/i', $query)) {
                $this->insert_id = mysqli_insert_id($this->dbh);
            }
            // Return number of rows affected
            $return_val = $this->rows_affected;
        } else {
            $num_rows = 0;
            if ($this->result instanceof mysqli_result) {
                while ($row = mysqli_fetch_object($this->result)) {
                    $this->last_result[$num_rows] = $row;
                    $num_rows++;
                }
            }
            // Log number of rows the query returned
            // and return number of rows selected
            $this->num_rows = $num_rows;
            $return_val = $num_rows;
        }

        return $return_val;
    }

	public function check_connection( ) {
		if ( ! empty( $this->dbh ) && mysqli_ping( $this->dbh ) ) {
			return true;
		}

		$error_reporting = false;

		for ( $tries = 1; $tries <= $this->reconnect_retries; $tries++ ) {

			if ( $this->db_connect( ) ) {

				return true;
			}

			sleep( 1 );
        }
    }



    private function _do_query($query) {
        if (!empty($this->dbh)) {
            $this->result = mysqli_query($this->dbh, $query);
        }
    }

    /**
     * Kill cached query results.
     * */
    public function flush() {
        $this->last_result = array();
        $this->col_info = null;
        $this->last_query = null;
        $this->rows_affected = $this->num_rows = 0;
        $this->last_error = '';

        if ($this->result instanceof mysqli_result) {
            mysqli_free_result($this->result);
            $this->result = null;

            // Sanity check before using the handle
            if (empty($this->dbh) || !($this->dbh instanceof mysqli)) {
                return;
            }

            // Clear out any results from a multi-query
            while (mysqli_more_results($this->dbh)) {
                mysqli_next_result($this->dbh);
            }
        } elseif (is_resource($this->result)) {
            mysql_free_result($this->result);
        }
    }


    protected function check_ascii($string) {
        if (function_exists('mb_check_encoding')) {
            if (mb_check_encoding($string, 'ASCII')) {
                return true;
            }
        } elseif (!preg_match('/[^\x00-\x7F]/', $string)) {
            return true;
        }

        return false;
    }

    /*
     * Check if the query is accessing a collation considered safe on the current version of MySQL
     */
    protected function check_safe_collation($query) {
        if ($this->checking_collation) {
            return true;
        }

        // We don't need to check the collation for queries that don't read data.
        $query = ltrim($query, "\r\n\t (");
        if (preg_match('/^(?:SHOW|DESCRIBE|DESC|EXPLAIN|CREATE)\s/i', $query)) {
            return true;
        }

        // All-ASCII queries don't need extra checking.
        if ($this->check_ascii($query)) {
            return true;
        }

        $table = $this->get_table_from_query($query);
        if (!$table) {
            return false;
        }

        $this->checking_collation = true;
        $collation = $this->get_table_charset($table);
        $this->checking_collation = false;

        // Tables with no collation, or latin1 only, don't need extra checking.
        if (false === $collation || 'latin1' === $collation) {
            return true;
        }

        $table = strtolower($table);
        if (empty($this->col_meta[$table])) {
            return false;
        }

        // If any of the columns don't have one of these collations, it needs more sanity checking.
        foreach ($this->col_meta[$table] as $col) {
            if (empty($col->Collation)) {
                continue;
            }

            if (!in_array($col->Collation, array(
                'utf8_general_ci',
                'utf8_bin',
                'utf8mb4_general_ci',
                'utf8mb4_bin'), true)) {
                return false;
            }
        }

        return true;
    }
    protected function get_table_from_query( $query ) {
		// Remove characters that can legally trail the table name.
		$query = rtrim( $query, ';/-#' );

		// Allow (select...) union [...] style queries. Use the first query's table name.
		$query = ltrim( $query, "\r\n\t (" );

		// Strip everything between parentheses except nested selects.
		$query = preg_replace( '/\((?!\s*select)[^(]*?\)/is', '()', $query );

		// Quickly match most common queries.
		if ( preg_match( '/^\s*(?:'
				. 'SELECT.*?\s+FROM'
				. '|INSERT(?:\s+LOW_PRIORITY|\s+DELAYED|\s+HIGH_PRIORITY)?(?:\s+IGNORE)?(?:\s+INTO)?'
				. '|REPLACE(?:\s+LOW_PRIORITY|\s+DELAYED)?(?:\s+INTO)?'
				. '|UPDATE(?:\s+LOW_PRIORITY)?(?:\s+IGNORE)?'
				. '|DELETE(?:\s+LOW_PRIORITY|\s+QUICK|\s+IGNORE)*(?:.+?FROM)?'
				. ')\s+((?:[0-9a-zA-Z$_.`-]|[\xC2-\xDF][\x80-\xBF])+)/is', $query, $maybe ) ) {
			return str_replace( '`', '', $maybe[1] );
		}

		// SHOW TABLE STATUS and SHOW TABLES WHERE Name = 'wp_posts'
		if ( preg_match( '/^\s*SHOW\s+(?:TABLE\s+STATUS|(?:FULL\s+)?TABLES).+WHERE\s+Name\s*=\s*("|\')((?:[0-9a-zA-Z$_.-]|[\xC2-\xDF][\x80-\xBF])+)\\1/is', $query, $maybe ) ) {
			return $maybe[2];
		}

		// SHOW TABLE STATUS LIKE and SHOW TABLES LIKE 'wp\_123\_%'
		// This quoted LIKE operand seldom holds a full table name.
		// It is usually a pattern for matching a prefix so we just
		// strip the trailing % and unescape the _ to get 'wp_123_'
		// which drop-ins can use for routing these SQL statements.
		if ( preg_match( '/^\s*SHOW\s+(?:TABLE\s+STATUS|(?:FULL\s+)?TABLES)\s+(?:WHERE\s+Name\s+)?LIKE\s*("|\')((?:[\\\\0-9a-zA-Z$_.-]|[\xC2-\xDF][\x80-\xBF])+)%?\\1/is', $query, $maybe ) ) {
			return str_replace( '\\_', '_', $maybe[2] );
		}

		// Big pattern for the rest of the table-related queries.
		if ( preg_match( '/^\s*(?:'
				. '(?:EXPLAIN\s+(?:EXTENDED\s+)?)?SELECT.*?\s+FROM'
				. '|DESCRIBE|DESC|EXPLAIN|HANDLER'
				. '|(?:LOCK|UNLOCK)\s+TABLE(?:S)?'
				. '|(?:RENAME|OPTIMIZE|BACKUP|RESTORE|CHECK|CHECKSUM|ANALYZE|REPAIR).*\s+TABLE'
				. '|TRUNCATE(?:\s+TABLE)?'
				. '|CREATE(?:\s+TEMPORARY)?\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?'
				. '|ALTER(?:\s+IGNORE)?\s+TABLE'
				. '|DROP\s+TABLE(?:\s+IF\s+EXISTS)?'
				. '|CREATE(?:\s+\w+)?\s+INDEX.*\s+ON'
				. '|DROP\s+INDEX.*\s+ON'
				. '|LOAD\s+DATA.*INFILE.*INTO\s+TABLE'
				. '|(?:GRANT|REVOKE).*ON\s+TABLE'
				. '|SHOW\s+(?:.*FROM|.*TABLE)'
				. ')\s+\(*\s*((?:[0-9a-zA-Z$_.`-]|[\xC2-\xDF][\x80-\xBF])+)\s*\)*/is', $query, $maybe ) ) {
			return str_replace( '`', '', $maybe[1] );
		}

		return false;
	}
    public function close() {
        if (!$this->dbh) {
            return false;
        }

        if ($this->use_mysqli) {
            $closed = mysqli_close($this->dbh);
        } else {
            $closed = mysql_close($this->dbh);
        }

        if ($closed) {
            $this->dbh = null;
            $this->ready = false;
            $this->has_connected = false;
        }

        return $closed;
    }

    /**
     * Set $this->charset and $this->collate
     */
    public function init_charset() {
        $charset = '';
        $collate = '';

        if (defined('DB_COLLATE')) {
            $collate = DB_COLLATE;
        }

        if (defined('DB_CHARSET')) {
            $charset = DB_CHARSET;
        }

        $charset_collate = $this->determine_charset($charset, $collate);

        $this->charset = $charset_collate['charset'];
        $this->collate = $charset_collate['collate'];
    }
    /**
     * Determines the best charset and collation to use given a charset and collation,
     */
    public function determine_charset( $charset, $collate ) {
		if ( empty( $this->dbh ) ) {
			return compact( 'charset', 'collate' );
		}

		if ( 'utf8' === $charset && $this->has_cap( 'utf8mb4' ) ) {
			$charset = 'utf8mb4';
		}

		if ( 'utf8mb4' === $charset && ! $this->has_cap( 'utf8mb4' ) ) {
			$charset = 'utf8';
			$collate = str_replace( 'utf8mb4_', 'utf8_', $collate );
		}

		if ( 'utf8mb4' === $charset ) {
			// _general_ is outdated, so we can upgrade it to _unicode_, instead.
			if ( ! $collate || 'utf8_general_ci' === $collate ) {
				$collate = 'utf8mb4_unicode_ci';
			} else {
				$collate = str_replace( 'utf8_', 'utf8mb4_', $collate );
			}
		}

		// _unicode_520_ is a better collation, we should use that when it's available.
		if ( $this->has_cap( 'utf8mb4_520' ) && 'utf8mb4_unicode_ci' === $collate ) {
			$collate = 'utf8mb4_unicode_520_ci';
		}

		return compact( 'charset', 'collate' );
	}

    public function set_sql_mode($modes = array()) {
        if (empty($modes)) {
            $res = mysqli_query($this->dbh, 'SELECT @@SESSION.sql_mode');

            if (empty($res)) {
                return;
            }

            $modes_array = mysqli_fetch_array($res);
            if (empty($modes_array[0])) {
                return;
            }
            $modes_str = $modes_array[0];

            if (empty($modes_str)) {
                return;
            }

            $modes = explode(',', $modes_str);
        }

        $modes = array_change_key_case($modes, CASE_UPPER);

        //foreach ($modes as $i => $mode) {
        //    if (in_array($mode, $this->incompatible_imodes)) {
        //        unset($modes[$i]);
        //    }
        //}

        $modes_str = implode(',', $modes);

        mysqli_query($this->dbh, "SET SESSION sql_mode='$modes_str'");
    }
    //@param int         $y      Optional. Row to return. Indexed from 0.
    public function get_row( $query = null, $output = OBJECT, $y = 0 ) {

		if ( $this->check_current_query && $this->check_safe_collation( $query ) ) {
			$this->check_current_query = false;
		}

		if ( $query ) {
			$this->query( $query );
		} else {
			return null;
		}

		if ( !isset( $this->last_result[$y] ) )
			return null;

		if ( $output == OBJECT ) {
			return $this->last_result[$y] ? $this->last_result[$y] : null;
		} elseif ( $output == ARRAY_A ) {
			return $this->last_result[$y] ? get_object_vars( $this->last_result[$y] ) : null;
		} elseif ( $output == ARRAY_N ) {
			return $this->last_result[$y] ? array_values( get_object_vars( $this->last_result[$y] ) ) : null;
		} elseif ( strtoupper( $output ) === OBJECT ) {
			// Back compat for OBJECT being previously case insensitive.
			return $this->last_result[$y] ? $this->last_result[$y] : null;
		} else {
			$this->print_error( " \$db->get_row(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N" );
		}
	}
    
    	public function insert( $table, $data, $format = null ) {
		return $this->_insert_replace_helper( $table, $data, $format, 'INSERT' );
	}
    	/** 
	 *
	 * @param string       $table  Table name
	 * @param array        $data   Data to insert (in column => value pairs).
	 *                             Both $data columns and $data values should be "raw" (neither should be SQL escaped).
	 *                             Sending a null value will cause the column to be set to NULL - the corresponding format is ignored in this case.
	 * @param array|string $format Optional. An array of formats to be mapped to each of the value in $data.
	 *                             If string, that format will be used for all of the values in $data.
	 *                             A format is one of '%d', '%f', '%s' (integer, float, string).
	 *                             If omitted, all values in $data will be treated as strings unless otherwise specified in wpdb::$field_types.
	 * @param string $type         Optional. What type of operation is this? INSERT or REPLACE. Defaults to INSERT.
	 * @return int|false The number of rows affected, or false on error.
	 */
   	private function _insert_replace_helper( $table, $data, $format = null, $type = 'INSERT' ) {
		$this->insert_id = 0;

		if ( ! in_array( strtoupper( $type ), array( 'REPLACE', 'INSERT' ) ) ) {
			return false;
		}

		$data = $this->process_fields( $table, $data, $format );
		if ( false === $data ) {
			return false;
		}

		$formats = $values = array();
		foreach ( $data as $value ) {
			if ( is_null( $value['value'] ) ) {
				$formats[] = 'NULL';
				continue;
			}

			$formats[] = $value['format'];
			$values[]  = $value['value'];
		}

		$fields  = '`' . implode( '`, `', array_keys( $data ) ) . '`';
		$formats = implode( ', ', $formats );

		$sql = "$type INTO `$table` ($fields) VALUES ($formats)";

		$this->check_current_query = false;
		return $this->query( $this->prepare( $sql, $values ) );
	}
    	protected function process_fields( $table, $data, $format ) {
		$data = $this->process_field_formats( $data, $format );
		if ( false === $data ) {
		    WS_Error::add_error('process field formats error');
			return false;
		}

		$data = $this->process_field_charsets( $data, $table );
		if ( false === $data ) {
		    WS_Error::add_error('process field charsets error');
			return false;
		}

		$data = $this->process_field_lengths( $data, $table );
		if ( false === $data ) {
		    WS_Error::add_error('process field lengths error');
			return false;
		}

		$converted_data = $this->strip_invalid_text( $data );

		if ( $data !== $converted_data ) {
		    WS_Error::add_error('strip invalid textprocess error');
			return false;
		}

		return $data;
	}
	protected function process_field_formats( $data, $format ) {
		$formats = $original_formats = (array) $format;

		foreach ( $data as $field => $value ) {
			$value = array(
				'value'  => $value,
				'format' => '%s',
			);

			if ( ! empty( $format ) ) {
				$value['format'] = array_shift( $formats );
				if ( ! $value['format'] ) {
					$value['format'] = reset( $original_formats );
				}
			} elseif ( isset( $this->field_types[ $field ] ) ) {
				$value['format'] = $this->field_types[ $field ];
			}

			$data[ $field ] = $value;
		}

		return $data;
	}
	protected function process_field_charsets( $data, $table ) {
		foreach ( $data as $field => $value ) {
			if ( '%d' === $value['format'] || '%f' === $value['format'] ) {
				/*
				 * We can skip this field if we know it isn't a string.
				 * This checks %d/%f versus ! %s because its sprintf() could take more.
				 */
				$value['charset'] = false;
			} else {
				$value['charset'] = $this->get_col_charset( $table, $field );
				if ( !$value['charset']  ) {
				    WS_Error::add_error('get col charset error');
					return false;
				}
			}

			$data[ $field ] = $value;
		}

		return $data;
	}
	protected function process_field_lengths( $data, $table ) {
		foreach ( $data as $field => $value ) {
			if ( '%d' === $value['format'] || '%f' === $value['format'] ) {
				/*
				 * We can skip this field if we know it isn't a string.
				 * This checks %d/%f versus ! %s because its sprintf() could take more.
				 */
				$value['length'] = false;
			} else {
				$value['length'] = $this->get_col_length( $table, $field );
				if (!$value['length'] ) {
					return false;
				}
			}

			$data[ $field ] = $value;
		}

		return $data;
	}
	protected function strip_invalid_text( $data ) {
		$db_check_string = false;

		foreach ( $data as &$value ) {
			$charset = $value['charset'];

			if ( is_array( $value['length'] ) ) {
				$length = $value['length']['length'];
				$truncate_by_byte_length = 'byte' === $value['length']['type'];
			} else {
				$length = false;
				// Since we have no length, we'll never truncate.
				// Initialize the variable to false. true would take us
				// through an unnecessary (for this case) codepath below.
				$truncate_by_byte_length = false;
			}

			// There's no charset to work with.
			if ( false === $charset ) {
				continue;
			}

			// Column isn't a string.
			if ( ! is_string( $value['value'] ) ) {
				continue;
			}

			$needs_validation = true;
			if (
				// latin1 can store any byte sequence
				'latin1' === $charset
			||
				// ASCII is always OK.
				( ! isset( $value['ascii'] ) && $this->check_ascii( $value['value'] ) )
			) {
				$truncate_by_byte_length = true;
				$needs_validation = false;
			}

			if ( $truncate_by_byte_length ) {
				mbstring_binary_safe_encoding();
				if ( false !== $length && strlen( $value['value'] ) > $length ) {
					$value['value'] = substr( $value['value'], 0, $length );
				}
				reset_mbstring_encoding();

				if ( ! $needs_validation ) {
					continue;
				}
			}

			// utf8 can be handled by regex, which is a bunch faster than a DB lookup.
			if ( ( 'utf8' === $charset || 'utf8mb3' === $charset || 'utf8mb4' === $charset ) && function_exists( 'mb_strlen' ) ) {
				$regex = '/
					(
						(?: [\x00-\x7F]                  # single-byte sequences   0xxxxxxx
						|   [\xC2-\xDF][\x80-\xBF]       # double-byte sequences   110xxxxx 10xxxxxx
						|   \xE0[\xA0-\xBF][\x80-\xBF]   # triple-byte sequences   1110xxxx 10xxxxxx * 2
						|   [\xE1-\xEC][\x80-\xBF]{2}
						|   \xED[\x80-\x9F][\x80-\xBF]
						|   [\xEE-\xEF][\x80-\xBF]{2}';

				if ( 'utf8mb4' === $charset ) {
					$regex .= '
						|    \xF0[\x90-\xBF][\x80-\xBF]{2} # four-byte sequences   11110xxx 10xxxxxx * 3
						|    [\xF1-\xF3][\x80-\xBF]{3}
						|    \xF4[\x80-\x8F][\x80-\xBF]{2}
					';
				}

				$regex .= '){1,40}                          # ...one or more times
					)
					| .                                  # anything else
					/x';
				$value['value'] = preg_replace( $regex, '$1', $value['value'] );


				if ( false !== $length && mb_strlen( $value['value'], 'UTF-8' ) > $length ) {
					$value['value'] = mb_substr( $value['value'], 0, $length, 'UTF-8' );
				}
				continue;
			}

			// We couldn't use any local conversions, send it to the DB.
			$value['db'] = $db_check_string = true;
		}
		unset( $value ); // Remove by reference.

		if ( $db_check_string ) {
			$queries = array();
			foreach ( $data as $col => $value ) {
				if ( ! empty( $value['db'] ) ) {
					// We're going to need to truncate by characters or bytes, depending on the length value we have.
					if ( 'byte' === $value['length']['type'] ) {
						// Using binary causes LEFT() to truncate by bytes.
						$charset = 'binary';
					} else {
						$charset = $value['charset'];
					}

					if ( $this->charset ) {
						$connection_charset = $this->charset;
					} else {
						if ( $this->use_mysqli ) {
							$connection_charset = mysqli_character_set_name( $this->dbh );
						} else {
							$connection_charset = mysql_client_encoding();
						}
					}

					if ( is_array( $value['length'] ) ) {
						$queries[ $col ] = $this->prepare( "CONVERT( LEFT( CONVERT( %s USING $charset ), %.0f ) USING $connection_charset )", $value['value'], $value['length']['length'] );
					} else if ( 'binary' !== $charset ) {
						// If we don't have a length, there's no need to convert binary - it will always return the same result.
						$queries[ $col ] = $this->prepare( "CONVERT( CONVERT( %s USING $charset ) USING $connection_charset )", $value['value'] );
					}

					unset( $data[ $col ]['db'] );
				}
			}

			$sql = array();
			foreach ( $queries as $column => $query ) {
				if ( ! $query ) {
					continue;
				}

				$sql[] = $query . " AS x_$column";
			}

			$this->check_current_query = false;
			$row = $this->get_row( "SELECT " . implode( ', ', $sql ), ARRAY_A );
			if ( ! $row ) {
				return false;
			}

			foreach ( array_keys( $data ) as $column ) {
				if ( isset( $row["x_$column"] ) ) {
					$data[ $column ]['value'] = $row["x_$column"];
				}
			}
		}

		return $data;
	}
    /**
	 * Retrieves the character set for the given column.
    */
    public function get_col_charset( $table, $column ) {
		$tablekey = strtolower( $table );
		$columnkey = strtolower( $column );


		if ( empty( $this->table_charset[ $tablekey ] ) ) {
			// This primes column information for us.
			$table_charset = $this->get_table_charset( $table );
			if (!$table_charset) {
				return false;
			}
		}

		// If still no column information, return the table charset.
		if ( empty( $this->col_meta[ $tablekey ] ) ) {
			return $this->table_charset[ $tablekey ];
		}

		// If this column doesn't exist, return the table charset.
		if ( empty( $this->col_meta[ $tablekey ][ $columnkey ] ) ) {
			return $this->table_charset[ $tablekey ];
		}

		// Return false when it's not a string column.
		if ( empty( $this->col_meta[ $tablekey ][ $columnkey ]->Collation ) ) {
			return false;
		}

		list( $charset ) = explode( '_', $this->col_meta[ $tablekey ][ $columnkey ]->Collation );
		return $charset;
	}
    
    protected function get_table_charset( $table ) {
		$tablekey = strtolower( $table );


		if ( isset( $this->table_charset[ $tablekey ] ) ) {
			return $this->table_charset[ $tablekey ];
		}

		$charsets = $columns = array();

		$table_parts = explode( '.', $table );
		$table = '`' . implode( '`.`', $table_parts ) . '`';
		$results = $this->get_results( "SHOW FULL COLUMNS FROM $table" );
		if ( ! $results ) {
			return false;
		}

		foreach ( $results as $column ) {
			$columns[ strtolower( $column->Field ) ] = $column;
		}

		$this->col_meta[ $tablekey ] = $columns;

		foreach ( $columns as $column ) {
			if ( ! empty( $column->Collation ) ) {
				list( $charset ) = explode( '_', $column->Collation );

				// If the current connection can't support utf8mb4 characters, let's only send 3-byte utf8 characters.
				if ( 'utf8mb4' === $charset && ! $this->has_cap( 'utf8mb4' ) ) {
					$charset = 'utf8';
				}

				$charsets[ strtolower( $charset ) ] = true;
			}

			list( $type ) = explode( '(', $column->Type );

			// A binary/blob means the whole query gets treated like this.
			if ( in_array( strtoupper( $type ), array( 'BINARY', 'VARBINARY', 'TINYBLOB', 'MEDIUMBLOB', 'BLOB', 'LONGBLOB' ) ) ) {
				$this->table_charset[ $tablekey ] = 'binary';
				return 'binary';
			}
		}

		// utf8mb3 is an alias for utf8.
		if ( isset( $charsets['utf8mb3'] ) ) {
			$charsets['utf8'] = true;
			unset( $charsets['utf8mb3'] );
		}

		// Check if we have more than one charset in play.
		$count = count( $charsets );
		if ( 1 === $count ) {
			$charset = key( $charsets );
		} elseif ( 0 === $count ) {
			// No charsets, assume this table can store whatever.
			$charset = false;
		} else {
			// More than one charset. Remove latin1 if present and recalculate.
			unset( $charsets['latin1'] );
			$count = count( $charsets );
			if ( 1 === $count ) {
				// Only one charset (besides latin1).
				$charset = key( $charsets );
			} elseif ( 2 === $count && isset( $charsets['utf8'], $charsets['utf8mb4'] ) ) {
				// Two charsets, but they're utf8 and utf8mb4, use utf8.
				$charset = 'utf8';
			} else {
				// Two mixed character sets. ascii.
				$charset = 'ascii';
			}
		}

		$this->table_charset[ $tablekey ] = $charset;
		return $charset;
	}
    
    public function get_col_length( $table, $column ) {
		$tablekey = strtolower( $table );
		$columnkey = strtolower( $column );


		if ( empty( $this->col_meta[ $tablekey ] ) ) {
			// This primes column information for us.
			$table_charset = $this->get_table_charset( $table );
			if (!$table_charset ) {
                WS_Error::add_error('get_table_charset error');
				return false;
			}
		}

		if ( empty( $this->col_meta[ $tablekey ][ $columnkey ] ) ) {
			return false;
		}

		$typeinfo = explode( '(', $this->col_meta[ $tablekey ][ $columnkey ]->Type );

		$type = strtolower( $typeinfo[0] );
		if ( ! empty( $typeinfo[1] ) ) {
			$length = trim( $typeinfo[1], ')' );
		} else {
			$length = false;
		}

		switch( $type ) {
			case 'char':
			case 'varchar':
				return array(
					'type'   => 'char',
					'length' => (int) $length,
				);

			case 'binary':
			case 'varbinary':
				return array(
					'type'   => 'byte',
					'length' => (int) $length,
				);

			case 'tinyblob':
			case 'tinytext':
				return array(
					'type'   => 'byte',
					'length' => 255,        // 2^8 - 1
				);

			case 'blob':
			case 'text':
				return array(
					'type'   => 'byte',
					'length' => 65535,      // 2^16 - 1
				);

			case 'mediumblob':
			case 'mediumtext':
				return array(
					'type'   => 'byte',
					'length' => 16777215,   // 2^24 - 1
				);

			case 'longblob':
			case 'longtext':
				return array(
					'type'   => 'byte',
					'length' => 4294967295, // 2^32 - 1
				);

			default:
				return false;
		}
	}
    
    public function has_cap( $db_cap ) {
		$version = $this->db_version();

		switch ( strtolower( $db_cap ) ) {
			case 'collation' :    // @since 2.5.0
			case 'group_concat' : // @since 2.7.0
			case 'subqueries' :   // @since 2.7.0
				return version_compare( $version, '4.1', '>=' );
			case 'set_charset' :
				return version_compare( $version, '5.0.7', '>=' );
			case 'utf8mb4' :      // @since 4.1.0
				if ( version_compare( $version, '5.5.3', '<' ) ) {
					return false;
				}
				$client_version = mysqli_get_client_info();

				/*
				 * libmysql has supported utf8mb4 since 5.5.3, same as the MySQL server.
				 * mysqlnd has supported utf8mb4 since 5.0.9.
				 */
				if ( false !== strpos( $client_version, 'mysqlnd' ) ) {
					$client_version = preg_replace( '/^\D+([\d.]+).*/', '$1', $client_version );
					return version_compare( $client_version, '5.0.9', '>=' );
				} else {
					return version_compare( $client_version, '5.5.3', '>=' );
				}
			case 'utf8mb4_520' : // @since 4.6.0
				return version_compare( $version, '5.6', '>=' );
		}

		return false;
	}
    public function db_version() {
        $server_info = mysqli_get_server_info( $this->dbh );
		return preg_replace( '/[^0-9.].*/', '', $server_info );
	}

}
