<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
    #[Route('/checkout', name: 'app_stripe_checkout')]
    public function checkout( Request $request): Response
    {

        // Définir la clé secrète de Stripe
        \Stripe\Stripe::setApiKey($this->getParameter('app.stripe_key'));
        $products = $request->getSession()->get('cart')
;
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'currency' => 'eur',
            'line_items' => [
                $products
                // [
                //     'price' => "price_1OxtSwEidB4Fdu1Ilrdbfwur", // On lui donne l'id du produit créé sur le site de Stripe directement
                //     'quantity' => 1,
                // ] //ici on peut ajout mméme code juste on chane price pour afficher la somme des différets produits 
            ],
            'allow_promotion_codes' => true,
            'customer_email' => "sam@gmail.com",
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_stripe_error', [], UrlGeneratorInterface::ABSOLUTE_URL),
            // 'client_reference_id' => 1
        ]);

        return $this->redirect($session->url, 303);

    }

    #[Route('/payment/success', name: 'app_stripe_success')]
    public function success(): Response
    {
        

        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

    #[Route('/payment/error', name: 'app_stripe_error')]
    public function error(): Response
    {
        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }
}
