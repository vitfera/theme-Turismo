<?php
use MapasCulturais\i;
?>

<!-- Script para adicionar indicadores de campos obrigatórios -->
<style>
.field label .required {
    color: #e74c3c;
    font-weight: bold;
    margin-left: 5px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aguardar um pouco para o Vue.js carregar os componentes
    setTimeout(function() {
        // Adicionar indicador para campo nome
        const nomeLabel = document.querySelector('entity-field[prop="name"] label');
        if (nomeLabel && !nomeLabel.querySelector('.required')) {
            nomeLabel.innerHTML += ' <span class="required">obrigatório</span>';
        }
        
        // Adicionar indicador para campo tipo
        const tipoLabel = document.querySelector('entity-field[prop="type"] label');
        if (tipoLabel && !tipoLabel.querySelector('.required')) {
            tipoLabel.innerHTML += ' <span class="required">obrigatório</span>';
        }
    }, 1000);
});
</script>