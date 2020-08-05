<?php

namespace Dashifen\ContactForm;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\ContactForm\Services\ContactFormEmitter;
use Dashifen\WPHandler\Hooks\Factory\HookFactoryInterface;
use Dashifen\WPHandler\Handlers\Plugins\AbstractPluginHandler;
use Dashifen\WPHandler\Hooks\Collection\Factory\HookCollectionFactoryInterface;

class ContactForm extends AbstractPluginHandler
{
  protected array $contactFormFieldGroup = [];
  
  /**
   * ContactForm constructor.
   *
   * @param HookFactoryInterface|null           $hookFactory
   * @param HookCollectionFactoryInterface|null $hookCollectionFactory
   *
   * @throws HandlerException
   */
  public function __construct(
    ?HookFactoryInterface $hookFactory = null,
    ?HookCollectionFactoryInterface $hookCollectionFactory = null
  ) {
    parent::__construct($hookFactory, $hookCollectionFactory);
    
    // now that our parent has handled the core functionality, we want to do
    // the work of this plugin.  namely, we want to load up the contact form's
    // JSON file into memory because we're going to need it later.
  
    $file = realpath(__DIR__ . '/../assets/acf/contact-form.json');
    $this->contactFormFieldGroup = file_exists($file)
      ? json_decode(file_get_contents($file), true)
      : [];
  }
  
  /**
   * getContactFormFieldGroup
   *
   * Returns the value of the contact form field group property.
   *
   * @return array
   */
  public function getContactFormFieldGroup(): array
  {
    return $this->contactFormFieldGroup;
  }
  
  /**
   * initialize
   *
   * Uses addAction and/or addFilter to connect protected methods of this
   * object to the WordPress ecosystem.
   *
   * @return void
   * @throws HandlerException
   */
  public function initialize(): void
  {
    if (!$this->isInitialized()) {
      $this->addAction('init', 'initializeAgents', 5);
      $this->addAction('init', fn() => self::display());
    }
  }
  
  /**
   * display
   *
   * Displays the contact form based on the settings an administrator has set
   * for this site.
   *
   * @return void
   * @throws ContactFormException
   */
  public static function display(): void
  {
    // this is a static method, so we can't use $this here.  but, we can
    // construct a new ContactForm object and use it to get at the field group
    // information.  because we never call this instance's initialize method,
    // we don't have to worry about it doing any WordPress stuff this time.
    
    $contactForm = new ContactForm();
    $fieldGroup = $contactForm->getContactFormFieldGroup();
    $emitter = new ContactFormEmitter($fieldGroup);
    $emitter->emit();
  }
}
