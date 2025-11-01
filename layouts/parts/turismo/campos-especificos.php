<?php
use MapasCulturais\i;
?>

<!-- Campos específicos do turismo -->
<mc-card>
    <template #title>
        <label><?= i::__('Horário de funcionamento') ?> <span class="required">obrigatório</span></label>
    </template>
    <template #content>
        <entity-field :entity="entity" classes="col-12" prop="turismo_horario_funcionamento" required></entity-field>
    </template>
</mc-card>

<mc-card>
    <template #title>
        <label><?= i::__('Valor de entrada') ?></label>
    </template>
    <template #content>
        <entity-field :entity="entity" classes="col-12" prop="turismo_valor_entrada"></entity-field>
    </template>
</mc-card>