<?php

/**
 * Plugin Name: ACF Contact Form
 * Description: A WordPress plugin that uses ACF to present a contact form.
 * Author URI: mailto:dashifen@dashifen.com
 * Author: David Dashifen Kees
 * Version: 1.0.0
 *
 * @noinspection PhpStatementHasEmptyBodyInspection
 * @noinspection PhpIncludeInspection
 */

use Dashifen\ContactForm\ContactForm;
use Dashifen\ContactForm\Agents\FieldGroupAgent;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\WPHandler\Agents\Collection\Factory\AgentCollectionFactory;

$autoloader = '';
if (file_exists($autoloader = __DIR__ . '/vendor/autoload.php'));
elseif (file_exists($autoloader = dirname(ABSPATH) . '/deps/vendor/autoload.php'));
elseif (file_exists($autoloader = dirname(ABSPATH) . '/vendor/autoload.php'));
elseif (file_exists($autoloader = ABSPATH . 'vendor/autoload.php'));
require_once $autoloader;

(function () {
  try {
    $contactForm = new Contactform();
    $agentCollectionFactory = new AgentCollectionFactory();
    $agentCollectionFactory->registerAgent(FieldGroupAgent::class);
    $contactForm->setAgentCollection($agentCollectionFactory);
    $contactForm->initialize();
  } catch (HandlerException $e) {
    wp_die($e->getMessage());
  }
})();
