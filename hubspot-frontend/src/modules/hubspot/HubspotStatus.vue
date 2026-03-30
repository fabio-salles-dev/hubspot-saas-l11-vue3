<script setup>
defineProps({
  connected: Boolean,
  account: Object,
  overview: Object,
  platformName: String
})

defineEmits(['connect', 'disconnect'])
</script>

<template>
  <div class="status-card">
    <!-- NÃO CONECTADO -->
    <div v-if="!connected" class="status-center">
      <div class="badge warning">⚠️ HubSpot não conectado</div>

      <button class="btn primary" @click="$emit('connect')">
        🔐 Conectar HubSpot
      </button>
    </div>

    <!-- CONECTADO -->
    <div v-else>
      <div class="status-header">
        <div class="badge success">✅ HubSpot conectado</div>

        <button class="btn danger small" @click="$emit('disconnect')">
          🔌 Desconectar
        </button>
      </div>

<<<<<<< HEAD
      <div class="account-box">
        <div>
          <span>Conta</span>
          <strong>{{ overview?.company_name ?? platformName }}</strong>
        </div>

        <div>
          <span>Portal ID</span>
          <strong>{{ account.portal_id }}</strong>
        </div>

        <div>
          <span>Região</span>
          <strong>{{ overview?.region ?? '—' }}</strong>
        </div>

        <div>
          <span>Timezone</span>
          <strong>{{ overview?.timezone ?? '—' }}</strong>
        </div>
      </div>
    </div>
  </div>
=======
  <div class="account-box">
  <div>
    <span>Conta</span>git remote 
    <strong>{{ account.company_name}}</strong>
  </div>

  <div>
    <span>Portal ID</span>
    <strong>{{ account.portal_id }}</strong>
  </div>

  <div>
    <span>Região</span>
    <!-- O campo da API oficial se chama dataCenterRegion -->
    <strong>{{ account?.region || overview?.region || '—' }}</strong>
  </div>

  <div>
    <span>Timezone</span>
    <strong>{{ account?.timezone || overview?.timeZone || '—' }}</strong>
  </div>
</div>

    </div>
  </div>
  <pre>{{ account }}</pre>

>>>>>>> c5786fb (feat - calling real datas from client)
</template>

<style scoped>
.status-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
  margin-bottom: 24px;
}

/* NÃO CONECTADO */
.status-center {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

/* HEADER CONECTADO */
.status-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

/* DADOS DA CONTA */
.account-box {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  background: #f8fafc;
  padding: 16px;
  border-radius: 12px;
}

.account-box div {
  display: flex;
  flex-direction: column;
  font-size: 14px;
}

.account-box span {
  color: #64748b;
  font-size: 12px;
}

.account-box strong {
  color: #0f172a;
  font-weight: 600;
}

/* BOTÕES */
.btn {
  padding: 12px 16px;
  border-radius: 10px;
  font-weight: 600;
  border: none;
  cursor: pointer;
}

.btn.small {
  padding: 8px 12px;
  font-size: 13px;
}

.btn.primary {
  background: #ff7a59;
  color: white;
}

.btn.primary:hover {
  background: #ff5c35;
}

.btn.danger {
  background: #e53935;
  color: white;
}

.btn.danger:hover {
  background: #b71c1c;
}

/* BADGES */
.badge {
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 600;
}

.badge.warning {
  background: #fff7ed;
  color: #c2410c;
}

.badge.success {
  background: #ecfdf5;
  color: #047857;
}

/* MOBILE */
@media (max-width: 768px) {
  .account-box {
    grid-template-columns: 1fr;
  }

  .status-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
}
</style>
