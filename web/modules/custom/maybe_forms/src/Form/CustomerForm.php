<?php

namespace Drupal\maybe_forms\Form;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\maybe_forms\Services\AccountService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the CustomerForm class.
 *
 * This class extends Drupal\Core\Form\FormBase to create a custom form for
 * create customer form .
 *
 * Usage:
 * - To access this form, visit the URL path '/customer'.
 * - This form allows administrators add customer.
 *
 * @ingroup customer
 */
class CustomerForm extends FormBase {
  /**
   * Account Service.
   *
   * @var Drupal\maybe_forms\Services\AccountService
   */
  protected $accountService;

  /**
   * Constructs a new instance of the CustomerForm class.
   *
   * @param \Drupal\your_module\AccountService $accountService
   *   The AccountService instance responsible for managing user accounts.
   */
  public function __construct(AccountService $accountService) {
    $this->accountService = $accountService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('maybe.account')
      );
  }

  /**
   * {@inheritdoc}
   *
   * Gets the unique form ID for the CustomerForm.
   *
   * @return string
   *   The form ID, which is used to identify this form within the system.
   */
  public function getFormId() {
    return 'maybe_customer_form';
  }

  /**
   * {@inheritdoc}
   *
   * Builds the CustomerForm form.
   *
   * This method constructs and defines the elements
   * and structure of the CustomerForm
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
      '#default_value' => ['country_code' => 'VN'],
    ];
    $form['mail'] = [
      '#type' => 'email',
      '#title' => t('Email'),
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
        'field_address' => $values['address'],
        'status' => TRUE,
      ];
      if ($this->accountService->createAccount($user, MAYBE_FORMS_CUSTOMER_ROLE)) {
        \Drupal::messenger()->addMessage(MAYBE_FORMS_CUSTOMER_CREATED_MESSAGE);
      }
    }
    catch (EntityStorageException $e) {
      \Drupal::messenger()->addError(MAYBE_FORMS_CUSTOMER_ERROR_MESSAGE);
    }
  }

}
