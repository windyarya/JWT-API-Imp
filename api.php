<?php 
require_once ('rest.php'); //INSERTNYA AJA YANG BELUM BENER sama DELETE BELUM NYOBA
require_once ('users.php');
	class Api extends Rest {
		
		public function __construct() {
			parent::__construct();
		}

		public function generateToken() {
			$email = $this->validateParameter('email', $this->param['email'], STRING);
			$pass = $this->validateParameter('pass', $this->param['pass'], STRING);
			try {
				$stmt = $this->dbConn->prepare("SELECT * FROM users WHERE email = :email AND password = :pass");
				$stmt->bindParam(":email", $email);
				$stmt->bindParam(":pass", $pass);
				$stmt->execute();
				$user = $stmt->fetch(PDO::FETCH_ASSOC);
				if(!is_array($user)) {
					$this->returnResponse(INVALID_USER_PASS, "Email or Password is incorrect.");
				}

				if( $user['active'] == 0 ) {
					$this->returnResponse(USER_NOT_ACTIVE, "User is not activated. Please contact to admin.");
				}

				$paylod = [
					'iat' => time(),
					'iss' => 'localhost',
					'exp' => time() + (15*60),
					'userId' => $user['id']
				];

				$token = JWT::encode($paylod, SECRETE_KEY);
				
				$data = ['token' => $token];
				$this->returnResponse(SUCCESS_RESPONSE, $data);
			} catch (Exception $e) {
				$this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
			}
		}

		public function addUser() {
			if ($this->validateRole() === 'admin') {
				$name = $this->validateParameter('name', $this->param['name'], STRING, false);
				$email = $this->validateParameter('email', $this->param['email'], STRING, false);
				$password = $this->validateParameter('password', $this->param['password'], STRING, false);
				$role = $this->validateParameter('role', $this->param['role'], STRING, false);
				$addr = $this->validateParameter('addr', $this->param['addr'], STRING, false);
				$mobile = $this->validateParameter('mobile', $this->param['mobile'], STRING, false);
				$active = $this->validateParameter('active', $this->param['active'], INTEGER, false);
				$createdBy = $this->validateRole();

				$user = new Users;
				$user->setName($name);
				$user->setEmail($email);
				$user->setPassword($password);
				$user->setRole($role);
				$user->setAddress($addr);
				$user->setMobile($mobile);
				$user->setActive($active);
				$user->setCreatedBy($createdBy);
				$user->setCreatedOn(date('Y-m-d'));

				if(!$user->insert()) {
					$message = 'Failed to insert.';
				} else {
					$message = "Inserted successfully.";
				}
			
				$this->returnResponse(SUCCESS_RESPONSE, $message);
			} else {
				$this->returnResponse(ACCESS_NOT_GRANTED, 'Access not granted. Only admin can add a user.');
			}
		}

		public function getUserDetails() {
			if ($this->validateRole() == 'admin') {
				$userId = $this->validateParameter('userId', $this->param['userId'], INTEGER);

				$user = new Users;
				$user->setId($userId);
				$users = $user->getUserDetailsById();
				if(!is_array($users)) {
					$this->returnResponse(SUCCESS_RESPONSE, ['message' => 'User details not found.']);
				}

				$response['userId'] = $users['id'];
				$response['username'] = $users['name'];
				$response['email'] = $users['email'];
				$response['mobile'] = $users['mobile'];
				$response['address'] = $users['address'];
				$response['createdBy'] = $users['created_by'];
				$response['lastUpdatedBy'] = $users['updated_by'];
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			} else if ($this->validateRole() == 'user') {
				$idUser = $this->userId;
				$usrId = $this->validateParameter('userId', $this->param['userId'], INTEGER);
				if ($idUser == $usrId) {
					$user = new Users;
					$user->setId($idUser);
					$users = $user->getUserDetailsById();
					if(!is_array($users)) {
						$this->returnResponse(SUCCESS_RESPONSE, ['message' => 'User details not found.']);
					}

					$response['userId'] = $users['id'];
					$response['username'] = $users['name'];
					$response['email'] = $users['email'];
					$response['mobile'] = $users['mobile'];
					$response['address'] = $users['address'];
					$response['createdBy'] = $users['created_by'];
					$response['lastUpdatedBy'] = $users['updated_by'];
					$this->returnResponse(SUCCESS_RESPONSE, $response);
				} else {
					$this->returnResponse(ACCESS_NOT_GRANTED, 'Access not granted. Only admin can access this particular information.');
				}
			}
		}

		public function getAllUserDetails() {
			if ($this->validateRole() == 'admin') {

				$user = new Users;
				$users[] = $user->getAllUsers();
				if(!is_array($users)) {
					$this->returnResponse(SUCCESS_RESPONSE, ['message' => 'User details not found.']);
				}

				$response = $users;
				$this->returnResponse(SUCCESS_RESPONSE, $response);
			} else {
				$this->returnResponse(ACCESS_NOT_GRANTED, 'Access not granted. Only admin can access this particular information.');
			}
		}

		public function updateUser() {
			if ($this->validateRole() == 'admin') {
				$userId = $this->validateParameter('userId', $this->param['userId'], INTEGER);
				$name = $this->validateParameter('name', $this->param['name'], STRING, false);
				$addr = $this->validateParameter('addr', $this->param['addr'], STRING, false);
				$mobile = $this->validateParameter('mobile', $this->param['mobile'], INTEGER, false);
				$updatedBy = $this->validateRole();

				$user = new Users;
				$user->setId($userId);
				$user->setName($name);
				$user->setAddress($addr);
				$user->setMobile($mobile);
				$user->setUpdatedBy($updatedBy);
				$user->setUpdatedOn(date('Y-m-d'));

				if(!$user->update()) {
					$message = 'Failed to update.';
				} else {
					$message = "Updated successfully.";
				}

				$this->returnResponse(SUCCESS_RESPONSE, $message);
			} else if ($this->validateRole() == 'user') {
				$idUser = $this->userId;
				$usrId = $this->validateParameter('userId', $this->param['userId'], INTEGER);
				if ($usrId == $idUser) {
					$name = $this->validateParameter('name', $this->param['name'], STRING, false);
					$addr = $this->validateParameter('addr', $this->param['addr'], STRING, false);
					$mobile = $this->validateParameter('mobile', $this->param['mobile'], INTEGER, false);
					$updatedBy = $this->validateRole();

					$user = new Users;
					$user->setId($usrId);
					$user->setName($name);
					$user->setAddress($addr);
					$user->setMobile($mobile);
					$user->setUpdatedBy($updatedBy);
					$user->setUpdatedOn(date('Y-m-d'));

					if(!$user->update()) {
						$message = 'Failed to update.';
					} else {
						$message = "Updated successfully.";
					}

					$this->returnResponse(SUCCESS_RESPONSE, $message);
				} else {
					$this->returnResponse(ACCESS_NOT_GRANTED, 'Access not granted. Only admin can update a user.');
				}
			}
		}

		public function deleteUser() {
			if ($this->validateRole() == 'admin') {
				$usrId = $this->validateParameter('userId', $this->param['userId'], INTEGER);

				$user = new Users;
				$user->setId($usrId);

				if(!$user->delete()) {
					$message = 'Failed to delete.';
				} else {
					$message = "deleted successfully.";
				}

				$this->returnResponse(SUCCESS_RESPONSE, $message);
			} else {
				$this->returnResponse(ACCESS_NOT_GRANTED, 'Access not granted. Only admin can delete a user');
			}
		}
	}
	
 ?>