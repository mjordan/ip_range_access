<?php

namespace Drupal\ip_range_access\Plugin\ContextReaction;

use Drupal\Core\Form\FormStateInterface;
use Drupal\context\ContextReactionPluginBase;

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
      throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
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
    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->setConfiguration([
      'deny_access' => $form_state->getValue('deny_access'),
    ]);
  }

}
