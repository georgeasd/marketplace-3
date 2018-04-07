<?php

namespace App\Api\Request\DB\Offer;


use App\Api\Request\Request;
use App\Api\Response\Response;
use App\Offer;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

class OfferMarkAppropriateRequest extends Request
{

    /** @var Guard */
    protected $guard;

    /**
     * @param Guard $guard
     */
    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * @inheritDoc
     */
    protected function shouldResolve()
    {
        /** @var User|null $user */
        $user = $this->guard->user();

        return $user && $user->is_admin;
    }

    /**
     * @inheritDoc
     */
    protected function rules(Validator $validator = null)
    {
        return [
            'id' => 'required|integer',
        ];
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    protected function doResolve($name, Collection $parameters)
    {
        /** @var Offer $offer */
        $offer = Offer::query()->where([
            'id' => $parameters['id'],
        ])->first();

        if ($offer && $offer->markAppropriate() && $offer->save()) {
            return new Response(true, new \App\Http\Resources\Offer($offer));
        } else {
            return new Response(false, []);
        }
    }
}