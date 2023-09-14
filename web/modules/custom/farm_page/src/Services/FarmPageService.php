<?php

namespace Drupal\farm_page\Services;

use Drupal\commerce_product\Entity\Product;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\taxonomy\Entity\Term;

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
  public function getStoreIdByUserId($uid) {
    $database = \Drupal::database();
    $query = $database->query("SELECT store_id FROM commerce_store_field_data WHERE uid =" . $uid);
    $result = $query->fetchAll();
    $store_id = $result[0]->store_id;
    return $store_id;
  }

  /**
   * Get feature product based on store id .
   *
   * @param int $store_id
   *   store id from function getStoreIdByUserId.
   *
   * @return feature_products_id
   *   Return array of feature products id to display on UI
   */
  public function getFeatureProducts($store_id) {
    $database = \Drupal::database();
    $query = $database->select('commerce_product', 'cp');
    $query->addField('cp', 'product_id', 'pid');
    $query->join('commerce_product__field_isfeature', 'fif', 'cp.product_id = fif.entity_id');
    $query->join('commerce_product__stores', 'cps', 'cp.product_id = cps.entity_id');
    $query->join('commerce_store', 'cs', 'cps.stores_target_id = cs.store_id');
    $query->condition('fif.field_isfeature_value', 1, '=');
    $query->condition('cs.store_id', $store_id, '=');
    $results = $query->execute()->fetchAll();
    $feature_products_id = [];
    foreach ($results as $product) {
      $featureProduct = Product::load($product->pid);
      array_push($feature_products_id, $featureProduct);
    }
    return $feature_products_id;
  }

  /**
   * Get Image URL based on store image field .
   *
   * @param array $store_images_field
   *   images id from Objects $store_images_field.
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
   * @param array $products_array
   *   An product array.
   *
   * @return products
   *   Return array of product to display on UI
   */
  public function getProductsInfomation($products_array) {
    $products = [];
    foreach ($products_array as $product_in_array) {
      $product = [];
      // Get Id.
      $product_id = $product_in_array->get('product_id')->getValue();
      $product['product_id'] = $product_id[0]['value'];
      // Get Names.
      $product_name = $product_in_array->get('title')->getValue();
      $product['product_name'] = $product_name[0]['value'];
      // Get Images.
      $product_images_field = $product_in_array->get('field_images')->getValue();
      $product_images_url = $this->getImagesUrlFromArray($product_images_field);
      $product['product_images'] = $product_images_url;
      // Get Created Date.
      $product_created = $product_in_array->get('created')->getValue();
      $product['product_created'] = $product_created[0]['value'];
      // Get Description.
      $product_description = $product_in_array->get('body')->getValue();
      $product['product_description'] = $product_description[0]['value'];
      // Get Category.
      $product_category_id = $product_in_array->get('field_category')->getValue();
      $product_category_name = Term::load($product_category_id[0]['target_id']);
      $product['product_category'] = $product_category_name->getName();
      array_push($products, $product);
    }
    return $products;
  }

}
