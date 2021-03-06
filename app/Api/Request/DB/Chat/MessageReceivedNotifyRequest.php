<?php

namespace App\Api\Request\DB\Chat;


use App\Api\Request\Request;
use App\Api\Response\Response;
use App\Events\MessageReceived;
use App\Message;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

/**
 * API Request to notify that a chat message has been received.
 *
 * @package App\Api\Request\DB\Chat
 */
class MessageReceivedNotifyRequest extends Request
{
    /** @var Guard */
    protected $guard;

    /**
     * MessageReceivedNotifyRequest constructor.
     *
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
            'id' => 'required_if:ids,null|sometimes|integer',
            'ids' => 'required_if:id,null|sometimes|array',
            'read' => 'sometimes|boolean',
        ];
    }

    /**
     * @inheritDoc
     *
     * @param            $name
     * @param Collection $parameters
     *
     * @return Response
     */
    protected function doResolve($name, Collection $parameters)
    {
        /** @var User $user */
        $user = $this->guard->user();
        $ids  = $parameters->get('ids', null);
        $read = $parameters->get('read', false);

        if ($ids === null) {
            $ids = [$parameters['id']];
        }

        /** @var Message[] $messages */
        $messages = Message::query()
            ->whereNested(function ($query) use ($ids) {
                /** @var Builder $query */
                foreach ($ids as $id) {
                    $query->orWhere(['id' => $id]);
                }

                return $query;
            })
            ->where(['to_username' => $user->username])
            ->get(['id', 'received', 'read']);

        if (count($messages) === 0) {
            return new Response(false, []);
        }

        foreach ($messages as $message) {
            $message->received = true;

            if ($read) {
                $message->read = true;
            }

            $message->save();

            broadcast(new MessageReceived($message, $user))->toOthers();
        }

        return new Response(true, []);
    }
}