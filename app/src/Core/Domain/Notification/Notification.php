<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Notification;

// definindo a classe do agregador de notificações
class Notification
{
    // atributos
    private $errors = [];

    // função responsável por retornar os erros
    public function getErrors(): array
    {
        return $this->errors;
    }

    // função responsável por adicionar erros
    /**
     * @param $error array[context, mensage]
     */
    public function addError(array $error): void
    {
        array_push($this->errors, $error);
    }

    // função responsável por verificar se existem erros
    public function hasErrors(): bool
    {
        if (count($this->errors) > 0) return true;
        return false;
    }

    // função responsável por retornar as mensagens de erros
    public function messages(string $context = ''): string
    {
        // inicializando a string de mensagens
        $messages = '';

        // iterando no array de erros
        foreach ($this->errors as $error) {
            // se o contexto for vazio ou corresponder ao arqumento passado,
            if ($context === '' || $error['context'] == $context) {
                // incrementa a string de mensagens
                $messages .= "{$error['context']}: {$error['message']},";
            }
        }

        return $messages;
    }
}
