<?php

namespace App\Http\Controllers\Front;

use Carbon\Carbon;
use App\Models\plan;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\OTPWithdraw;


use App\Models\posts;
use App\Models\video;
use App\Models\expert;
use App\Models\tagert;
use GuzzleHttp\Client;
use App\Models\Archive;
use App\Models\binance;
use App\Models\Massage;
use App\Models\binanceUser;
use App\Models\TargetsRecmo;
use Illuminate\Http\Request;
use App\Models\transfer_many;
use App\Models\recommendation;
use App\Models\plan_recommendation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\VadioResource;
use App\Models\ImageSubmissionBinance;
use App\Http\Resources\ArchiveResource;
use App\Http\Resources\RecommendationResource;
use App\Http\Resources\Withdraw_moneyResource;
use App\Http\Requests\Storetransfer_manyRequest;
use App\Http\Controllers\Helper\NotficationController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\Helper\OTP\OtpRegisterController;
use App\Http\Controllers\Helper\OTP\providerOTPController;



 
 

class TabsController extends Controller
{
    public function Videos()
    {
        // return $user=User::where('id',8)->get();
return VadioResource::collection(video::orderBy('created_at', 'desc')->get());
    }
    public function Archive()
    {
        $archives = Archive::with([
            'recommendation.target',
            'recommendation.plan2' => function ($query) {
                $query->select('id', 'name');
            }
        ])->get()->sortBy('created_at');

        $post = posts::where('status', 'is_post')->orderBy('created_at')->get();

        $combinedResult = collect([$archives, $post])->flatten()->sortBy('created_at')->values();

        return response()->json([
            'data' => $combinedResult
        ]);
    }

    public function getPosts()
    {
        $user = auth('api')->user();
        $plan_ids = $user->Role->pluck('id')->toArray();
        if (!$user) {
            return response()->json([
                'Success' => false,
                'Massage' => "Invalid token",
            ]);
        }
        if ($user->state == 'super_admin' || $user->state == 'admin') {
            $post = posts::whereIn('plan_id', $plan_ids)->orderBy('created_at')->get();
            return response()->json([
                'data' => $post
            ]);
        } else {
            $post = posts::where('plan_id', $user->plan_id)->orderBy('created_at')->get();
            return response()->json([
                'data' => $post
            ]);
        }
    }



    public function Advice(Request $request)
    {
        
        
      $header = $request->header('Authorization');

$user = auth('api')->user();

if (!$user) {
    return response()->json([
        'Success' => false,
        'Message' => "Invalid token",
    ]);
}

$ali = plan_recommendation::where('planes_id', $user->plan_id)->get();
$recomIds = $ali->pluck('recomondations_id')->toArray();

$excludedIds = [1465, 1466];

$recom = Recommendation::with(['target', 'tragetsRecmo'])
    ->whereNotIn('user_id', $excludedIds)
    ->whereIn('id', $recomIds)
    ->orderBy('created_at', 'desc')
    ->get();

$recomIds = $recom->pluck('id')->toArray();

$expertIds = expert::select('last_tp', 'recomondations_id', 'status')
    ->whereIn('recomondations_id', $recomIds)
    ->get();

$recom->each(function ($targetDone) use ($expertIds) {
    $expertId = $expertIds->where('recomondations_id', $targetDone->id)->first();
    if ($expertId) {
        $targetDone->targetDone = $expertId->last_tp;
        $targetDone->statusDone = $expertId->status;
    } else {
        $targetDone->targetDone = 0;
        $targetDone->statusDone = null;  // إضافة هذا السطر لتجنب أي أخطاء إذا كانت الحالة غير موجودة
    }
});

$post = posts::where('status', 'is_advice') // تصحيح الشرط من 'status', 'is_advice', 'planes_id'
    ->where('plan_id', $user->plan_id)
    ->orderBy('created_at')
    ->get();

$combinedResult = collect([$recom, $post])
    ->flatten()
    ->each(function ($item) {
        $item->created_at = date('Y-m-d H:i:s', strtotime($item->created_at . '+3 hours'));
        $item->updated_at = date('Y-m-d H:i:s', strtotime($item->updated_at . '+3 hours'));
    })
    ->sortByDesc('created_at')  // استخدام sortByDesc لترتيب البيانات من الأحدث إلى الأقدم
    ->values();

return response()->json([
    'data' => $combinedResult,
]);

    }

