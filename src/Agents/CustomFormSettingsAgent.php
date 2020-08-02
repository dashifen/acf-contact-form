<?php

namespace Dashifen\ContactForm\Agents;

use Dashifen\ContactForm\ContactForm;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Agents\AbstractPluginAgent;
use Dashifen\WPHandler\Handlers\Plugins\PluginHandlerInterface;

class CustomFormSettingsAgent extends AbstractPluginAgent
{
  /**
   * @var ContactForm
   * @noinspection PhpDocFieldTypeMismatchInspection
   */
  protected PluginHandlerInterface $handler;
  
  /**
   * initialize
   *
   * Uses addAction and/or addFilter to hook protected methods of this object
   * into the WordPress ecosystem.
   *
   * @return void
   * @throws HandlerException
   */
  public function initialize(): void
  {
    if (!$this->isInitialized()) {
      $this->addAction('acf/init', 'registerContactFormSettings');
      
      // we can't run the next action until the prior one is complete.  so,
      // we run this one at 15 because the that one uses the default priority
      // of 10.
      
      $this->addAction('acf/init', 'addContactFormFields', 15);
      $this->addAction('admin_notices', 'notifyOnMissingSettings');
    }
  }
  
  /**
   * registerContactFormSettings
   *
   * Adds the Custom Form settings page as a child of the core WP settings
   * menu item.
   */
  protected function registerContactFormSettings(): void
  {
    if ($this->withACF()) {
      acf_add_options_page(
        [
          'menu_title'  => 'Contact Form',
          'page_title'  => 'Contact Form Settings',
          'menu_slug'   => 'contact-form-settings',
          'parent_slug' => 'options-general.php',
          'capability'  => 'manage_options',
        ]
      );
    }
  }
  
  private function withACF(): bool
  {
    return function_exists('acf_add_options_page');
  }
  
  /**
   * addSharingSettingsFields
   *
   * Adds the actual ACF fields and field groups to the options page we
   * added above.  This PHP was generated with the ACF plugin.
   *
   * @return void
   */
  protected function addContactFormFields()
  {
    if ($this->withACF()) {
      if (!empty($fieldGroup = $this->handler->getContactFormFieldGroup())) {
        acf_add_local_field_group($fieldGroup);
      }
    }
  }
  
  /**
   * notifyOnMissingData
   *
   * Sets an admin notice when the sharing and analytics information is
   * not available.
   *
   * @return void
   */
  protected function notifyOnMissingSettings(): void
  {
    if ($this->withACF() && $this->hasEmptySettings()) {
      /** @noinspection HtmlUnknownTarget */
      
      $link = sprintf(
        '<a href="%s">%s</a>',
        admin_url('options-general.php?page=contact-form-settings'),
        'Contact Form settings'
      ); ?>

      <div class='notice notice-error'>
        <p>Please fully complete the <?= $link ?> before publishing the
          page on which the form will appear.</p>
      </div>
    
    <?php }
  }
  
  
  /**
   * hasEmptySettings
   *
   * Returns true if our contact form settings contains any empty fields and
   * false otherwise.
   *
   * @return bool
   */
  private function hasEmptySettings(): bool
  {
    $fieldGroup = $this->handler->getContactFormFieldGroup();
    
    // it's possible that the JSON file could not be found by our handler.
    // in that case, the fields have to be empty because we don't even know
    // what they are!
    
    return !empty($fieldGroup)
      ? $this->findEmptyFields($fieldGroup['fields'])
      : true;
  }
  
  /**
   * findEmptyFields
   *
   * Recursively searches through the fields of our contact form settings to
   * determine if any of them are empty.
   *
   * @param array $fields
   *
   * @return bool
   */
  private function findEmptyFields(array $fields): bool
  {
    foreach ($fields as $field) {
      
      // as we search these fields, if we encounter sub-fields, we recursively
      // call this method to look for empties within this group.  then, if we
      // find empties in the sub-fields, or if the current field is empty, we
      // return true because we've found at least one empty field.
      
      $emptySubFields = isset($field['sub-fields'])
        && $this->findEmptyFields($field['sub-fields']);
      
      if ($emptySubFields || empty(get_field($field['key'], 'option'))) {
        return true;
      }
    }
    
    // otherwise, if we never returned true above, then we never found an
    // empty field.  therefore, we return false here.
    
    return false;
  }
}
