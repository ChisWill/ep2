<?php

declare(strict_types=1);

namespace Ep\Auth\Method;

use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HttpSession implements AuthenticationMethodInterface
{
    public function __construct(
        private IdentityRepositoryInterface $identityRepository,
        private SessionInterface $session,
        private string $id = '__id'
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        $id = $this->session->get($this->id);
        return $id ? $this->identityRepository->findIdentity($id) : null;
    }

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
}
