<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UpdateContact\UpdateContactService;
use Exception;

class UpdateContactController extends Controller
{

    protected UpdateContactService $updateContactService;

    public function __construct(UpdateContactService $updateContactService)
    {
        $this->updateContactService = $updateContactService;
    }

    //  執行「變更」Email
    public function updateEmail(Request $request)
    {
        try
        {
            $this->updateContactService->initiateUpdateContactProcess($request);          
            return response()->json([
                'code'    => 200, 
                'message' => 'success'
            ]);

        }
        catch (Exception $e)
        {
            return response()->json([
                'code'    => 400, 
                'message' => $e->getMessage()
            ]);
        }
    }

    //  載入「確認變更」頁面
    public function updateContact(Request $request)
    {
        try
        {
            $email = $request->route('email');
            $token = $request->route('token');

            $response = $this->updateContactService->authorizeUpdateContactPage(
                $email, $token
            );
            
            return view('update_contact', $response);
        }
        catch (Exception $e)
        {
            return redirect()->route('login')->with(
                'error', $e->getMessage()
            );
        }
    }

    // 執行「變更/取消」通訊帳號流程
    public function buttonConfirm(Request $request)
    {
        try
        {
            $requestData = $request->only([
                'email', 'token', 'contact_type', 'action'
            ]);

            $response = $this->updateContactService->handleConfirmation($requestData);
            return redirect()->route($response['route'])->with(...($response['session']));
        }
        catch (Exception $e)
        {
            return redirect()->route('login')->with(
                'error', $e->getMessage()
            );
        };
    }

    //  載入「完成變更」頁面
    public function completeConfirm(Request $request)
    {
        session()->flash(
            'success', '已成功變更，請重新登入'
        );

        return view('complete_confirm');
    }
}

