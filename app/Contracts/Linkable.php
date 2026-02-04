<?php

namespace App\Contracts;

interface Linkable
{
    public function getLinkableType(): string;

    public function getLinkableId(): int;
}
