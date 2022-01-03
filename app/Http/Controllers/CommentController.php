<?php

namespace App\Http\Controllers;


use App\Models\Comment;
use App\Models\Serie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CommentController extends Controller
{
    public function create($serie_id)
    {
        if(!Auth::check()) return redirect('/serie/'.$serie_id);
        $serie=Serie::find($serie_id);
        return view('comment.create',['serie'=>$serie]);
    }

public function index(){
dd('index');
}

public function show(Request $request , $serie_id , $id){
        $value = $request->get('_method');
        if($value=='PATCH')$this->update($request , $serie_id ,$id);
}

    public function store(Request $request,$serie_id)
    {
        $this->validate(
            $request,
            [
                'contenu' => 'required',
                'note' => 'required'
            ]
        );

        $commentaire= new Comment();
        $user = Auth::user();
        $serie=Serie::find($serie_id);

        if($user!=null){
            $commentaire->serie_id = $serie->id;
            $commentaire->user_id = $user->id;
            $commentaire->content = $request->contenu;
            $commentaire->note = $request->note;
            if(isset($request->validated) && $request->validated == "on")
                $commentaire->validated = 1;
            else
                $commentaire->validated = 0;

            $commentaire->save();
        }

        return redirect('serie/'.$serie_id);
    }

    public function edit($serie_id ,$id)
    {
        $commentaire = Comment::find($id);
        return view('comment.edit', ['commentaire' => $commentaire , 'serie_id'=>$serie_id]);
    }

    public function update(Request $request, $serie_id ,$id)
    {
        $this->validate(
            $request,
            [
                'contenu' => 'required',
                'note' => 'required'
            ]
        );
        $commentaire = Comment::findOrFail($id);
        $commentaire->content=$request->get('contenu');
        $commentaire->note=$request->get('note');
        $commentaire->save();

        return redirect()->route('home');
    }

    public function destroy(Request $request, $id)
    {
        dd('destroy');
        $commentaire = Comment::find($id);

        $commentaire->delete();

        return back();
    }
}