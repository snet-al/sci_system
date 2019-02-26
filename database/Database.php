<?php
use Illuminate\Database\Capsule\Manager as Capsule;

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


        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $this->host,
            'database'  => $this->db,
            'username'  => $this->user,
            'password'  => $this->pass,
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);
        $capsule->setAsGlobal();
	$capsule->bootEloquent();

    }

	public function table($tableName)
    {
	    return Capsule::table($tableName);
    }
}
