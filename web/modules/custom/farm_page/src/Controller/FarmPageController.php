<?php

namespace Drupal\farm_page\Controller;

use Drupal\commerce_store\Entity\Store;
use Drupal\Core\Controller\ControllerBase;
use Drupal\farm_page\Services\FarmPageService;
use Drupal\user\Entity\User;

/**
 * Returns responses for Farm Page routes.
 */
class FarmPageController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

  /**
   * Build the farm homepage for farmer.
   *
   * @return mixed
   *   Return Farmer and Farm information according to current user Id
   */
  public function myFarm($uid) {
    $isExist = FALSE;
    $user = User::load($uid);
    if ($user == NULL) {
      $message = '404 user not found';
      return [
        '#theme' => 'farm_homepage',
        '#isExist' => $isExist,
        "#message" => $message,
        '#attached' => [
          'library' => [],
        ],
      ];
    }
    elseif ($user) {
      $store_id = FarmPageService::getStoreIdByUserId($uid);
      if ($store_id == NULL) {
        $message = '404 store not found';
        return [
          '#theme' => 'farm_homepage',
          '#isExist' => $isExist,
          "#message" => $message,
          '#attached' => [
            'library' => [],
          ],
        ];
      }
      else {
        $store = Store::load($store_id);
        $isExist = TRUE;
        return [
          '#theme' => 'farm_homepage',
          '#isExist' => $isExist,
          "#store" => $store,
          '#attached' => [
            'library' => [],
          ],
        ];
      }
    }
  }

}
