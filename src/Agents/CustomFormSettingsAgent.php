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
  
  private function withACF (): bool
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
  protected function addContactFormFields ()
  {
    if ($this->withACF()) {
      acf_add_local_field_group($this->handler->getContactFormFieldGroup());
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
  protected function notifyOnMissingSettings (): void
  {
    if ($this->withACF()) {
      $allData = array_merge(
        $this->getSharingSettings(),
        $this->getAnalyticsSettings()
      );
      
      if ($this->isDataMissing($allData)) {
        /** @noinspection HtmlUnknownTarget */
        
        $link = sprintf('<a href="%s">%s</a>',
                        admin_url('admin.php?page=' . $this->sharingMenuSlug),
                        $this->sharingMenuName); ?>
        
        <div class='notice notice-error'>
          <p>Please fully complete the <?= $link ?> information
            before launching this site.</p>
        </div>
      
      <?php }
    }
  }
}
