<?php

namespace App\Repositories;

use Illuminate\Http\Request;

interface RepositoryInterface
{
    public function all($page, $limit);

    public function find(int $id);

    public function create(array $data);
}
