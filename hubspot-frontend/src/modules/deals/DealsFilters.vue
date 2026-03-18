<script setup>
import { ref } from 'vue'

const emit = defineEmits(['filter', 'create'])
const status = ref('')
</script>

<template>
  <div class="filters">
    <select v-model="status" @change="emit('filter', { status })">
      <option value="">Todos</option>
      <option value="open">Em aberto</option>
      <option value="won">Ganho</option>
      <option value="lost">Perdido</option>
    </select>

    <button class="btn primary" @click="emit('create')">
      ➕ Novo Negócio
    </button>
  </div>
</template>
