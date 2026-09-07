<script setup>
import {
  ref,
  onMounted
} from "vue";

import {
  getDeals,
  deleteDeal
} from "../modules/deals/deals.service";

import DealsTable from "../modules/deals/DealsTable.vue";
import DealForm from "../modules/deals/DealForm.vue";

// ============================================================
// STATE
// ============================================================

const deals = ref([]);
const loading = ref(false);
const error = ref(null);

const showForm = ref(false);
const selectedDeal = ref(null);

// ============================================================
// LOAD DEALS
// ============================================================

const loadDeals = async () => {
  if (loading.value) {
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    const { data } = await getDeals();

    deals.value =
      Array.isArray(data?.data)
        ? data.data
        : Array.isArray(data)
          ? data
          : [];

  } catch (e) {
    console.error(
      "Erro ao carregar negócios:",
      e
    );

    deals.value = [];

    error.value =
      "Não foi possível carregar os negócios.";

  } finally {
    loading.value = false;
  }
};

// ============================================================
// FORM
// ============================================================

const openForm = (deal = null) => {
  selectedDeal.value =
    deal ? { ...deal } : null;

  showForm.value = true;
};

const closeForm = () => {
  showForm.value = false;
  selectedDeal.value = null;
};

// ============================================================
// CREATE
// ============================================================

const createDeal = () => {
  openForm();
};

// ============================================================
// EDIT
// ============================================================

const editDeal = (deal) => {
  if (!deal) {
    return;
  }

  openForm(deal);
};

// ============================================================
// DELETE
// ============================================================

const removeDeal = async (deal) => {
  if (!deal?.id) {
    return;
  }

  const confirmed =
    window.confirm(
      `Remover o negócio "${deal.title ?? "Sem título"}"?`
    );

  if (!confirmed) {
    return;
  }

  error.value = null;

  try {
    await deleteDeal(deal.id);

    await loadDeals();

  } catch (e) {
    console.error(
      "Erro ao remover negócio:",
      e
    );

    error.value =
      "Não foi possível remover o negócio.";
  }
};

// ============================================================
// SAVED
// ============================================================

const handleSaved = async () => {
  closeForm();

  await loadDeals();
};

// ============================================================
// INIT
// ============================================================

onMounted(async () => {
  await loadDeals();
});
</script>

<template>
  <div class="page">

    <!-- ================================================== -->
    <!-- HEADER -->
    <!-- ================================================== -->

    <div class="page-header">

      <div>
        <h1>
          💼 Negócios
        </h1>

        <p>
          Gerencie os negócios cadastrados.
        </p>
      </div>

      <button
        type="button"
        class="btn primary"
        @click="createDeal"
      >
        + Novo negócio
      </button>

    </div>

    <!-- ================================================== -->
    <!-- ERROR -->
    <!-- ================================================== -->

    <div
      v-if="error"
      class="error-message"
    >
      {{ error }}
    </div>

    <!-- ================================================== -->
    <!-- LOADING -->
    <!-- ================================================== -->

    <div
      v-if="loading"
      class="loading"
    >
      Carregando negócios...
    </div>

    <!-- ================================================== -->
    <!-- TABLE -->
    <!-- ================================================== -->

    <DealsTable
      v-else
      :deals="deals"
      @edit="editDeal"
      @delete="removeDeal"
    />

    <!-- ================================================== -->
    <!-- FORM -->
    <!-- ================================================== -->

    <DealForm
      v-if="showForm"
      :deal="selectedDeal"
      @close="closeForm"
      @saved="handleSaved"
    />

  </div>
</template>

<style scoped>
.page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* =====================================================
   HEADER
===================================================== */

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.page-header h1 {
  margin: 0;
  color: #1e293b;
}

.page-header p {
  margin: 6px 0 0;
  color: #64748b;
}

/* =====================================================
   BUTTON
===================================================== */

.btn {
  padding: 10px 16px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;

  transition:
    background 0.2s ease,
    transform 0.2s ease;
}

.btn:hover {
  transform: translateY(-1px);
}

.btn.primary {
  background: #2563eb;
  color: #ffffff;
}

.btn.primary:hover {
  background: #1d4ed8;
}

/* =====================================================
   LOADING
===================================================== */

.loading {
  padding: 30px;
  text-align: center;
  color: #64748b;
}

/* =====================================================
   ERROR
===================================================== */

.error-message {
  padding: 12px 16px;

  border-radius: 10px;

  background: #fef2f2;

  color: #b91c1c;
}

/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>