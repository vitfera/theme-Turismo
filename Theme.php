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
            'turismo_horario_funcionamento',
            'turismo_contato'
        ];

        // Campos obrigatórios para Roteiros Turísticos (Projects)
        $requiredProjectFields = [
            'turismo_duracao_estimada',
            'turismo_ponto_partida'
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

        // Ajusta pseudo query para identificar a tela de busca de roteiros
        $app->hook('search-projects-initial-pseudo-query', function(&$initial_pseudo_query){
            $initial_pseudo_query['@turismo'] = 1;
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

        parent::_init();

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

        // Registrar metadados específicos do turismo para Spaces
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

        // Registrar metadados específicos do turismo para Projects
        $app->hook('entity(Project).meta', function(&$metadata) {
            $metadata['turismo_duracao_estimada'] = [
                'label' => \MapasCulturais\i::__('Duração estimada'),
                'type' => 'text',
                'serialize' => function($value) { return $value; },
                'unserialize' => function($value) { return $value; }
            ];

            $metadata['turismo_nivel_dificuldade'] = [
                'label' => \MapasCulturais\i::__('Nível de dificuldade'),
                'type' => 'select',
                'options' => [
                    'facil' => \MapasCulturais\i::__('Fácil'),
                    'moderado' => \MapasCulturais\i::__('Moderado'),
                    'dificil' => \MapasCulturais\i::__('Difícil'),
                ],
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