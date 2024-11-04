<?php

namespace App\Http\Controllers\Boot;

use App\Models\plan;
use App\Models\User;
use App\Models\Admin;
use GuzzleHttp\Client;
use App\Traits\ResponseJson;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminUserController extends Controller
{
    use ResponseJson;


    public function getAllAdminAndMyAdmin()
    {


        $user = auth('api')->user();
        $userPlanId = $user->plan_id;
        $bossIds = $user->admins;
        $bossIdsArray = json_decode($bossIds, true);

        if (is_null($bossIdsArray)) {  //check if bossarray is null


                 $excludedIds = [1465, 1466];
                
                $Admins = Admin::with(['users:id,name,email'])
                    ->where('plan_id', '<=', $userPlanId)
                    ->whereNotIn('user_id', $excludedIds)
                    ->get();

            $adminsArray = $Admins->pluck('users')->toArray();
            $uniqueAdminsArray = collect($adminsArray)->unique()->values()->all();


            // Combine both results into an array
            return $responseData = [
                'allAdmin' => $uniqueAdminsArray,
                'myadmin' => $uniqueAdminsArray,
            ];
        } else {
            // Fetch the user IDs and names based on the boss IDs as a collection of objects
            $adminUsers = User::whereIn('id', $bossIdsArray['boss'])
                ->select('id', 'name', 'email')
                ->get();

 $excludedIds = [1465, 1466,1485,1493];
                $Admins = Admin::with(['users:id,name,email'])
                    ->where('plan_id', '<=', $userPlanId)
                    ->whereNotIn('user_id', $excludedIds)
                    ->get();

            $adminsArray = $Admins->pluck('users')->toArray();
            $uniqueAdminsArray = collect($adminsArray)->unique()->values()->all();


            // Combine both results into an array
            return $responseData = [
                'allAdmin' => $uniqueAdminsArray,
                'myadmin' => $adminUsers,
            ];
        }
    }

    public function setAdmin(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(
                [
                    'success' => false,
                    'message' => "Invalid Token"

                ]
            );
        }

        $user->update([

            'admins' => $request['admins'],
        ]);

        return $this->success($user);
    }
}
