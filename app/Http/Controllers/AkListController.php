<?php

namespace Oplan\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Oplan\Http\Requests;
use Oplan\Http\Controllers\Controller;

use Oplan\Veranstaltung;
use Oplan\Termin;

class AkListController extends Controller
{
  
    public function __construct(Request $request)  {
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Veranstaltung $veranstaltung)
    {
        $aks = Termin::where('veranstaltung_id', $veranstaltung->id)->get();
        return view('ak.list', ['list' => $aks]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function xmlExport(Request $request, Veranstaltung $veranstaltung)
    {
        $doc = $veranstaltung->generatePentabarfXML();
        $content = $doc->saveXML();
        return (new Response($content, '200'))->header('Content-Type', 'text/xml');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Veranstaltung $veranstaltung, $id)
    {
        if (intval($id)) {
            $ak = Termin::find($id);
        }
        if (!$ak) {
            $ak = Termin::where('slug', $id)->first();
        }
        if (!$ak) {
            abort(404);
        }
        return view('ak.details', ['ak' => $ak]);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showById($id)
    {
        $ak = Termin::find($id);
        return redirect()->action('AkListController@show', [ $ak->veranstaltung->kuerzel, $ak->slug ?: $ak->id ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
