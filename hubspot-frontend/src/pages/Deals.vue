<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

import DealsTable from '../modules/deals/DealsTable.vue'
import DealsFilters from '../modules/deals/DealsFilters.vue'
import DealForm from '../modules/deals/DealForm.vue'

const deals = ref([])
const pagination = ref({})
const loading = ref(false)
const filters = ref({ status: '' })

const showForm = ref(false)
const selectedDeal = ref(null)

const loadDeals = async (page = 1) => {
    loading.value = true
    const { data } = await api.get('/hubspot/deals', {
        params: { ...filters.value, page }
    })

    deals.value = data.data
    pagination.value = data
    loading.value = false
}

const createDeal = () => {
    selectedDeal.value = null
    showForm.value = true
}

const editDeal = deal => {
    selectedDeal.value = deal
    showForm.value = true
}

const deleteDeal = async deal => {
    if (!confirm('Remover este negócio?')) return
    await api.delete(`/deals/${deal.id}`)
    loadDeals()
}

onMounted(loadDeals)
</script>

<template>
    <div class="page">
        <h1>💼 Negócios</h1>

        <DealsFilters @filter="filters = $event; loadDeals()" @create="createDeal" />

        <DealsTable :deals="deals" :loading="loading" @edit="editDeal" @delete="deleteDeal" />

        <DealForm v-if="showForm" :deal="selectedDeal" @close="showForm = false"
            @saved="showForm = false; loadDeals()" />
    </div>
</template>
