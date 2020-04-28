<?php

namespace App\Http\Controllers;

use App\DataKelas;
use App\Kelas;
use Illuminate\Http\Request;
use App\User;

class testController extends Controller
{
    public function test()
    {
        $kelas = User::where('id', auth()->user()->id)->with(['kelas' => function ($query) {
            return $query->with('user')->get();
        }])->get();
        foreach ($kelas as $kls) {
            foreach ($kls->kelas as $value) {
                if ($value->user->status == 0) {
                    # code...
                    return $value->user->count();
                }
            }
        }
    }
}
