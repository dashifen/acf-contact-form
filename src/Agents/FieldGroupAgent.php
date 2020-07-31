<?php

namespace Dashifen\ContactForm\Agents;

use Dashifen\ACFAgent\AbstractFieldGroupAgent;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Handlers\Plugins\PluginHandlerInterface;

class FieldGroupAgent extends AbstractFieldGroupAgent
{
  /**
   * FieldGroupAgent constructor.
   *
   * @param PluginHandlerInterface $handler
   */
  public function __construct(PluginHandlerInterface $handler)
  {
    die($handler->getPluginDir() . '/acf');
    parent::__construct($handler, $handler->getPluginDir() . '/acf');
  }
  
  /**
   * initialize
   *
   * Adds a filter to turn off the importing of ACF fields by this plugin for
   * the time being.
   *
   * @return void
   * @throws HandlerException
   */
  public function initialize(): void
  {
    // we'll use WordPress's add_filter function here so that we can reference
    // the __return_false function as a string.  the addFilter method of our
    // Agents doesn't allow that at the moment.  then, we just call our the
    // parent initialize method and we're done here.
    
    add_filter('acf-agent-import', '__return_false');
    parent::initialize();
  }
  
  /**
   * shouldExport
   *
   * Returns true if this plugin should export the specified ACF field group's
   * JSON notation.
   *
   * @param string $acfName
   *
   * @return bool
   */
  protected function shouldExport(string $acfName): bool
  {
    return acf_get_field_group($acfName)['title'] === 'Contact Form';
  }
}
