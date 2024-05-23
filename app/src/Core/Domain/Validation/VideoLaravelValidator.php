<?php

// definindo o namespace, referente ao caminho das pastas
namespace Core\Domain\Validation;

// importações
use Core\Domain\Entity\Entity;
use Core\Domain\ValueObject\ValueObject;
use Illuminate\Support\Facades\Validator;

// definindo o validador da entidade video
class VideoLaravelValidator implements ValidatorInterface
{
    // função de validação dos atributos
    public function validate(Entity|ValueObject $object): void
    {
        // convertendo o objeto para array
        $data = $this->convertForArray($object);

        // regras de validação, utilizando o Laravel
        $validator = Validator::make($data, [
            'title' => 'required|min:3|max:255',
            'description' => 'required|min:3|max:255',
            'yearLaunched' => 'required|integer|min:1',
            'duration' => 'required|integer|min:1',
        ]);

        // em caso de falha de validação,
        if ($validator->fails()) {
            // popula o array do agregador de notificações
            foreach ($validator->errors()->messages() as $error) {
                $object->notification->addError([
                    'context' => 'video',
                    'message' => $error[0],
                ]);
            }
        }
    }

    // função auxiliar de conversão do objeto em array, considerando somente alguns atributos
    private function convertForArray(Entity $object): array
    {
        // selecionando os atributos a serem considerados
        return [
            'title' => $object->title,
            'description' => $object->description,
            'yearLaunched' => $object->yearLaunched,
            'duration' => $object->duration,
        ];
    }
}
