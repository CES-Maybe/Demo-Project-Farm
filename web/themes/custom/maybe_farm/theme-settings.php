<?php

/**
 * @file
 * Theme settings form for Maybe Farm theme.
 */

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function maybe_farm_form_system_theme_settings_alter(&$form, &$form_state) {

  $form['maybe_farm'] = [
    '#type' => 'details',
    '#title' => t('Maybe Farm'),
    '#open' => TRUE,
  ];

  $form['maybe_farm']['font_size'] = [
    '#type' => 'number',
    '#title' => t('Font size'),
    '#min' => 12,
    '#max' => 18,
    '#default_value' => theme_get_setting('font_size'),
  ];

}
