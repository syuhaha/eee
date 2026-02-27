<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TExistingAccount
 * 
 * @property int $id
 * @property Carbon $acquire_time
 * @property string $account
 * @property string $password
 * @property int $status
 * @property Carbon $update_time
 * @property Carbon|null $failure_time
 *
 * @package App\Models
 */
class TExistingAccount extends Model
{
	protected $table = 't_existing_account';
	public $timestamps = false;

	protected $casts = [
		'acquire_time' => 'datetime',
		'status' => 'int',
		'update_time' => 'datetime',
		'failure_time' => 'datetime'
	];

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'acquire_time',
		'account',
		'password',
		'status',
		'update_time',
		'failure_time'
	];
}
