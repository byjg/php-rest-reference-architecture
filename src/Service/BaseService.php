<?php

namespace RestReferenceArchitecture\Service;

use ByJG\RestServer\Exception\Error404Exception;
use ByJG\Serializer\ObjectCopy;
use RestReferenceArchitecture\Repository\BaseRepository;

abstract class BaseService
{
    protected BaseRepository $repository;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function get($id)
    {
        return $this->repository->get($id);
    }

    public function getOrFail($id)
    {
        $result = $this->repository->get($id);
        if (empty($result)) {
            throw new Error404Exception('Id not found');
        }
        return $result;
    }

    public function list(?int $page = null, ?int $size = null): array
    {
        return $this->repository->list($page, $size);
    }

    public function create(array $payload)
    {
        $model = $this->repository->getMapper()->getEntity($payload);
        $this->repository->save($model);
        return $model;
    }

    public function update(array $payload)
    {
        $model = $this->getOrFail($payload['id'] ?? null);
        ObjectCopy::copy($payload, $model);
        $this->repository->save($model);
        return $model;
    }

    public function save($model): void
    {
        $this->repository->save($model);
    }

    public function delete($id): void
    {
        $this->repository->delete($id);
    }
}
