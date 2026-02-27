<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TInvitationCodeMng
 * 
 * @property int $id
 * @property Carbon $generated_time
 * @property string $invitation_code
 * @property int $operate_times
 * @property int $status
 * @property string|null $user_ip
 * @property Carbon $update_time
 *
 * @package App\Models
 */
class TInvitationCodeMng extends Model
{
	protected $table = 't_invitation_code_mng';
	public $timestamps = false;

	protected $casts = [
		'generated_time' => 'datetime',
		'operate_times' => 'int',
		'status' => 'int',
		'update_time' => 'datetime'
	];

	protected $fillable = [
		'generated_time',
		'invitation_code',
		'operate_times',
		'status',
		'user_ip',
		'update_time'
	];
}
