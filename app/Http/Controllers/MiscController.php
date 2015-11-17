<?php

namespace Oplan\Http\Controllers;

use Illuminate\Http\Request;

use Oplan\Http\Requests;
use Oplan\Http\Controllers\Controller;

use Oplan\Veranstaltung;

// ...diverses
class MiscController extends Controller
{
    /**
     * Startseite
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('homepage', ['veranst' => Veranstaltung::get() ]);
    }

}
