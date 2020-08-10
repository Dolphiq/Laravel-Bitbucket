<?php

declare(strict_types=1);

/*
 * This file is part of Laravel Bitbucket.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\Bitbucket\Auth\Authenticator;

use Bitbucket\Client;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use GrahamCampbell\Bitbucket\Events\TokenRefreshed;

/**
 * This is the oauth authenticator class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
final class OauthSecretAuthenticator extends AbstractAuthenticator
{
    /**
     * Authenticate the client, and return it.
     *
     * @param  string[]  $config
     *
     * @return Client
     * @throws InvalidArgumentException
     *
     */
    public function authenticate(array $config)
    {
        if (!$this->client) {
            throw new InvalidArgumentException('The client instance was not given to the oauth authenticator.');
        }

        if (!array_key_exists('key', $config)) {
            throw new InvalidArgumentException('The oauth secret authenticator requires a key.');
        }
        if (!array_key_exists('secret', $config)) {
            throw new InvalidArgumentException('The oauth secret authenticator requires a secret.');
        }

        // first fetch the token, then login with the new generated token with it

        $tokenResponse = Http::withOptions([
            'debug' => false,
        ])->asForm()->withBasicAuth($config['key'], $config['secret'])->
        post('https://bitbucket.org/site/oauth2/access_token', [
            'grant_type' => 'client_credentials'
        ])->json();

        $token = $tokenResponse['access_token'] ?? null;
        TokenRefreshed::dispatch($token);
        $this->client->authenticate(Client::AUTH_OAUTH_TOKEN, $token);
        return $this->client;
    }
}
