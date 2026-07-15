<?php

namespace MercadoPago\Net;

/** MPResource class. */
class MPResource
{
    private MPResponse $response;
    public ?string $financing_group;

    public function setResponse(MPResponse $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): MPResponse
    {
        return $this->response;
    }
}
