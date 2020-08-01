<?php

namespace Dashifen\ContactForm;

use Dashifen\WPHandler\Handlers\HandlerException;
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
   * @throws ContactFormException
   */
  public function __construct(
    ?HookFactoryInterface $hookFactory = null,
    ?HookCollectionFactoryInterface $hookCollectionFactory = null
  ) {
    parent::__construct($hookFactory, $hookCollectionFactory);
    
    // now that our parent has handled the core functionality, we want to do
    // the work of this plugin.  namely, we want to load up the contact form's
    // JSON file into memory because we're going to need it later.
    
    $file = realpath(__DIR__ . '/../acf/contact-form.json');
    
    if (file_exists($file)) {
      throw new ContactFormException(
        'contact-form.json not found',
        ContactFormException::FILE_NOT_FOUND
      );
    }
    
    $this->contactFormFieldGroup = json_decode(file_get_contents($file), true);
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
    }
  }
  
}
