<?php

namespace RestReferenceArchitecture\Repository;

use RestReferenceArchitecture\Psr11;
use ByJG\MicroOrm\Exception\OrmModelInvalidException;
use ReflectionException;
use ByJG\AnyDataset\Db\DbDriverInterface;
use ByJG\MicroOrm\Query;
use ByJG\MicroOrm\Repository;
use RestReferenceArchitecture\Model\Clientes;

class ClientesRepository extends BaseRepository
{
    /**
     * ClientesRepository constructor.
     *
     * @param DbDriverInterface $dbDriver
     * @throws OrmModelInvalidException
     * @throws ReflectionException
     */
    public function __construct(DbDriverInterface $dbDriver)
    {
        $this->repository = new Repository($dbDriver, Clientes::class);
    }


    /**
     * @param mixed $email
     * @return null|Clientes[]
     */
    public function getByEmail($email)
    {
        $query = Query::getInstance()
            ->table('clientes')
            ->where('clientes.email = :value', ['value' => $email]);
        $result = $this->repository->getByQuery($query);
        return $result;
    }

    /**
     * @param mixed $cpf
     * @return null|Clientes[]
     */
    public function getByCpf($cpf)
    {
        $query = Query::getInstance()
            ->table('clientes')
            ->where('clientes.cpf = :value', ['value' => $cpf]);
        $result = $this->repository->getByQuery($query);
        return $result;
    }

    /**
     * @param mixed $nome
     * @return null|Clientes[]
     */
    public function getByNome($nome)
    {
        $query = Query::getInstance()
            ->table('clientes')
            ->where('clientes.nome = :value', ['value' => $nome]);
        $result = $this->repository->getByQuery($query);
        return $result;
    }

    /**
     * @param mixed $email
     * @return null|Clientes[]
     */
    public function getByEmail($email)
    {
        $query = Query::getInstance()
            ->table('clientes')
            ->where('clientes.email = :value', ['value' => $email]);
        $result = $this->repository->getByQuery($query);
        return $result;
    }

    /**
     * @param mixed $cpf
     * @return null|Clientes[]
     */
    public function getByCpf($cpf)
    {
        $query = Query::getInstance()
            ->table('clientes')
            ->where('clientes.cpf = :value', ['value' => $cpf]);
        $result = $this->repository->getByQuery($query);
        return $result;
    }

    /**
     * @param mixed $dataCadastro
     * @return null|Clientes[]
     */
    public function getByDataCadastro($dataCadastro)
    {
        $query = Query::getInstance()
            ->table('clientes')
            ->where('clientes.data_cadastro = :value', ['value' => $dataCadastro]);
        $result = $this->repository->getByQuery($query);
        return $result;
    }

}
