<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Episode;
use App\Models\Serie;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SerieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $series = DB::table('series');
        foreach ($request->keys() as $key){
            $value = $request->get($key,'');
            if ($key == 'tri'){
                if (in_array($value,['nom','genre','premiere','note'])){
                    $series = $series->orderByRaw($value.' IS NULL, '.$value.' '.(in_array($value,['note','premiere'])?'DESC':'ASC'));
                }
            } else if ($key == 'genre'){
                $series = $series->where($key, $value);
            } else if ($key == 'nom'){
                $value = strtoupper($value);
                $series = $series->whereRaw(DB::raw("UPPER(nom) LIKE '%".$value."%'"));
            }
        }
        return view('serie.index',['series' => $series->get()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Application|Factory|View|Response
     */
    public function show(Request $request, $id)
    {
        $serie = Serie::findOrFail($id);
        foreach ($request->keys() as $key){
            $value = $request->get($key,'');
            if ($key=='review'){
                if ($value=='1'){
                    Auth::user()->review($id);
                    $ep = $serie->episodes()->where('saison',1)->where('numero',1)->first();
                    return redirect(route('serie.episode',['serie_id'=>$id,'episode_id'=>$ep->id]));
                }
            }
        }
        $saisons = Episode::all()->where('serie_id',$serie->id)->groupBy('saison')->sortBy('numero');
        $comments = Comment::all()->where('serie_id', $serie->id)->sortBy('note');
        return view('serie.show',['serie'=>$serie, 'saisons' => $saisons, 'comments' => $comments]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
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
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function home(){
        $news=Serie::all()->sortBy('premiere', null, true)->take(5);
        $notes=Serie::all()->sortBy('note', null, true)->take(5);
        return view('welcome',['news'=>$news, 'notes'=>$notes]);
    }

    public function check($id){
        Auth::user()->checkSeen($id);
        return redirect('/serie/'.$id);
    }

}
