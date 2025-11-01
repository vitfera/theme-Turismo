<?php

namespace Turismo;

use MapasCulturais\App;
use MapasCulturais\Definitions;
use MapasCulturais\i;
use MapasCulturais\Utils;

class Theme extends \MapasCulturais\Themes\BaseV2\Theme
{
    public function _init()
    {
        $app = App::i();
        $self = $this;

        // Campos obrigatórios para Atrativos Turísticos (Spaces)
        $requiredSpaceFields = [
            'endereco',
            'En_CEP',
            'En_Nome_Logradouro',
            'En_Num',
            'En_Bairro',
            'En_Municipio', 
            'En_Estado',
            'turismo_horario_funcionamento',
            'turismo_contato',
            'turismo_tipo_atrativo',
            'turismo_segmento_turistico'
        ];

        // Campos obrigatórios para Roteiros Turísticos (Projects)
        $requiredProjectFields = [
            'turismo_duracao_estimada',
            'turismo_ponto_partida',
            'turismo_categoria_roteiro'
        ];

        /*
         * Modifica a consulta da API de espaços para só retornar Atrativos Turísticos
         */
        $app->hook('ApiQuery(Space).where', function(&$where) use ($app) {
            $where .= "
                AND (e._type BETWEEN 80 AND 89)
            ";
        });

        /*
         * Modifica a consulta da API de projetos para só retornar Roteiros Turísticos
         */
        $app->hook('ApiQuery(Project).where', function(&$where) use ($app) {
            $where .= "
                AND (e._type BETWEEN 80 AND 89)
            ";
        });

        // Ajusta pseudo query para identificar a tela de busca de atrativos
        $app->hook('search-spaces-initial-pseudo-query', function(&$initial_pseudo_query){
            $initial_pseudo_query['@turismo'] = 1;
        });

        // Evita que sejam carregados outros tipos na tela de busca de atrativos
        $app->hook('ApiQuery(Space).params', function(&$params){
            if(($params['@turismo'] ?? false) && empty($params['type'])) {
                $params['type'] = "BET(80,89)";
                unset($params['@turismo']);
            }
        });

        // Ajusta pseudo query para identificar a tela de busca de roteiros
        $app->hook('search-projects-initial-pseudo-query', function(&$initial_pseudo_query){
            $initial_pseudo_query['@turismo'] = 1;
        });

        // Evita que sejam carregados outros tipos na tela de busca de roteiros
        $app->hook('ApiQuery(Project).params', function(&$params){
            if(($params['@turismo'] ?? false) && empty($params['type'])) {
                $params['type'] = "BET(80,89)";
                unset($params['@turismo']);
            }
        });

        // Remove tipos não turísticos da interface
        $app->hook('mapas.printJsObject:before', function() use ($app) {
            // Para espaços - só mostrar tipos de atrativos turísticos
            $space_options = $this->jsObject['EntitiesDescription']['space']['type']['options'];
            $space_options_order = $this->jsObject['EntitiesDescription']['space']['type']['optionsOrder'];

            $new_space_options = [];
            foreach($space_options as $id => $value) {
                if($id >= 80 && $id <= 89) {
                    $new_space_options[$id] = $value;
                }
            }

            $space_options_order = array_filter($space_options_order, fn($id) => $id >= 80 && $id <= 89);

            $this->jsObject['EntitiesDescription']['space']['type']['options'] = $new_space_options;
            $this->jsObject['EntitiesDescription']['space']['type']['optionsOrder'] = $space_options_order;

            // Para projetos - só mostrar tipos de roteiros turísticos
            $project_options = $this->jsObject['EntitiesDescription']['project']['type']['options'];
            $project_options_order = $this->jsObject['EntitiesDescription']['project']['type']['optionsOrder'];

            $new_project_options = [];
            foreach($project_options as $id => $value) {
                if($id >= 80 && $id <= 89) {
                    $new_project_options[$id] = $value;
                }
            }

            $project_options_order = array_filter($project_options_order, fn($id) => $id >= 80 && $id <= 89);

            $this->jsObject['EntitiesDescription']['project']['type']['options'] = $new_project_options;
            $this->jsObject['EntitiesDescription']['project']['type']['optionsOrder'] = $project_options_order;

        }, 1000);

        // Validação de campos obrigatórios para Spaces
        $app->hook('GET(space.edit):before', function () use($self, $requiredSpaceFields) {
            $self->spaceRequiredProperties($requiredSpaceFields);
        });

        $app->hook('PUT(space.single):before', function () use($self, $requiredSpaceFields) {
            $self->spaceRequiredProperties($requiredSpaceFields);
        });

        // Validação de campos obrigatórios para Projects
        $app->hook('GET(project.edit):before', function () use($self, $requiredProjectFields) {
            $self->projectRequiredProperties($requiredProjectFields);
        });

        $app->hook('PUT(project.single):before', function () use($self, $requiredProjectFields) {
            $self->projectRequiredProperties($requiredProjectFields);
        });

        /* ALTERA O TIPO DE REQUISIÇÃO DO SALVAMENTO PARA PUT */
        $app->hook('view(space.edit).updateMethod', function(&$update_method) {
            $update_method = 'PUT';
        });

        $app->hook('view(project.edit).updateMethod', function(&$update_method) {
            $update_method = 'PUT';
        });

        // Validação de erros para Spaces
        $app->hook('entity(Space).validationErrors', function (&$errors) use($requiredSpaceFields) {
            if($this->isNew()) {
               return; 
            }

            foreach($requiredSpaceFields as $field) {
                if(!$this->$field && !isset($errors[$field])) {
                    $errors[$field] = [i::__('campo obrigatório')];
                }
            }
        });

        // Validação de erros para Projects
        $app->hook('entity(Project).validationErrors', function (&$errors) use($requiredProjectFields) {
            if($this->isNew()) {
               return; 
            }

            foreach($requiredProjectFields as $field) {
                if(!$this->$field && !isset($errors[$field])) {
                    $errors[$field] = [i::__('campo obrigatório')];
                }
            }
        });

        parent::_init();

        // Registrar tipos de espaços turísticos
        $app->hook('mapas.config(space-types)', function(&$config) {
            $config[\MapasCulturais\i::__('Atrativos')] = [
                'range' => [80, 89],
                'items' => [
                    80 => ['name' => \MapasCulturais\i::__('Atrativo Natural')],
                    81 => ['name' => \MapasCulturais\i::__('Atrativo Cultural/Histórico')],
                    82 => ['name' => \MapasCulturais\i::__('Atrativo Religioso/Fé')],
                    83 => ['name' => \MapasCulturais\i::__('Atrativo Gastronômico')],
                    84 => ['name' => \MapasCulturais\i::__('Atrativo Rural/Vivência')],
                    85 => ['name' => \MapasCulturais\i::__('Esporte e Aventura')],
                    86 => ['name' => \MapasCulturais\i::__('Centro de Atendimento ao Turista')],
                    87 => ['name' => \MapasCulturais\i::__('Equipamento de Apoio')],
                ]
            ];
        });

        // Registrar tipos de projetos turísticos (roteiros)
        $app->hook('mapas.config(project-types)', function(&$config) {
            $config[\MapasCulturais\i::__('Roteiros')] = [
                'range' => [80, 89],
                'items' => [
                    80 => ['name' => \MapasCulturais\i::__('Roteiro Cultural')],
                    81 => ['name' => \MapasCulturais\i::__('Roteiro Ecológico')],
                    82 => ['name' => \MapasCulturais\i::__('Roteiro Gastronômico')],
                    83 => ['name' => \MapasCulturais\i::__('Roteiro Religioso')],
                    84 => ['name' => \MapasCulturais\i::__('Roteiro de Aventura')],
                    85 => ['name' => \MapasCulturais\i::__('Roteiro Rural')],
                    86 => ['name' => \MapasCulturais\i::__('Roteiro Náutico')],
                ]
            ];
        });

        // Registrar taxonomias específicas do turismo
        $app->hook('mapas.config(taxonomies)', function(&$config) {
            // Taxonomias para Espaços (Atrativos)
            $config['turismo_tipo_atrativo'] = [
                'required' => true,
                'entities' => ['space'],
                'restrictToTypes' => ['space' => [80, 81, 82, 83, 84, 85, 86, 87]],
                'terms' => [
                    'atrativo-natural' => \MapasCulturais\i::__('Atrativo natural'),
                    'atrativo-cultural-historico' => \MapasCulturais\i::__('Atrativo cultural/histórico'),
                    'religioso-fe' => \MapasCulturais\i::__('Religioso / de fé'),
                    'gastronomico' => \MapasCulturais\i::__('Gastronômico'),
                    'rural-vivencia' => \MapasCulturais\i::__('Rural / de vivência'),
                    'esporte-aventura' => \MapasCulturais\i::__('Esporte e aventura'),
                    'cat' => \MapasCulturais\i::__('Centro de atendimento ao turista (CAT)'),
                    'equipamento-de-apoio' => \MapasCulturais\i::__('Equipamento de apoio'),
                ]
            ];

            $config['turismo_segmento_turistico'] = [
                'required' => true,
                'entities' => ['space'],
                'restrictToTypes' => ['space' => [80, 81, 82, 83, 84, 85, 86, 87]],
                'terms' => [
                    'turismo-cultural' => \MapasCulturais\i::__('Turismo cultural'),
                    'ecoturismo' => \MapasCulturais\i::__('Ecoturismo'),
                    'turismo-de-experiencia' => \MapasCulturais\i::__('Turismo de experiência'),
                    'turismo-religioso' => \MapasCulturais\i::__('Turismo religioso'),
                    'turismo-gastronomico' => \MapasCulturais\i::__('Turismo gastronômico'),
                    'turismo-de-eventos' => \MapasCulturais\i::__('Turismo de eventos'),
                    'turismo-nautico' => \MapasCulturais\i::__('Turismo náutico'),
                    'turismo-rural' => \MapasCulturais\i::__('Turismo rural'),
                ]
            ];

            $config['turismo_servicos_disponiveis'] = [
                'required' => false,
                'entities' => ['space'],
                'restrictToTypes' => ['space' => [80, 81, 82, 83, 84, 85, 86, 87]],
                'terms' => [
                    'visita-guiada' => \MapasCulturais\i::__('Visita guiada'),
                    'loja-souvenir' => \MapasCulturais\i::__('Loja / souvenir'),
                    'alimentacao-no-local' => \MapasCulturais\i::__('Alimentação no local'),
                    'estacionamento' => \MapasCulturais\i::__('Estacionamento'),
                    'acessivel-pcd' => \MapasCulturais\i::__('Acessível PCD'),
                    'banheiro' => \MapasCulturais\i::__('Banheiro público'),
                    'area-para-fotos' => \MapasCulturais\i::__('Área para fotos'),
                ]
            ];

            $config['turismo_publico_alvo'] = [
                'required' => false,
                'entities' => ['space'],
                'restrictToTypes' => ['space' => [80, 81, 82, 83, 84, 85, 86, 87]],
                'terms' => [
                    'familias' => \MapasCulturais\i::__('Famílias'),
                    'escolar' => \MapasCulturais\i::__('Escolares'),
                    'melhor-idade' => \MapasCulturais\i::__('Melhor idade'),
                    'jovem-aventura' => \MapasCulturais\i::__('Jovens / aventura'),
                    'turista-internacional' => \MapasCulturais\i::__('Turista internacional'),
                ]
            ];

            $config['turismo_acessibilidade'] = [
                'required' => false,
                'entities' => ['space'],
                'restrictToTypes' => ['space' => [80, 81, 82, 83, 84, 85, 86, 87]],
                'terms' => [
                    'acesso-cadeira-de-rodas' => \MapasCulturais\i::__('Acesso para cadeira de rodas'),
                    'audiodescricao' => \MapasCulturais\i::__('Audiodescrição'),
                    'material-bilingue' => \MapasCulturais\i::__('Material bilíngue'),
                    'piso-tatil' => \MapasCulturais\i::__('Piso tátil'),
                ]
            ];

            // Taxonomias para Projetos (Roteiros)
            $config['turismo_categoria_roteiro'] = [
                'required' => true,
                'entities' => ['project'],
                'restrictToTypes' => ['project' => [80, 81, 82, 83, 84, 85, 86]],
                'terms' => [
                    'roteiro-cultural' => \MapasCulturais\i::__('Roteiro Cultural'),
                    'roteiro-ecologico' => \MapasCulturais\i::__('Roteiro Ecológico'),
                    'roteiro-gastronomico' => \MapasCulturais\i::__('Roteiro Gastronômico'),
                    'roteiro-religioso' => \MapasCulturais\i::__('Roteiro Religioso'),
                    'roteiro-aventura' => \MapasCulturais\i::__('Roteiro de Aventura'),
                    'roteiro-rural' => \MapasCulturais\i::__('Roteiro Rural'),
                    'roteiro-nautico' => \MapasCulturais\i::__('Roteiro Náutico'),
                ]
            ];

            // Taxonomias para Eventos
            $config['turismo_tipo_evento'] = [
                'required' => false,
                'entities' => ['event'],
                'terms' => [
                    'festival' => \MapasCulturais\i::__('Festival'),
                    'feira-mostra' => \MapasCulturais\i::__('Feira / Mostra'),
                    'evento-esportivo' => \MapasCulturais\i::__('Evento esportivo'),
                    'cavalgada' => \MapasCulturais\i::__('Cavalgada / Cultural'),
                    'religioso' => \MapasCulturais\i::__('Religioso / Romaria'),
                    'gastronomico-evento' => \MapasCulturais\i::__('Gastronômico'),
                ]
            ];
        });

        // Registrar campos personalizados
        $app->hook('entity(Space).meta', function(&$metadata) {
            $metadata['turismo_horario_funcionamento'] = [
                'label' => \MapasCulturais\i::__('Horário de funcionamento'),
                'type' => 'text',
                'serialize' => function($value) { return $value; },
                'unserialize' => function($value) { return $value; }
            ];

            $metadata['turismo_valor_entrada'] = [
                'label' => \MapasCulturais\i::__('Valor de entrada'),
                'type' => 'text',
                'serialize' => function($value) { return $value; },
                'unserialize' => function($value) { return $value; }
            ];

            $metadata['turismo_contato'] = [
                'label' => \MapasCulturais\i::__('Contato'),
                'type' => 'text',
                'serialize' => function($value) { return $value; },
                'unserialize' => function($value) { return $value; }
            ];

            $metadata['turismo_observacoes'] = [
                'label' => \MapasCulturais\i::__('Observações'),
                'type' => 'longtext',
                'serialize' => function($value) { return $value; },
                'unserialize' => function($value) { return $value; }
            ];
        });

        $app->hook('entity(Project).meta', function(&$metadata) {
            $metadata['turismo_duracao_estimada'] = [
                'label' => \MapasCulturais\i::__('Duração estimada'),
                'type' => 'text',
                'serialize' => function($value) { return $value; },
                'unserialize' => function($value) { return $value; }
            ];

            $metadata['turismo_ponto_partida'] = [
                'label' => \MapasCulturais\i::__('Ponto de partida'),
                'type' => 'text',
                'serialize' => function($value) { return $value; },
                'unserialize' => function($value) { return $value; }
            ];

            $metadata['turismo_ponto_chegada'] = [
                'label' => \MapasCulturais\i::__('Ponto de chegada'),
                'type' => 'text',
                'serialize' => function($value) { return $value; },
                'unserialize' => function($value) { return $value; }
            ];

            $metadata['turismo_melhor_epoca'] = [
                'label' => \MapasCulturais\i::__('Melhor época de visita'),
                'type' => 'text',
                'serialize' => function($value) { return $value; },
                'unserialize' => function($value) { return $value; }
            ];
        });

        // Ícone personalizado para o tema turismo
        $app->hook("component(mc-icon).iconset", function(&$icon){
            $icon['space'] = "material-symbols:location-on";
            $icon['project'] = "material-symbols:route";
            $icon['event'] = "material-symbols:event";
        });
    }

    public function spaceRequiredProperties($requiredFields)
    {
        $this->jsObject['entity']['requiredProperties']['space'] = $requiredFields;
    }

    public function projectRequiredProperties($requiredFields)
    {
        $this->jsObject['entity']['requiredProperties']['project'] = $requiredFields;
    }
}