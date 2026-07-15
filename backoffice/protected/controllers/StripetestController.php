<?php
class StripetestController extends CommonController
{

    public function beforeAction($action)
    {
        $path = CMedia::homeDir()."/protected/vendor/stripe/vendor/autoload.php";        
        require $path;
        return true;
    }

    public function actionCreateaccount()
    {        
        $stripe = new \Stripe\StripeClient('sk_test_f95wSoGGaVzxbOgxcUXV0dvx');     
        
        //$result = $stripe->accounts->create(['type' => 'express']);

        $params = [
            'country' => 'US',
            'type' => 'express',
            'capabilities' => [
              'card_payments' => ['requested' => true],
              'transfers' => ['requested' => true],
            ],
            'business_type' => 'individual',
            'email'=>"marktupaz11@yahoo.ph",
            'individual'=>[
                'first_name'=>"Jane",
                'last_name'=>"Austen"
            ],
            'business_profile' => ['url' => 'https://yahoo.com'],
        ];
        dump($params);

        try {
            $result = $stripe->accounts->create($params);
            dump($result->id);
            dump($result);
        } catch (Exception $e) {
            dump($e->getMessage());
        }
    }
    
    public function actionaccountlink()
    {
        $account = 'acct_1Lf42u2STD3KsoEX';

        $stripe = new \Stripe\StripeClient('sk_test_f95wSoGGaVzxbOgxcUXV0dvx');     

        $result = $stripe->accountLinks->create(
            [
                'account' => $account,
                'refresh_url' => 'http://localhost/kmrs2/backoffice/stripetest/reauth',
                'return_url' => 'http://localhost/kmrs2/backoffice/stripetest/account_return',
                'type' => 'account_onboarding',
            ]
        );
        dump($result);

        // Stripe\AccountLink Object
        //     (
        //         [object] => account_link
        //         [created] => 1662367972
        //         [expires_at] => 1662368272
        //         [url] => https://connect.stripe.com/setup/e/acct_1Leazs2R1Wm0DJuf/sxlEGObhxKgo
        //     )

    }
  
    public function actionretrieveaccount()
    {
        $account = 'acct_1Leazs2R1Wm0DJuf';
        $stripe = new \Stripe\StripeClient('sk_test_f95wSoGGaVzxbOgxcUXV0dvx');     

        $result =  $stripe->accounts->retrieve(
            $account,
            []
        );
        //charges_enabled
        //details_submitted
        dump($result);
    }

    public function actionCreateloginlink()
    {
        $account = 'acct_1Leazs2R1Wm0DJuf';
        $stripe = new \Stripe\StripeClient(
            'sk_test_f95wSoGGaVzxbOgxcUXV0dvx'
          );
          $result =$stripe->accounts->createLoginLink(
            $account,
            []
          );
        dump($result);

    }

    public function actionCheckoutsession()
    {
        $account = 'acct_1Leazs2R1Wm0DJuf';        
        $stripe = new \Stripe\StripeClient(
            'sk_test_f95wSoGGaVzxbOgxcUXV0dvx'
          );

        $session = $stripe->checkout->sessions->create([
            // 'line_items' => [[ 
            //   'name'=>"Test payment",
            //   'amount' => 20*100,
            //   'currency'=>"usd",
            //   'quantity' => 1,
            // ]],
            'customer_email'=>"test@yahoo.com",
            'line_items'=>array(
                array(
                    'price_data'=>array(
                        'currency'=>"USD",
                        'unit_amount'=>5*100,
                        'product_data'=>array(
                            'name'=>"Test Payment",
                            'description'=>"Test payment description"
                        )
                    ),
                    'quantity'=>1
                )
            ),
            'mode' => 'payment',
            'success_url' => 'http://localhost/kmrs2/backoffice/stripetest/success',
            'cancel_url' => 'http://localhost/kmrs2/backoffice/stripetest/failure',
            // 'payment_intent_data' => [
            //   'application_fee_amount' => 1*100,
            //   'transfer_data' => [
            //     'destination' => $account,
            //   ],
            // ],
        ]);
        dump($session); 
    }
    
    public function actionPaymentintent()
    {
        $stripe = new \Stripe\StripeClient(
            'sk_test_f95wSoGGaVzxbOgxcUXV0dvx'
        );

        $params = [
            'payment_method'=>'pm_1LuYvRE4iIFf0ivgVRGLo5Tc',
            'amount' => 2*100,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'capture_method' => 'manual',
            'confirm'=>true,
            'return_url'=>"http://localhost/kmrs2/backoffice/stripetest/return.php"
        ];
        // $params = [
        //     'amount' => 3*100,
        //     'currency' => 'usd',
        //     'payment_method_types' => ['card'],
        //     'capture_method' => 'manual',
        // ];
        dump($params);        

        $result = $stripe->paymentIntents->create($params);
        dump($result);
    }

