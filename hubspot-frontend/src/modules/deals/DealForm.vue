<script setup>
import { ref, watch } from "vue";

import {
  createDeal,
  updateDeal
} from "./deals.service";

const props = defineProps({
  deal: {
    type: Object,
    default: null
  }
});

const emit = defineEmits([
  "close",
  "saved"
]);

const emptyForm = () => ({
  title: "",
  value: "",
  status: "open",
  client_id: ""
});

const form = ref(
  emptyForm()
);

const saving = ref(false);
const error = ref(null);

watch(
  () => props.deal,
  (deal) => {
    form.value = deal
      ? { ...emptyForm(), ...deal }
      : emptyForm();
  },
  {
    immediate: true
  }
);

const save = async () => {
  if (!form.value.title?.trim()) {
    error.value =
      "Informe o título do negócio.";

    return;
  }

  saving.value = true;
  error.value = null;

  try {
    const payload = {
      title:
        form.value.title.trim(),

      value:
        Number(form.value.value) || 0,

      status:
        form.value.status,

      client_id:
        form.value.client_id || null
    };

    if (form.value.id) {
      await updateDeal(
        form.value.id,
        payload
      );
    } else {
      await createDeal(
        payload
      );
    }

    emit("saved");

  } catch (e) {
    console.error(
      "Erro ao salvar negócio:",
      e
    );

    error.value =
      "Não foi possível salvar o negócio.";

  } finally {
    saving.value = false;
  }
};
</script>

<template>
  <div class="modal">
    <h2>
      {{ form.id ? "Editar" : "Novo" }}
      Negócio
    </h2>

    <p
      v-if="error"
      class="error"
    >
      {{ error }}
    </p>

    <input
      v-model="form.title"
      placeholder="Título"
    />

    <input
      v-model="form.value"
      type="number"
      min="0"
      step="0.01"
      placeholder="Valor"
    />

    <select v-model="form.status">
      <option value="open">
        Aberto
      </option>

      <option value="won">
        Ganho
      </option>

      <option value="lost">
        Perdido
      </option>
    </select>

    <div class="actions">
      <button
        class="btn primary"
        :disabled="saving"
        @click="save"
      >
        {{ saving ? "Salvando..." : "Salvar" }}
      </button>

      <button
        class="btn"
        :disabled="saving"
        @click="emit('close')"
      >
        Cancelar
      </button>
    </div>
  </div>
</template>