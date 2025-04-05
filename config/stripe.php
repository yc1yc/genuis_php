<?php
// Clés Stripe (à remplacer par vos clés réelles)
define('STRIPE_PUBLIC_KEY', 'pk_test_votre_cle_publique');
define('STRIPE_SECRET_KEY', 'sk_test_votre_cle_secrete');

// Configuration Stripe
require_once __DIR__ . '/../vendor/autoload.php';
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Configuration du webhook (à définir dans votre dashboard Stripe)
define('STRIPE_WEBHOOK_SECRET', 'whsec_votre_cle_webhook');

// Configuration des devises
define('STRIPE_CURRENCY', 'eur');
define('STRIPE_CURRENCY_SYMBOL', '€');

// Configuration des paiements
define('STRIPE_PAYMENT_DESCRIPTION', 'Réservation de véhicule - The Genuis');
define('STRIPE_STATEMENT_DESCRIPTOR', 'THE GENUIS CAR');

// Paramètres de paiement par défaut
define('STRIPE_PAYMENT_METHODS', ['card']);
define('STRIPE_LOCALE', 'fr');

// URLs de redirection
define('STRIPE_SUCCESS_URL', SITE_URL . '/confirmation.php?session_id={CHECKOUT_SESSION_ID}');
define('STRIPE_CANCEL_URL', SITE_URL . '/cart.php?payment=cancelled');
