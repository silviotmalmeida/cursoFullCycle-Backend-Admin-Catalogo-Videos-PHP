<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Factory;

// importações
use Core\Domain\Validation\ValidatorInterface;
use Core\Domain\Validation\VideoLaravelValidator;

// classe-fábrica do validador, para evitar acoplamento na entidade
class VideoValidatorFactory
{
    // método de criação
    public static function create(): ValidatorInterface
    {
        // utilizando o validador do Laravel
        return new VideoLaravelValidator();
    }
}
