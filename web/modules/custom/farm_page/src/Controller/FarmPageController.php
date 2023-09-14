<?php

namespace Drupal\farm_page\Controller;

use Drupal\commerce_store\Entity\Store;
use Drupal\Core\Controller\ControllerBase;
use Drupal\farm_page\Services\FarmPageService;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Farm Page routes.
 */
class FarmPageController extends ControllerBase {
  /**
   * Farm Page Service.
   *
   * @var \Drupal\farm_page\Services\FarmPageService
   */
  protected $farmPageService;

  /**
   * Constructs a new instance of the FarmPageService class.
   *
   * @param \Drupal\farm_page\Services\FarmPageService $farmPageService
   *   to use method from FarmPageService.
   */
  public function __construct(FarmPageService $farmPageService) {
    $this->farmPageService = $farmPageService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('farm_page.store'),
      );
  }

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
    $is_exist = FALSE;
    $user = User::load($uid);
    if ($user == NULL) {
      $message = '404 user not found';
      return [
        '#theme' => 'farm_homepage',
        '#is_exist' => $is_exist,
        "#message" => $message,
        '#attached' => [
          'library' => [],
        ],
      ];
    }
    elseif ($user) {
      $store_id = $this->farmPageService->getStoreIdByUserId($uid);
      if ($store_id == NULL) {
        $message = '404 store not found';
        return [
          '#theme' => 'farm_homepage',
          '#is_exist' => $is_exist,
          "#message" => $message,
          '#attached' => [
            'library' => [],
          ],
        ];
      }
      else {
        $store = Store::load($store_id);
        $products_array = $this->farmPageService->getFeatureProducts((int) $store_id);
        $store_images_field = $store->get('field_farm_image')->getValue();
        $store_images_url = $this->farmPageService->getImagesUrlFromArray($store_images_field);
        $products = $this->farmPageService->getProductsInfomation($products_array);
        $is_exist = TRUE;
        return [
          '#theme' => 'farm_homepage',
          '#is_exist' => $is_exist,
          '#store' => $store,
          '#store_images' => $store_images_url,
          '#products' => $products,
          '#attached' => [
            'library' => [],
          ],
        ];
      }
    }
  }

}
