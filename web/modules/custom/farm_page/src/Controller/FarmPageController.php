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

}
