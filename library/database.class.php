<?php
/*
 * @Author Rory Standley <rorystandley@gmail.com> https://github.com/rorystandley/MySQL-CRUD-PHP-OOP
 * @Version 1.3
 * @Package Database
 * For now this will be adapted from the Github repo until I get a chance to give it its own correct DB class
 */
class Database{	
	/*
	 * Extra variables that are required by other function such as boolean con variable
	 */
	private $con = false; // Check to see if the connection is active
	private $result = array(); // Any results from a query will be stored here
    private $myQuery = "";// used for debugging process with SQL return
    private $numResults = "";// used for returning the number of rows
	
	// Function to make connection to database
	public function connect($db_host,$db_user,$db_pass,$db_name){
		if(!$this->con){
			$myconn = @mysql_connect($db_host,$db_user,$db_pass);  // mysql_connect() with variables defined at the start of Database class
            if($myconn){
            	$seldb = @mysql_select_db($db_name,$myconn); // Credentials have been pass through mysql_connect() now select the database
                if($seldb){
                	$this->con = true;
                    return true;  // Connection has been made return TRUE
                }else{
                	array_push($this->result,mysql_error()); 
                    return false;  // Problem selecting database return FALSE
                }  
            }else{
            	array_push($this->result,mysql_error());
                return false; // Problem connecting return FALSE
            }  
        }else{  
            return true; // Connection has already been made return TRUE 
        }  	
	}
	
	// Function to disconnect from the database
    public function disconnect(){
    	// If there is a connection to the database
    	if($this->con){
    		// We have found a connection, try to close it
    		if(@mysql_close()){
    			// We have successfully closed the connection, set the connection variable to false
    			$this->con = false;
				// Return true tjat we have closed the connection
				return true;
			}else{
				// We could not close the connection, return false
				return false;
			}
		}
    }

    public function selectAll() {
        $query = 'select * from `'.$this->_table.'`';
        $this->myQuery = $query;
        return $this->sql($query,"select");
    }

    public function select($id) {
        $query = 'select * from `'.$this->_table.'` where `id` = \''.mysql_real_escape_string($id).'\'';
        return $this->sql($query,"select");    
    }
	
	public function sql($sql,$type=""){
		$query = @mysql_query($sql);
        $this->myQuery = $sql; // Pass back the SQL
		if($query){
            if($type == "select"){
                $this->numResults = mysql_num_rows($query);
                // Loop through the query results by the number of rows returned
                for($i = 0; $i < $this->numResults; $i++){
                    $r = mysql_fetch_array($query);
                    $key = array_keys($r);
                    for($x = 0; $x < count($key); $x++){
                        // Sanitizes keys so only alphavalues are allowed
                        if(!is_int($key[$x])){
                            if(mysql_num_rows($query) >= 1){
                                $this->result[$i][$key[$x]] = $r[$key[$x]];
                            }else{
                                $this->result = null;
                            }
                        }
                    }
                }
                return $this->result; // Query was successful
            }elseif($type == "insert"){
                return true;
            }elseif($type == "delete"){
                return true;
            }else{
                return false;
            }
            
		}else{
			array_push($this->result,mysql_error());
			return $this->getResult(); // No rows where returned
		}
	}
	
	// Private function to check if table exists for use with queries
	private function tableExists($table){
		$tablesInDb = @mysql_query('SHOW TABLES FROM '.$this->db_name.' LIKE "'.$table.'"');
        if($tablesInDb){
        	if(mysql_num_rows($tablesInDb)==1){
                return true; // The table exists
            }else{
            	array_push($this->result,$table." does not exist in this database");
                return false; // The table does not exist
            }
        }
    }
	
	// Public function to return the data to the user
    public function getResult(){
        $val = $this->result;
        $this->result = array();
        return $val;
    }

    //Pass the SQL back for debugging
    public function getSql(){
        $val = $this->myQuery;
        $this->myQuery = array();
        return $val;
    }

    //Pass the number of rows back
    public function numRows(){
        $val = $this->numResults;
        $this->numResults = array();
        return $val;
    }
} 
