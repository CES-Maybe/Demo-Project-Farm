<?php

namespace Drupal\maybe_forms\Services;

use Drupal\commerce_store\Entity\Store;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class AccountService.
 *
 * The AccountService class provides methods for managing user accounts.
 *
 * @package Maybe
 */
class FarmService {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor for YourServiceClass.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

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
      'name' => $data['name'],
      'mail' => $data['mail'],
      'address' => $data['address'],
      'default_currency' => MAYBE_FORMS_CURRENCY,
    ]);
    $store->save();
  }

  /**
   * Get a farm by owner user ID.
   *
   * @param int $ownerUserId
   *   The user ID of the store owner.
   *
   * @return \Drupal\commerce_store\Entity\Store|null
   *   The store entity if found, or null if not found.
   */
  public function getFarmByOwnerId($ownerUserId) {
    $farm = $this->entityTypeManager
      ->getStorage('commerce_store')
      ->loadByProperties(['uid' => $ownerUserId]);
    return reset($farm);
  }

  /**
   * Update farm information.
   *
   * @param int $ownerId
   *   The owner ID of the store to update.
   * @param array $farmFields
   *   An associative array of store fields to update,
   *   where the keys are field names and the values
   *   are the new values for those fields.
   *
   * @return bool
   *   TRUE if the update was successful, FALSE otherwise.
   */
  public function updateFarmInfo($ownerId, array $farmFields) {
    try {
      $farmId = reset(\Drupal::entityQuery('commerce_store')->condition('uid', $ownerId)->execute());
      $store = \Drupal::entityTypeManager()->getStorage('commerce_store')->load($farmId);
      if ($store) {
        foreach ($farmFields as $field_name => $field_value) {
          $store->set($field_name, $field_value);
        }
        $store->save();
        return TRUE;
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('maybe_forms')->error('Error updating store: @error', ['@error' => $e->getMessage()]);
    }
    return FALSE;
  }

}
