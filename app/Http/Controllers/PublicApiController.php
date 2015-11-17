<?php

namespace Oplan\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Oplan\Http\Requests;
use Oplan\Http\Controllers\Controller;

use Oplan\Veranstaltung;
use Oplan\Termin;

class PublicApiController extends Controller
{
  
    public function __construct(Request $request)  {
        $this->middleware('Oplan\Http\Middleware\VeranstaltungMiddleware', ['except' => 'getVeranstaltungen']);
        
    }
    /**
     * 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getStundenplanNames($ver_k)
    {
        $veranst = Veranstaltung::byKey($ver_k);
        
        $res = DB::select("SELECT DISTINCT zielgruppe FROM termin WHERE veranstaltung_id = ?", [$veranst->id]);
        $list = array();
        foreach($res as $zg) {
          $items = explode(" ", $zg->zielgruppe);
          $list += array_flip($items);
        }
        foreach($list as $k=>&$v) $v = ucwords(str_replace("_", " ", $k));
        return response()->json($list);
    }
    
  
    /**
     * 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getVeranstaltungen()
    {
        $vv = Veranstaltung::all();
        return response()->json($vv);
    }
    

    /**
     * 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAk(Request $request, $ver_k, $id)
    {
        $termin = Termin::find($id);
        if (!$termin) abort(404, "Ak $id nicht gefunden");
        
        $raumfrei = [];
        return response()->json([
          "ak" => $termin,
          "raumbedarf" => $termin->raumbedarf,
          "frei" => $raumfrei
        ]);
        
    }
    
    /**
     * 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getStundenplan(Request $request)
    {
        
        if (!(isset($request->w) && strlen($request->w) == 7)) die ("Bitte Kalenderwoche w=yyyy_ww angeben");
        $dateParam = str_replace("_","W",$request->w);

        if (isset($request->g)) {
            $title = $request->g;
            $kw = strtotime($dateParam);
            $start = date("Y-m-d",$kw);
            $end = date("Y-m-d",$kw+3600*24*7);
            header("X-Zeitbereich: $start -- $end");
            $res = DB::select("
            SELECT von,bis,tt.id,concat(count(if(raum<>'',1,null)),'/',count(bb.id)) anz,if(count(if(raum = '',1,null))>0,'wunsch','ok') typ ,sum(min_platz) min_platz,zielgruppe,kurztitel,
            group_concat(concat(kommentar,'|',praeferenz,'|',raum) separator '<br>') zuteilung 
            FROM termin tt LEFT OUTER JOIN raumbedarf bb  ON tt.id = bb.termin_id
            WHERE zielgruppe LIKE :g AND von > :start AND von < :end GROUP BY tt.id",
            ['g' => "%{$request->g}%", 'start' => $start, 'end' => $end]);
            
        } else if (isset($request->raum)) {
            $title = $request->raum;
            $kw = strtotime($dateParam);
            $start = date("Y-m-d",$kw);
            $end = date("Y-m-d",$kw+3600*24*7);
            $raum = $db->quote($request->raum);
            $res = DB::select("
            SELECT id,von,bis,kommentar,'' kurztitel,if(blocked=0,'frei','block') typ,status 
              FROM raumfrei WHERE raum_nummer = :raum AND von > :start AND von < :end
            UNION
            
            SELECT bb.id,von,bis,kommentar,tt.kurztitel ,'ok' typ, '' status
              FROM raumbedarf bb INNER JOIN termin tt on bb.termin_id=tt.id 
              WHERE raum = :raum AND von > :start AND von < :end
            UNION
            
            SELECT bb.id,von,bis,kommentar,tt.kurztitel ,'wunsch' typ, '' status
              FROM raumbedarf bb INNER JOIN termin tt on bb.termin_id=tt.id 
              WHERE praeferenz = :raum AND raum<>praeferenz AND von > :start AND von < :end",
              ['raum' => $request->raum, 'start' => $start, 'end' => $end]);
            
        } else {
            die("Bitte Gruppe g=STR oder raum=STR angeben");
        }

        return response()->json($res);
        
    }

}
