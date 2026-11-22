<?php

declare(strict_types=1);

/** English UI texts. Keys must match config/lang/pl.php exactly (enforced by TranslationKeysTest). */
return [
    // Layout, navigation, footer
    'app.name' => 'ApexSteward',
    'app.skip_to_content' => 'Skip to content',
    'app.brand_aria' => 'F1 Stewards, home page',
    'app.brand_title' => 'F1 STEWARDS',
    'app.brand_tagline' => 'Incident Management Portal',
    'app.footer_left' => 'ApexSteward · FIA Stewards Hub',
    'app.footer_right' => 'Adjudication Console',
    'nav.main_aria' => 'Main navigation',
    'nav.mobile_aria' => 'Mobile navigation',
    'nav.incidents' => 'Incidents',
    'nav.standings' => 'Standings',
    'nav.new_protest' => 'New protest',
    'nav.logout' => 'Log out',
    'nav.language_aria' => 'Interface language',
    'theme.toggle_label' => 'Dark theme',
    'theme.toggle_title' => 'Change theme',
    'role.TEAM_PRINCIPAL' => 'Team Principal',
    'role.FIA_STEWARD' => 'FIA Steward',
    'pagination.aria' => 'Pagination',
    'pagination.prev' => 'Previous',
    'pagination.next' => 'Next',

    // Error pages
    'error.eyebrow' => 'SYSTEM MESSAGE / :status',
    'error.back_home' => 'Back to the home page',
    'error.400.title' => 'Bad request',
    'error.400.message' => 'The address or the request data is invalid. Check the link and try again.',
    'error.401.title' => 'Login required',
    'error.401.message' => 'You need to log in to see this page.',
    'error.403.title' => 'Access denied',
    'error.403.message' => 'Your role does not allow this operation.',
    'error.404.title' => 'Page not found',
    'error.404.message' => 'The page does not exist or has been moved. Return to race control and pick another section.',
    'error.422.title' => 'Request cannot be processed',
    'error.422.message' => 'The data is syntactically correct but cannot be accepted.',
    'error.500.title' => 'Server error',
    'error.500.message' => 'Something went wrong on our side. Please try again in a moment.',

    // Page titles
    'incidents.page_title' => 'Incidents log',
    'report.page_title' => 'New protest',
    'standings.page_title' => 'Super Licence points standings',
    'adjudicate.page_title' => 'Ruling for :code',

    // Login
    'login.page_title' => 'Log in',
    'login.eyebrow' => 'FIA / RACE CONTROL',
    'login.heading' => 'Log in to the console',
    'login.lead' => 'Enter your details to continue.',
    'login.email' => 'E-mail address',
    'login.email_placeholder' => 'e.g. toto@mercedes.f1',
    'login.password' => 'Password',
    'login.password_placeholder' => 'Enter your password',
    'login.submit' => 'Log in',
    'login.invalid_credentials' => 'Invalid e-mail or password',

    // Statuses
    'status.REPORTED' => 'Reported',
    'status.UNDER_INVESTIGATION' => 'Under investigation',
    'status.PENALTY_ISSUED' => 'Penalty issued',
    'status.NO_FURTHER_ACTION' => 'No further action',
    'licence_status.SUSPENDED' => 'Suspended',
    'licence_status.AT_BAN_RISK' => 'Ban risk',
    'licence_status.CAUTION' => 'Caution',
    'licence_status.ACTIVE' => 'Active',
    'licence_status.CLEAN' => 'Clean',
];
