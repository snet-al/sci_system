<?php 
class SCI_Escape
{
	static public function escape ($input, $owner)
	{
	    //TODO: return $input; because json decode switch content-type if text || html escape elseif json try to decode
	    return $input;
	    return trim($owner->db->connection->quote($input),"'");
	    //TODO: return addslashes($input); this should be used if quote does not function
	}
}