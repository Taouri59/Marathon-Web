<?php

namespace App\Http\Controllers;

use App\Models\Serie;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function profil(){
        $user = Auth::user();
        $tmp = $user->seen();
        $continuer = new Collection();
        $revoir = new Collection();
        foreach ($tmp as $ep){
            if ($user->isSeenSerie($ep->serie_id)){
                $revoir->add(Serie::find($ep->serie_id));
            } else {
                $continuer->add(Serie::find($ep->serie_id));
            }
        }
        $continuer = $continuer->unique();
        $revoir =  $revoir->unique();
        return view('user.profil',['user' => $user, 'continuer' => $continuer, 'revoir' => $revoir]);
    }
}
