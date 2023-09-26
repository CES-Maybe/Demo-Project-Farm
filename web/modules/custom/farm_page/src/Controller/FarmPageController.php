<?php

namespace Drupal\farm_page\Controller;

use Drupal\commerce_store\Entity\Store;
use Drupal\Core\Controller\ControllerBase;
use Drupal\farm_page\Services\FarmPageService;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $address = $store->get('address')->getValue()[0];
        $farm_data = [
          'name' => $store->getName(),
          'description' => $store->get('field_description')->getValue()[0]['value'],
          'farm_images' => reset($this->farmService->getImagesUrlFromArray($store_images_field)),
          'phone' => $store->get('field_store_phone')->getValue()[0]['value'],
          'email' => $store->getEmail(),
          'address' => $address['address_line1'] . ', ' . $address['administrative_area'] . ', ' . $address['country_code'],
        ];
        return [
          '#theme' => 'farm_homepage',
          '#data' => [
            'farm' => $farm_data,
            'is_exist' => $is_exist,
            'tags' => $tags,
            'featured_products' => $featured_products_info,
            'products_by_category' => $products_by_category,
            'uid' => $uid,
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
    return [
      '#theme' => 'list_farm',
      '#attached' => [
        'library' => ['farm_page/list-farm'],
      ],
    ];
  }

  /**
   * Build list farm.
   *
   * @return mixed
   *   Return Farmr and Farm information
   */
  public function listProductOfFarm($uid, $categories_id = NULL) {
    $store_id = $this->farmService->getStoreIdByUserId($uid);
    $store = Store::load($store_id);
    $products = $this->farmService->getProductByStore($uid, $categories_id);
    $categories = $this->farmService->getTermOfTaxonomy('categories');
    $address = $store->get('address')->getValue()[0];
    $farm = [
      'name' => $store->getName(),
      'description' => !empty($store->get('field_description')) ? $store->get('field_description')->getValue()[0]['value'] : "",
      'farm_images' => reset($this->farmService->getImagesUrlFromArray($store_images_field)),
      'phone' => !empty($store->get('field_store_phone')) ? $store->get('field_store_phone')->getValue()[0]['value'] : "",
      'email' => $store->getEmail(),
      'address' => $address['address_line1'] . ', ' . $address['administrative_area'] . ', ' . $address['country_code'],
    ];
    $current_categories = 'All categories';
    if ($categories_id == NULL) {
      $current_categories = 'All categories';
    }
    elseif (count($products) != 0) {
      $current_categories = $products[0]['category'][0]['name'];
    }
    return [
      '#theme' => 'farm_product',
      '#data' => [
        'products' => $products,
        'categories' => $categories,
        'farm' => $farm,
        'uid' => $uid,
        'current_categories' => $current_categories,
      ],
      '#attached' => [
        'library' => ['farm_page/farm-product'],
      ],
    ];
  }

  /**
   * Controller method to search for farms based on name.
   *
   * This method handles the AJAX request for searching farms by name and
   * returning a JSON response with the rendered farm page view.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the rendered farm page view.
   */
  public function searchFarm() {
    $name = \Drupal::request()->get('name');
    $stores = $this->farmService->searchFarmsByName($name);
    $template = [
      '#theme' => 'farm_page_views_farms',
      '#data' => [
        'stores' => $stores,
      ],
    ];
    $rendered_template = \Drupal::service('renderer')->render($template);
    return new JsonResponse(['content' => $rendered_template]);
  }

}
