<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use Illuminate\Http\Request;

class RateController extends Controller
{
    public function index()
    {
        return Rate::all();
    }

    public function show($date)
    {
        return Rate::where('date', $date)->get();
    }
}
