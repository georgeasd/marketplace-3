<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\Resource;

/**
 * User JSON resource
 *
 * @package App\Http\Resources
 */
class User extends Resource
{

    /**
     * @inheritDoc
     */
    public function toArray($request)
    {
        /** @var \App\User|User $this */

        return [
            'username' => $this->username,
            'email' => $this->email,
            'display_name' => $this->display_name,
            'status' => $this->status,
            'description' => $this->description,
            'profile_image' => $this->profile_image
                ? Image::make($this->profile_image) : null,
        ];
    }
}