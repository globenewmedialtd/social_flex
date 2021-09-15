# Social Flex

This module provides handling for custom flexible groups. To make it work you must have the following existing fields attached to it:
- field_group_allowed_visibility
- field_flexible_group_visibility
- field_group_allowed_join_method

Please also create the same field groups! Have a look at social_group_flexible_group for the required field groups. 

## Installation

Install like any other module.

## Configuration

Your custom flexible groups will be detected automatically at admin/config/opensocial/social-flex.
Please tick them to activate the flexible group logic.

At the moment due to segmentation fault you need still a module for any custom flexible group type you create.
But the module just need a .info and .module file. The module file has to include the following hook:

``
function YOURMODULE_social_group_types_alter(array &$social_group_types) {

  $social_group_types[] = 'GROUP_TYPE'

}

``


