<?php
// $Id: signup_form.inc,v 1.3.2.3 2010/12/28 17:28:47 ezrag Exp $


/**
 * @file
 * Site-specific code related to the form when users signup for a node.
 */

/**
 * Return the site-specific custom fields for the signup user form.
 *
 * To customize this for your site, copy this entire function into
 * your theme's template.php file, rename the function to
 * phptemplate_signup_user_form(), and modify to taste.  Feel free to
 * alter any elements in this section, remove them, or add any others.
 *
 * WARNING: If your site allows anonymous signups and you alter the
 * 'Name' field in this function, you will probably have to implement a
 * version of theme_signup_anonymous_username() for your site.
 *
 * In order for the form to be rendered properly and for the custom
 * fields to be fully translatable when printed in other parts of the
 * Signup module (displayed in signup lists, emails, etc), the name of
 * the form element must be $form['signup_form_data']['NameOfDataField'],
 * where NameOfDataField is replaced with the actual name of the data
 * field.  For translation to work, the displayed name of the field
 * (the '#title' property) be the same as the name of the data field,
 * but wrapped in t().  See below for examples.
 *
 * Fieldsets are not currently supported in this form.  Any
 * '#default_value' will be filled in by default when the form is
 * presented to the user.  Any field marked '#required' must be filled
 * in before the user can sign up.
 *
 * If you do not want any additional fields, the function can simply
 * return an empty array: "return array();"
 *
 * @param $node
 *   The fully loaded node object where this signup form is appearing.
 *
 * @return
 *   Array defining the form to present to the user to signup for a node.
 *
 * @see theme_signup_anonymous_username()
 */
