<?php
/*
 *
 * Model Class for table kategori
 * generated on 28 October 2014 14:25:53
 *
 *
 * This file is auto generated by Akane Console Tools
 * you can customize it to your need
 * for more information
 * type command "php console" from Akane directory on Terminal console
 * 
 */
class kategori
{
	var $main;
	
	function __construct() {
		$this->main = get_instance();
	}
	
	function single($id){
		return $this->main->db->get_data('kategori', '', "id='$id'");
	}
	
	function name($id){
		$data = $this->main->db->get_data('kategori', '', "id='$id'");
		return $data[0]['name']; # change this to field that contain name
	}

	function all($limit='',$keyword=''){
		$where = '';
		if ($keyword!=''){
			# please change this searchable column name to your need
			$searchable = array('id','name');

			if (count($searchable) > 1){
				$wheres = array();
				foreach ($searchable as $field){
					$wheres[] = $field." LIKE '%".$keyword."%'";
				}
				$where = implode(' OR ',$wheres);
			} else {
				$where = $searchable[0]." LIKE '%".$keyword."%'";
			}
		}
		$data = $this->main->db->get_data('kategori', '', $where, '', $limit);
		return $data;
	}	
}