<?php 
require_once ('database/dbconn.php');
	class Users {
		private $id;
		private $name;
		private $email;
		private $role;
		private $pass;
		private $address;
		private $mobile;
		private $active;
		private $updatedBy;
		private $updatedOn;
		private $createdBy;
		private $createdOn;
		private $tableName = 'users';
		private $dbConn;

		function setId($id) { $this->id = $id; }
		function getId() { return $this->id; }
		function setName($name) { $this->name = $name; }
		function getName() { return $this->name; }
		function setPassword($pass) { $this->pass = $pass; }
		function getPassword() { return $this->pass; }
		function setEmail($email) { $this->email = $email; }
		function getEmail() { return $this->email; }
		function setRole($role) {$this->role = $role;}
		function getRole() {return $this->role;}
		function setAddress($address) { $this->address = $address; }
		function getAddress() { return $this->address; }
		function setMobile($mobile) { $this->mobile = $mobile; }
		function getMobile() { return $this->mobile; }
		function setActive($active) { $this->active = $active; }
		function getActive() { return $this->active; }
		function setUpdatedBy($updatedBy) { $this->updatedBy = $updatedBy; }
		function getUpdatedBy() { return $this->updatedBy; }
		function setUpdatedOn($updatedOn) { $this->updatedOn = $updatedOn; }
		function getUpdatedOn() { return $this->updatedOn; }
		function setCreatedBy($createdBy) { $this->createdBy = $createdBy; }
		function getCreatedBy() { return $this->createdBy; }
		function setCreatedOn($createdOn) { $this->createdOn = $createdOn; }
		function getCreatedOn() { return $this->createdOn; }

		public function __construct() {
			$db = new dbconn();
			$this->dbConn = $db->connect();
		}

		public function getAllUsers() {
			$stmt = $this->dbConn->prepare("SELECT * FROM " . $this->tableName);
			$stmt->execute();
			$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $users;
		}

		public function getUserDetailsById() {

			$sql = "SELECT 
						* FROM users WHERE id = :userId";

			$stmt = $this->dbConn->prepare($sql);
			$stmt->bindParam(':userId', $this->id);
			$stmt->execute();
			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			return $user;
		}

		public function insert() {
			
			$sql = 'INSERT INTO ' . $this->tableName . '(id, name, email, password, role, address, mobile, active, created_by, created_on) VALUES(null, :name, :email, :password, :role, :address, :mobile, :active, :createdBy, :createdOn)';

			$stmt = $this->dbConn->prepare($sql);
			$stmt->bindParam(':name', $this->name);
			$stmt->bindParam(':email', $this->email);
			$stmt->bindParam(':password', $this->pass);
			$stmt->bindParam(':role', $this->role);
			$stmt->bindParam(':address', $this->address);
			$stmt->bindParam(':mobile', $this->mobile);
			$stmt->bindParam(':active', $this->active);
			$stmt->bindParam(':createdBy', $this->createdBy);
			$stmt->bindParam(':createdOn', $this->createdOn);
			
			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}

		public function update() {
			
			$sql = "UPDATE $this->tableName SET";
			if( null != $this->getName()) {
				$sql .=	" name = '" . $this->getName() . "',";
			}

			if( null != $this->getAddress()) {
				$sql .=	" address = '" . $this->getAddress() . "',";
			}

			if( null != $this->getMobile()) {
				$sql .=	" mobile = " . $this->getMobile() . ",";
			}

			$sql .=	" updated_by = :updatedBy, 
					  updated_on = :updatedOn
					WHERE 
						id = :userId";

			$stmt = $this->dbConn->prepare($sql);
			$stmt->bindParam(':userId', $this->id);
			$stmt->bindParam(':updatedBy', $this->updatedBy);
			$stmt->bindParam(':updatedOn', $this->updatedOn);
			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}

		public function delete() {
			$stmt = $this->dbConn->prepare('DELETE FROM ' . $this->tableName . ' WHERE id = :userId');
			$stmt->bindParam(':userId', $this->id);
			
			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}
	}
 ?>