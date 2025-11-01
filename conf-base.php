<?php

return [
    'app.enabled.agents'        => true,
    'app.enabled.projects'      => true,
    'app.enabled.events'        => true,
    'app.enabled.apps'          => false,
    'app.enabled.seals'         => true,
    'app.enabled.opportunities' => false,
    
    // Configurações específicas do tema Turismo
    'turismo.ownerAgentId'      => null, // Definir conforme necessário
    
    // Notificações
    'notifications.entities.update' => 0,
    'notifications.user.access'     => 0,
    'notifications.seal.toExpire'   => 0,
    
    // Configurações de mapa
    'maps.includeGoogleLayers'  => true,
    'maps.googleApiKey'         => null, // Definir conforme necessário
    
    // Labels personalizados para o tema
    'app.siteName'              => 'Mapa do Turismo',
    'app.siteDescription'       => 'Portal de atrativos, roteiros e eventos turísticos',
    
    // Tipos de entidades específicos do turismo
    'turismo.space.types' => [
        80 => 'Atrativo Natural',
        81 => 'Atrativo Cultural/Histórico', 
        82 => 'Atrativo Religioso',
        83 => 'Atrativo Gastronômico',
        84 => 'Atrativo Rural',
        85 => 'Esporte e Aventura',
        86 => 'Centro de Atendimento ao Turista',
        87 => 'Equipamento de Apoio'
    ],
    
    'turismo.project.types' => [
        80 => 'Roteiro Cultural',
        81 => 'Roteiro Ecológico',
        82 => 'Roteiro Gastronômico', 
        83 => 'Roteiro Religioso',
        84 => 'Roteiro de Aventura',
        85 => 'Roteiro Rural',
        86 => 'Roteiro Náutico'
    ],
    
    // Configurações de busca
    'search.spaces.verified'    => false,
    'search.agents.verified'    => false,
    'search.events.verified'    => false,
    'search.projects.verified'  => false,
];