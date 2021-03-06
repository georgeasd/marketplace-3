<?php

namespace App\Api\Request\DB\Chat;


use App\Api\Request\DB\MultiRequest;
use App\Events\MessageReceived;
use App\Message;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

/**
 * API request to fetch chat messages between the current user and another user
 *
 * @package App\Api\Request\DB\Chat
 */
class MessagesRequest extends MultiRequest
{
    protected $modelClass = Message::class;

    protected $defaultScope = Message::SCOPE_PERSONAL;

    protected $orderBased = true;

    protected $perPageDefault = 15;

    /**
     * @var Guard
     */
    protected $guard;

    /**
     * MessagesRequest constructor.
     *
     * @param Guard $guard
     */
    public function __construct(Guard $guard)
    {
        parent::__construct($this->modelClass, $this->resourceClass,
            $this->orderBased);
        $this->guard = $guard;
    }

    /**
     * @inheritDoc
     */
    protected function shouldResolve()
    {
        return $this->guard->check();
    }

    /**
     * @inheritDoc
     *
     * @param Validator|null $validator
     *
     * @return array
     */
    protected function rules(
        Collection $parameters,
        Validator $validator = null
    )
    {
        return [
            'with' => 'sometimes|string',
        ];
    }

    /**
     * @inheritDoc
     *
     * @param Collection $parameters
     *
     * @return array
     */
    protected function urlParameters(Collection $parameters)
    {
        return ['with'];
    }

    /**
     * @inheritDoc
     *
     * @param            $query
     * @param Collection $parameters
     *
     * @return \Illuminate\Database\Eloquent\Builder|Builder|\Laravel\Scout\Builder
     */
    protected function additionalQuery($query, Collection $parameters)
    {
        parent::additionalQuery($query, $parameters);

        $with = $parameters['with'];

        return $query->whereNested(function ($query) use ($with) {
            /** @var Builder $query */
            return $query
                ->where(['to_username' => $with])
                ->orWhere(['from_username' => $with]);
        });
    }

    /**
     * @inheritDoc
     *
     * @param $results
     */
    protected function onResults($results)
    {
        /** @var User $user */
        $user = $this->guard->user();
        foreach ($results as $message) {
            /** @var Message $message */
            if ( ! $message->read
                && $message->to_username === $user->username
            ) {
                $message->received = true;
                $message->read     = true;
                $message->save();

                broadcast(new MessageReceived($message, $user))->toOthers();
            }
        }
    }

    /**
     * @inheritDoc
     * @param Collection $parameters
     *
     * @return string
     */
    protected function resourceClass(Collection $parameters)
    {
        return \App\Http\Resources\Message::class;
    }
}