<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;
use Destiny\Utils\Date;

class UserService extends Service {
	
	/**
	 * Singleton instance
	 *
	 * var UserService
	 */
	protected static $instance = null;

	/**
	 * Singleton instance
	 *
	 * @return UserService
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Return true if the $username has already been used, false otherwise.
	 *
	 * @param string $username
	 * @return boolean
	 */
	public function getIsUsernameTaken($username, $excludeUserId = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT COUNT(*) FROM `dfl_users` WHERE username = :username AND userId != :excludeUserId AND userStatus IN (\'Active\',\'Suspended\',\'Inactive\')' );
		$stmt->bindValue ( 'username', $username, \PDO::PARAM_STR );
		$stmt->bindValue ( 'excludeUserId', $excludeUserId, \PDO::PARAM_INT );
		$stmt->execute ();
		return ($stmt->fetchColumn () > 0) ? true : false;
	}

	/**
	 * Return true if the $email has already been used, false otherwise.
	 *
	 * @param string $username
	 * @param string $excludeUserId
	 * @return boolean
	 */
	public function getIsEmailTaken($email, $excludeUserId = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT COUNT(*) FROM `dfl_users` WHERE email = :email AND userId != :excludeUserId AND userStatus IN (\'Active\',\'Suspended\',\'Inactive\')' );
		$stmt->bindValue ( 'email', $email, \PDO::PARAM_STR );
		$stmt->bindValue ( 'excludeUserId', $excludeUserId, \PDO::PARAM_INT );
		$stmt->execute ();
		return ($stmt->fetchColumn () > 0) ? true : false;
	}

	/**
	 * Get the user record by userId
	 *
	 * @param string $userId
	 */
	public function getUserById($userId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT * FROM `dfl_users` WHERE userId = :userId LIMIT 0,1' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * Add a new user
	 *
	 * @param array $user
	 */
	public function addUser(array $user) {
		$conn = Application::instance ()->getConnection ();
		$user ['createdDate'] = Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' );
		$conn->insert ( 'dfl_users', $user );
		return $conn->lastInsertId ();
	}

	/**
	 * Update a user record
	 *
	 * @param int $userId
	 * @param array $user
	 */
	public function updateUser($userId, array $user) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_users', $user, array (
				'userId' => $userId 
		) );
	}

	/**
	 * Return a list of the users roles
	 *
	 * @param int $userId
	 * @return array
	 */
	public function getUserRoles($userId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT roleId FROM dfl_users_roles WHERE userId = :userId' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->execute ();
		$roles = array ();
		while ( $roleId = $stmt->fetchColumn () ) {
			$roles [] = intval ( $roleId );
		}
		return $roles;
	}

	/**
	 * Get the user record by external Id
	 *
	 * @param string $authId
	 * @param string $authProvider
	 */
	public function getUserByAuthId($authId, $authProvider) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT u.* FROM dfl_users_auth AS a 
			INNER JOIN dfl_users AS u ON (u.userId = a.userId)
			WHERE a.authId = :authId AND a.authProvider = :authProvider 
			LIMIT 0,1
		' );
		$stmt->bindValue ( 'authId', $authId, \PDO::PARAM_STR );
		$stmt->bindValue ( 'authProvider', $authProvider, \PDO::PARAM_STR );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * Return a users auth profile
	 *
	 * @param string $authId
	 * @param string $authProvider
	 * @return array
	 */
	public function getUserAuthProfile($userId, $authProvider) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT a.* FROM dfl_users_auth AS a 
			WHERE a.userId = :userId AND a.authProvider = :authProvider 
			LIMIT 0,1
		' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'authProvider', $authProvider, \PDO::PARAM_STR );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * Get all the profiles for a specific uer
	 *
	 * @param int $userId
	 * @return array
	 */
	public function getAuthProfilesByUserId($userId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT a.* FROM dfl_users_auth AS a 
			WHERE a.userId = :userId
		' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	/**
	 * Updates a users auth profile
	 *
	 * @param array $auth
	 */
	public function updateUserAuthProfile($userId, $authProvider, array $auth) {
		$conn = Application::instance ()->getConnection ();
		$auth ['modifiedDate'] = Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' );
		$conn->update ( 'dfl_users_auth', $auth, array (
				'userId' => $userId,
				'authProvider' => $authProvider 
		) );
	}

	/**
	 * Add a auth profile to a user
	 *
	 * @param array $auth
	 * @return void
	 */
	public function addUserAuthProfile(array $auth) {
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_users_auth', array (
				'userId' => $auth ['userId'],
				'authProvider' => $auth ['authProvider'],
				'authId' => $auth ['authId'],
				'authToken' => $auth ['authToken'],
				'createdDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ),
				'modifiedDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) 
		), array (
				\PDO::PARAM_INT,
				\PDO::PARAM_STR,
				\PDO::PARAM_INT,
				\PDO::PARAM_STR,
				\PDO::PARAM_STR,
				\PDO::PARAM_STR 
		) );
	}

	/**
	 * Remove auth profile
	 *
	 * @param int $userId
	 * @param string $authProvider
	 */
	public function removeAuthProfile($userId, $authProvider) {
		$conn = Application::instance ()->getConnection ();
		$conn->delete ( 'dfl_users_auth', array (
				'userId' => $userId,
				'authProvider' => $authProvider 
		) );
	}

}