<?php

namespace Drupal\farm_page\Services;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\media\Entity\Media;
use Drupal\taxonomy\Entity\Term;

/**
 * Logic handler.
 */
class FarmPageService {

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
   * Get store id based on user id from URL .
   *
   * @param int $uid
   *   User id from URL.
   *
   * @return store_id
   *   Return Store id to access to Store infomation
   */
  public function getStoreIdByUserId($uid) {
    $database = \Drupal::database();
    $query = $database->query("SELECT store_id FROM commerce_store_field_data WHERE uid =" . $uid);
    $result = $query->fetchAll();
    $store_id = $result[0]->store_id;
    return $store_id;
  }

  /**
   * Get term by tid .
   *
   * @param mixed $terms
   *   Terms from Objects.
   *
   * @return terms_list
   *   Return a list of term with id and name
   */
  public function getTaxonomyTerm($terms) {
    $terms_list = [];
    foreach ($terms as $term) {
      $term_infomation = Term::load($term['target_id']);
      $terms_item = [];
      $terms_item['id'] = $term['target_id'];
      $terms_item['name'] = $term_infomation->getName();
      array_push($terms_list, $terms_item);
    }
    return $terms_list;
  }

  /**
   * Get feature product based on store id .
   *
   * @param int $store_id
   *   Store id from function getStoreIdByUserId.
   *
   * @return featured_products_id
   *   Return array of featured products id to display on UI
   */
  public function getFeaturedProducts($store_id) {
    $database = \Drupal::database();
    $query = $database->select('commerce_product', 'cp');
    $query->addField('cp', 'product_id', 'pid');
    $query->join('commerce_product__field_isfeature', 'fif', 'cp.product_id = fif.entity_id');
    $query->join('commerce_product__stores', 'cps', 'cp.product_id = cps.entity_id');
    $query->join('commerce_store', 'cs', 'cps.stores_target_id = cs.store_id');
    $query->condition('fif.field_isfeature_value', 1, '=');
    $query->condition('cs.store_id', $store_id, '=');
    $results = $query->execute()->fetchAll();
    $featured_products_id = [];
    foreach ($results as $product) {
      $featuredProduct = Product::load($product->pid);
      array_push($featured_products_id, $featuredProduct);
    }
    return $featured_products_id;
  }

  /**
   * Get Image URL based on store image field .
   *
   * @param array $store_images_field
   *   Images id from Objects $store_images_field.
   *
   * @return images_url
   *   Return array of images URL to display on UI
   */
  public function getImagesUrlFromArray($store_images_field) {
    $images_url = [];
    foreach ($store_images_field as $image) {
      $media = Media::load($image['target_id']);
      $fid = $media->getSource()->getSourceFieldValue($media);
      $file = File::load($fid);
      $url = $file->createFileUrl();
      array_push($images_url, $url);
    }
    return $images_url;
  }

  /**
   * Create an array of product to display on UI .
   *
   * @param array $products
   *   An product array.
   *
   * @return products_list
   *   Return array of product to display on UI
   */
  public function getProductsInfomation($products) {
    $products_list = [];
    foreach ($products as $product) {
      $product_info = [];
      // Get Id.
      $product_id = $product->get('product_id')->getValue();
      $product_info['product_id'] = $product_id[0]['value'];
      // Get Names.
      $product_name = $product->get('title')->getValue();
      $product_info['product_name'] = $product_name[0]['value'];
      // Get Images.
      $product_images_field = $product->get('field_images')->getValue();
      // Check If Featured Product.
      $product_is_featured = NULL;
      if (!empty($product->get('field_isfeature')->getValue())) {
        $product_is_featured = $product->get('field_isfeature')->getValue()[0]['value'];
      }
      if ($product_is_featured == 1) {
        $style = 'featured_product';
        $product_images_url = $this->getImagesUrlWithStyle($product_images_field, $style);
      }
      else {
        $product_images_url = $this->getImagesUrlFromArray($product_images_field);
      }
      $product_info['product_images'] = $product_images_url;
      // Get Created Date.
      $product_created = $product->get('created')->getValue();
      $product_info['product_created'] = $product_created[0]['value'];
      // Get Description.
      $product_description = $product->get('body')->getValue();
      $product_info['description'] = $this->truncateString($product_description[0]['value'], 55);
      // Get Category.
      $terms = $product->get('field_category')->getValue();
      $product_info['category'] = $this->getTaxonomyTerm($terms);
      // Get Variations.
      $variations = $product->get('variations')->getValue();
      if ($variations != NULL) {
        $product_price_list = [];
        $product_sku_list = [];
        foreach ($variations as $variation) {
          $product_variation = ProductVariation::load($variation['target_id']);
          $price = $product_variation->get('price')->getValue();
          $sku = $product_variation->get('sku')->getValue();
          array_push($product_price_list, $price[0]);
          array_push($product_sku_list, $sku[0]);
          $product_info['price'] = $product_price_list;
          $product_info['sku_list'] = $product_sku_list;
        }
      }
      array_push($products_list, $product_info);
    }
    return $products_list;
  }

