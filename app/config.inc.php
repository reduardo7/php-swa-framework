<?php
/**
 * Application configurations.
 */

// Production values
$GLOBALS[AppConfig] = array(
    // MySQL
    AppConfigCnxDb                     => '',
    AppConfigCnxUser                   => '',
    AppConfigCnxPass                   => '',
    AppConfigCnxServer                 => 'localhost',
    // e-Mail Send
    AppConfigMailUser                  => '',
    AppConfigMailPass                  => '',
    AppConfigMailCopyTo                => '',
    AppConfigMailCopyToName            => '',
    AppConfigMailPort                  => 25,
    AppConfigMailHost                  => 'mail.eduardocuomo.com.ar',
    AppConfigMailSmtpAuth              => true,
    // Web Page
    AppConfigWebTitle                  => 'Web Title'
);

// Development
if (ApplicationEnvIsDevelopment) {
    $GLOBALS[AppConfig][AppConfigCnxDb]     = '';
    $GLOBALS[AppConfig][AppConfigCnxUser]   = '';
    $GLOBALS[AppConfig][AppConfigCnxPass]   = '';
    $GLOBALS[AppConfig][AppConfigCnxServer] = 'localhost';
}

// APP Configs

/**
 * Login configuration.
 * Array with pages where not check login status (isValidPage returns TRUE).
 *
 * @uses
 * array(
 *     '[CONTEXT]/[VIEW]',
 *     ...
 * )
 * Use "*" as ALL selector (for CONTEXT or VIEW).
 *
 * @example
 * array(
 *     '*\/login',
 *     'admin/login'
 * )
 *
 * @var array
 *
 * @see Login::isValidPage()
 */
$GLOBALS[AppConfig][AppConfigLoginNoValidatePages] = array('admin/login', 'admin/db-update', 'admin/recuperar');


// Custom configs
