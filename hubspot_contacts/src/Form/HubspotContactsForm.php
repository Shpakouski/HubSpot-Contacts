<?php

namespace Drupal\hubspot_contacts\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements an hubspot contacts form.
 */
class HubspotContactsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hubspot_contacts_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
    ];

    $form['last_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Last Name'),
      ];

      $form['subject'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Subject'),
      ];

      $form['message'] = [
        '#type' => 'text_format',
        '#title' => $this->t('Message'),
      ];
      
      $form['client_mail'] = [
        '#type' => 'email',
        '#title' => $this->t('Email'),
      ]; 



    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */

 
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('client_mail') !== filter_var($form_state->getValue('client_mail'), FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('client_mail', $this->t('The Email is wrong. Please enter a correct Email.'));
    }

    
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    mail("YOUR_MAIL@gmail.com",$form_state->getValue('subject'),strip_tags($form_state->getValue('message')['value']));
    $arr = array(
    'properties' => array(
            
            array(
                'property' => 'email',
                'value' => $form_state->getValue('client_mail'),
            ),
            array(
                'property' => 'firstname',
                'value' => $form_state->getValue('first_name'),
            ),
            array(
                'property' => 'lastname',
                'value' => $form_state->getValue('last_name'),
            ),
        )
    );
    $post_json = json_encode($arr);
    $hapikey = 'YOUR_API_KEY';
    $endpoint = 'https://api.hubapi.com/contacts/v1/contact?hapikey=' . $hapikey;
    $ch = @curl_init();
    @curl_setopt($ch, CURLOPT_POST, true);
    @curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
    @curl_setopt($ch, CURLOPT_URL, $endpoint);
    @curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = @curl_exec($ch);
    $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_errors = curl_error($ch);
    @curl_close($ch);
    echo "curl Errors: " . $curl_errors;
    echo "\nStatus code: " . $status_code;
    echo "\nResponse: " . $response;
    drupal_set_message('Mail has been sent.', 'status');
    file_put_contents('modules/hubspot_contacts/log.txt',"Mail has been sent. Client mail is {$form_state->getValue('client_mail')}\r\n",FILE_APPEND);

}

}
