<?php

/**
 * Plugin Name: ACF Contact Form
 * Description: A WordPress plugin that uses ACF to present a contact form.
 * Author URI: mailto:dashifen@dashifen.com
 * Author: David Dashifen Kees
 * Version: 0.10.0
 */

use Dashifen\ContactForm\ContactForm;
use Dashifen\ContactForm\Agents\FieldGroupAgent;
use Dashifen\WPHandler\Handlers\HandlerException;
use Dashifen\ContactForm\Agents\CustomFormSettingsAgent;
use Dashifen\WPHandler\Agents\Collection\Factory\AgentCollectionFactory;

if (!class_exists('Dashifen\ContactForm\ContactForm')) {
  require_once 'vendor/autoload.php';
}

(function () {
  try {
    $contactForm = new Contactform();
    $agentCollectionFactory = new AgentCollectionFactory();
    $agentCollectionFactory->registerAgent(CustomFormSettingsAgent::class);
    $agentCollectionFactory->registerAgent(FieldGroupAgent::class);
    $contactForm->setAgentCollection($agentCollectionFactory);
    $contactForm->initialize();
  } catch (HandlerException $e) {
    wp_die($e->getMessage());
  }
})();
