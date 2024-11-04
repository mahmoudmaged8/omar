<?php


namespace App\Http\Controllers\Deposits;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\DepositsBinance;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper\NotficationController;

class DepositsController extends Controller
{

    public function getDeposits()
    {


               $api_key = env('API_KEY_DEPOSITE');
        $api_secret = env('KEY_SECRT_DEPOSITE');
        
 
        $currentTimestamp = $this->timestampBinance();
        $twoHoursAgoTimestamp = $currentTimestamp - (2 * 60 * 60 * 1000); // آخر ساعتين بالمللي ثانية
        
        $params = [
            'timestamp' => $currentTimestamp,
            'startTime' => $twoHoursAgoTimestamp,
            'endTime' => $currentTimestamp,
        ];
        
        $query = http_build_query($params);
        
        // إنشاء معرف التوقيع
        $signature = hash_hmac('sha256', $query, $api_secret);
        
        $client = new Client();
         $response = $client->get('https://api.binance.com/sapi/v1/capital/deposit/hisrec', [
            'headers' => [
                'X-MBX-APIKEY' => $api_key,
            ],
            'query' => $query . "&signature={$signature}", // إضافة معرف التوقيع إلى الاستعلام
        ]);
        
         $deposits = json_decode($response->getBody()->getContents());
        foreach ($deposits as $deposit) {
            $textid = trim(str_replace('Off-chain transfer', '', $deposit->txId));
            $mount = $deposit->amount;
            $network = $deposit->network;
            $this->insertDeposit($mount, $textid, $network, $user_id = 1); // Pass parameters here
        }
    }




    public  function createBinanceSignature($data, $apiSecret)
    {
        return hash_hmac('sha256', $data, $apiSecret);
    }





    protected function timestampBinance()
    {
        $client = new Client();
        $response = $client->get('https://api.binance.com/api/v3/time');
        $serverTime = json_decode($response->getBody(), true);
        return $serverTime['serverTime'];
    }





    public function insertDeposit($mount, $textid, $network, $user_id)
    {

        $existingDeposit = DepositsBinance::where('textId', $textid)->first();

        // If the record with the same textId exists, do not insert a new one
        if ($existingDeposit) {
            $body = "هناك خطا يرجي التاكد او هذه العنوان موجود سابقا ";
            $notfy = new NotficationController();
            $user = 'gg';
            $notfy->notfication($user, $body);

            return 'Duplicate';
        }


        $test = DepositsBinance::create([
            'amount' => $mount,
            'textId' => $textid,
            'network' => $network,
            'user_id' => $user_id,




        ]);

        return 'ok';
    }

    public function walteaddress(Request $request)
    {
        return response()->json([
            "success" => true,
            "wallet" => "TLNaJdkATC5NnmHfnLfskXMG85NtihQT29"
        ]);
    }
}
