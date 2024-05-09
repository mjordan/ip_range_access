<?php

namespace Drupal\ip_range_access\Plugin\ContextReaction;

use Drupal\Core\Form\FormStateInterface;
use Drupal\context\ContextReactionPluginBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Denies access (returns 403 Access Denied) to user.
 *
 * @ContextReaction(
 *   id = "deny_access",
 *   label = @Translation("Deny access to node or media")
 * )
 */
class DenyAccessReaction extends ContextReactionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'deny_access' => FALSE,
      'proxy_prepend_url' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Deny access to node or media.');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $config = $this->getConfiguration();
    $current_url = \Drupal::request()->getSchemeAndHttpHost() . \Drupal::service('path.current')->getPath();
    if ($config['deny_access']) {
      if ($this->configuration['log_executed']) {
        \Drupal::logger('ip_range_access')->info("User was denied access to %url.", ['%url' => $current_url]);
      }
      throw new AccessDeniedHttpException();
    }
    if (strlen($config['proxy_prepend_url'])) {
      $redirect_url = $config['proxy_prepend_url'] . $current_url;
      if ($this->configuration['log_executed']) {
        \Drupal::logger('ip_range_access')->info("User was redirected to %url.", ['%url' => $redirect_url]);
      }
      $response = new RedirectResponse($redirect_url);
      $response->send();
      exit;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form['deny_access'] = [
      '#title' => $this->t('Deny access to node or media'),
      '#type' => 'checkbox',
      '#description' => $this->t("Check this box to return a 403 Access Denied response to the user. If you enter a proxy URL below, you should uncheck this box. Note: you should consider adding the \"User's Role\" condition to this Context to prevent administrators from being blocked from accessing content."),
      '#default_value' => isset($config['deny_access']) ? $config['deny_access'] : FALSE,
    ];
    $form['log_executed'] = [
      '#title' => $this->t('Log that this reaction was executed'),
      '#type' => 'checkbox',
      '#description' => $this->t('Check this box to log that this reaction was executed.'),
      '#default_value' => isset($config['log_executed']) ? $config['log_executed'] : FALSE,
    ];
    $form['proxy_prepend_url'] = [
      '#type' => 'textfield',
      '#title' => t('Proxy URL'),
      '#default_value' => $this->configuration['proxy_prepend_url'],
      '#maxlength' => 256,
      '#description' => t('URL to redirect users to, e.g., an Ezproxy login URL. The current URL will be appended to this URL. If you use this option, you should uncheck the "Deny access to node or media" option above because you are not denying access, you are redirecting the user. Leave this field blank to not redirect user.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setConfiguration([
      'deny_access' => $form_state->getValue('deny_access'),
      'log_executed' => $form_state->getValue('log_executed'),
      'proxy_prepend_url' => $form_state->getValue('proxy_prepend_url'),
    ]);
  }

}
