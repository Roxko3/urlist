<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $text
 * @property string $url
 * @property int $user_id 
 */
#[Fillable(['text', 'url', 'user_id'])]
#[Hidden(['user_id'])]
class Url extends Model
{
    protected function url(): Attribute
    {
        return Attribute::make(
            set: function ($value)
            {
                if(!Str::startsWith($value, 'http'))
                {
                    $value = 'http://' . $value;
                }

                return $value;
            }
        );
    }
}