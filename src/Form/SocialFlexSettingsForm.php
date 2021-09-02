<?php

namespace Drupal\social_flex\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\GroupType;
use Drupal\social_flex\SocialFlexCommonService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SocialFlexSettingsForm.
 */
class SocialFlexSettingsForm extends ConfigFormBase {

  /**
   * Drupal\social_flex\SocialFlexCommonService.
   *
   * @var SocialFlexCommonService $socialFlexCommonService
   */
  protected $socialFlexCommonService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->socialFlexCommonService = $container->get('social_flex.common');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_flex_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $social_flex_config = $this->configFactory->getEditable('social_flex.settings');

    // Add an introduction text to explain what can be done here.
    $form['introduction']['warning'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Be aware that when disabling group types, the flexible group handling wont work anymore.'),
    ];

    $group_types = $this->socialFlexCommonService->getFlexibleGroupsForSettings();

    if (isset($group_types) && !empty($group_types)) {

      $form['flexible_group_types'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Enable flexible group handling per group type'),
        '#description' => $this->t('Select the group types for which you want to enable flexible group type handling.'),
        '#options' => $group_types,
        '#default_value' => $social_flex_config->get('flexible_group_types'),
      ];
    

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#button_level' => 'raised',
        '#value' => $this->t('Save configuration'),
      ];

    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('social_flex.settings');
    $config->set('flexible_group_types', $form_state->getValue('flexible_group_types'));
    $config->save();
  }

  /**
   * Gets the configuration names that will be editable.
   */
  protected function getEditableConfigNames() {
    // @todo Implement getEditableConfigNames() method.
  }

}
