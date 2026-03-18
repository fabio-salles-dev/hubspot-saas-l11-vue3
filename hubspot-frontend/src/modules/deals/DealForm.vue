<script setup>
import { ref, watch } from 'vue'
import api from '../../services/api'

const props = defineProps({ deal: Object })
const emit = defineEmits(['close', 'saved'])

const form = ref({
  title: '',
  value: '',
  status: 'open',
  client_id: ''
})

watch(
  () => props.deal,
  d => {
    if (d) form.value = { ...d }
  },
  { immediate: true }
)

const save = async () => {
  if (form.value.id) {
    await api.put(`/deals/${form.value.id}`, form.value)
  } else {
    await api.post('/deals', form.value)
  }
  emit('saved')
}
</script>

<template>
  <div class="modal">
    <h2>{{ form.id ? 'Editar' : 'Novo' }} Negócio</h2>

    <input v-model="form.title" placeholder="Título" />
    <input v-model="form.value" placeholder="Valor" />
    <select v-model="form.status">
      <option value="open">Aberto</option>
      <option value="won">Ganho</option>
      <option value="lost">Perdido</option>
    </select>

    <div class="actions">
      <button class="btn primary" @click="save">Salvar</button>
      <button class="btn" @click="emit('close')">Cancelar</button>
    </div>
  </div>
</template>
