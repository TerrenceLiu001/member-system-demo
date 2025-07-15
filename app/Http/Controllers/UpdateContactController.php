<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ContactUpdateService;
use Exception;

class UpdateContactController extends Controller
{
    //  執行「變更」Email
    public function updateEmail(Request $request)
    {
        try
        {
            $contactType = ContactUpdateService::isRequestValid($request);
            ($contactType === 'email') ? ContactUpdateService::prepareUpdateForEmail($request)
                                       : throw new Exception("功能尚未開通");
            
            return response()->json(['code' => 200, 'message' => 'success']);

        }
        catch (Exception $e)
        {
            return response()->json(['code' => 400, 'message' => $e->getMessage()]);
        }
    }

    //  載入「確認變更」頁面
    public function updateContact(Request $request)
    {
        try
        {
            $email = $request->route('email');
            $token = $request->route('token');

            $record = ContactUpdateService::authorizeUpdateContactAccess($email, $token);

            return view('update_contact', [
                'email'     => $email, 
                'new_contact' => $record->new_contact,
                'token'     => $token
            ]);
        }
        catch (Exception $e)
        {
            return redirect()->route('login')->with('error', $e->getMessage());
        }
    }

    //  執行「變更通訊」流程
    public function updateConfirm(Request $request)
    {
        try
        {
            ContactUpdateService::finishConfirm($request->token);
            return redirect()->route('complete_confirm');
        }
        catch (Exception $e)
        {
            return redirect()->route('login')->with('error', $e->getMessage());
        }
    } 

    //  執行「取消變更」流程
    public function cancelConfirm(Request $request)
    {
        try
        {
            ContactUpdateService::cancelRequest($request->token);
            return redirect()->route('login')->with('success', '已成功取消變更，請再次登入');
        }
        catch (Exception $e)
        {
            return redirect()->route('login')->with('error', $e->getMessage());
        }
    }

    //  載入「完成變更」頁面
    public function completeConfirm(Request $request)
    {
        session()->flash('success', '已成功變更，請重新登入');
        return view('complete_confirm');
    }
}