    // public function actionconfirmpayment()
    // {
    //     $payment_intent = 'pi_3Ls1ItE4iIFf0ivg1MAzLHhy';
    //     dump($payment_intent);
    //     $stripe = new \Stripe\StripeClient(
    //         'sk_test_f95wSoGGaVzxbOgxcUXV0dvx'
    //     );

    //     $intent = $stripe->paymentIntents->retrieve(
    //         $payment_intent,
    //         []
    //     );        
    //     $intent->confirm();        
    //     dump($intent);
    // }

    public function actionRetrievepayment()
    {
        $payment_intent = 'pi_3Ls5z6E4iIFf0ivg0UneBTPM';
        $stripe = new \Stripe\StripeClient(
            'sk_test_f95wSoGGaVzxbOgxcUXV0dvx'
        );

        $intent = $stripe->paymentIntents->retrieve(
            $payment_intent,
            []
        );
        dump($intent);
    }

    public function actioncapturepayment()
    {
        $payment_intent = 'pi_3Ls2EJE4iIFf0ivg1ghdlTxL';
        dump($payment_intent);
        $stripe = new \Stripe\StripeClient(
            'sk_test_f95wSoGGaVzxbOgxcUXV0dvx'
        );

        // $result = $stripe->paymentIntents->capture(
        //     $payment_intent,
        //     []
        // );
        // dump($result);

        $intent = $stripe->paymentIntents->retrieve(
            $payment_intent,
            []
        );
        $intent->capture();
        dump($intent);
        //$intent->capture(['amount_to_capture' => 750]);
    }

    public function actionsuccess()
    {
        //$id = 'cs_test_a1pWBikZwk7VDIqXDhtSPs666lB4QWAyUQwVyIK26vvNKcrTEqtGRZjqE5';        
        $id='cs_test_a1zvzG1kvISpUldOvTlQWpEspgBT8IJTaxnNPMcgTfE3Qu4NxJcPuNEbxt';
        $stripe = new \Stripe\StripeClient(
            'sk_test_f95wSoGGaVzxbOgxcUXV0dvx'
          );
        // $result = $stripe->checkout->sessions->retrieve(
        //     $id,
        //     []
        // );   
        $result = $stripe->checkout->sessions->retrieve(
            $id,
            ['expand'=>['customer', 'payment_intent']]
        );           
        dump($result);
    }

    public function actionRetrieveintent()
    {
        $stripe = new \Stripe\StripeClient(
            'sk_test_f95wSoGGaVzxbOgxcUXV0dvx'
        );

        $result = $stripe->paymentIntents->retrieve(
            'pi_3LhUvoE4iIFf0ivg0M4KJN7t',
            ['expand'=>['customer']]
          );
        dump($result);

    }

    public function actionTransfer()
    {
        $payment_intent = 'ch_3Ls2EJE4iIFf0ivg1S0ZYm37';
        $account = 'acct_1Ljgtr2QMHzvhJii';  

        $stripe = new \Stripe\StripeClient(
            'sk_test_f95wSoGGaVzxbOgxcUXV0dvx'
        );

        $params = [
            'amount' => 2*100,
            'currency' => 'usd',
            'destination' => $account,
            'source_transaction' => $payment_intent,
        ];
        dump($params);
        try {

            $transfer = $stripe->transfers->create($params);
            dump($transfer);

        } catch (Exception $e) {
          dump($e->getMessage());
        }	
    }    

    public function actionTransfer2()
    {
        \Stripe\Stripe::setApiKey('sk_test_f95wSoGGaVzxbOgxcUXV0dvx');

        $transfer = \Stripe\Transfer::create([
            'amount' => 1.*100,
            'currency' => 'usd',
            'destination' => 'acct_1Ljgtr2QMHzvhJii',            
          ]);
        dump($transfer);
    }

    public function actionRetrieveCharge()
    {        
        $stripe = new \Stripe\StripeClient(
            'sk_test_f95wSoGGaVzxbOgxcUXV0dvx'
        );
        $charge =$stripe->charges->retrieve(
            'ch_3LhUvoE4iIFf0ivg0OMIJl8r',
            ['expand'=>['payment_intent']]
        );
        dump($charge);
    }

    public function actionDebit()
    {
        $account = 'acct_1Ljgtr2QMHzvhJii';    
        $stripe = new \Stripe\StripeClient(
            'sk_test_f95wSoGGaVzxbOgxcUXV0dvx'
        );
        $charge = $stripe->charges->create([
            'amount'   => 2*100,
            'currency' => 'usd',
            'source' => $account
        ]);
        dump($charge);
    }

}
// end class