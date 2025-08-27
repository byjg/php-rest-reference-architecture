<?php

namespace RestReferenceArchitecture\Rest;

use ByJG\Config\Exception\ConfigException;
use ByJG\Config\Exception\ConfigNotFoundException;
use ByJG\Config\Exception\DependencyInjectionException;
use ByJG\Config\Exception\InvalidDateException;
use ByJG\Config\Exception\KeyNotFoundException;
use ByJG\MicroOrm\Exception\InvalidArgumentException;
use ByJG\MicroOrm\Exception\OrmBeforeInvalidException;
use ByJG\MicroOrm\Exception\OrmInvalidFieldsException;
use ByJG\RestServer\Exception\Error400Exception;
use ByJG\RestServer\Exception\Error401Exception;
use ByJG\RestServer\Exception\Error403Exception;
use ByJG\RestServer\Exception\Error404Exception;
use ByJG\RestServer\HttpRequest;
use ByJG\RestServer\HttpResponse;
use ByJG\Serializer\ObjectCopy;
use OpenApi\Attributes as OA;
use ReflectionException;
use RestReferenceArchitecture\Model\Clientes;
use RestReferenceArchitecture\Psr11;
use RestReferenceArchitecture\Repository\ClientesRepository;
use RestReferenceArchitecture\Model\User;
use RestReferenceArchitecture\Util\JwtContext;
use RestReferenceArchitecture\Util\OpenApiContext;

