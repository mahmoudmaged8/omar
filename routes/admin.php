<?php

use App\Models\plan;
use App\Models\Massage;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteGroup;
use App\Http\Controllers\ChatActions;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdsController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\PayController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Front\adminPlan;
use App\Http\Controllers\videoController;
use App\Http\Controllers\bannedController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\PayMopController;
use App\Http\Controllers\TickerController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\All_UserController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\UserDataAdminPanel;
use App\Http\Controllers\AfilliateCalculation;
use App\Http\Controllers\ChatAdviceController;
use App\Http\Controllers\Front\TabsController;
use App\Http\Controllers\Binance\buyController;
use App\Http\Controllers\BotTransferController;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\TransferManyController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\Boot\TikersUserController;
use App\Http\Controllers\chatAdviceAdminController;
use App\Http\Controllers\Front\ChatGroupController;
use App\Http\Controllers\Binance\getLogesController;
use App\Http\Controllers\NotificationPlansController;
use App\Http\Controllers\Binance\transactionController;
use App\Http\Controllers\Binance\sellController;
use App\Http\Controllers\Deposits\DepositsUserController;
use App\Http\Controllers\TransactionUser\TransactionUserController;
use App\Http\Controllers\Dashboard\Log\LogBotRecomndationController;
use App\Http\Controllers\MYBoot\AdminstrategicController;
use App\Http\Controllers\Binance\getAllBuyForUserController;
use App\Http\Controllers\Dashboard\Notfication\notifactionForUserController;
use App\Http\Controllers\Helper\OTP\OtpRegisterController;





// use App\Models\marktingFess;
// use App\Models\User;

Route::post('stopBotRecomindation', [RecommendationController::class, 'stopBotRecomindation']);

Route::post('t',[OtpRegisterController::class,'index']);


// routes for super with admin
Route::middleware(['SuperWithAdmin'])->group(function () {
    Route::apiResource('Recommendation', RecommendationController::class);
 // for log buy sell recomindation
Route::post('gettreansactionRecomindation',[LogBotRecomndationController::class,'getAllTransaticon']);

    Route::get('adminPlan', [RecommendationController::class, 'adminPlan']); //for admin in dashbord
    Route::post('chatAdmin', [chatAdviceAdminController::class, 'chat']);
    Route::post('adviceAdmin', [chatAdviceAdminController::class, 'Advice']);
    Route::post('adminChatPlan', [chatAdviceAdminController::class, 'StoreMassageAdmin']); // FOR SEND MASSAGE ADMIN
    Route::post('adminForPlan', [chatAdviceAdminController::class, 'adminForPlan']);
    Route::apiResource('post', PostController::class);
    // for chat group Delete MessageS
    Route::post('messagePlan', [ChatActions::class, 'deletePlan']);
    Route::delete('messageSuper/{id}', [ChatActions::class, 'deleteMessageSuper']);
    Route::post('banPlan', [ChatActions::class, 'banPlan']);
    Route::post('unbanPlan', [ChatActions::class, 'unbanPlan']);
    Route::POST('historyDepositWeb',[DepositsUserController::class,'historyDepositWeb']);
    Route::post('historyTransactionWeb',[TransactionUserController::class,'historyTransactionWeb']);
    
      // for buy && sell now
    Route::post('buyNowAdmin',[AdminstrategicController::class, 'buy']);
    Route::post('SellNowAdmin',[AdminstrategicController::class, 'sell']);
    //GET ALL CURRENCY
     Route::post('getallcurrency',[getAllBuyForUserController::class,'getallcurrency']);
     // SELL ALL CURRENCY
    Route::post('sellNow',[sellController::class,'sellWithoutrecomindation']);
    Route::get('/all-tickers',[TickerController::class,'getAllTickers']); // for ticker

    Route::post('notfi',[notifactionForUserController::class,'sendNotf']); // for notfication

});
// routes for super  admin

