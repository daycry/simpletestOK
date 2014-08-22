
<?php
Class users_model extends CI_Model
{
	public function __construct()
	{
		$this->load->database();
	}
	
	 function login($username, $password)
	 {
	   $this->db->select('id, username, password');
	   $this->db->from('sl_users');
	   $this->db->where('username', $username);
	   $this->db->where('password', MD5($password));
	   $this->db->limit(1);

	   $query = $this->db->get();

	   if($query->num_rows() == 1)
	   {
		 return $query->result();
	   }
	   else
	   {
		 return false;
	   }
	 }
	 
	 function add_user($datos){
		 $this->db->insert('sl_users', $datos);
		 $id = $this->db->insert_id();
		 return $id;
	 }
	 
	 function get_user( $id, $campo){
		 $this->db->where($campo, $id);
		 $query = $this->db->get('sl_users');
		 return $query->row_array();
	 }
	 
	 function get_users(){
		 $query = $this->db->get('sl_users');
		 return $query->result_array();
	 }
	 
	 function edit_user($id, $datos){
		 $this->db->where('id', $id);
		 $this->db->update('sl_users', $datos);
		 return true;
	 }
}
?>

