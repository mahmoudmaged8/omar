<?php

namespace App\Http\Controllers\Boot;

use App\Models\Bots;
use App\Models\User;
use GuzzleHttp\Client;
use App\Models\binance;
use App\Models\feesBot;
use App\Events\recommend;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\ClientException;
use App\Http\Controllers\Helper\NotficationController;
use App\Http\Controllers\MarktingFees\MarktingFessController;

class fessBotController extends Controller
{
    public $notfication;
    public $MyBotController;
    public function __construct()
    {
        $this->notfication = new NotficationController();
        $this->MyBotController = new MyBotController();
    }
    public function fees(Request $request)
    {
         
              $binace = binance::where('status_fees', 1)->get();

        if ($binace->isEmpty()) {

            $binacetwo = binance::where('status_fees', 2)->get();

            if ($binacetwo->isEmpty()) {
                return 'NOT HAVE ANY BUY';
            } else {

                foreach ($binacetwo as $binance) {
                    if ($binance->status_fees = 2) {

                        $user = User::find($binance->user_id);
                        $binance->status_fees = 0;
                        $binance->save();
                        // $this->NotficationBuy($user->fcm_token, $binance->symbol);
                    }
                }
            }

            return 'NOT HAVE ANY FEES';
        }
        foreach ($binace as $fees) {
            $user = User::find($fees->user_id);
            $botMony = $fees->fees;
            $userMony = $user->number_points - $botMony;
            $user->number_points = $userMony;
            $user->save();

            $fees->status_fees = 0;
            $fees->save();

            // $modelFess = feesBot::create([
            //     'user_id' => $fees->user_id,
            //     'fees' => $fees->fees,
            //     'number_bot' => $fees->bot_num,
            //     'ticker' => $fees->symbol,
            //     'profusdt' => $fees->profit_per,
            //     'status' => 'success'
            // ]);
                
                  Http::timeout(120)->post('https://upvela.gfoura.smartidea.tech/api/subscription_bots', [
                'user_id' => $user->id,
                'coming_affiliate' => $user->comming_afflite,
                'bot_id'=>$fees->bot_num,
                'bot_money'=>$botMony
                ]);
                
            //   return 'ok'; 
            //  $MarktingBoot=new MarktingFessController();
            //  $MarktingBoot->forFees($user->id,$botMony,$fees->bot_num);

            // if ($userMony < 0) {
            //     $data = [
            //         'shutdown' => 0,
            //         "userid" => $user->id,
            //     ];

            //     $response = Http::post('http://51.161.128.30:5015/shutdown', $data);
            //     $responseBody = $response->body();

            //     // for profit
            //     // $this->NotfictionProfit($fees->profit_per, $user->fcm_token);

            //     // for fees
            //     $userMony = $user->number_points;

            //     // $this->Notfictionfess($user->fcm_token, $userMony);
            // } elseif ($userMony == 0 || $userMony <= 5) {
            //     // for profit
            //     $this->Notfictionfive($user->fcm_token, $fees->profit_per,);

            //     $this->NotfictionProfit($fees->profit_per, $user->fcm_token);
            // } else {
            //     $this->NotfictionProfit($fees->profit_per, $user->fcm_token);
            // }
        }

        return 'ok';
    }
    //   for profit
    public function NotfictionProfit($profit, $userfcm)
    {
        if ($profit > 0) {

            $body = "مبروك تمت تحقيق الهدف بنجاح وقد حققت مكسب $profit %";
            $this->notfication->notfication($userfcm, $body);
        } else {
            $body = "مبروك تمت تحقيق الهدف بنجاح ";
            $this->notfication->notfication($userfcm, $body);
        }
    }
    // for cheack have than 0
    public function Notfictionfess($user, $userMony)
    {
        $body = "عميلنا العزيز رصيدك $userMony وللأسف تم إيقاف كل الأبوات الخاصة بك يرجى الشحن وتفعيل الأبوات مرة أخرى للاستمرار في تحقيق الأرباح";

        $this->notfication->notfication($user, $body);
    }
    // for cheack have = 0 ,5

    public function Notfictionfive($user, $userMony)
    {

        $body = "عميلنا العزيز رصيدك $userMony يرجى الشحن في أسرع وقت حتى لا يتم إيقاف الأبوات الخاصة بأسيتادتكم والاستمرار في تحقيق الأرباح";
        $this->notfication->notfication($user, $body);
    }

    public function NotficationBuy($user, $tiker)
    {
        $body = "$tiker قام البوت  بشراء  لك عمله ";
        $this->notfication->notfication($user, $body);
    }
}
