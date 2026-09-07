<script setup>
defineProps({
  contacts: {
    type: Number,
    default: 0
  },

  companies: {
    type: Number,
    default: 0
  },

  deals: {
    type: Number,
    default: 0
  }
});
</script>

<template>
  <div class="metrics-header">
    <h2>📊 Métricas do HubSpot</h2>
    <p>
      Visão geral dos seus contatos, empresas e negócios.
    </p>
  </div>

  <div class="metrics">
    <div class="metric-card">
      <span class="metric-icon">👥</span>

      <strong>
        {{ contacts }}
      </strong>

      <small>
        Contatos
      </small>
    </div>

    <div class="metric-card">
      <span class="metric-icon">🏢</span>

      <strong>
        {{ companies }}
      </strong>

      <small>
        Empresas
      </small>
    </div>

    <div class="metric-card">
      <span class="metric-icon">💼</span>

      <strong>
        {{ deals }}
      </strong>

      <small>
        Negócios
      </small>
    </div>
  </div>
</template>

<style scoped>
.metrics-header {
  margin-bottom: 20px;
  text-align: center;
}

.metrics-header h2 {
  margin: 0 0 6px;
  color: #333;
}

.metrics-header p {
  margin: 0;
  color: #64748b;
}

.metrics {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

.metric-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;

  min-height: 120px;
  padding: 20px;

  background: #ffffff;
  border-radius: 12px;

  box-shadow:
    0 2px 6px
    rgba(0, 0, 0, 0.05);
}

.metric-icon {
  font-size: 26px;
  margin-bottom: 8px;
}

.metric-card strong {
  font-size: 28px;
  color: #0f172a;
}

.metric-card small {
  margin-top: 4px;
  color: #64748b;
  font-size: 13px;
}

@media (max-width: 768px) {
  .metrics {
    grid-template-columns: 1fr;
  }
}
</style>