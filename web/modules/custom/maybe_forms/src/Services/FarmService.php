<?php

namespace Drupal\maybe_forms\Services;

use Drupal\commerce_store\Entity\Store;

/**
 * Class AccountService.
 *
 * The AccountService class provides methods for managing user accounts.
 *
 * @package Maybe
 */
class FarmService {

  /**
   * Creates a new store entity with the provided data.
   *
   * @param array $data
   *   An associative array containing store data:
   *   - 'owner_id': The user ID of the store owner.
   *   - 'name_store': The name of the store.
   *   - 'mail': The email address associated with the store.
   *   - 'address': The address of the store.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   Thrown if there is an issue saving the store entity.
   */
  public function createStore($data) {
    $store = Store::create([
      'type' => MAYBE_FORMS_STORE_TYPE,
      'uid' => $data['owner_id'],
      'name' => $data['name_store'],
      'mail' => $data['mail'],
      'address' => $data['address'],
      'default_currency' => MAYBE_FORMS_CURRENCY,
    ]);
    $store->save();
  }

}
