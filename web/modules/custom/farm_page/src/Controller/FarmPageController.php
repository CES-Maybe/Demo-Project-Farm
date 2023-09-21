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
        $featured_products = $this->farmService->getFeaturedProducts((int) $store_id);
        $tags = NULL;
        $categories_id = NULL;
        $store_images_field = NULL;
        $products_by_category = NULL;
        $featured_products_info = NULL;
        if (isset($store)) {
          $tags = $this->farmService->getTaxonomyTerm($store->get('field_tags')->getValue());
          $categories_id = $store->get('field_products_by_category')->getValue();
          $store_images_field = $store->get('field_farm_image')->getValue();
          $products_by_category = $this->farmService->getProductsByCategory($store_id, $categories_id);
        }
        if (isset($featured_products)) {
          $featured_products_info = $this->farmService->getProductsInfomation($featured_products);
        }
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
            'tags' => $tags,
            'featured_products' => $featured_products_info,
            'products_by_category' => $products_by_category,
          ],
          '#attached' => [
            'library' => ['farm_page/farm_page'],
          ],
        ];
      }
    }
  }

  /**
   * Build list farm.
   *
   * @return mixed
   *   Return Farmr and Farm information
   */
  public function listFarm() {
    $stores = $this->farmService->getAllStores();
    return [
      '#theme' => 'list_farm',
      '#data' => [
        'stores' => $stores,
      ],
      '#attached' => [
        'library' => ['farm_page/list-farm'],
      ],
    ];
  }

}
