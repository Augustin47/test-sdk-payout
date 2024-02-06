<?php

namespace App\Http\Controllers;

use App\SDK\Sandbox\src\PayoutClass;
use App\SDK\Sandbox\tests\PaymentClassTest;
use App\SDK\Sandbox\tests\PayoutClassTest;
use Illuminate\Http\Request;

class TestSdk extends Controller
{
    public function do(){
        $test = new PayoutClassTest('ddd');
        $response = $test->testDo();
        return response()->json($response);
    }

    public function check(){
        $test = new PayoutClassTest('ddd');
        $response = $test->testCheck();
        return response()->json($response);
    }

    public function balance(){
        $test = new PayoutClassTest('ddd');
        $response = $test->testBalance();
        return response()->json($response);
    }
}
