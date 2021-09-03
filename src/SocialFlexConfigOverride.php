<?php

namespace Drupal\social_flex;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Class SocialFlexConfigOverride.
 *
 * @package Drupal\social_flex
 */
class SocialFlexConfigOverride implements ConfigFactoryOverrideInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;


  /**
   * Constructs the configuration override.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\social_flex\SocialFlexCommonService $social_flex_common_service
   *   The social flex common service
   */
  public function __construct(
    ConfigFactoryInterface $config_factory  
    ) {
    $this->configFactory = $config_factory;
  }

  /**
   * Load overrides.
   */
  public function loadOverrides($names) {
    $overrides = [];

    

    // Add Content access views filter to exclude
    // nodes, with visibility group, placed in group you are not a member of.
    $config_names = [
      'views.view.latest_topics' => [
        'default',
        'page_latest_topics',
      ],
      'views.view.upcoming_events' => [
        'default',
        'block_community_events',
        'block_my_upcoming_events',
        'page_community_events',
        'upcoming_events_group',
      ],
    ];

    // Filter plugin for Flexible group node access.
    $filter_node_access = [
      'id' => 'flexible_group_node_access',
      'table' => 'node_access',
      'field' => 'flexible_group_node_access',
      'relationship' => 'none',
      'group_type' => 'group',
      'admin_label' => '',
      'operator' => '=',
      'value' => [],
      'group' => 1,
      'exposed' => FALSE,
      'expose' => [
        'operator_id' => '',
        'label' => '',
        'description' => '',
        'use_operator' => FALSE,
        'operator' => '',
        'identifier' => '',
        'required' => FALSE,
        'remember' => FALSE,
        'multiple' => FALSE,
        'remember_roles' => [
          'authenticated' => 'authenticated',
        ],
      ],
      'is_grouped' => FALSE,
      'group_info' => [
        'label' => '',
        'description' => '',
        'identifier' => '',
        'optional' => TRUE,
        'widget' => 'select',
        'multiple' => FALSE,
        'remember' => FALSE,
        'default_group' => 'All',
        'default_group_multiple' => [],
        'group_items' => [],
      ],
      'plugin_id' => 'flexible_group_node_access',
    ];

    foreach ($config_names as $config_name => $displays) {
      if (in_array($config_name, $names)) {
        // Loop through the displays.
        foreach ($displays as $display) {
          $overrides[$config_name]['display'][$display]['display_options']['filters']['flexible_group_node_access'] = $filter_node_access;
        }
      }
    }

    $social_flex_config = $this->configFactory->getEditable('social_flex.settings');
    $flex_group_types = $social_flex_config->get('flexible_group_types');

    $config_names = [
      'search_api.index.social_all',
      'search_api.index.social_groups',
    ];

    $search_api_view_modes = $this->getSearchApiViewModes();

    
    foreach ($config_names as $config_name) {
      if (in_array($config_name, $names)) {
        $overrides[$config_name] = [
          'field_settings' => [
            'rendered_item' => [
              'configuration' => [
                'view_mode' => [
                  'entity:group' => $search_api_view_modes
                ],
              ],
            ],
          ],
        ];
      }
    }
    

    $config_names = [
      'views.view.search_all',
      'views.view.search_groups',
    ];

    foreach ($config_names as $config_name) {
      if (in_array($config_name, $names)) {
        $overrides[$config_name] = [
          'display' => [
            'default' => [
              'display_options' => [
                'row' => [
                  'options' => [
                    'view_modes' => [
                      'entity:group' => $search_api_view_modes
                    ],
                  ],
                ],
              ],
            ],
          ],
        ];
      }
    }

    /*

    $config_names = [
      'views.view.group_members',
      'views.view.group_manage_members',
    ];

    $membership_filters = $this->getGroupViewsFilters();

    foreach ($config_names as $config_name) {
      if (in_array($config_name, $names)) {
        $overrides[$config_name] = [
          'dependencies' => [
            'config' => [
              'flexible_group-group_membership' => 'group.content_type.flexible_group-group_membership',
            ],
          ],
          'display' => [
            'default' => [
              'display_options' => [
                'filters' => [
                  'type' => [
                    'value' => $membership_filters
                  ],
                ],
              ],
            ],
          ],
        ];
      }
    }

    $config_names = [
      'views.view.group_events' => 'flexible_group-group_node-event',
      'views.view.group_topics' => 'flexible_group-group_node-topic',
    ];

    foreach ($config_names as $config_name => $content_type) {
      if (in_array($config_name, $names)) {
        $overrides[$config_name] = [
          'dependencies' => [
            'config' => [
              'group-content-type' => 'group.content_type.' . $content_type,
              'group-type' => 'group.type',
            ],
          ],
          'display' => [
            'default' => [
              'display_options' => [
                'arguments' => [
                  'gid' => [
                    'validate_options' => [
                      'bundles' => [
                        'flexible_group' => 'flexible_group',
                      ],
                    ],
                  ],
                ],
                'filters' => [
                  'type' => [
                    'value' => [
                      $content_type => $content_type,
                    ],
                  ],
                ],
              ],
            ],
          ],
        ];
      }
    }

    $config_name = 'views.view.newest_groups';

    $displays = [
      'page_all_groups',
      'block_newest_groups',
    ];

    if (in_array($config_name, $names)) {
      foreach ($displays as $display_name) {
        $overrides[$config_name] = [
          'display' => [
            $display_name => [
              'cache_metadata' => [
                'contexts' => [
                  'user' => 'user',
                ],
              ],
            ],
          ],
        ];
      }
    }

    $config_name = 'block.block.views_block__group_managers_block_list_managers';

    
    if (in_array($config_name, $names, FALSE)) {
      $overrides[$config_name] = [
        'visibility' => [
          'group_type' => [
            'group_types' => $flex_group_types
          ],
        ],
      ];
    }
    

    $config_name = 'block.block.membershiprequestsnotification';

    
    if (in_array($config_name, $names, FALSE)) {
      $overrides[$config_name] = [
        'visibility' => [
          'group_type' => [
            'group_types' => $flex_group_types
          ],
        ],
      ];
    }
    

    $config_name = 'block.block.membershiprequestsnotification_2';

    
    if (in_array($config_name, $names, FALSE)) {
      $overrides[$config_name] = [
        'visibility' => [
          'group_type' => [
            'group_types' => $flex_group_types
          ],
        ],
      ];
    }

    */
    

    $config_name = 'message.template.create_content_in_joined_group';
    $activity_bundle_entities = $this->getActivityBundleEntities();
    
    if (in_array($config_name, $names, FALSE)) {
      $overrides[$config_name]['third_party_settings']['activity_logger']['activity_bundle_entities'] = $activity_bundle_entities;
    }
    

    $config_name = 'message.template.join_to_group';
    $activity_bundle_entities_membership = $this->getActivityBundleEntitiesMembership();
    
    if (in_array($config_name, $names, FALSE)) {
      $overrides[$config_name]['third_party_settings']['activity_logger']['activity_bundle_entities'] = $activity_bundle_entities_membership;
    }
    

    $config_name = 'message.template.invited_to_join_group';
    $activity_bundle_entities_invitation = $this->getActivityBundleEntitiesInvitation();

    if (in_array($config_name, $names, FALSE)) {
      $overrides[$config_name]['third_party_settings']['activity_logger']['activity_bundle_entities'] = $activity_bundle_entities_invitation;
    }
  


    $config_name = 'message.template.approve_request_join_group';
    
    if (in_array($config_name, $names, FALSE)) {
      $overrides[$config_name]['third_party_settings']['activity_logger']['activity_bundle_entities'] = $activity_bundle_entities_membership;
    }
    
    /*

    $config_name = 'views.view.group_managers';
    $filters = $this->getFiltersForGroupMangersView();
    
    if (in_array($config_name, $names, FALSE)) {
      $overrides[$config_name] = [
        'display' => [
          'default' => [
            'display_options' => [
              'filters' => $filters 
            ],
          ],
        ],
      ];
    }      
    */

    // Add join options to the all-groups and search groups views.
    $filter_overview_join_methods = [
      'id' => 'field_group_allowed_join_method_value',
      'table' => 'group__field_group_allowed_join_method',
      'field' => 'field_group_allowed_join_method_value',
      'relationship' => 'none',
      'group_type' => 'group',
      'admin_label' => '',
      'operator' => 'or',
      'value' => [],
      'group' => 1,
      'exposed' => TRUE,
      'expose' => [
        'operator_id' => 'field_group_allowed_join_method_value_op',
        'label' => 'Join method',
        'description' => '',
        'use_operator' => FALSE,
        'operator' => 'field_group_allowed_join_method_value_op',
        'identifier' => 'field_group_allowed_join_method',
        'required' => FALSE,
        'remember' => FALSE,
        'multiple' => FALSE,
        'remember_roles' => [
          'authenticated' => 'authenticated',
        ],
      ],
      'is_grouped' => FALSE,
      'group_info' => [
        'label' => '',
        'description' => '',
        'identifier' => 'field_group_allowed_join_method',
        'optional' => TRUE,
        'widget' => 'select',
        'multiple' => FALSE,
        'remember' => FALSE,
        'default_group' => 'All',
        'default_group_multiple' => [],
        'group_items' => [],
      ],
      'plugin_id' => 'list_field',
    ];

    $config_names_groups = [
      'views.view.newest_groups' => [
        'default',
        'page_all_groups',
      ],
    ];

    foreach ($config_names_groups as $config_name_groups => $displays_groups) {
      if (in_array($config_name_groups, $names)) {
        foreach ($displays_groups as $display_group) {
          $overrides[$config_name_groups]['display'][$display_group]['display_options']['filters']['field_group_allowed_join_method_value'] = $filter_overview_join_methods;
        }
      }
    }

    // Add join methods as option to search api groups.
    if (in_array('search_api.index.social_groups', $names)) {
      $overrides['search_api.index.social_groups'] = [
        'dependencies' => [
          'config' => [
            'field_storage_group_field_group_allowed_join_method' => 'field.storage.group.field_group_allowed_join_method',
          ],
        ],
        'field_settings' => [
          'field_group_allowed_join_method' => [
            'label' => 'Allowed join method',
            'datasource_id' => 'entity:group',
            'property_path' => 'field_group_allowed_join_method',
            'type' => 'string',
            'dependencies' => [
              'config' => [
                'field_storage_group_field_group_allowed_join_method' => 'field.storage.group.field_group_allowed_join_method',
              ],
            ],
          ],
        ],
      ];
    }

    // Add search api specific filter for join method.
    $filter_sapi_join_methods = [
      'id' => 'field_group_allowed_join_method',
      'table' => 'search_api_index_social_groups',
      'field' => 'field_group_allowed_join_method',
      'relationship' => 'none',
      'group_type' => 'group',
      'admin_label' => '',
      'operator' => 'or',
      'value' => [],
      'group' => 1,
      'exposed' => TRUE,
      'expose' => [
        'operator_id' => 'field_group_allowed_join_method_op',
        'label' => 'Join method',
        'description' => '',
        'use_operator' => FALSE,
        'operator' => 'field_group_allowed_join_method_op',
        'identifier' => 'field_group_allowed_join_method',
        'required' => FALSE,
        'remember' => FALSE,
        'multiple' => FALSE,
        'remember_roles' => [
          'authenticated' => 'authenticated',
        ],
      ],
      'is_grouped' => FALSE,
      'group_info' => [
        'label' => '',
        'description' => '',
        'optional' => TRUE,
        'widget' => 'select',
        'multiple' => FALSE,
        'remember' => FALSE,
        'default_group' => 'All',
        'default_group_multiple' => [],
        'group_items' => [],
      ],
      'plugin_id' => 'search_api_options',
    ];

    if (in_array('views.view.search_groups', $names)) {
      $overrides['views.view.search_groups']['display']['default']['display_options']['filters']['field_group_allowed_join_method'] = $filter_sapi_join_methods;
    }

    return $overrides;
  }

  protected function getFiltersForGroupMangersView() {

    $social_flex_config = $this->configFactory->getEditable('social_flex.settings');
    $group_types = $social_flex_config->get('flexible_group_types');

    $filters = [];
    $role_target_id = 3;
    foreach($group_types as $key => $type) {
      if ($key === $type) {
        $role_target_name = 'group_roles_target_id_' . $role_target_id;
        $filters[$role_target_name] = [
          'id' => $role_target_name,
          'table' => 'group_content__group_roles',
          'field' => 'group_roles_target_id',
          'relationship' => 'group_content',
          'group_type' => 'group',
          'admin_label' => '',
          'operator' => '=',
          'value' => $type . '-group_manager',
          'group' => 1,
          'exposed' => FALSE,
          'expose' => [
            'operator_id' => '',
            'label' => '',
            'description' => '',
            'use_operator' => FALSE,
            'operator' => '',
            'identifier' => '',
            'required' => FALSE,
            'remember' => FALSE,
            'multiple' => FALSE,
            'remember_roles' => [
              'authenticated' => 'authenticated',
            ],
            'placeholder' => '',
          ],
          'is_grouped' => FALSE,
          'group_info' => [
            'label' => '',
            'description' => '',
            'identifier' => '',
            'optional' => TRUE,
            'widget' => 'select',
            'multiple' => FALSE,
            'remember' => FALSE,
            'default_group' => 'All',
            'default_group_multiple' => [],
            'group_items' => [],
          ],
          'plugin_id' => 'string',
        ];
        $role_target_id++;
      }
    }

    return $filters;


  }

  protected function getSearchApiViewModes() {

    $social_flex_config = $this->configFactory->getEditable('social_flex.settings');
    $group_types = $social_flex_config->get('flexible_group_types');

    $view_modes = [];
    foreach($group_types as $key => $type) {
      if ($key === $type) {
        $view_modes[$type] = 'teaser';
      }
    }

    return $view_modes;
    
  }

  protected function getGroupViewsFilters() {

    $social_flex_config = $this->configFactory->getEditable('social_flex.settings');
    $group_types = $social_flex_config->get('flexible_group_types');

    $filters = [];
    foreach($group_types as $key => $type) {
      if ($key === $type) {
        $filter_name = $type . '-group_membership';
        $filters[$filter_name] = $filter_name;
      }
    }

    return $filters;    

  }

  protected function getActivityBundleEntities() {

    $social_flex_config = $this->configFactory->getEditable('social_flex.settings');
    $group_types = $social_flex_config->get('flexible_group_types');

    $bundles = [];
    foreach($group_types as $key => $type) {
      if ($key === $type) {
        $bundle_name_event = 'group_content-' . $type . '-group_node-event';
        $bundle_name_topic = 'group_content-' . $type . '-group_node-topic';
        $bundles[$bundle_name_event] = $bundle_name_event;
        $bundles[$bundle_name_topic] = $bundle_name_topic;
      }
    }

    return $bundles;
  }

  protected function getActivityBundleEntitiesMembership() {

    $social_flex_config = $this->configFactory->getEditable('social_flex.settings');
    $group_types = $social_flex_config->get('flexible_group_types');

    $bundles = [];
    foreach($group_types as $key => $type) {
      if ($key === $type) {
        $bundle_name = 'group_content-' . $type . '-group_membership';
      }
      $bundles[$bundle_name] = $bundle_name;
      
    }

    return $bundles;

  }

  protected function getActivityBundleEntitiesInvitation() {

    $social_flex_config = $this->configFactory->getEditable('social_flex.settings');
    $group_types = $social_flex_config->get('flexible_group_types');

    $bundles = [];
    foreach($group_types as $key => $type) {
      if ($key === $type) {
        $bundle_name = 'group_content-' . $type . '-group_invitation';
      }
      $bundles[$bundle_name] = $bundle_name;
      
    }

    return $bundles;

  }  

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'SocialFlexConfigOverride';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

}