class ClientesRest
{
    /**
     * Get the Clientes by id
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws Error401Exception
     * @throws Error404Exception
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ReflectionException
     */
    #[OA\Get(
        path: "/clientes/{id}",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Clientes"],
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(
            type: "integer",
            format: "int32"
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The object Clientes",
        content: new OA\JsonContent(ref: "#/components/schemas/Clientes")
    )]
    public function getClientes(HttpResponse $response, HttpRequest $request): void
    {
        JwtContext::requireAuthenticated($request);

        $clientesRepo = Psr11::get(ClientesRepository::class);
        $id = $request->param('id');

        $result = $clientesRepo->get($id);
        if (empty($result)) {
            throw new Error404Exception('Id not found');
        }
        $response->write(
            $result
        );
    }

    /**
     * List Clientes
     *
     * @param mixed $response
     * @param mixed $request
     * @return void
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws Error401Exception
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ReflectionException
     */
    #[OA\Get(
        path: "/clientes",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Clientes"]
    )]
    #[OA\Parameter(
        name: "page",
        description: "Page number",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "integer",
        )
    )]
    #[OA\Parameter(
        name: "size",
        description: "Page size",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "integer",
        )
    )]
    #[OA\Parameter(
        name: "orderBy",
        description: "Order by",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "string",
        )
    )]
    #[OA\Parameter(
        name: "filter",
        description: "Filter",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "string",
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The object Clientes",
        content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/Clientes"))
    )]
    #[OA\Response(
        response: 401,
        description: "Not Authorized",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    public function listClientes(HttpResponse $response, HttpRequest $request): void
    {
        JwtContext::requireAuthenticated($request);

        $repo = Psr11::get(ClientesRepository::class);

        $page = $request->get('page');
        $size = $request->get('size');
        // $orderBy = $request->get('orderBy');
        // $filter = $request->get('filter');

        $result = $repo->list($page, $size);
        $response->write(
            $result
        );
    }


    /**
     * Create a new Clientes
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @return void
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws Error400Exception
     * @throws Error401Exception
     * @throws Error403Exception
     * @throws InvalidArgumentException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ReflectionException
     */
    #[OA\Post(
        path: "/clientes",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Clientes"]
    )]
    #[OA\RequestBody(
        description: "The object Clientes to be created",
        required: true,
        content: new OA\JsonContent(
            required: ["nome", "email"],
            properties: [

                new OA\Property(property: "nome", type: "string", format: "string"),
                new OA\Property(property: "email", type: "string", format: "string"),
                new OA\Property(property: "telefone", type: "string", format: "string", nullable: true),
                new OA\Property(property: "cpf", type: "string", format: "string", nullable: true),
                new OA\Property(property: "dataCadastro", type: "string", format: "date-time", nullable: true),
                new OA\Property(property: "status", type: "string", default: "ativo", enum: ["ativo", "inativo", "pendente", "bloqueado"])
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "The object rto be created",
        content: new OA\JsonContent(
            required: ["id"],
            properties: [

                new OA\Property(property: "id", type: "integer", format: "int32")
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Not Authorized",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    public function postClientes(HttpResponse $response, HttpRequest $request): void
    {
        JwtContext::requireRole($request, User::ROLE_ADMIN);

        $payload = OpenApiContext::validateRequest($request);

        $model = new Clientes();
        ObjectCopy::copy($payload, $model);

        $clientesRepo = Psr11::get(ClientesRepository::class);
        $clientesRepo->save($model);

        $response->write(["id" => $model->getId()]);
    }


    /**
     * Update an existing Clientes
     *
     * @param HttpResponse $response
     * @param HttpRequest $request
     * @return void
     * @throws Error401Exception
     * @throws Error404Exception
     * @throws ConfigException
     * @throws ConfigNotFoundException
     * @throws DependencyInjectionException
     * @throws InvalidDateException
     * @throws KeyNotFoundException
     * @throws InvalidArgumentException
     * @throws OrmBeforeInvalidException
     * @throws OrmInvalidFieldsException
     * @throws Error400Exception
     * @throws Error403Exception
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ReflectionException
     */
    #[OA\Put(
        path: "/clientes",
        security: [
            ["jwt-token" => []]
        ],
        tags: ["Clientes"]
    )]
    #[OA\RequestBody(
        description: "The object Clientes to be updated",
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/Clientes")
    )]
    #[OA\Response(
        response: 200,
        description: "Nothing to return"
    )]
    #[OA\Response(
        response: 401,
        description: "Not Authorized",
        content: new OA\JsonContent(ref: "#/components/schemas/error")
    )]
    public function putClientes(HttpResponse $response, HttpRequest $request): void
    {
        JwtContext::requireRole($request, User::ROLE_ADMIN);

        $payload = OpenApiContext::validateRequest($request);

        $clientesRepo = Psr11::get(ClientesRepository::class);
        $model = $clientesRepo->get($payload['id']);
        if (empty($model)) {
            throw new Error404Exception('Id not found');
        }
        ObjectCopy::copy($payload, $model);

        $clientesRepo->save($model);
    }

    #[OA\Put(
        path: "/clientes/status",
        description: "Atualizar status do cliente (ativar/desativar/bloquear)",
        security: [["jwt-token" => []]],
        tags: ["Clientes"]
    )]
    #[OA\RequestBody(
        description: "The status update request",
        required: true,
        content: new OA\JsonContent(
            required: ["id", "status"],
            properties: [
                new OA\Property(property: "id", description: "Cliente ID", type: "integer", format: "int32"),
                new OA\Property(property: "status", description: "New status value", type: "string", enum: ["ativo", "inativo", "pendente", "bloqueado"])
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Status updated successfully",
        content: new OA\JsonContent(
            required: ["result", "message"],
            properties: [
                new OA\Property(property: "result", description: "Operation result", type: "string"),
                new OA\Property(property: "message", description: "Success message", type: "string")
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Invalid status value"
    )]
    #[OA\Response(
        response: 404,
        description: "Cliente not found"
    )]
    public function putClienteStatus(HttpResponse $response, HttpRequest $request): void
    {
        // 1. Autenticação
        JwtContext::requireRole($request, User::ROLE_ADMIN);

        // 2. Validação de entrada
        $payload = OpenApiContext::validateRequest($request);
        $this->validateStatusValue($payload['status']);

        // 3. Lógica de negócio
        $model = $this->findAndValidateCliente($payload['id']);
        $this->updateClienteStatus($model, $payload['status']);

        // 4. Resposta padronizada
        $this->sendSuccessResponse($response, $payload['status']);
    }

    /**
     * Validate status value against business rules
     */
    private function validateStatusValue(string $status): void
    {
        $validStatuses = ['ativo', 'inativo', 'pendente', 'bloqueado'];

        if (!in_array($status, $validStatuses)) {
            throw new Error400Exception('Status inválido. Deve ser um dos: ' . implode(', ', $validStatuses));
        }
    }

    /**
     * Find and validate cliente exists
     */
    private function findAndValidateCliente(int $id): Clientes
    {
        $clienteRepo = Psr11::get(ClientesRepository::class);
        $model = $clienteRepo->get($id);

        if (empty($model)) {
            throw new Error404Exception('Cliente não encontrado');
        }

        return $model;
    }

    /**
     * Update cliente status with business logic
     */
    private function updateClienteStatus(Clientes $model, string $status): void
    {
        // Regras de negócio específicas
        if ($status === 'bloqueado' && $model->getStatus() === 'ativo') {
            // Log da ação de bloqueio
            // Notificar cliente sobre bloqueio
        }

        $model->setStatus($status);
        $clienteRepo = Psr11::get(ClientesRepository::class);
        $clienteRepo->save($model);
    }

    /**
     * Send standardized success response
     */
    private function sendSuccessResponse(HttpResponse $response, string $status): void
    {
        $response->write([
            "result" => "ok",
            "message" => "Status atualizado com sucesso para: " . $status
        ]);
    }

}
