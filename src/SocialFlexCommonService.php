<?php

namespace Drupal\social_flex;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\group\Entity\GroupType;


/**
 * Helper service
 */
class SocialFlexCommonService {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;  

  /**
   * SocialFlexCommonService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager. 
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityFieldManagerInterface $entity_field_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
  }  


  /**
   * Detect if we have a Flesible Group
   */
  public function isFlexibleGroup(GroupInterface $group) {

    $field_group_allowed_visibility  = $group->hasField('field_group_allowed_visibility');
    $field_flexible_group_visibility = $group->hasField('field_flexible_group_visibility');
    $field_group_allowed_join_method = $group->hasField('field_group_allowed_join_method');

    if ($field_group_allowed_visibility &&
        $field_flexible_group_visibility && 
        $field_group_allowed_join_method) {

        return TRUE;

    }
    
    return FALSE;  
  
  }

  /**
   * Return all Flexible group machine names
   */
  public function getFlexibleGroups() {

    $group_types = [];
    /** @var \Drupal\group\Entity\GroupType $group_type */
    //$group_type_storage = $this->entityTypeManager->getStorage('group_type');
    //$test = GroupType::loadMultiple();
    /*
    foreach (GroupType::loadMultiple() as $group_type) {
      $group_id = $group_type->id();
      if ($this->detectFlexibleGroupFields($group_id)) {
        // Forms have a dash instead of an underscore        
        $group_types[] = $group_id;
      }      
    }
    */

    $group_types[] = 'super';

    return $group_types;

  }

  /**
   * Return all Flexible group form_id
   */
  public function getFlexibleGroupForms() {

    $group_forms = [];
    /** @var \Drupal\group\Entity\GroupType $group_type */

    //$group_type_storage = $this->entityTypeManager->getStorage('group_type');
    foreach (GroupType::loadMultiple() as $group_type) {
      if ($this->detectFlexibleGroupFields($group_type->id())) {
        $group_id = $group_type->id();
        //$form_id = str_replace('_','-',$group_type->id());
        $group_forms['edit'][] = "group_{$group_id}_edit_form";
        $group_forms['add'][] = "group_{$group_id}_add_form";

      }      
    }

    return $group_forms;

  }

  protected function detectFlexibleGroupFields($group_type) {
    $all_bundle_fields = $this->entityFieldManager->getFieldDefinitions('group', $group_type);
    if (isset($all_bundle_fields['field_group_allowed_visibility']) &&
        isset($all_bundle_fields['field_flexible_group_visibility']) &&
        isset($all_bundle_fields['field_group_allowed_join_method'])
    ) {
      return TRUE;
    }    
    return FALSE;
  }




}













