<?php

namespace RestReferenceArchitecture\Model;

use ByJG\MicroOrm\Attributes\FieldAttribute;
use ByJG\MicroOrm\Attributes\FieldUuidAttribute;
use ByJG\MicroOrm\Attributes\TableAttribute;
use ByJG\MicroOrm\Attributes\TableMySqlUuidPKAttribute;
use ByJG\MicroOrm\Literal\HexUuidLiteral;
use OpenApi\Attributes as OA;

/**
 * Class Clientes
 * @package RestReferenceArchitecture\Model
 */
#[OA\Schema(required: ["id", "nome", "email"], type: "object", xml: new OA\Xml(name: "Clientes"))]
#[TableAttribute("clientes")]
class Clientes
{

    /**
     * @var int|null
     */
    #[OA\Property(type: "integer", format: "int32")]
    #[FieldAttribute(primaryKey: true, fieldName: "id")]
    protected int|null $id = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string")]
    #[FieldAttribute(fieldName: "nome")]
    protected string|null $nome = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string")]
    #[FieldAttribute(fieldName: "email")]
    protected string|null $email = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string", nullable: true)]
    #[FieldAttribute(fieldName: "telefone")]
    protected string|null $telefone = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "string", nullable: true)]
    #[FieldAttribute(fieldName: "cpf")]
    protected string|null $cpf = null;

    /**
     * @var string|null
     */
    #[OA\Property(type: "string", format: "date-time", nullable: true)]
    #[FieldAttribute(fieldName: "data_cadastro")]
    protected string|null $dataCadastro = null;

    /**
     * @var string|null
     */
    #[OA\Property(
        description: "Status do cliente no sistema",
        type: "string",
        default: "ativo",
        enum: ["ativo", "inativo", "pendente", "bloqueado"])]
    #[FieldAttribute(fieldName: "status")]
    protected ?string $status = null;

    /**
     * @return int|null
     */
    public function getId(): int|null
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId(int|null $id): static
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNome(): string|null
    {
        return $this->nome;
    }

    /**
     * @param string|null $nome
     * @return $this
     */
    public function setNome(string|null $nome): static
    {
        $this->nome = $nome;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): string|null
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     * @return $this
     */
    public function setEmail(string|null $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTelefone(): string|null
    {
        return $this->telefone;
    }

    /**
     * @param string|null $telefone
     * @return $this
     */
    public function setTelefone(string|null $telefone): static
    {
        $this->telefone = $telefone;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCpf(): string|null
    {
        return $this->cpf;
    }

    /**
     * @param string|null $cpf
     * @return $this
     */
    public function setCpf(string|null $cpf): static
    {
        $this->cpf = $cpf;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDataCadastro(): string|null
    {
        return $this->dataCadastro;
    }

    /**
     * @param string|null $dataCadastro
     * @return $this
     */
    public function setDataCadastro(string|null $dataCadastro): static
    {
        $this->dataCadastro = $dataCadastro;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return $this
     */
    public function setStatus(?string $status): static
    {
        $this->status = $status;
        return $this;
    }

}
