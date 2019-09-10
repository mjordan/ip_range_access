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
    if ($config['deny_access']) {
      throw new AccessDeniedHttpException();
    }
    if (strlen($config['proxy_prepend_url'])) {
      $current_url = $host = \Drupal::request()->getSchemeAndHttpHost() . \Drupal::service('path.current')->getPath();
      $redirect_url = $config['proxy_prepend_url'] . $current_url;
      $response = new RedirectResponse($redirect_url);
      $response->send();
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
      '#description' => $this->t('Check this box to return a 403 Access Denied response to the user.'),
      '#default_value' => isset($config['deny_access']) ? $config['deny_access'] : FALSE,
    ];
    $form['proxy_prepend_url'] = [
      '#type' => 'textfield',
      '#title' => t('Proxy URL'),
      '#default_value' => $this->configuration['proxy_prepend_url'],
      '#maxlength' => 256,
      '#description' => t('URL to redirect users to, e.g., an Ezproxy login URL. Leave blank to not redirect user.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setConfiguration([
      'deny_access' => $form_state->getValue('deny_access'),
      'proxy_prepend_url' => $form_state->getValue('proxy_prepend_url'),
    ]);
  }

}
