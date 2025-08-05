<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MemberEditService;
use Exception;

class MemberCenterController extends Controller
{

    protected MemberEditService $memberEditService;

    public function __construct(MemberEditService $memberEditService)
    {
        $this->memberEditService = $memberEditService;
    }

    //  載入「會員中心首頁」頁面
    public function memberHome(Request $request)
    {
        $user = $request->attributes->get('user');
        return view(
            'member_home', 
            ['username' => $user->username ]
        );
    }

    //  載入「設定帳號」的頁面
    public function setAccount(Request $request)
    {
        $user = $request->attributes->get('user');
        return view(
            'member_data', 
            ['user' => $user ]
        );
    }


    // 編輯「會員資料」
    public function editMemberData(Request $request)
    {
        try
        {
            $user = $request->attributes->get('user');
            $data = $request->only([
                'username', 'gender', 'age_group', 'address',
                'country',  'mobile',  'email'
            ]);

            $this->memberEditService->handleEdit($user, $data);
            return response()->json([
                'code'    => 200, 
                'message' => 'success', 
                'url'     => route('member_home')
            ]);
        }
        catch ( Exception $e)
        {
            return response()->json([
                'code'    => 400, 
                'message' => $e->getMessage() 
            ]);
        }
    }
}
