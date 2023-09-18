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
  protected $farmService;

  /**
   * Constructs a new instance of the FarmPageService class.
   *
   * @param \Drupal\farm_page\Services\FarmPageService $farmService
   *   to use method from FarmPageService.
   */
  public function __construct(FarmPageService $farmService) {
    $this->farmService = $farmService;
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
        '#data' => [
          'is_exist' => $is_exist,
          'message' => $message,
        ],
        '#attached' => [
          'library' => ['farm_page/farm_page'],
        ],
      ];
    }
    elseif ($user) {
      $store_id = $this->farmService->getStoreIdByUserId($uid);
      if ($store_id == NULL) {
        $message = '404 store not found';
        return [
          '#theme' => 'farm_homepage',
          '#data' => [
            'is_exist' => $is_exist,
            'message' => $message,
          ],
          '#attached' => [
            'library' => ['farm_page/farm_page'],
          ],
        ];
      }
      else {
        $store = Store::load($store_id);
        $products = $this->farmService->getFeatureProducts((int) $store_id);
        $tags = $this->farmService->getTaxonomyTerm($store->get('field_tags')->getValue());
        $store_images_field = $store->get('field_farm_image')->getValue();
        $store_images_url = $this->farmService->getImagesUrlFromArray($store_images_field);
        $full_information_products = $this->farmService->getProductsInfomation($products);
        $is_exist = TRUE;
        $farm_data = [
          'name' => $store->getName(),
          'description' => $store->get('field_description')->getValue()[0]['value'],
          'farm_images' => reset($this->farmService->getImagesUrlFromArray($store_images_field)),
        ];
        return [
          '#theme' => 'farm_homepage',
          '#data' => [
            'farm' => $farm_data,
            'is_exist' => $is_exist,
            'store' => $store,
            'store_images' => $store_images_url,
            'tags' => $tags,
            'products' => $full_information_products,
          ],
          '#attached' => [
            'library' => ['farm_page/farm_page'],
          ],
        ];
      }
    }
  }

}
