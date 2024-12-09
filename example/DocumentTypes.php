<?php

namespace Maestro\Example;

enum DocumentTypes: string
{
    case CPF   = 'cpf';
    case CNPJ  = 'cnpj';
    case CNH   = 'cnh';
    case CRLV  = 'crlv';
    case RNTMA = 'renagro';
}