Route::middleware('SuperAdmin')->group(function () {
        Route::apiResource('plan', PlanController::class);

    Route::get('loges', [getLogesController::class, 'index']); //for binance
    Route::delete('loges/{id}', [getLogesController::class, 'deleteloges']);
    Route::resource('video', videoController::class);
    Route::resource('posts', PostController::class);
    Route::resource('archive', ArchiveController::class);
    // Route::apiResource('Recommendation', RecommendationController::class);
    // for User in admin   
    Route::apiResource('User', All_UserController::class);
    Route::post('get_user', [All_UserController::class, 'get_user'])->name('get_user');
     Route::post('usersProfit', [All_UserController::class, 'usersProfit'])->name('usersProfit');
    Route::get('search/{id}', [All_UserController::class, 'serach'])->name('serach');
    Route::get('selectUserFromPlan/{id}', [All_UserController::class, 'selectUserFromPlan'])->name('selectUserFromPlan');
    Route::get('get_all_subscrib/{id}', [All_UserController::class, 'get_all_subscrib']);
    Route::apiResource('banned', bannedController::class);
    Route::resource('telegram', TelegramController::class);
    // Chat Advice
    Route::get('ChatAdvice', [ChatAdviceController::class, 'getChat']);
    Route::post('ChatAdvice_store', [ChatAdviceController::class, 'store']);
    Route::apiResource('coupons', CouponController::class);
    Route::apiResource('payment', PaymentController::class);
    // Route::apiResource('post', PostController::class);
    // Withdraw
    Route::apiResource('withdraw', TransferManyController::class);
    // for pending
    Route::get('pending', [PayController::class, 'pending']);
    Route::post('ActivePending', [PayController::class, 'ActivePending']);
    // dataUser AdminPanel
    Route::get('dataUserCount', [UserDataAdminPanel::class, 'UserCount']);
    Route::get('dataUserCountBanned', [UserDataAdminPanel::class, 'UserCountBanned']);
    Route::get('dataAdminCount', [UserDataAdminPanel::class, 'AdminCount']);
    Route::get('dataAdvicesCount', [UserDataAdminPanel::class, 'AdvicesCount']);
    Route::get('dataLastPaymentCount', [UserDataAdminPanel::class, 'LastPaymentCount']);
    Route::get('dataLastAdviceCount', [UserDataAdminPanel::class, 'LastAdviceCount']);
    // for sofdelete
    Route::get('softDeleteUser', [AuthController::class, 'softDeleteUser']);
    // Admin Notification
    Route::apiResource('NotificationPlans', NotificationPlansController::class);
    Route::put('restoreSoftDeleteUser/{id}', [AuthController::class, 'restoreSoftDeleteUser']);
    // Bot
    // Get Bot Controller
    Route::get('/bot-controller', [BotController::class, 'getBotStatus']);
    Route::post('/bot-controller', [BotController::class, 'updateBotStatus']);
    Route::apiResource('bot-transfer', BotTransferController::class);
    // Get all user active his bot
    Route::get('get-all-user-bot', [BotController::class, 'getAllUserBot']);
    // Update On User in colum is_bot
    Route::post('update-bot-user', [BotController::class, 'updateBotUser']);
    // Set Bot Status For user
    Route::post('set-bot-status', [BotController::class, 'setBotStatus']);
    // Add Bot Status For user
    Route::post('add-bot-status-for-user', [BotController::class, 'AddBotStatuForUser']);
    // Get All Tickers
    // update Tickers
    Route::post('/update-tickers',[TickerController::class,'updateTicker']);
    // delete Tickers
    Route::post('/delete-tickers',[TickerController::class,'deleteTicker']);
    // add Tickers
    Route::post('/add-tickers',[TickerController::class,'addTicker']);
    // API ADS Table
    Route::apiResource('ads', AdsController::class);
    // API To Get All Bots
    Route::get('/get-all-bots',[BotController::class,'getAllHavingBots']);
    //Route Get all Recomendation for  determined plane 
    Route::post('/get-all-recomendation_plan/{plan_id}',[RecommendationController::class,'getAllRecomendationPlan']);
    // getSumMoney
    Route::post('getSumMoney', [All_UserController::class, 'getSumMoney']);
    Route::post('get-all-user-admin-data', [All_UserController::class, 'getAllUserAdminData']);
    Route::post('get-all-user-admin', [All_UserController::class, 'getAllUserAdmin']);
    Route::post('get-all-user-admin-recommendation', [All_UserController::class, 'getAllUserAdminRecommendation']);
    Route::post('get_user_parent', [All_UserController::class, 'get_user_parent']);
    Route::post('get_money_user_transaction', [All_UserController::class, 'get_money_user_transaction']);
    Route::post('usersProfit',[All_UserController::class, 'usersProfit']);
    // // For Web 

});
 













Route::post('SearchTicker',[SearchTickerController::class,'getCurency']);

Route::post('userByDate',[All_UserController::class,'getuser_basd_data']);























