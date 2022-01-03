<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use phpDocumentor\Reflection\Types\Boolean;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'administrateur',
        'avatar'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    function comments() {
        return $this-> hasMany(Comment::class);
    }

    function seen() {
        return $this->belongsToMany(Episode::class, 'seen')
            ->as('when')
            ->withPivot('date_seen')
            ->get();
    }

    public function seenEpisode($episode_id){
        return $this->seen()->where('id',$episode_id)->count()>0;
    }

    public function isSeenSerie($serie_id){
        $epsSerie = Serie::find($serie_id)->episodes();
        $epsSeen = $this->seen();
        $result = false;
        foreach ($epsSerie as $epSerie){
            $result = false;
            foreach ($epsSeen as $epSeen){
                if ($epSeen->id == $epSerie->id){
                    $result = true;
                    break;
                }
            }
            if (!$result) break;
        }
        return $result;
    }

    public function checkSeen($serie_id){
        $epsSerie = Serie::find($serie_id)->episodes();
        $epsSeen = $this->seen();
        foreach ($epsSerie as $epSerie){
            $find = false;
            foreach ($epsSeen as $epSeen){
                if ($epSeen->id == $epSerie->id){
                    $find = true;
                    break;
                }
            }
            if (!$find){
                DB::table('seen')->insert(['user_id' => $this->id, 'episode_id' => $epSerie->id, 'date_seen' => now()]);
            }
        }
    }

    public function checkEpisode($episode_id){
        $ep = Episode::find($episode_id);
        if ($ep->seen()->where('user_id',$this->id)->count() == 0){
            DB::table('seen')->insert(['user_id' => $this->id, 'episode_id' => $ep->id, 'date_seen' => now()]);
        }
    }

    public function review($serie_id){
        $epsSerie = Serie::find($serie_id)->episodes();
        foreach ($epsSerie as $ep){
            DB::table('seen')->where('user_id', $this->id)->where('episode_id', $ep->id)->delete();
        }
    }

    public function getCurrentEpisode($serie_id){
        $epsSerie = Serie::find($serie_id)->episodes();
        $epsSeen = $this->seen();
        $ep = null;
        foreach ($epsSerie as $epSerie){
            foreach ($epsSeen as $epSeen){
                if ($epSeen->id == $epSerie->id){
                    if ($ep==null || $epSeen->saison >= $ep->saison){
                        if ($ep!=null && $epSeen->saison == $ep->saison && $epSeen->numero < $ep->numero) {break;}
                        $ep = $epSeen;
                    }
                    break;
                }
            }
        }
        if  ($ep==null){
            $ep = $epsSerie->where('saison',1)->where('numero',1)->first();
        } else {
            $epNext = $epsSerie->where('saison',$ep->saison)->where('numero',$ep->numero+1)->first();;
            if ($epNext==null){
                $epNext = Episode::all()->where('serie_id',$ep->serie_id)->where('saison',$ep->saison+1)->where('numero',1)->first();
                if ($epNext==null){
                    return redirect('/serie');
                }
            }
            $ep = $epNext;
        }
        return $ep;
    }
}
