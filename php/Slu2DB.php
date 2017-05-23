<?php
/**
 * Class for Attribute-Value & Utterance label to SQL Query Conversion
 * 
 * @author estepanov
 */
class Slu2DB {
	
	/**
	 * Map SLU concepts & utterance classes to DB columns
	 * 
	 * EXTEND!
	 */
	private $mapping = array(
		'actor'          => 'actors',
		'actor.name'     => 'actors',
		'movie'          => 'title',
		'movie.name'     => 'title',
		'director'       => 'director',
		'director.name'  => 'director',
		'character'      => 'character',
		'character.name' => 'character',
	
	);
	
	/**
	 * Returns db column w.r.t. $str
	 */
	private function db_mapping($str) {
		return $this->mapping[$str];
	}
	
	/**
	 * Meta function to
	 * - map slu concepts to DB
	 * - map utterance classifier class to db
	 * - construct sql query
	 */
	public function slu2sql($concepts, $class) {

		$db_class    = $this->db_mapping($class);
		
		$db_concepts = array();
		foreach ($concepts as $attr => $val) {
			$db_concepts[$this->db_mapping($attr)] = $val;
		}
		
				
		// construct SQL query
		$query  = 'SELECT ';
		$query .= $db_class;
		$query .= ' FROM movie WHERE ';
		
		$tmp = array();
		foreach ($db_concepts as $attr => $val) {
			//$tmp[] = $attr . ' LIKE "%' . $val . '%"';
			$tmp[] = $attr . ' LIKE "' . $val . '%"';
		}
		$query .= implode(' AND ', $tmp);
		$query .= ';';
		
		return $query;
	}
}
