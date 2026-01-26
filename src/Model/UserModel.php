<?php

namespace App\Model;

use Symfony\Component\Serializer\Attribute\Groups;

readonly class UserModel
{
    public function __construct(
        #[Groups(['user:read'])]
        public int $id,
        #[Groups(['user:read'])]
        public string $login,
        #[Groups(['user:read'])]
        public string $city,
        #[Groups(['user:write'])]
        public array $roles

    ){}
}
