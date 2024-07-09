<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class GameOn extends Model
{
  protected $columns = array('id','player1','player2','game_type','winner','loser','market_deck','play_deck','player1_deck','player2_deck','player1_moves','pend','player2_moves','turn','amt','bet_no','command_change','fail_one','fail_one_data','fail_two','fail_two_data','game_status','timer_one','timer_two','timer_count_one','timer_count_two','game_status','created_at','updated_at'); // add all columns from you table

  public function scopeExclude($query,$value = array())
  {
    return $query->select( array_diff( $this->columns,(array) $value) );
  }
}
