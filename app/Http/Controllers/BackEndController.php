<?php

namespace Oplan\Http\Controllers;

use Illuminate\Http\Request;

use Oplan\Termin;
use Oplan\Veranstaltung;

use Oplan\Http\Requests;
use Oplan\Http\Controllers\Controller;

class BackEndController extends Controller
{
    
    public function __construct(Request $request)  {
        $this->middleware('Oplan\Http\Middleware\VeranstaltungMiddleware');
        $this->middleware('my_api_auth');
        
    }
    
    /**
     * returns true if user is authenticated
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        return response()->json(["success" => "true", "user" => $request->user()]);
    }


    /**
     * 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function modifyAk(Request $request, $ver_k, $id)
    {
        $termin = Termin::find($id);
        if (!$termin) abort(404, "Ak $id nicht gefunden");
        if ($termin->veranstaltung_id != $request->veranst->id) abort(403);
        
        if ($request->has('von') && $request->has('bis')) {
            $termin->von = $request->von;
            $termin->bis = $request->bis;
            $termin->save();
        }
    }

    /**
     * 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createAk(Request $request)
    {
        $termin = new Termin();
        $termin->veranstaltung_id = $request->veranst->id;
        $termin->von = $request->create_von;
        $termin->bis = $request->create_bis;
        $termin->kurztitel = $request->kurztitel;
        $termin->langtitel = $request->kurztitel;
        $termin->zielgruppe = $request->zielgruppe;
        $termin->save();
        
        echo json_encode(array("success" => true, "id" => $termin->id));
        
    }
    
    /**
     * 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteRaumbuchung(Request $request, $ver_k, $id)
    {
        //
    }
    
    /**
     * 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createRaumbuchung(Request $request)
    {
        //
    }


}
