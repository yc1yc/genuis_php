<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/stripe.php';
require_once '../includes/functions.php';
require_once '../includes/cart.php';

ensureSessionStarted();
header('Content-Type: application/json');

try {
    // Vérification du jeton CSRF
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        throw new Exception('Token de sécurité invalide');
    }

    // Vérification du panier
    $cart = getCart();
    if (empty($cart)) {
        throw new Exception('Votre panier est vide');
    }

    // Création de la session de paiement Stripe
    $lineItems = [];
    foreach ($cart as $item) {
        $lineItems[] = [
            'price_data' => [
                'currency' => STRIPE_CURRENCY,
                'unit_amount' => round($item['total_price'] * 100), // Stripe utilise les centimes
                'product_data' => [
                    'name' => $item['vehicle_name'],
                    'description' => "Location du {$item['start_date']} au {$item['end_date']}",
                    'images' => [$item['image_url']],
                ],
            ],
            'quantity' => 1,
        ];

        // Ajout des options comme des lignes séparées si présentes
        if (!empty($item['options'])) {
            foreach ($item['options'] as $option) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => STRIPE_CURRENCY,
                        'unit_amount' => round($option['price'] * 100),
                        'product_data' => [
                            'name' => "Option: {$option['name']}",
                            'description' => "Pour {$item['vehicle_name']}",
                        ],
                    ],
                    'quantity' => 1,
                ];
            }
        }
    }

    // Création de la session de paiement
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => STRIPE_PAYMENT_METHODS,
        'line_items' => $lineItems,
        'mode' => 'payment',
        'success_url' => STRIPE_SUCCESS_URL,
        'cancel_url' => STRIPE_CANCEL_URL,
        'locale' => STRIPE_LOCALE,
        'customer_email' => $_POST['email'] ?? null,
        'metadata' => [
            'cart_id' => session_id(),
            'total_amount' => getCartTotal()
        ]
    ]);

    echo json_encode(formatSuccess('Session de paiement créée', [
        'sessionId' => $session->id,
        'publicKey' => STRIPE_PUBLIC_KEY
    ]));

} catch (Exception $e) {
    echo json_encode(formatError($e->getMessage()));
}
