<?php
/**
 * @file
 * campaignion_thankyou_page.field_group.inc
 */

/**
 * Implements hook_field_group_info().
 */
function campaignion_thankyou_page_field_group_info() {
  $export = array();

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_advanced_settings|node|thank_you_page|form';
  $field_group->group_name = 'group_advanced_settings';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'thank_you_page';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Advanced settings',
    'weight' => '5',
    'children' => array(
      0 => 'path',
      1 => 'xmlsitemap',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'label' => 'Advanced settings',
      'instance_settings' => array(
        'required_fields' => 1,
        'classes' => 'group-advanced-settings field-group-fieldset field-group-heading field-group-no-label',
        'description' => '',
      ),
      'formatter' => 'open',
    ),
  );
  $export['group_advanced_settings|node|thank_you_page|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_content|node|thank_you_page|form';
  $field_group->group_name = 'group_content';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'thank_you_page';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Content',
    'weight' => '3',
    'children' => array(
      0 => 'body',
      1 => 'field_main_image',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'label' => 'Content',
      'instance_settings' => array(
        'required_fields' => 1,
        'classes' => 'group-content field-group-fieldset field-group-heading',
        'description' => '',
      ),
      'formatter' => 'open',
    ),
  );
  $export['group_content|node|thank_you_page|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_share_block|node|thank_you_page|form';
  $field_group->group_name = 'group_share_block';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'thank_you_page';
  $field_group->mode = 'form';
  $field_group->parent_name = 'group_share';
  $field_group->data = array(
    'label' => 'Share block',
    'weight' => '10',
    'children' => array(
      0 => 'share_light',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'label' => 'Share block',
      'instance_settings' => array(
        'required_fields' => 1,
        'classes' => 'group-share-block field-group-fieldset',
        'description' => '',
      ),
      'formatter' => 'collapsed',
    ),
  );
  $export['group_share_block|node|thank_you_page|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_share|node|thank_you_page|form';
  $field_group->group_name = 'group_share';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'thank_you_page';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Share options',
    'weight' => '4',
    'children' => array(
      0 => 'group_share_block',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'label' => 'Share options',
      'instance_settings' => array(
        'required_fields' => 1,
        'classes' => 'group-share field-group-fieldset field-group-heading',
        'description' => '',
      ),
      'formatter' => 'open',
    ),
  );
  $export['group_share|node|thank_you_page|form'] = $field_group;

  return $export;
}