  /**
   * Get products based on store id and categories id .
   *
   * @param int $store_id
   *   Store id from function getStoreIdByUserId.
   * @param iterable $categories_id
   *   Categories id from Store.
   *
   * @return products
   *   Return array of products
   */
  public function getProductsByCategory($store_id, $categories_id) {
    $products_by_category = [];
    foreach ($categories_id as $category_id) {
      $entity_type_manager = \Drupal::entityTypeManager();
      $entity_type = 'commerce_product';
      $query = $entity_type_manager->getStorage($entity_type)->getQuery();
      $query->condition('type', 'default');
      $query->condition('field_category', $category_id['target_id']);
      $query->condition('stores', $store_id);
      $result = $query->execute();
      $products = $entity_type_manager->getStorage($entity_type)->loadMultiple($result);
      $product_info = $this->getProductsInfomation($products);
      if ($product_info != NULL) {
        array_push($products_by_category, $product_info);
      }
    }
    return $products_by_category;
  }

  /**
   * Get all stores.
   *
   * @return \Drupal\commerce_store\Entity\StoreInterface[]
   *   An array of store entities.
   */
  public function getAllStores() {
    $storeStorage = $this->entityTypeManager->getStorage('commerce_store');
    $stores = [];
    foreach ($storeStorage->loadMultiple() as $store) {
      $storeData = $this->getStoreData($store);
      $stores[] = $storeData;
    }
    return $stores;
  }

  /**
   * Retrieves data for a Drupal Commerce store entity.
   *
   * This method extracts and organizes relevant information from a store entity
   * to create a data array containing the store's name, image URL, headline,
   * and owner's user ID.
   *
   * @param \Drupal\commerce_store\Entity\StoreInterface $store
   *   The Drupal Commerce store entity for which to retrieve data.
   *
   * @return array
   *   An associative array containing the following keys:
   *   - 'name': The name of the store.
   *   - 'image': The URL of the store's image (if available).
   *   - 'headline': The headline associated with the store.
   *   - 'owner_id': The user ID of the store's owner.
   */
  private function getStoreData($store) {
    $storeData = [
      'name' => $store->getName(),
      'image' => $this->getStoreImageUrl($store),
      'headline' => $store->get('field_headline')->first()->getValue()['value'],
      'owner_id' => $store->getOwner()->id(),
    ];
    return $storeData;
  }

  /**
   * Retrieves the URL of the image associated with a Drupal Commerce store.
   *
   * This method fetches the URL of the image associated with a store entity,
   * provided that the store has an associated image. If no image is found or if
   * any required entities are missing, it returns null.
   *
   * @param \Drupal\commerce_store\Entity\StoreInterface $store
   *   The Drupal Commerce store entity for which to retrieve the image URL.
   *
   * @return string|null
   *   The URL of the store's image if available, or null if no image is found
   *   or if any required entities are missing.
   */
  private function getStoreImageUrl($store) {
    $imageField = $store->get('field_farm_image')->first();
    if (!$imageField) {
      return NULL;
    }
    $media = Media::load($imageField->target_id);
    if (!$media) {
      return NULL;
    }
    $file = File::load($media->getSource()->getSourceFieldValue($media));
    if (!$file) {
      return NULL;
    }
    return $file->createFileUrl();
  }

  /**
   * Get products based on store id and categories id .
   *
   * @param int $owner_id
   *   Owner id from url.
   * @param iterable $categories_id
   *   Categories id from Store.
   *
   * @return products
   *   Return array of products
   */
  public function getProductByStore($owner_id, $categories_id = NULL) {
    $farm_id = $this->getStoreIdByUserId($owner_id);
    $entity_type_manager = \Drupal::entityTypeManager();
    $entity_type = 'commerce_product';
    $query = $entity_type_manager->getStorage($entity_type)->getQuery();
    $query->condition('type', 'default');
    if ($categories_id != NULL) {
      $query->condition('field_category', $categories_id);
    }
    $query->condition('stores', $farm_id);
    $result = $query->execute();
    $products = $entity_type_manager->getStorage($entity_type)->loadMultiple($result);
    return $this->getProductsInfomation($products);
  }

  /**
   * Retrieve all terms from a specific vocabulary.
   *
   * @param string $vocabulary_machine_name
   *   The machine name of the vocabulary.
   *
   * @return array
   *   An array of taxonomy terms in the specified vocabulary.
   */
  public function getTermOfTaxonomy($vocabulary_machine_name) {
    $terms = [];
    if (!empty($vocabulary_machine_name)) {
      $terms = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadTree($vocabulary_machine_name);
    }
    return $terms;
  }

  /**
   * Get Image URL with image_style: featured_product applied .
   *
   * @param array $store_images_field
   *   Images id from Objects $store_images_field.
   * @param string $style
   *   Image style machine name in configuration.
   *
   * @return images_url
   *   Return array of images URL with image_style: featured_product
   */
  public function getImagesUrlWithStyle($store_images_field, $style) {
    $images_url = [];
    foreach ($store_images_field as $image) {
      $media = Media::load($image['target_id']);
      $fid = $media->getSource()->getSourceFieldValue($media);
      $file = File::load($fid);
      // Get origin image URI.
      $image_uri = $file->getFileUri();
      $style_image = ImageStyle::load($style);
      $url = $style_image->buildUrl($image_uri);
      array_push($images_url, $url);
    }
    return $images_url;
  }

  /**
   * Truncate a string if it exceeds a specified maximum length.
   *
   * @param string $inputString
   *   The input string to truncate.
   * @param int $maxLength
   *   The maximum length of the output string.
   *
   * @return string
   *   The truncated string, possibly with '...' added.
   */
  public function truncateString($inputString, $maxLength) {
    if (mb_strlen($inputString, 'UTF-8') <= $maxLength) {
      return $inputString;
    }
    else {
      return mb_substr($inputString, 0, $maxLength, 'UTF-8') . '...';
    }
  }

}
