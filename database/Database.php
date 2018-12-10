<?php 
class Database 
{
	public $db;
	public $host;
	public $user;
	public $pass;
	public $connection;
	
	public function __construct($h,$u,$p,$d)
	{
		$this->db = $d;
		$this->host = $h;
		$this->pass = $p;
		$this->user = $u;
		$this->stmt = null;
		$this->resource = null;

		try {
		    $this->connection = new PDO('mysql:host='.$h.';dbname='.$d.';charset=utf8', $u, $p);
		} catch (PDOException $e) {
		    print "Error!!!: " . $e->getMessage() . "<br/>";
		    die();
		}
		$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function query($sql, $params = null)
	{
	    try {
	        if ($this->isSelect($sql)) {
                $stmt = $this->connection->prepare($sql, [
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                ]);
                if ($params) {
                    $stmt->execute($params);
                } else {
                    $stmt->execute();
                }
                $this->stmt = $stmt;

            } else {
                $stmt = $this->exec($sql, $params);
                return ($stmt || $stmt == 0);
            }
	    } catch (PDOException $e) {
	        echo "Syntax Error:: ".$e->getMessage();
	        die();
	    }

		if ($stmt) {
			return $stmt;
		}else{
			return false;
		}
	}

	public function exec($sql, $params = null)
	{
	    try {
	        if ($params) {
	            $result = $this->connection->exec($sql, $params);
	        } else {
	            $result = $this->connection->exec($sql);
	        }
	    } catch (PDOException $e) {
	        echo "Syntax Error:: ".$e->getMessage();
	        die();
	    }
	    return $result;
	}

	public function fetch($resource)
	{
		if (! $resource) {
			return false;
		}
		$this->resource = $resource;
		return $resource->fetch(PDO::FETCH_ASSOC);
	}

	public function count($resource)
	{
		if (! $resource) {
			return false;
		}
		return $resource->rowCount();
	}

	public function last_id()
	{
		$stmt = "SELECT LAST_INSERT_ID() AS id";
		$result = $this->query($stmt);
		if ($result) {
		    while ($row = $this->fetch($result)) {
				$id = $row["id"];
			}
			if (isset($id)) {
			    return $id;
			}
			return false;
		} else {
			return false;
		}
	}

	public function error()
	{
	    return $this->connection->errorInfo();
	}
	
	public function table($table)
	{
		require_once(BASEPATH.'database/Table.php');
		$t = new Table($table);
		$t->db = $this;
		return $t;
	}
	
	public function isSelect($sqlString)
	{
	    $sqlString = trim($sqlString);
	    $sqlString = strtoupper($sqlString);
	    if ($sqlString != '' && (strpos($sqlString, "SELECT") === 0)) {
	        return true;
	    }
	    return false;
	}
}