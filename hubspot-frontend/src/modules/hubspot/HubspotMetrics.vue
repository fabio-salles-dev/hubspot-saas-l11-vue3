<script setup>
defineProps({
  contacts: Number,
  companies: Number,
  deals: Number
})

const getGrowth = (key) => {
  if (!props.history || props.history.length < 2) return 0;

  const last = props.history[props.history.length - 1][key];
  const prev = props.history[props.history.length - 2][key];

  if (!prev) return 0;

  return ((last - prev) / prev * 100).toFixed(1);
};
</script>

<template>
  <div class="header" style="margin-bottom: 20px; text-align: center;">
    <h2>📊 Métricas do HubSpot</h2>
    <p>Visão geral dos seus contatos, empresas e negócios.</p>
  </div>
  <div class="metrics">
    <div class="metric-card">
      👥 <strong>{{ contacts }}</strong>
      <small>Contatos</small>
    </div>

    <div class="metric-card">
      🏢 <strong>{{ companies }}</strong>
      <small>Empresas</small>
    </div>

    <div class="metric-card">
      💼 <strong>{{ deals }}</strong>
      <small>Negócios</small>
    </div>
  </div>
  
</template>
