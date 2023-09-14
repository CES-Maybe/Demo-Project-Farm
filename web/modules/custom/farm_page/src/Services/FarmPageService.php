<?php

namespace Drupal\farm_page\Services;

/**
 * Logic handler.
 */
class FarmPageService {

  /**
   * Get store id based on user id from URL .
   *
   * @param int $uid
   *   user id from URL.
   *
   * @return store_id
   *   Return Store id to access to Store infomation
   */
  public static function getStoreIdByUserId($uid) {
    $database = \Drupal::database();
    $query = $database->query("SELECT store_id FROM commerce_store_field_data WHERE uid =" . $uid);
    $result = $query->fetchAll();
    $store_id = $result[0]->store_id;
    return $store_id;
  }

}
