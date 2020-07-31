<?php

namespace Dashifen\ContactForm;

use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Handlers\Plugins\AbstractPluginHandler;

class ContactForm extends AbstractPluginHandler
{
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
