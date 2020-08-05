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
    $this->settings = $this->getFieldGroupValues($fieldGroup['fields']);
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
      $value = get_field($field['key'], 'option');
      
      // if our value is an array, we want to make sure it's flat before we
      // add it to our settings.  luckily, we have a method that does just
      // that defined below.
      
      if (is_array($value)) {
        $settings = array_merge($settings, $this->flatten($value, $field['name']));
      } else {
        
        // otherwise, we can just add it to our settings array directly.
        // notice that we use the name here even though we selected with the
        // key
        
        $settings[$field['name']] = $value;
      }
    }
    return $settings;
  }
  
  /**
   * flatten
   *
   * Recursively flattens arrays of ACF values producing an array of fields
   * and values that we can access without worrying about how they might have
   * been organized into groups in the actual field group.
   *
   * @param array  $values
   * @param string $field
   *
   * @return array
   */
  private function flatten(array $values, string $field = ''): array
  {
    // first, if this isn't an associative array, then we'll just return it.
    // this is useful for fields like checkboxes and other data that ACF needs
    // to be an array.
    
    if (!acf_is_associative_array($values)) {
      return [$field => $values];
    }
    
    // otherwise, we'll loop over our values and, if any of them are an array,
    // we recursively call this method to flatten those arrays as well.  for
    // non-array values, we just add them to the $flattened array.
    
    $flattened = [];
    foreach ($values as $field => $value) {
      if (is_array($value)) {
        $flattened = array_merge($flattened, $this->flatten($value, $field));
      } else {
        $flattened[$field] = $value;
      }
    }
    
    return $flattened;
  }
  
  /**
   * emit
   *
   * Emits the contact form by constructing a context used to render a twig
   * template.
   *
   * @return void
   * @throws ContactFormException
   */
  public function emit(): void
  {
    $twig = realpath(__DIR__ . '/../../assets/twig/contact-form.twig');
    Timber::render_string(file_get_contents($twig), $this->getTwigFormContext());
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
    
    return [
      'legend'       => $this->settings['contact-form-legend'],
      'instructions' => $this->settings['contact-form-instructions'],
      'fields'       => [
        $this->getTwigFieldContext('name', 'text'),
        $this->getTwigFieldContext('email', 'email'),
        $this->getTwigFieldContext('website', 'url'),
        $this->getTwigFieldContext('message', 'textarea'),
      ],
    ];
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
    $required = in_array($name, $this->settings['contact-form-required-fields'])
      || $name === "message";
    
    return [
      'value'    => '',
      'type'     => $type,
      'name'     => $name,
      'id'       => 'acf-contact-form-' . $name,
      'class'    => 'acf-contact-form-' . $name . '-container',
      'label'    => $this->getDefaultFieldLabel($name),
      'required' => (int) $required,
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
