<?php

namespace Drupal\example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Egulias\EmailValidator;
use Egulias\EmailValidator\Validation;

/**
 * Implements an example form.
 */
class ExampleForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'example_form';
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
      
      //первая валидация почты по типу в браузере
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

   //Вторая валидация от сервера.. 
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('client_mail') !== filter_var($form_state->getValue('client_mail'), FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('client_mail', $this->t('The Email is wrong. Please enter a correct Email.'));
    }

    
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
   

    // $mailManager = \Drupal::service('plugin.manager.mail');
    // $langcode = \Drupal::currentUser()->getPreferredLangcode();
    // $params['context']['subject'] = "$form_state->getValue('subject')";
    // $params['context']['message'] = "$form_state->getValue('message')";
    // $to = "YOUR_MAIL@MAIL.COM";
    // $mailManager->mail('example', 'mail', $to, $langcode, $params);
  


    //почту на локалке не получилось проверить, не знаю что случилось
mail("Здесь почта hubspota, привязанная к inbox ящику в hubspote","$form_state->getValue('subject')","$form_state->getValue('message')");

//mail("waytoprofi@gmail.coYOUR_MAIL@MAIL.COM", "My Subject Super", "Hello! How do you do?");


//create contact - создаем контакт в hubspot
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

    //API KEY https://knowledge.hubspot.com/integrations/how-do-i-get-my-hubspot-api-key
    $hapikey = 'Здесь нужен API key hubspota, напоминание где взять https://knowledge.hubspot.com/integrations/how-do-i-get-my-hubspot-api-key';
    
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

    // Для удобства ответ от hubspot 
   // drupal_set_message("curl Errors: $curl_errors", 'status');
    drupal_set_message("Status code: $status_code", 'status');
    drupal_set_message("Response: $response", 'status');
    
    //log
   file_put_contents('modules/example/log.txt',"Почта отправлена. Почта клиента {$form_state->getValue('client_mail')}\r\n",FILE_APPEND);

}

}
