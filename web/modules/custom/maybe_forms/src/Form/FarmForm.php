<?php

namespace Drupal\maybe_forms\Form;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\maybe_forms\Services\AccountService;
use Drupal\maybe_forms\Services\FarmService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the FarmForm class.
 *
 * This class extends Drupal\Core\Form\FormBase to create a custom form for
 * create form farm .
 *
 * Usage:
 * - To access this form, visit the URL path '/farm'.
 * - This form allows administrators add or edit farm.
 *
 * @ingroup farm
 */
class FarmForm extends FormBase {
  /**
   * Farm Service.
   *
   * @var Drupal\maybe_forms\Services\FarmService
   */
  protected $farmService;
  /**
   * Account Service.
   *
   * @var Drupal\maybe_forms\Services\AccountService
   */
  protected $accountService;

  /**
   * Constructs a new instance of the FarmForm class.
   *
   * @param \Drupal\your_module\FarmService $farmService
   *   The FarmService instance responsible
   *   for managing farm-related operations.
   * @param \Drupal\your_module\AccountService $accountService
   *   The AccountService instance responsible for managing user accounts.
   */
  public function __construct(FarmService $farmService, AccountService $accountService) {
    $this->farmService = $farmService;
    $this->accountService = $accountService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('maybe.farm'),
          $container->get('maybe.account')
      );
  }

  /**
   * {@inheritdoc}
   *
   * Gets the unique form ID for the FarmForm.
   *
   * @return string
   *   The form ID, which is used to identify this form within the system.
   */
  public function getFormId() {
    return 'maybe_forms_farm';
  }

  /**
   * {@inheritdoc}
   *
   * Builds the FarmForm form.
   *
   * This method constructs and defines the elements
   * and structure of the FarmForm
   * form, including fields, buttons, and any other form elements.
   *
   * @param array $form
   *   An associative array representing the form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param mixed $id
   *   (Optional) An identifier for the form, if needed for customization.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $form['address'] = [
      '#type' => 'address',
      '#title' => t('Address'),
      '#default_value' => [
        'country_code' => 'VN',
      ],
    ];
    $form['mail'] = [
      '#type' => 'email',
      '#title' => t('Email'),
      '#required' => TRUE,
    ];
    $form['name_store'] = [
      '#type' => 'textfield',
      '#title' => t('Farm name'),
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('mail');
    if ($this->accountService->checkUserExists($email)) {
      $form_state->setErrorByName('mail', MAYBE_FORMS_UNIQUE_ERROR_MESSAGE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $values = $form_state->getValues();
      $user = [
        'name' => $values['mail'],
        'pass' => MAYBE_FORMS_DEFAULT_PASSWORD,
        'address' => $values['address'],
        'role' => $values['roles'],
      ];
      $user_id = $this->accountService->createAccount($user, MAYBE_FORMS_FARMER_ROLE);
      $store = [
        'address' => $values['address'],
        'owner_id' => $user_id,
        'name_store' => $values['name_store'],
        'mail' => $values['mail'],
      ];
      $this->farmService->createStore($store);
      \Drupal::messenger()->addMessage(MAYBE_FORMS_FARM_CREATED_MESSAGE);
    }
    catch (EntityStorageException $e) {
      \Drupal::messenger()->addError(MAYBE_FORMS_ERROR_MESSAGE);
    }
  }

}
