# 🗺️ Tema Turismo para Mapas Culturais

## Visão Geral

O **tema Turismo** adapta o Mapas Culturais para uso por Secretarias Municipais e Estaduais de Cultura e Turismo, permitindo o mapeamento de atrativos turísticos, roteiros e eventos de forma integrada.

## Características

- **Base**: Estende o tema BaseV2
- **Entidades**: Utiliza as entidades existentes (spaces, projects, events) com nomenclaturas e taxonomias específicas do turismo
- **Tipos**: Define novos tipos específicos para turismo (IDs 80-89)
- **Idiomas**: Suporte completo em Português e Inglês

## Estrutura de Entidades

### Atrativos (Spaces)
- **Tipos**: Natural, Cultural/Histórico, Religioso, Gastronômico, Rural, Esporte/Aventura, CAT, Equipamento de Apoio
- **Campos específicos**: Horário de funcionamento, Valor de entrada, Contato, Observações
- **Taxonomias**: Tipo de atrativo, Segmento turístico, Serviços disponíveis, Público-alvo, Acessibilidade

### Roteiros (Projects)  
- **Tipos**: Cultural, Ecológico, Gastronômico, Religioso, Aventura, Rural, Náutico
- **Campos específicos**: Duração estimada, Ponto de partida, Ponto de chegada, Melhor época
- **Taxonomias**: Categoria do roteiro

### Eventos (Events)
- **Taxonomias**: Tipo de evento turístico (Festival, Feira, Esportivo, etc.)

## Configuração

### Ativação do Tema

1. Copie a pasta do tema para `src/themes/Turismo/`
2. Configure no arquivo de configuração principal:

```php
'themes.active' => 'Turismo',
```

### Tipos de Entidade

O tema utiliza os seguintes ranges de IDs:

- **Spaces (Atrativos)**: 80-89
- **Projects (Roteiros)**: 80-89  
- **Events**: Sem restrição de tipo

### Campos Obrigatórios

#### Para Atrativos (Spaces):
- Endereço completo
- Horário de funcionamento  
- Contato
- Tipo de atrativo
- Segmento turístico

#### Para Roteiros (Projects):
- Duração estimada
- Ponto de partida
- Categoria do roteiro

## Taxonomias Disponíveis

### Tipos de Atrativo
- Atrativo natural
- Atrativo cultural/histórico
- Religioso / de fé
- Gastronômico
- Rural / de vivência
- Esporte e aventura
- Centro de atendimento ao turista (CAT)
- Equipamento de apoio

### Segmentos Turísticos
- Turismo cultural
- Ecoturismo
- Turismo de experiência
- Turismo religioso
- Turismo gastronômico
- Turismo de eventos
- Turismo náutico
- Turismo rural

### Serviços Disponíveis
- Visita guiada
- Loja / souvenir
- Alimentação no local
- Estacionamento
- Acessível PCD
- Banheiro público
- Área para fotos

### Público-alvo
- Famílias
- Escolares
- Melhor idade
- Jovens / aventura
- Turista internacional

### Acessibilidade
- Acesso para cadeira de rodas
- Audiodescrição
- Material bilíngue
- Piso tátil

## Relacionamentos

- **Roteiros → Atrativos**: Projetos podem incluir múltiplos espaços
- **Roteiros → Eventos**: Projetos podem incluir eventos
- **Eventos → Espaços**: Eventos acontecem em espaços

## Filtros de Busca

As listagens incluem filtros por:
- Tipo de atrativo/roteiro
- Segmento turístico
- Localização (município/estado)
- Serviços disponíveis
- Acessibilidade

## Desenvolvimento

### Estrutura de Arquivos
```
Turismo/
├── Theme.php              # Classe principal do tema
├── conf-base.php         # Configurações base
├── translations/         # Traduções
│   ├── pt_BR.php
│   ├── en_US.php
│   └── replacements
├── layouts/              # Templates de layout
├── views/               # Views específicas
└── assets/              # CSS, JS, imagens
```

### Hooks Utilizados

- `mapas.config(space-types)`: Registra tipos de atrativos
- `mapas.config(project-types)`: Registra tipos de roteiros  
- `mapas.config(taxonomies)`: Registra taxonomias específicas
- `entity(Space).meta`: Registra campos personalizados para espaços
- `entity(Project).meta`: Registra campos personalizados para projetos
- `ApiQuery(Space).where`: Filtra apenas atrativos turísticos
- `ApiQuery(Project).where`: Filtra apenas roteiros turísticos

## Licença

GPL-3.0 - Mesma licença do Mapas Culturais

## Suporte

Para dúvidas e suporte, consulte a documentação do Mapas Culturais ou entre em contato com a equipe de desenvolvimento.