<?php

/**
 * @file
 * Contains \Drupal\invoicer_invoice\Form\InvoicerInvoiceConfigForm.
 */

namespace Drupal\invoicer_invoice\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure file system settings for this site.
 */
class InvoicerInvoiceConfigForm extends ConfigFormBase {
  protected $delimiter;
  protected $configuration;

  public function __construct(
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($config_factory);
    $this->delimiter = "|";
    $this->configuration = $config_factory->getEditable('invoicer_invoice.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'invoicer_invoice_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['invoicer_invoice.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = array();

    $form['vat_types'] = array(
      '#type' => 'textarea',
      '#title' => t('VAT types'),
      '#size' => 5000,
      '#maxlength' => 5000,
      '#default_value' => $this->getFormattedValues($this->configuration->get('vat_types')),
      '#description' => t('The VAT types'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();
    foreach ($form_state->getValues() as $key => $value) {
      $values = NULL;
      if ($key == 'vat_types') {
        $vatTypes = explode("\n", $value);

        $result = [];
        foreach ($vatTypes as $index => $vatType) {
          $vatType = trim($vatType);

          $position = mb_strpos($vatType, $this->delimiter);

          $vat = trim(mb_substr($vatType, 0, $position));
          $vatTypeHumanReadable = trim(mb_substr($vatType, $position + 1, mb_strlen($vatType)));
          if (is_numeric($vat)) {
            $result[$index]['value'] = $vat;
            $result[$index]['value_label'] = $vatTypeHumanReadable;
          }
        }

        $values = $result;
      }

      $this->configuration->set($key, $values);
    }

    $this->configuration->save();

    parent::submitForm($form, $form_state);
  }

  private function getFormattedValues($values) {
    $result = [];
    foreach ($values as $value) {
      $result[] = "{$value['value']}{$this->delimiter}{$value['value_label']}";
    }
    return implode("\n", $result);
  }
}