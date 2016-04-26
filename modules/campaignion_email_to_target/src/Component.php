<?php

namespace Drupal\campaignion_email_to_target;

use \Drupal\little_helpers\Webform\FormState;
use \Drupal\little_helpers\Webform\Submission;
use \Drupal\little_helpers\Webform\Webform;
use \Drupal\campaignion_action\Loader;

use \Drupal\campaignion_email_to_target\Api\Client;

class Component {
  protected $component;
  protected $payment = NULL;
  public function __construct(array $component) {
    $this->component = $component;
  }

  /**
   * Get a list of parent form keys for this component.
   *
   * @return array
   *   List of parent form keys - just like $element['#parents'].
   */
  public function parents($webform) {
    $parents = array($this->component['form_key']);
    $parent = $this->component;
    while ($parent['pid'] != 0) {
      $parent = $webform->component($parent['pid']);
      array_unshift($parents, $parent['form_key']);
    }
    return $parents;
  }

  /** 
   * Render the webform component.
   */
  public function render(&$element, &$form, &$form_state) {
    // Get list of targets for this node.
    $node = node_load($this->component['nid']);
    $webform = new Webform($node);
    $action = Loader::instance()->actionFromNode($node);
    $options = $action->getOptions();
    $submission_o = $webform->formStateToSubmission($form_state);

    $postcode = str_replace(' ', '', $submission_o->valueByKey('postcode'));
    $test_mode = !empty($form_state['test_mode']);
    $email = $submission_o->valueByKey('email');

    $element = [
      '#type' => 'fieldset',
      '#theme' => 'campaignion_email_to_target_selector_component',
    ] + $element + [
      '#type' => 'fieldset',
      '#title' => $this->component['name'],
      '#description' => $this->component['extra']['description'],
      '#tree' => TRUE,
      '#element_validate' => array('campaignion_email_to_target_selector_validate'),
      '#cid' => $this->component['cid'],
    ];

    if ($test_mode) {
      $element['test_mode'] = [
        '#prefix' => '<p class="test-mode-info">',
        '#markup' => t('Test-mode is active: All emails will be sent to %email.', ['%email' => $email]),
        '#suffix' => '</p>',
      ];
    }

    $element['#attributes']['class'][] = 'email-to-target-selector-wrapper';
    $element['#attributes']['class'][] = 'webform-prefill-exclude';
    try {
      $api = Client::fromConfig();
      $targets = $api->getTargets($options['dataset_name'], $postcode);

      if (!empty($targets)) {
        $last_id = NULL;
        foreach ($targets as $target) {
          if (!empty($test_mode)) {
            $target['email'] = $email;
          }
          $message = $action->getMessage();
          $message->replaceTokens($target, $submission_o->unwrap());
          $t = [
            '#type' => 'container',
            '#attributes' => ['class' => ['email-to-target-target']],
            '#target' => $target,
            '#message' => $message->toArray(),
          ];
          $t['send'] = [
            '#type' => 'checkbox',
            '#title' => $target['salutation'],
            '#default_value' => TRUE,
          ];
          $t['subject'] = [
            '#type' => 'textfield',
            '#title' => t('Subject'),
            '#default_value' => $message->subject,
            '#disabled' => empty($options['users_may_edit']),
          ];
          $t['header'] = [
            '#prefix' => '<pre class="email-to-target-header">',
            '#markup' => $message->header,
            '#suffix' => '</pre>',
          ];
          $t['message'] = [
            '#type' => 'textarea',
            '#title' => t('Message'),
            '#default_value' => $message->message,
            '#disabled' => empty($options['users_may_edit']),
          ];
          $t['footer'] = [
            '#prefix' => '<pre class="email-to-target-footer">',
            '#markup' => $message->footer,
            '#suffix' => '</pre>',
          ];
          $element[$target['id']] = $t;
          $last_id = $target['id'];
        }
        if (count($targets) == 1) {
          $c = &$element[$last_id];
          $c['#attributes']['class'][] = 'email-to-target-single';
          $c['send']['#type'] = 'markup';
          $c['send']['#markup'] = "<p class=\"target\">{$c['send']['#title']}</p>";
        }
        else {
          $element['#attached']['js'] = [drupal_get_path('module', 'campaignion_email_to_target') . '/js/target_selector.js'];
        }
      }
      else {
        watchdog('campaignion_email_to_target', 'The API found no targets (dataset=@dataset, postcode=@postcode).', [
          '@dataset' => $options['dataset_name'],
          '@postcode' => $postcode,
        ], WATCHDOG_WARNING);
        $element['no_target'] = [
          '#markup' => t("There don't seem to be any targets for your selection."),
        ];
        $element['#attributes']['class'][] = 'email-to-target-no-targets';
      }
    }
    catch (\Exception $e) {
      watchdog_exception('campaignion_email_to_target', $e);
      $element['#title'] = t('Service temporary unavailable');
      $element['error'] = [
        '#markup' => t('We are sorry! The service is temporary unavailable. The administrators have been informed. Please try again in a few minutes …'),
      ];
      $element['#attributes']['class'][] = 'email-to-target-error';
    }
  }

  public function validate(array $element, array &$form_state) {
    $values = &drupal_array_get_nested_value($form_state['values'], $element['#parents']);

    $original_values = $values;
    $values = [];
    $only_one = count($original_values) == 1;
    foreach ($original_values as $id => $edited_message) {
      if (!empty($edited_message['send']) || $only_one) {
        $e = &$element[$id];
        $values[] = serialize($edited_message + $e['#message']);
      }
    }
    if (empty($values)) {
      form_error($element, t('Please select at least one target'));
    }
  }

  public function sendEmails($data, $submission) {
    $nid = $submission->nid;
    $node = $submission->webform->node;
    $root_node = $node->tnid ? node_load($node->tnid) : $node;
    $send_count = 0;

    foreach ($data as $serialized) {
      $m = unserialize($serialized);
      $message = new Message($m);
      $message->replaceTokens(NULL, $submission->unwrap());
      unset($m);

      // Set the HTML property based on availablity of MIME Mail.
      $email['html'] = FALSE;
      // Pass through the theme layer.
      $t = 'campaignion_email_to_target_mail';
      $theme_d = ['message' => $message, 'submission' => $submission];
      $email['message'] = theme([$t, $t . '_' . $nid], $theme_d);

      $email['from'] = $message->from;
      $email['subject'] = $message->subject;

      $email['headers'] = [
        'X-Mail-Domain' => variable_get('site_mail_domain', 'supporter.campaignion.org'),
        'X-Action-UUID' => $root_node->uuid,
      ];

      // Verify that this submission is not attempting to send any spam hacks.
      if (_webform_submission_spam_check($message->to, $email['subject'], $email['from'], $email['headers'])) {
        watchdog('campaignion_email_to_target', 'Possible spam attempt from @remote !message',
                array('@remote' => ip_address(), '!message'=> "<br />\n" . nl2br(htmlentities($email['message']))));
        drupal_set_message(t('Illegal information. Data not submitted.'), 'error');
        return FALSE;
      }

      $language = $GLOBALS['language'];
      $mail_params = array(
        'message' => $email['message'],
        'subject' => $email['subject'],
        'headers' => $email['headers'],
        'submission' => $submission,
        'email' => $email,
      );

      // Mail the submission.
      $m = drupal_mail('campaignion_email_to_target', 'email_to_target', $message->to, $language, $mail_params, $email['from']);
      if ($m['result']) {
        $send_count += 1;
      }
    }
  }
}
