<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EpisodeController extends Controller
{
    public function show(Request $request, $serie_id, $episode_id){
        $ep = Episode::find($episode_id);
        foreach ($request->keys() as $key){
            $value = $request->get($key,'');
            if ($key=='next'){
                if ($value==1){
                    Auth::user()->checkEpisode($episode_id);
                    $epNext = Episode::all()->where('serie_id',$ep->serie_id)->where('saison',$ep->saison)->where('numero',$ep->numero+1)->first();
                    if ($epNext==null){
                        $epNext = Episode::all()->where('serie_id',$ep->serie_id)->where('saison',$ep->saison+1)->where('numero',1)->first();
                        if ($epNext==null){
                            return redirect('/serie');
                        }
                    }
                    return view('serie.episode',['episode' => $epNext]);
                }
            }
        }
        return view('serie.episode',['episode' => $ep]);
    }
}
