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

        // Desabilitar oportunidades no tema Turismo
        $app->config['app.enabled.opportunities'] = false;

        // Registrar tipos de espaços turísticos PRIMEIRO
        $app->hook('mapas.config(space-types)', function(&$config) {
            // Limpar tipos existentes e adicionar apenas os turísticos
            $config = [];
            
            // Usar as configurações do conf-base.php
            $turismo_space_types = [
                80 => 'Atrativo Natural',
                81 => 'Atrativo Cultural/Histórico',
                82 => 'Atrativo Religioso',
                83 => 'Atrativo Gastronômico',
                84 => 'Atrativo Rural',
                85 => 'Esporte e Aventura',
                86 => 'Centro de Atendimento ao Turista',
                87 => 'Equipamento de Apoio'
            ];
            
            $items = [];
            foreach($turismo_space_types as $id => $name) {
                $items[$id] = ['name' => \MapasCulturais\i::__($name)];
            }
            
            $config[\MapasCulturais\i::__('Atrativos')] = [
                'range' => [80, 89],
                'items' => $items
            ];
        });

        // Registrar tipos de projetos turísticos PRIMEIRO
        $app->hook('mapas.config(project-types)', function(&$config) {
            // Limpar tipos existentes e adicionar apenas os turísticos
            $config = [];
            
            // Usar as configurações do conf-base.php
            $turismo_project_types = [
                80 => 'Roteiro Cultural',
                81 => 'Roteiro Ecológico',
                82 => 'Roteiro Gastronômico',
                83 => 'Roteiro Religioso',
                84 => 'Roteiro de Aventura',
                85 => 'Roteiro Rural',
                86 => 'Roteiro Náutico'
            ];
            
            $items = [];
            foreach($turismo_project_types as $id => $name) {
                $items[$id] = ['name' => \MapasCulturais\i::__($name)];
            }
            
            $config[\MapasCulturais\i::__('Roteiros')] = [
                'range' => [80, 89],
                'items' => $items
            ];
        });

        // Campos obrigatórios para Atrativos Turísticos (Spaces)
        $requiredSpaceFields = [
            'endereco',
            'En_CEP',
            'En_Nome_Logradouro',
            'En_Num',
            'En_Bairro',
            'En_Municipio', 
            'En_Estado',
            'turismo_horario_funcionamento'
        ];

        // Campos obrigatórios para Roteiros Turísticos (Projects)
        $requiredProjectFields = [
            'turismo_duracao_estimada',
            'turismo_ponto_partida'
        ];

        /*
         * Filtros de API para mostrar apenas tipos turísticos são tratados via hooks ApiQuery.params
         * Os hooks where abaixo foram substituídos pelos hooks params para melhor compatibilidade
         */

        // Ajusta pseudo query para identificar a tela de busca de atrativos
        $app->hook('search-spaces-initial-pseudo-query', function(&$initial_pseudo_query){
            $initial_pseudo_query['@turismo'] = 1;
        });

        // Ajusta pseudo query para identificar a tela de busca de roteiros
        $app->hook('search-projects-initial-pseudo-query', function(&$initial_pseudo_query){
            $initial_pseudo_query['@turismo'] = 1;
        });

        // Evita que seja carregado outros tipos na tela de busca de atrativos
        $app->hook('ApiQuery(Space).params', function(&$params){
            if(($params['@turismo'] ?? false) && empty($params['type'])) {
                $params['type'] = "BET(80,89)";
                unset($params['@turismo']);
            }
        });

        // Evita que seja carregado outros tipos na tela de busca de roteiros
        $app->hook('ApiQuery(Project).params', function(&$params){
            if(($params['@turismo'] ?? false) && empty($params['type'])) {
                $params['type'] = "BET(80,89)";
                unset($params['@turismo']);
            }
        });

        // Desabilitar oportunidades nas consultas da API
        $app->hook('ApiQuery(Opportunity).where', function(&$where) use ($app) {
            $where .= " AND e.id IS NULL"; // Nunca retorna oportunidades
        });

        // Remove tipos não turísticos da interface
        $app->hook('mapas.printJsObject:before', function() use ($app) {
            // Desabilitar oportunidades na interface
            if(isset($this->jsObject['enabledEntities']['opportunities'])) {
                $this->jsObject['enabledEntities']['opportunities'] = false;
            }
            if(isset($this->jsObject['global']['enabledEntities']['opportunities'])) {
                $this->jsObject['global']['enabledEntities']['opportunities'] = false;
            }
            
            // Remove o componente home-opportunities da página inicial
            if(isset($this->jsObject['home']['opportunities'])) {
                unset($this->jsObject['home']['opportunities']);
            }
            
            // Alterar labels da taxonomia "area" para "Segmento Turístico"
            if(isset($this->jsObject['taxonomyTerms']['area'])) {
                $this->jsObject['taxonomyTerms']['area']['name'] = 'Segmento Turístico';
            }
            
            // Remover oportunidades completamente da interface
            if(isset($this->jsObject['EntitiesDescription']['opportunity'])) {
                unset($this->jsObject['EntitiesDescription']['opportunity']);
            }
            
            // Filtrar apenas tipos turísticos para espaços (80-87)
            if(isset($this->jsObject['EntitiesDescription']['space']['type']['options'])) {
                $space_options = $this->jsObject['EntitiesDescription']['space']['type']['options'];
                $new_space_options = [];
                $new_space_order = [];
                
                for($i = 80; $i <= 87; $i++) {
                    if(isset($space_options[$i])) {
                        $new_space_options[$i] = $space_options[$i];
                        $new_space_order[] = $i;
                    }
                }
                
                $this->jsObject['EntitiesDescription']['space']['type']['options'] = $new_space_options;
                $this->jsObject['EntitiesDescription']['space']['type']['optionsOrder'] = $new_space_order;
            }
            
            // Filtrar apenas tipos turísticos para projetos (80-86)
            if(isset($this->jsObject['EntitiesDescription']['project']['type']['options'])) {
                $project_options = $this->jsObject['EntitiesDescription']['project']['type']['options'];
                $new_project_options = [];
                $new_project_order = [];
                
                for($i = 80; $i <= 86; $i++) {
                    if(isset($project_options[$i])) {
                        $new_project_options[$i] = $project_options[$i];
                        $new_project_order[] = $i;
                    }
                }
                
                $this->jsObject['EntitiesDescription']['project']['type']['options'] = $new_project_options;
                $this->jsObject['EntitiesDescription']['project']['type']['optionsOrder'] = $new_project_order;
            }

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
    }

    function register() {
        parent::register();
        $app = App::i();

        // Registrar metadados com validações melhoradas
        $metadata = [
            'MapasCulturais\Entities\Space' => [
                'turismo_horario_funcionamento' => [
                    'label' => 'Horário de funcionamento',
                    'type' => 'text',
                    'placeholder' => 'Ex: Segunda a sexta das 8h às 17h, Sábados das 9h às 16h',
                    'serialize' => function($value) { return $value; },
                    'unserialize' => function($value) { return $value; }
                ],
                'turismo_valor_entrada' => [
                    'label' => 'Valor de entrada',
                    'type' => 'text', 
                    'placeholder' => 'Ex: R$ 10,00 (inteira) / R$ 5,00 (meia) ou Gratuito',
                    'serialize' => function($value) { return $value; },
                    'unserialize' => function($value) { return $value; }
                ]
            ],
            'MapasCulturais\Entities\Project' => [
                'turismo_duracao_estimada' => [
                    'label' => 'Duração estimada',
                    'type' => 'text',
                    'placeholder' => 'Ex: 2 horas, 1 dia, 3 dias/2 noites',
                    'serialize' => function($value) { return $value; },
                    'unserialize' => function($value) { return $value; }
                ],
                'turismo_nivel_dificuldade' => [
                    'label' => 'Nível de dificuldade',
                    'type' => 'select',
                    'options' => [
                        'facil' => 'Fácil',
                        'moderado' => 'Moderado',
                        'dificil' => 'Difícil',
                    ],
                    'serialize' => function($value) { return $value; },
                    'unserialize' => function($value) { return $value; }
                ],
                'turismo_ponto_partida' => [
                    'label' => 'Ponto de partida',
                    'type' => 'text',
                    'placeholder' => 'Ex: Centro de Atendimento ao Turista, Praça Central',
                    'serialize' => function($value) { return $value; },
                    'unserialize' => function($value) { return $value; }
                ],
                'turismo_ponto_chegada' => [
                    'label' => 'Ponto de chegada',
                    'type' => 'text',
                    'placeholder' => 'Ex: Mirante do Morro, Museu da Cidade',
                    'serialize' => function($value) { return $value; },
                    'unserialize' => function($value) { return $value; }
                ]
            ]
        ];

        // Registrar metadados
        foreach($metadata as $entity_class => $metas){
            foreach($metas as $key => $cfg){
                $def = new \MapasCulturais\Definitions\Metadata($key, $cfg);
                $app->registerMetadata($def, $entity_class);
            }
        }

        // Registrar taxonomias turísticas
        $this->registerTourismTaxonomies();
    }

    private function registerTourismTaxonomies() {
        $app = App::i();

        // Substituir a taxonomia "área de atuação" pelos segmentos turísticos
        $segmentos_turisticos = [
            'Turismo Cultural',
            'Ecoturismo', 
            'Turismo de Aventura',
            'Turismo Rural',
            'Turismo Gastronômico',
            'Turismo Religioso',
            'Turismo Náutico',
            'Turismo de Eventos',
            'Turismo de Negócios',
            'Turismo de Saúde e Bem-estar',
            'Turismo Pedagógico',
            'Turismo Social',
            'Observação de Fauna',
            'Pesca Esportiva',
            'Mergulho',
            'Trilhas e Caminhadas',
            'Rapel e Escalada',
            'Esportes Aquáticos',
            'Ciclismo',
            'Turismo Fotográfico'
        ];

        // Sobrescrever a taxonomia "area" (ID 1) com os segmentos turísticos
        $taxo_area_turismo = new \MapasCulturais\Definitions\Taxonomy(
            1, 
            'area', 
            'Segmento Turístico', 
            $segmentos_turisticos, 
            false, 
            true
        );
        $app->registerTaxonomy('MapasCulturais\Entities\Space', $taxo_area_turismo);

        // Registrar taxonomia para Serviços Disponíveis  
        $servicos_disponiveis = [
            'Visita Guiada',
            'Loja de Souvenirs',
            'Alimentação no Local',
            'Estacionamento',
            'Acessibilidade',
            'Wi-Fi Gratuito',
            'Sanitários',
            'Área de Descanso',
            'Centro de Informações',
            'Aluguel de Equipamentos'
        ];

        $taxo_servicos = new \MapasCulturais\Definitions\Taxonomy(
            102, 
            'turismo_servicos', 
            'Serviços Disponíveis', 
            $servicos_disponiveis, 
            false, 
            false
        );
        $app->registerTaxonomy('MapasCulturais\Entities\Space', $taxo_servicos);
    }

    /**
     * Retorna os filtros específicos para busca de atrativos e roteiros turísticos
     */
    protected function _getFilters()
    {
        $filters = parent::_getFilters();
        $app = App::i();

        // Filtros para Espaços (Atrativos Turísticos)
        $filters['space'] = [
            [
                'fieldType' => 'text',
                'label' => 'Município',
                'isArray' => false,
                'placeholder' => 'Pesquisar por município',
                'isInline' => false,
                'filter' => [
                    'param' => 'En_Municipio',
                    'value' => 'ILIKE(*{val}*)'
                ]
            ],
            [
                'label' => 'Tipo de Atrativo',
                'placeholder' => 'Selecione os tipos',
                'filter' => [
                    'param' => 'type',
                    'value' => 'IN({val})'
                ]
            ],
            [
                'label' => 'Segmento Turístico',
                'placeholder' => 'Selecione os segmentos',
                'filter' => [
                    'param' => 'area',
                    'value' => 'IN({val})'
                ]
            ],
            [
                'label' => 'Serviços Disponíveis',
                'placeholder' => 'Selecione os serviços',
                'filter' => [
                    'param' => 'turismo_servicos',
                    'value' => 'IN({val})'
                ]
            ],
            [
                'fieldType' => 'text',
                'label' => 'Horário de Funcionamento',
                'isArray' => false,
                'placeholder' => 'Ex: segunda a sexta',
                'isInline' => false,
                'filter' => [
                    'param' => 'turismo_horario_funcionamento',
                    'value' => 'ILIKE(*{val}*)'
                ]
            ]
        ];

        // Filtros para Projetos (Roteiros Turísticos)
        $filters['project'] = [
            [
                'fieldType' => 'text',
                'label' => 'Ponto de Partida',
                'isArray' => false,
                'placeholder' => 'Pesquisar por ponto de partida',
                'isInline' => false,
                'filter' => [
                    'param' => 'turismo_ponto_partida',
                    'value' => 'ILIKE(*{val}*)'
                ]
            ],
            [
                'label' => 'Nível de Dificuldade',
                'placeholder' => 'Selecione o nível',
                'filter' => [
                    'param' => 'turismo_nivel_dificuldade',
                    'value' => 'IN({val})'
                ]
            ],
            [
                'fieldType' => 'text',
                'label' => 'Duração',
                'isArray' => false,
                'placeholder' => 'Ex: 2 horas, 1 dia',
                'isInline' => false,
                'filter' => [
                    'param' => 'turismo_duracao_estimada',
                    'value' => 'ILIKE(*{val}*)'
                ]
            ]
        ];

        return $filters;
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