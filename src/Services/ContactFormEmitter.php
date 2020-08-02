<?php

namespace Dashifen\ContactForm\Services;

use Timber\Timber;
use Dashifen\WPDebugging\WPDebuggingTrait;
use Dashifen\ContactForm\ContactFormException;

class ContactFormEmitter
{
  use WPDebuggingTrait;
  
  private array $settings;
  
  /**
   * ContactFormEmitter constructor.
   *
   * @param array $fieldGroup
   */
  public function __construct(array $fieldGroup)
  {
    self::debug(get_field('field_5f24be54c775a', 'option'), true);
    
    $this->settings = $this->getFieldGroupValues($fieldGroup['fields']);
    self::debug($this->settings, true);
  }
  
  /**
   * getFieldGroupValues
   *
   * Recursively searches through our fields to get field values.
   *
   * @param array $fields
   *
   * @return array
   */
  private function getFieldGroupValues(array $fields): array
  {
    $settings = [];
    foreach ($fields as $field) {
      if (isset($field['sub_fields'])) {
        $subFields = $this->getFieldGroupValues($field['sub_fields']);
        self::debug($subFields, true);
      }
      
      
      $settings[$field['name']] = get_field($field['key'], 'option');
    }
    
    
    
    return $settings;
  }
  
  /**
   * emit
   *
   * Emits the contact form by constructing a context used to render a twig
   * template.
   *
   * @return string
   * @throws ContactFormException
   */
  public function emit(): string
  {
    $twig = realpath(__DIR__ . '/../../assets/twigs/contact-form.twig');
    return Timber::fetch($twig, $this->getTwigFormContext());
  }
  
  /**
   * getTwigFormContext
   *
   * Constructs an array used by the emit method as the context for the
   * rendering of our contact form's twig template.
   *
   * @return array
   * @throws ContactFormException
   */
  private function getTwigFormContext(): array
  {
    // our context is made up of a legend, instructions, and an array of
    // fields.  each field has the following indices:  class, id, required,
    // label, type, name, and value.  many of these values are already in
    // our settings property, but others we may construct based on those data.
    
    self::debug(
      [
        'legend'       => $this->settings['contact-form-legend'],
        'instructions' => $this->settings['contact-form-instructions'],
        'fields'       => [
          $this->getTwigFieldContext('name', 'text'),
          $this->getTwigFieldContext('email', 'email'),
          $this->getTwigFieldContext('website', 'url'),
          $this->getTwigFieldContext('message', 'textarea'),
        ],
      ], true
    );
  }
  
  /**
   * getTwigFieldContext
   *
   * Returns an array that describes a field that will appear in our form.
   *
   * @param string $name
   * @param string $type
   *
   * @return array
   * @throws ContactFormException
   */
  private function getTwigFieldContext(string $name, string $type): array
  {
    // class, id, required, label, type, name, and value
    
    return [
      'value' => '',
      'type'  => $type,
      'name'  => $name,
      'id'    => 'acf-contact-form-' . $name,
      'class' => 'acf-contact-form-' . $name . '-container',
      'label' => $this->getDefaultFieldLabel($name),
    ];
  }
  
  /**
   * getDefaultFieldLabel
   *
   * Gets the default field labels
   *
   * @param string $name
   *
   * @return string
   * @throws ContactFormException
   */
  private function getDefaultFieldLabel(string $name): string
  {
    switch ($name) {
      default:
        throw new ContactFormException(
          'Unknown field: ' . $name,
          ContactFormException::UNKNOWN_FIELD
        );
      
      case 'name':
        $label = 'Your Name';
        break;
      
      case 'email':
        $label = 'Your Email Address';
        break;
      
      case 'website':
        $label = 'Your Website';
        break;
      
      case 'message':
        $label = 'Your Message';
        break;
    }
    
    return apply_filters('acf-contact-form-' . $name . '-label', $label);
  }
}
