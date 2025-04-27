<?php

namespace App\Interfaces;

interface OrderRepositoryInterface
{
    public function index(array $query);

    public function show($id);

    public function store(array $data);

    public function update(array $data, $id);

    public function delete($id);
}
