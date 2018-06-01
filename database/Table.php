<?php 
class Table
{
	public $table;
	public $db;
	
    public function __construct($table_name)
    {
		$this->table = $table_name;
	}
	
    public function columns()
    {
		$table = $this->table;
		$columns = [];
		
		$stmt = " DESCRIBE $table";
		$result = $this->db->query($stmt);
		if (!$result) {
			return $this->db->error();
		} else {
            while ($row = $this->db->fetch($result)) {
                $columns[] = $row;
            }
        }
		
		return $columns;
    }
    
    public function add_column($col_name)
    {
		$table = $this->table;
		
		$stmt = "ALTER TABLE `".$table."` ADD `".$col_name."` VARCHAR(255) NOT NULL ;";
		$result = $this->db->query($stmt);
		
		return $result;
	}
    public function add($rows = null)
    {
		$table = $this->table;
		$stmt = "INSERT INTO $table";
		if (isset($rows) && gettype($rows) == 'array' && isset($rows[0]) && gettype($rows[0]) == 'array') {
			$cols = null;
			foreach ($rows as $row) {
				if ($cols != null) {
					$tmp_cols = array_keys($row);
					if (($cl=count($tmp_cols)) != count($cols)) {
						echo "Nr i kolonave nuk eshte i njejte";
						return false;
					}
					for ($i = 0; $i < $cl; $i++) {
						if ($tmp_cols[$i] != $cols[$i]) {
							echo "Nr i kolonave nuk eshte i njejte";
							return false;
						}
					}
					$cols = $tmp_cols;
				} else {
					$cols = array_keys($row);
				}
			}
			$cols = implode(',',$cols);
			
			$values = [];
			foreach ($rows as $row) {
				$values[] = "('" . implode("', '", array_values($row)) . "')";
			}
			$stmt .= "(".$cols.")"." VALUES ".implode(',',$values) . " ; ";
		} else {
			$cols = array_keys($rows);
			$cols = implode(',', $cols);
			
			$values = array_values($rows);
			$values = implode("','",$values);
			$stmt .= "(".$cols.")"." VALUES ('".$values."') ;";
		}
		$result = $this->db->query($stmt);
		
		return $result;
    }
    
    public function update($row, $where)
    {
		$table = $this->table;
		$stmt = "UPDATE $table SET ";
		foreach ($row as $col_name => $value_to_add) {
			$stmt .=  " ".$col_name." = ".$col_name. " + ".$value_to_add ." ,";
		}
		$stmt = substr($stmt, 0, strlen($stmt)-1);
		$stmt .= "WHERE 1 ";
		foreach ($where as $col_name => $value) {
			$stmt .= " AND ".$col_name." = '".$value."'  ";
		}
		$stmt .= " ; ";

		$result = $this->db->query($stmt);
		return $result;
    }
    
    public function edit($row, $where)
    {
		$table = $this->table;
		$stmt = "UPDATE $table SET ";
		foreach ($row as $col_name => $value_to_add) {
			$stmt .= " ".$col_name." = '".$value_to_add ."' ,";
		}
		$stmt = substr($stmt,0,strlen($stmt)-1);
		$stmt .= "WHERE 1  ";
		foreach ($where as $col_name => $value) {
			$stmt .= " AND ".$col_name." = '".$value."'  ";
		}
		$result = $this->db->query($stmt);
		if (!$result) {
			echo $this->db->error();
		}
		return $result;
    }
    
    public function delete($where, $condition = "AND") 
    {
		$condition = "AND";
		$table = $this->table;
		$stmt = "DELETE FROM $table WHERE 1";
		$have_condition = false;
		foreach ($where as $col_name => $value) {
			$have_condition = true;
			if (is_array($value)) {
				$stmt .= " $condition ".$col_name." IN ('".implode("','",$value)."')  ";
			} else {
				$stmt .= " $condition ".$col_name." = '".$value."'  ";
			}
		}
		$stmt .= " ;";
		if (!$have_condition) {
			echo "nuk eshte percaktuar kushti";
			return false;
		}
		$result = $this->db->query($stmt);
		if (!$result) {
			echo $stmt;
			echo $this->db->error();
		}
		return $result;
    }
    
    public function select($cols="*", $where=array())
    {
		$table = $this->table;
		$stmt = "SELECT ";
		if ($cols == "*") {
			$stmt .= " ".$cols." ";
		} else {
			foreach ($cols as $col_name) {
				$stmt .= " ".$col_name.", ";
			}
			$stmt = substr($stmt, 0, strlen($stmt)-2);
		}
		$stmt .= " FROM $table WHERE 1 ";
		
		$condition = "AND";
		foreach ($where as $col_name => $value) {
			$stmt .= " $condition ".$col_name." = '".$value."'  ";
		}
		$result = $this->db->query($stmt);
		if (!$result) {
			echo $this->db->error();
			return false;
		} else {
			$rows = [];
			while ($row = $this->db->fetch($result)) {
				$rows[] = $row;
			}
		}
		return $rows;
	}
}