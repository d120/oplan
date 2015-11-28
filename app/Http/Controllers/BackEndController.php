<?php

namespace Oplan\Http\Controllers;

use Illuminate\Http\Request;

use Oplan\Termin;
use Oplan\Veranstaltung;
use Oplan\Raumbedarf;

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
    public function createRaumbedarf(Request $request, $ver_k, $ak_id)
    {
        $termin = Termin::find($ak_id);
        if (!$termin || $termin->veranstaltung_id != $request->veranst->id) abort(422, "Invalid ak_id");
        
        $b = new Raumbedarf();
        $b->termin_id = $termin->id;
        $b->min_platz = $request->min_platz;
        $b->kommentar = $request->kommentar;
        $b->praeferenz = "";
        $b->raum = "";
        $b->save();
        
        return response()->json(["success" => true, "id" => $b->id]);
    }
    
    /**
     * 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateRaumbedarf(Request $request, $ver_k, $ak_id, $b_id)
    {
        $termin = Termin::find($ak_id);
        if (!$termin || $termin->veranstaltung_id != $request->veranst->id) abort(404, "AK not found");
        $bedarf = Raumbedarf::find($b_id);
        if (!$bedarf || $bedarf->termin_id != $termin->id) abort(404, "Raumbedarf not found");
        $bedarf->min_platz = $request->min_platz;
        $bedarf->kommentar = $request->kommentar;
        $bedarf->praeferenz = $request->praeferenz;
        $bedarf->save();
        return response()->json(["success" => "true"]);
    }
    /**
     * 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteRaumbedarf(Request $request, $ver_k, $ak_id, $b_id)
    {
        $termin = Termin::find($ak_id);
        if (!$termin || $termin->veranstaltung_id != $request->veranst->id) abort(404, "AK not found");
        $bedarf = Raumbedarf::find($b_id);
        if (!$bedarf || $bedarf->termin_id != $termin->id) abort(404, "Raumbedarf not found");
        $bedarf->delete();
        return response()->json(["success" => "true"]);
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