    // for transfarMony
    public function TransfarManyClient(Storetransfer_manyRequest $request)
    {
        $timestamp = time(); // Get the current timestamp
        $uniqueId = uniqid(); // Generate a unique identifier
        $randomNumber = mt_rand(1000, 9999); // Generate a random number

        $transactionId = $timestamp . $uniqueId . $randomNumber;

        // 50 -2 = 48


        $amount = $request['money'] - 1;
          $user = auth('api')->user();
     


        if ($request['money'] > $user->money) {
            return response()->json(['success' => false, 'message' => 'You dont have all that money']);
        } else {
            
            $check = $user->money -= $request['money'];
            $user->money = $check;
            $user->save();
            
                 $otp=new OtpRegisterController();
                 $codeOtp=$otp->genrateOtp();
            
            $transfer_many = transfer_many::create([
            'money' => $amount,
            'Visa_number' => $request['Visa_number'],
            'status' => 'pending',
            'transaction_id' => $transactionId,
            'user_id' => $user->id,
            'ip_user' => $request->ip(),
            'otp'=>$codeOtp,
            'check_otp'=>0,


        ]);
        $provider=new providerOTPController();
                 $codeOtp=$provider->sendOtp($user->email, $user->name, $codeOtp);
                // Mail::to($user->email)->send(new OTPWithdraw($codeOtp,$user->name)); // Replace with your own mail class

        }
        

        $notfication = new NotficationController();
        $bodyManger = "تم طلب سحب " . $request['money'] . " من " . $user->name;
        $notfication->notficationManger($bodyManger);
        $notfication->Myahya($bodyManger);

        return response()->json(['success' => true, 'message' => 'Money deducted']);
    }

