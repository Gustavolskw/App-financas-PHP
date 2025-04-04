<?php
namespace Acc\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property int $id
 * @property string $userId
 * @property string $userEmail
 * @property string $name
 * @property string $description
 * @property bool $status
 * @mixin \Illuminate\Database\Eloquent\Model
 * @package Auth\Entity
 */
class Account extends Model
{
    protected $table = 'accounts';

    protected $primaryKey = 'id';
    protected $fillable = ['userId', 'userEmail', 'name', 'description', 'status',];
    public $timestamps = true;

}