<?php

namespace GrahamCampbell\Bitbucket\Events;

use Illuminate\Foundation\Events\Dispatchable;

class TokenRefreshed
{
    use Dispatchable;

    public $token;

    /**
     * Create a new event instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

}
