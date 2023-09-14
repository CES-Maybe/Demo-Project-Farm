<?php

namespace Drupal\farm_page\Controller;

use Drupal\Core\Controller\ControllerBase;

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
  public function myFarm() {

    $uid = \Drupal::currentUser()->id();
    return [
      '#theme' => 'farm_homepage',
      "#uid" => $uid,
      '#attached' => [
        'library' => [],
      ],
    ];
  }

}