function theme_signup_user_form($node) {
  global $user;
  $form = array();
  if (variable_get('signup_ignore_default_fields', 0)) {
    return $form;
  }
  // If this function is providing any extra fields at all, the following
  // line is required for form form to work -- DO NOT EDIT OR REMOVE.
  $form['signup_form_data']['#tree'] = TRUE;

  $form['signup_form_data']['class_type'] = array(
    '#type'        => 'radios',
    '#title'       => t('Which are you registering for'),
    '#description' => t('Users with the <em>administer nodes</em> permission will be able to override these options.'),
    '#weight' =>   0,
    '#options'     => array( 
        'group'     => t('6 Week Series Group Class'), 
        'immersion' => t('Weekend Immersion Group Class'),
        'private'   => t('Private Session')), 
  );

  $form['signup_form_data']['location'] = array(
    '#type'      => 'textfield',
    '#title'     => t('Location'),
    '#size'      => 40, 
    '#maxlength' => 64,
    '#required'  => FALSE,
    '#weight'    => 1,
  );

  $form['signup_form_data']['MothersName'] = array(
    '#type'      => 'textfield',
    '#title'     => t('Expectant Mother\'s Full Name'),
    '#size'      => 40, 
    '#maxlength' => 64,
    '#weight'    => 2,
  );


  $form['signup_form_data']['partner'] = array( 
    '#type'   => 'markup',
    '#value'  => '<h3>About Your Partner</h3>',
    '#weight' => 3,
  );

  $form['signup_form_data']['FathersName'] = array(
    '#type'      => 'textfield',
    '#title'     => t('Partner or Father\'s Name'),
    '#size'      => 40, 
    '#maxlength' => 64,
    '#weight'    => 4,
  );

  $form['signup_form_data']['FathersEmail'] = array(
    '#type'      => 'textfield',
    '#title'     => t('Partner or Father\'s email'),
    '#size'      => 40, 
    '#maxlength' => 64,
    '#weight'    => 4,
  );

  $form['signup_form_data']['Phone'] = array(
    '#type'      => 'textfield',
    '#title'     => t('Home Phone Number'),
    '#size'      => 40, 
    '#maxlength' => 64,
    '#weight'    => 5,
  );

  $form['signup_form_data']['cellPhone'] = array(
    '#type'      => 'textfield',
    '#title'     => t('Cell Phone Number'),
    '#size'      => 40, 
    '#maxlength' => 64,
    '#weight'    => 6,
  );

  $form['signup_form_data']['partnerCellPhone'] = array(
    '#type'      => 'textfield',
    '#title'     => t('Your Partner\'s Cell Phone'),
    '#size'      => 40, 
    '#maxlength' => 64,
    '#weight'    => 7,
  );

  $form['signup_form_data']['mailingAddress'] = array(
    '#type'   => 'textarea',
    '#title'  => t('Mailing Address'),
    '#rows'   => 4,
    '#weight' => 8,
  );

  $form['signup_form_data']['pregnancy'] = array( 
    '#type'   => 'markup',
    '#value'  => t('<h3>About Your Pregnancy</h3>'),
    '#weight' => 9,
  );

  $form['signup_form_data']['dueDate'] = array(
    '#type'      => 'textfield',
    '#title'     => t('Approximate Due Date'),
    '#size'      => 40, 
    '#maxlength' => 64,
    '#weight'    => 10,
  );

  $form['signup_form_data']['firstPregnancy'] = array(
    '#type'    => 'radios',
    '#title'   => t('Is this your first pregnancy?'),
    '#options' => array( 'yes' => t('Yes'), 
                          'no' => t('No'),
    ),
    '#weight'    => 11,
  );

  $form['signup_form_data']['children'] = array(
    '#type'   => 'textarea',
    '#title'  => t('Number and ages of current children'),
    '#rows'   => 4,
    '#weight' => 12,
  );

  $form['signup_form_data']['birthLocation'] = array(
    '#type'      => 'textfield',
    '#title'     => t('Where do you plan to give birth?'),
    '#size'      => 40, 
    '#maxlength' => 64,
    '#weight'    => 12,
  );

  $form['signup_form_data']['drMidWife'] = array(
    '#type'      => 'textfield',
    '#title'     => t('Who is your midwife or doctor?'),
    '#size'      => 40, 
    '#maxlength' => 64,
    '#weight'    => 13,
  );

  $form['signup_form_data']['doula'] = array(
    '#type'      => 'textfield',
    '#title'     => t('Are you using a doula, who is it?'),
    '#size'      => 40, 
    '#maxlength' => 64,
    '#weight'    => 14,
  );

  $form['signup_form_data']['doulaInfo'] = array(
    '#type'    => 'radios',
    '#title'   => t('If you are not using a doula, would you like information on our doula support?'),
    '#options' => array( 'yes'   => t('Yes'), 'no' => t('No'),
    '#weight'  => 15,
    ),
  );

  $form['signup_form_data']['additional'] = array( 
    '#markup' => 'fieldset',
    '#value'  => t('Additional Information'),
    '#weight'  => 16,
  );

  $form['signup_form_data']['howDidYouHear'] = array(
    '#type'      => 'textfield',
    '#title'     => t('How did you hear about Rhythm of Birth Classes?'),
    '#size'      => 40, 
    '#maxlength' => 64,
    '#weight'    => 17,
  );

  $form['signup_form_data']['otherClasses'] = array(
    '#type'   => 'textarea',
    '#title'  => t('Are you taking any other childbirth, newborn, or breastfeeding classes or have you taken any in the past?  If so, which one (s)?'),
    '#rows'   => 4,
    '#weight' => 18,
  );
 
  $form['signup_form_data']['breastfeeding'] = array(
    '#type'    => 'radios',
    '#title'   => t('Are you planning on breastfeeding?'),
    '#options' => array( 'yes' => t('Yes'), 
                          'no' => t('No'),
    ),
    '#weight' => 19,
  );

  $form['signup_form_data']['otherInfo'] = array(
    '#type'   => 'textarea',
    '#title'  => t('Any special requirements or accommodations needed for you or your partner?'),
    '#rows'   => 4,
    '#weight' => 20,
  );

  $form['signup_form_data']['payment'] = array( 
    '#type'        => 'fieldset',
    '#title'       => t('Fees and Payment information'),
    '#collapsible' => FALSE,
    '#collapsed'   => FALSE,
    '#weight'      => 21,
  );

  $form ['signup_form_data']['payment']['paymentInfo'] = array(
    '#type'  => 'markup',
    '#value' => ' My fee is $200 per couple for a 6 week series class or a weekend immersion class. Private class fees are discussed individually.  I take cash or check. Please make checks payable to <u>Rhythm of Birth</u>.  A non-refundable deposit of $50 is due at time of registration. The balance payment of $150 must be paid at or before the first class. ',
  );

  $form['signup_form_data']['refund'] = array( 
    '#type'        => 'fieldset',
    '#title'       => t('Refunds'),
    '#collapsible' => FALSE,
    '#collapsed'   => FALSE,
    '#description' => t(''),
    '#weight'      => 22,
  );


  $form ['signup_form_data']['refund']['refund'] = array(
    '#type'  => 'markup',
    '#title' => t('Refunds'),
    '#value' => 'There are no refunds in the case of absence of a class for any reason.  We can discuss how you can get the information that was missed.',
  );

  $form['signup_form_data']['cancellation'] = array( 
    '#type'        => 'fieldset',
    '#title'       => t('Cancellation'),
    '#collapsible' => FALSE,
    '#collapsed'   => FALSE,
    '#description' => t(''),
    '#weight'      => 23,
  );

  $form ['signup_form_data']['cancellation']['cancellation'] = array(
    '#type'  => 'markup',
    '#title' => t('Cancellation'),
    '#value' => 'In the event I need to cancel a class (ie. attending a birth, illness, family emergency etc.), I will schedule a make-up class for the group.',
  );

  $form['signup_form_data']['attendingClass'] = array(  
    '#type'        => 'fieldset',
    '#title'       => t('Attending Class'),
    '#collapsible' => FALSE,
    '#collapsed'   => FALSE,
    '#description' => t(''),
    '#weight'      => 24,
  );

  $form['signup_form_data']['attendingClass']['attendingClass'] = array(
    '#type'  => 'markup',
    '#value' => 'Please arrive to class on time and dress comfortably.  Please turn off cell phones upon entering class.  Light refreshments will be offered.  Please advise of any dietary restrictions.',
  );

  // If the user is logged in, fill in their name by default.
  if ($user->uid) {
    $form['signup_form_data']['Name']['#default_value'] = $user->name;
  }

  return $form;
}

/**
 * Returns the value to use for the user name for anonymous signups.
 *
 * WARNING: If you implemented your own version of theme_signup_form_data()
 * that changed or removed the custom 'Name' field and your site
 * allows anonymous signups, you will need to modify this, too.
 *
 * This value is used for the %user_name email token for anonymous users, and
 * also to identify a particular anonymous signup in various places in the UI.
 *
 * @param $form_data
 *   Array of custom signup form values for the current signup.
 * @param $email
 *   E-mail address of the anonymous user who signed up.
 * @return
 *   A string with the proper value for the %user_name email token.
 *
 * @see theme_signup_user_form()
 */
function theme_signup_anonymous_username($form_data, $email) {
  // In some cases, the best you can do is to use the anonymous user's
  // supplied email address, in which case, you should uncomment this:
  //return $email;

  // WARNING: This line is only valid if you left the 'Name' field in
  // your site's version of theme_signup_user_form().
  return $form_data['Name'];
}

