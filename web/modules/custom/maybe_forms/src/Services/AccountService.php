<?php

namespace Drupal\maybe_forms\Services;

use Drupal\user\Entity\User;

/**
 * Class AccountService.
 *
 * The AccountService class provides methods for managing user accounts.
 *
 * @package Maybe
 */
class AccountService {

  /**
   * Creates a new user account with role.
   *
   * @param array $data
   *   An associative array containing user account data:
   *   - 'name': The username for the new user.
   *   - 'mail': The email address for the new user.
   *   - 'pass': The password for the new user.
   *   - 'address': The address for the new user (optional).
   * @param string $role
   *   The role to assign to the new user.
   *
   * @return int|false
   *   The user ID of the created user on success, or FALSE on failure.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   Thrown if there was an issue saving the user entity.
   */
  public function createAccount($data, $role) {
    $user = User::create();
    $user->setUsername($data['name']);
    $user->setEmail($data['mail']);
    $user->setPassword($data['pass']);
    $user->enforceIsNew();
    $user->set('field_address', $data['field_address']);
    $user->set('status', $data['status']);
    $user->addRole($role);
    $user->save();
    return $user->id();
  }

  /**
   * Checks if a user with the given username exists in the system.
   *
   * @param string $username
   *   The username to check for existence.
   *
   * @return bool
   *   TRUE if a user with the specified username exists, FALSE otherwise.
   */
  public function checkUserExists($username) {
    $user = user_load_by_name($username);
    return (bool) $user;
  }

  /**
   * Retrieves a user entity by its user ID.
   *
   * @param int $uid
   *   The user ID of the user to retrieve.
   *
   * @return \Drupal\user\Entity\User|null
   *   The user entity if found, or NULL
   *   if the user does not exist or an error occurs.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   Thrown if there is an issue loading the user entity.
   */
  public function getUserById($uid) {
    try {
      $user = User::load($uid);
      return $user;
    }
    catch (\Exception $e) {
      \Drupal::logger('maybe_forms')->error('Error loading user: @error', ['@error' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * Update user information.
   *
   * @param int $uid
   *   The user ID of the user to update.
   * @param array $user_fields
   *   An associative array of user fields to update,
   *   where the keys are field names and the values
   *   are the new values for those fields.
   *
   * @return bool
   *   TRUE if the update was successful, FALSE otherwise.
   */
  public function updateUserInfo($uid, array $user_fields) {
    try {
      $user = User::load($uid);
      if ($user) {
        foreach ($user_fields as $field_name => $field_value) {
          $user->set($field_name, $field_value);
        }
        $user->save();
        return TRUE;
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('maybe_form')->error('Error updating user: @error', ['@error' => $e->getMessage()]);
    }
    return FALSE;
  }

}