    public function historyTransFarMany(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'Success' => false,
                'Massage' => "Invalid token",
            ]);
        }
        return Withdraw_moneyResource::collection(transfer_many::where('user_id', $user->id)->get());
    }


    public function getCurrentDateTime(Request $request)
    {
        // // $currentDateTime = now(); // This retrieves the current date and time in Laravel's Carbon instance.
        //  $currentDateTime = Carbon::now('Africa/Cairo');
        // return response()->json([
        //     // 'datetime' => $currentDateTime->toDateTimeString(),
        //     'datetime' => $currentDateTime
        // ]);
        $timezone = 'Africa/Cairo';
        $url = "http://worldtimeapi.org/api/timezone/{$timezone}";

        try {
            $client = new Client();
            $response = $client->get($url);
            $data = json_decode($response->getBody(), true);

            $currentDateTime = $data['datetime'];

            return response()->json([
                'datetime' => $currentDateTime,
                'timezone' => $timezone,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch accurate time.',
            ], 500);
        }
    }

    public function userExpire()
    {
    }

    public function addValueToBinance(Request $request)
    {

        $request->validate([
            'binanceApiKey' => 'required',
            'binanceSecretKey' => 'required',
        ]);

        $user = auth('api')->user();


         $apiKey = $request->input('binanceApiKey');
        $secretKey = $request->input('binanceSecretKey');

        $apiUrl = 'https://api.binance.com/api/v3/account';




        $apiUrl = 'https://api.binance.com/api/v3/account';

        // Timestamp for the request
        $timestamp = round(microtime(true) * 1000);

        // Create a query string with the required parameters
        $queryString = http_build_query([
            'timestamp' => $timestamp,
        ]);

        // Create a signature for the request
        $signature = hash_hmac('sha256', $queryString, $secretKey);

        // Make the GET request with authentication headers
         $response = Http::withHeaders([
            'X-MBX-APIKEY' => $apiKey,
        ])->get($apiUrl . '?' . $queryString . '&signature=' . $signature);

        // Check if the response is successful (status code 200)
        if ($response->successful()) {
            $accountData = $response->json();

            $user->update([
                'binanceApiKey' =>  $apiKey,
                'binanceSecretKey' => $secretKey
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Value added to records successfully'
            ]);
        } else {
            // Handle the error response
            $errorResponse = $response->json();
            return response()->json([
                'success' => false,
                'message' => 'You have a problem with ApiKEY and SecretKey'
            ]);
        }
    }

    public function binanceTransaction(Request $request)
    {

        $user = auth('api')->user();

        // Validate the incoming request (add more validation as needed)
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Upload and save the image
        if ($request->hasFile('image')) {
            $img = time() . '.' . $request->image->extension();
            $imagePath = $request->image->move(public_path('ImagePaymentBinance'), $img);
        } else {
            return response()->json([
                'Success' => false,
                'Massage' => "Not Uploaded Image",
            ]);
        }

        // Create a new image submission
        ImageSubmissionBinance::create([
            'user_id' => $user->id,
            'image' => $imagePath,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Image submitted successfully'], 201);
    }

    public function binanceTransactionUsers(Request $request)
    {
        $user = auth('api')->user();
        // Make validaiton on user authirty
        if ($user->state == 'super_admin' || $user->state == 'admin') {
            $users = User::whereHas('transaction_binance')->get();
            return response()->json(['users' => $users], 200);
        }
        return response()->json([
            'Success' => false,
            'Massage' => "Invalid Authirty",
        ]);
    }

    public function acceptImageBinance(Request $request, $ImageSubmissionBinanceId)
    {
        // Find the image submission
        $imageSubmission = ImageSubmissionBinance::findOrFail($ImageSubmissionBinanceId);
        $user = auth('api')->user();
        // Make validaiton on user authirty
        if ($user->state == 'super_admin' || $user->state == 'admin') {
            $imageSubmission->status = 'active';
            $imageSubmission->price = $request->input('price');
            $imageSubmission->save();

            return response()->json(['message' => 'Image accepted and price set successfully'], 200);
        }
        return response()->json([
            'Success' => false,
            'Massage' => "Invalid Authirty",
        ]);
    }

    public function cancelImageBinance(Request $request, $ImageSubmissionBinanceId)
    {
        // Find the image submission
        $imageSubmission = ImageSubmissionBinance::findOrFail($ImageSubmissionBinanceId);
        $user = auth('api')->user();
        // Make validaiton on user authirty
        if ($user->state == 'super_admin' || $user->state == 'admin') {
            $imageSubmission->status = 'cancel';
            $imageSubmission->save();

            return response()->json(['message' => 'Image cancel successfully'], 200);
        }
        return response()->json([
            'Success' => false,
            'Massage' => "Invalid Authirty",
        ]);
    }



    public function crybto(Request $request)
    {
        $client = new Client();


        $response = $client->post('https://crypto.smartidea.tech/api/get', [
            'form_params' => [
                'email' => $request->email,

            ]
        ]);

        $statusCode = $response->getStatusCode();
        return $content = $response->getBody()->getContents();

        return 111;
    }


    // getRecmoData

    public function getRecmoData($recomId)
    {
        $recom = recommendation::where('id', $recomId)->first();
        $targets = TargetsRecmo::where('recomondations_id', $recomId)->pluck('target')->toArray();
        $entry = $recom->entry_price;
        $parts = explode(' - ', $entry);
        // Convert the string parts to float values
        $output = array_map('floatval', $parts);
        // $facilityImages = FacilityImages::where('facility_id', $facilityId)->get()->pluck('image')->toArray();
        return response()->json([
            'ticker' => $recom->currency,
            'targets' => $targets,
            'entry' => $output,
            'stopLose' => $recom->stop_price,
        ]);
    }


    public function myAdvice(Request $request)
    {
        $user = auth('api')->user();
        $binance = binanceUser::where('user_id', $user->id)->get();
        $get = $binance->pluck('recomondations_id')->toArray();
        return      $recommendation = recommendation::with(['target', 'tragetsRecmo'])->whereIn('id', $get)->get();
    }



    public function testbot(Request $request)
    {
        return $this->store($request);
    }

    public function store(Request $request)
    {


        $request['active'] = 1;
        $request['planes_id'] = 1;
        $request['archive'] = 0;

        $ttt = $request['entry'];

        // Convert the array $ttt to a comma-separated string
        $entryAsString = implode(', ', $ttt);

        // Create a new recommendation record with the specified values
        $test = recommendation::create([
            'currency' => $request['ticker'],
            'entry_price' => $entryAsString, // Store the comma-separated string
            'stoploss' => $request['stoploss'], // Assuming you have a 'stoploss' field
            'user_id' => 1, // You can replace 1 with the appropriate user ID
        ]);

        $testArray = $test->toArray(); // Convert the Eloquent model to an array
        return $testArray['recomondations_id'] = $test->id; // Add the recomondations_id field

        $jsonData = json_encode($testArray);


        $url = 'http://51.161.128.30:5015/recomondations';

        return  $url = Http::post($url, $jsonData);





        // if ($request->has('targets')) {
        //   return  $targets = $request->input('targets');

        //     // Check if $targets is a string, and if so, convert it to an array
        //     if (is_string($targets)) {
        //         $targets = json_decode($targets, true);
        //     }

        //     // Check if $targets is now an array before proceeding with the foreach loop

        //     if (is_array($targets)) {
        //         foreach ($targets as $target) {
        //             $tts = TargetsRecmo::create([
        //                 'recomondations_id' => $test->id,
        //                 'target' => $target,
        //             ]);
        //         }
        //     } else {
        //     }
        // }
        // else{
        //     return 'not';
        // }




        $plansReecommindations = $request->input('totalPlan');
        $array = array_unique($plansReecommindations);
        $array2 = array_values($array);



        if (!empty($array2)) {

            foreach ($array2 as $plansReecommindation) {

                $tts = plan_recommendation::create([
                    'recomondations_id' => $test['id'],
                    "planes_id" => $plansReecommindation,
                ]);
            }
        }

        $recom = plan::whereIn('id', $array2)
            ->orderBy('created_at')
            ->get();

        //   return 500;


        // foreach ($recom as $value) {
        //     event(new recommend($test, $value->nameChannel));
        //     $this->sendNotification($value->nameChannel);
        //     $this->telgrame($value->id, $request->desc, $request->title);
        // }

        // event(new recommend($test, $targets, $plan->nameChannel));

        //  $this->telgrame($request->planes_id);


        return response()->json([
            'success' => true,
        ]);
    }
    
    
    
    
    
    
      
}

    
    
    
 
